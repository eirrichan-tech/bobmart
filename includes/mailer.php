<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/simple_pdf.php';

// Finds PHPMailer whether it's in PHPMailer/src/ or directly in PHPMailer/
function load_phpmailer(): bool {
    $base = __DIR__ . '/../PHPMailer/';
    foreach ([$base . 'src/', $base] as $dir) {
        if (file_exists($dir . 'PHPMailer.php')) {
            require_once $dir . 'Exception.php';
            require_once $dir . 'PHPMailer.php';
            require_once $dir . 'SMTP.php';
            return true;
        }
    }
    return false;
}

// Helpers so it still works if your view names a column slightly differently
function m_name($r) { return $r['item_name'] ?? $r['name'] ?? 'Item'; }
function m_sub($r)  { return $r['subtotal'] ?? ($r['quantity'] * $r['price']); }
function m_total(array $rows) {
    return $rows[0]['total_amount'] ?? array_sum(array_map('m_sub', $rows));
}

// Draws the receipt PDF
function build_receipt_pdf(array $rows): string {
    $f = $rows[0];                           // first row has the order-level info
    $pdf = new SimplePDF();
    $pdf->text(200, 60, 'BOBMART - ORDER RECEIPT', 16, true);
    $pdf->text(50, 100, 'Order #' . $f['order_id'] . '    Status: ' . $f['status'], 11, true);
    $pdf->text(50, 118, 'Customer: ' . $f['customer_name'] . ' (' . $f['customer_email'] . ')');
    $pdf->text(50, 136, 'Date: ' . ($f['created_at'] ?? ''));
    if (!empty($f['shipping_address'])) $pdf->text(50, 154, 'Ship to: ' . $f['shipping_address']);

    $y = 190;
    $pdf->text(50, $y, 'Product', 11, true);
    $pdf->text(300, $y, 'Qty', 11, true);
    $pdf->text(360, $y, 'Price', 11, true);
    $pdf->text(460, $y, 'Subtotal', 11, true);
    $pdf->line(50, $y + 6, 545, $y + 6);
    $y += 26;

    foreach ($rows as $r) {                  // one line per product
        if ($y > 760) { $pdf->newPage(); $y = 60; }
        $pdf->text(50, $y, substr(m_name($r), 0, 42));
        $pdf->text(300, $y, (string)$r['quantity']);
        $pdf->text(360, $y, '$' . number_format($r['price'], 2));
        $pdf->text(460, $y, '$' . number_format(m_sub($r), 2));
        $y += 20;
    }
    $pdf->line(50, $y - 8, 545, $y - 8);
    $pdf->text(360, $y + 12, 'GRAND TOTAL', 12, true);
    $pdf->text(460, $y + 12, '$' . number_format(m_total($rows), 2), 12, true);
    $pdf->text(50, $y + 50, 'Thank you for shopping at Bobmart!', 10);
    return $pdf->output();
}

/* Sends the "order status updated" email.
   $rows = all rows of ONE order from order_details_view.
   Returns true or false. On failure, the reason is in $GLOBALS['mail_error']. */
function send_status_email(array $rows): bool {
    $GLOBALS['mail_error'] = '';
    if (!$rows) { $GLOBALS['mail_error'] = 'No order rows.'; return false; }
    if (!load_phpmailer()) {
        $GLOBALS['mail_error'] = 'PHPMailer folder not found. Copy it into bobmart/PHPMailer/.';
        return false;
    }
    $f = $rows[0];

    // 1) Build the email body: greeting + table of products + grand total
    $html  = "<h2>Hi " . htmlspecialchars($f['customer_name']) . ",</h2>";
    $html .= "<p>Your order <b>#{$f['order_id']}</b> is now <b>" . htmlspecialchars($f['status']) . "</b>.</p>";
    $html .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse'>";
    $html .= "<tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>";
    foreach ($rows as $r) {
        $html .= "<tr><td>" . htmlspecialchars(m_name($r)) . "</td><td>{$r['quantity']}</td>"
               . "<td>$" . number_format($r['price'], 2) . "</td><td>$" . number_format(m_sub($r), 2) . "</td></tr>";
    }
    $html .= "<tr><td colspan='3'><b>Grand Total</b></td><td><b>$" . number_format(m_total($rows), 2) . "</b></td></tr></table>";
    $html .= "<p>Your receipt is attached as a PDF.</p>";

    // 2) Send through PHPMailer to Mailtrap
    try {
        $mail = new PHPMailer(true);          // true = throw errors we can catch
        $mail->isSMTP();
        $mail->Host     = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->Port     = MAIL_PORT;
        $mail->Timeout = 15;                                   // give up after 15 seconds, not 120
        if (MAIL_PORT == 465) $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->setFrom('no-reply@bobmart.com', 'Bobmart');
        $mail->addAddress($f['customer_email'], $f['customer_name']);
        $mail->isHTML(true);
        $mail->Subject = "Bobmart Order #{$f['order_id']} - {$f['status']}";
        $mail->Body    = $html;
        $mail->AltBody = "Your order #{$f['order_id']} is now {$f['status']}. Grand total: $" . number_format(m_total($rows), 2);
        $mail->addStringAttachment(build_receipt_pdf($rows), "receipt_order_{$f['order_id']}.pdf", 'base64', 'application/pdf');
        $mail->send();
        return true;
    } catch (\Throwable $ex) {
        $GLOBALS['mail_error'] = $ex->getMessage();
        return false;
    }
}