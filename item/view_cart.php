<?php
session_start();
include('../includes/auth.php'); include('../includes/config.php'); include('../includes/functions.php');
require_login();
$rows = cart_rows($conn);
$grand = array_sum(array_column($rows, 'subtotal'));
include('../includes/header.php');
?>
<div class="dashboard-container">
  <h2 class="header-title">My Cart</h2>
  <?php if (!$rows): ?>
    <p style="text-align:center">Your cart is empty. <a href="shop.php">Continue shopping</a></p>
  <?php else: ?>
  <table class="styled-table">
    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['name']) ?></td>
        <td>$<?= number_format($r['sell_price'], 2) ?></td>
        <td>
          <form action="cart_update.php" method="POST" novalidate style="display:flex;gap:6px">
            <input type="hidden" name="item_id" value="<?= $r['id'] ?>">
            <input type="text" name="qty" value="<?= $r['qty'] ?>" style="width:60px">
            <button name="action" value="update" class="btn-primary">Update</button>
            <button name="action" value="remove" class="btn-primary" style="background:#f43f5e">Remove</button>
          </form>
        </td>
        <td>$<?= number_format($r['subtotal'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <h3 style="text-align:right;margin-top:15px">Grand Total: $<?= number_format($grand, 2) ?></h3>
  <p style="text-align:right"><a href="checkout.php" class="btn-primary">Checkout</a></p>
  <?php endif; ?>
</div>
<?php include('../includes/footer.php'); ?>