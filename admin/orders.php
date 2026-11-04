<?php
session_start();
include('../includes/auth.php');
include('../includes/config.php');
require_once '../includes/mailer.php';
require_role(['order_staff']);                              // customers can't open this page

if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/* ---------- Update status + send email ---------- */
if (isset($_POST['update_status'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new = $_POST['status'] ?? '';

    if (!in_array($new, ['Pending', 'Shipped', 'Delivered', 'Cancelled'], true)) {
        $_SESSION['error'] = "Invalid status.";
    } else {
        $u = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE order_id = ?");
        mysqli_stmt_bind_param($u, "si", $new, $order_id);
        mysqli_stmt_execute($u);

        $q = mysqli_prepare($conn, "SELECT * FROM order_details_view WHERE order_id = ?");
        mysqli_stmt_bind_param($q, "i", $order_id);
        mysqli_stmt_execute($q);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($q), MYSQLI_ASSOC);

        if ($rows && send_status_email($rows)) {
            $_SESSION['success'] = "Order #$order_id set to '$new' and the email with PDF receipt was sent via Mailtrap.";
        } else {
            $_SESSION['error'] = "Order #$order_id set to '$new', but the email failed: " . ($GLOBALS['mail_error'] ?? 'unknown error');
        }
    }
    header("Location: orders.php");
    exit;
}

/* ---------- List all orders, grouped with their items ---------- */
$res = mysqli_query($conn, "SELECT * FROM order_details_view ORDER BY created_at DESC, order_id DESC");
$orders = [];
while ($r = mysqli_fetch_assoc($res)) $orders[$r['order_id']][] = $r;
include('../includes/header.php');
?>
<style>
        .msg-ok{color:#15803d;background:#dcfce7;padding:10px;border-radius:6px;margin-bottom:15px;font-size:14px}
        .msg-err{color:#be123c;background:#ffe4e6;padding:10px;border-radius:6px;margin-bottom:15px;font-size:14px}
        .dashboard-container .styled-table, .dashboard-container .styled-table td { color:#0f172a; }
        .dashboard-container .styled-table th { color:#0284c7; }
    </style>
<div class="dashboard-container">
    <h2 class="header-title">Order Management</h2>
    <div class="back-row"><a href="../item/index.php" class="back-btn">&larr; Back to Inventory</a><a href="manage_users.php" class="back-btn">Users</a><a href="../item/shop.php" class="back-btn">View Shop</a></div>

    <?php if (isset($_SESSION['success'])): ?><div class="msg-ok"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?><div class="msg-err"><?= e($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

    <div style="overflow-x:auto">
    <table class="styled-table">
        <thead>
            <tr><th>Order</th><th>Customer</th><th>Items</th><th>Grand Total</th><th>Status</th><th>Date</th><th>Update</th></tr>
        </thead>
        <tbody>
        <?php if (!$orders): ?>
            <tr><td colspan="7" style="text-align:center">No orders yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($orders as $oid => $lines): $f = $lines[0]; ?>
            <tr>
                <td>#<?= (int)$oid ?></td>
                <td><?= e($f['customer_name']) ?><br><small><?= e($f['customer_email']) ?></small></td>
                <td>
                    <?php foreach ($lines as $l): ?>
                        <?= e(m_name($l)) ?> &times; <?= (int)$l['quantity'] ?> = $<?= number_format(m_sub($l), 2) ?><br>
                    <?php endforeach; ?>
                </td>
                <td><b>$<?= number_format(m_total($lines), 2) ?></b></td>
                <td><b><?= e($f['status']) ?></b></td>
                <td><?= e($f['created_at']) ?></td>
                <td>
                    <form action="orders.php" method="POST" style="display:flex;gap:6px">
                        <input type="hidden" name="order_id" value="<?= (int)$oid ?>">
                        <select name="status" style="padding:4px;font-size:12px">
                            <?php foreach (['Pending','Shipped','Delivered','Cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $f['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status" class="btn-primary" style="padding:4px 10px;font-size:12px">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include('../includes/footer.php'); ?>
