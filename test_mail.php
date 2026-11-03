<?php
require_once __DIR__ . '/includes/mailer.php';

$rows = [
    ['order_id' => 999, 'status' => 'Shipped', 'customer_name' => 'Test Customer',
     'customer_email' => 'test@example.com', 'created_at' => date('Y-m-d H:i:s'),
     'total_amount' => 1800.00, 'item_name' => 'Hirono: The Other One', 'quantity' => 2, 'price' => 600.00, 'subtotal' => 1200.00],
    ['order_id' => 999, 'status' => 'Shipped', 'customer_name' => 'Test Customer',
     'customer_email' => 'test@example.com', 'created_at' => date('Y-m-d H:i:s'),
     'total_amount' => 1800.00, 'item_name' => 'Skullpanda: Wonderland', 'quantity' => 1, 'price' => 600.00, 'subtotal' => 600.00],
];

if (send_status_email($rows)) {
    echo "<h2 style='color:green'>Sent! Open your Mailtrap Sandbox inbox. You should see the email with a PDF attached.</h2>";
} else {
    echo "<h2 style='color:red'>Failed</h2><p>" . htmlspecialchars($GLOBALS['mail_error']) . "</p>";
}