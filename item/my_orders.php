<?php
session_start();
include('../includes/auth.php'); include('../includes/config.php'); include('../includes/functions.php');
require_login();
$st = mysqli_prepare($conn, "SELECT * FROM order_details_view WHERE user_id=? ORDER BY created_at DESC, order_id DESC");
mysqli_stmt_bind_param($st, "i", $_SESSION['user_id']); mysqli_stmt_execute($st);
$orders = [];
foreach (mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC) as $r) $orders[$r['order_id']][] = $r;
include('../includes/header.php');
?>
<div class="dashboard-container">
  <a href="shop.php" class="back-btn">&larr; Back to Shop</a>
  <h2 class="header-title">My Orders</h2>
  <?php if (!$orders): ?><p style="text-align:center">No orders yet.</p><?php endif; ?>
  <?php foreach ($orders as $oid => $lines): $f = $lines[0]; ?>
    <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:14px;background:#fff">
      <b>Order #<?= $oid ?></b> · <span class="badge"><?= e($f['status']) ?></span> · <?= e($f['created_at']) ?>
      <table class="styled-table">
        <?php foreach ($lines as $l): ?>
          <tr><td><?= e($l['item_name']) ?></td><td><?= $l['quantity'] ?> × $<?= number_format($l['price'], 2) ?></td><td>$<?= number_format($l['subtotal'], 2) ?></td></tr>
        <?php endforeach; ?>
        <tr><th colspan="2">Grand Total</th><th>$<?= number_format($f['total_amount'], 2) ?></th></tr>
      </table>
    </div>
  <?php endforeach; ?>
</div>
<?php include('../includes/footer.php'); ?>