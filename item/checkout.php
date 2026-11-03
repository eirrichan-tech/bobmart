<?php
session_start();
include('../includes/auth.php'); include('../includes/config.php'); include('../includes/functions.php');
require_login();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // makes DB failures throw exceptions

$rows = cart_rows($conn);
if (!$rows) { $_SESSION['error'] = "Your cart is empty."; header("Location: shop.php"); exit; }
$grand = array_sum(array_column($rows, 'subtotal'));
$error = ''; $address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    if (strlen($address) < 10) {
        $error = "Please enter a complete shipping address (at least 10 characters).";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $total = 0; $lines = [];
            foreach ($rows as $r) {
                $st = mysqli_prepare($conn, "SELECT i.sell_price, s.quantity FROM items i JOIN stock s ON s.item_id=i.id
                                             WHERE i.id=? AND i.status='Active' FOR UPDATE");
                mysqli_stmt_bind_param($st, "i", $r['id']); mysqli_stmt_execute($st);
                $p = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
                if (!$p || $p['quantity'] < $r['qty']) throw new Exception("Not enough stock for " . $r['name'] . ".");
                $total += $p['sell_price'] * $r['qty'];
                $lines[] = [$r['id'], $r['qty'], $p['sell_price']];
            }

            $uid = $_SESSION['user_id'];
            $o = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?,?, 'Pending', ?)");
            mysqli_stmt_bind_param($o, "ids", $uid, $total, $address); mysqli_stmt_execute($o);
            $order_id = mysqli_insert_id($conn);

            $oi = mysqli_prepare($conn, "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?,?,?,?)");
            $us = mysqli_prepare($conn, "UPDATE stock SET quantity = quantity - ? WHERE item_id=? AND quantity >= ?");
            foreach ($lines as [$iid, $q, $pr]) {
                mysqli_stmt_bind_param($oi, "iiid", $order_id, $iid, $q, $pr); mysqli_stmt_execute($oi);
                mysqli_stmt_bind_param($us, "iii", $q, $iid, $q); mysqli_stmt_execute($us);
            }
            mysqli_commit($conn);
            unset($_SESSION['cart']);
            $_SESSION['success'] = "Order #$order_id placed successfully!";
            header("Location: my_orders.php"); exit;
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            $error = "Checkout failed: " . $ex->getMessage();
        }
    }
}
include('../includes/header.php');
?>
<div class="card-container" style="max-width:600px;margin:auto">
  <h2 class="header-title">Checkout</h2>
  <?php if ($error): ?><p style="color:#f43f5e"><?= e($error) ?></p><?php endif; ?>
  <table class="styled-table">
    <?php foreach ($rows as $r): ?>
      <tr><td><?= e($r['name']) ?> × <?= $r['qty'] ?></td><td>$<?= number_format($r['subtotal'], 2) ?></td></tr>
    <?php endforeach; ?>
    <tr><th>Grand Total</th><th>$<?= number_format($grand, 2) ?></th></tr>
  </table>
  <form method="POST" novalidate>
    <label>Shipping Address</label>
    <textarea name="address" rows="3"><?= e($address) ?></textarea>
    <p style="font-size:13px">Payment: Cash on Delivery</p>
    <button class="btn-primary" style="width:100%">Place Order</button>
  </form>
</div>
<?php include('../includes/footer.php'); ?>