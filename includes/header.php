<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobmart - Blind Box Store Management</title>
    <link rel="stylesheet" href="/bobmart/includes/estetik/style.css">
</head>
<body>

    <div class="page-container">
        <header class="site-header">
            <h1>📦 BOBMART</h1>
            <span>Blind Box E-Commerce & Inventory Management System</span>
        </header>

        <div class="hero-banner">
            <div class="hero-banner-content">
                <h2>Unbox the Secret Series</h2>
                <p>Welcome back, <b><?= htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['name'] ?? 'Guest') ?></b></p>
            </div>
            <div>
                <a href="/bobmart/user/logout.php" class="btn-submit" style="background: #f43f5e; border: none;">Logout</a>
            </div>
        </div>

        <nav class="top-nav">
  <a href="/bobmart/item/shop.php">Shop</a>
  <?php if (!isset($_SESSION['user_id'])): ?>
    <a href="/bobmart/users/login.php">Login</a>
    <a href="/bobmart/users/register.php">Register</a>
  <?php else: ?>
    <a href="/bobmart/item/view_cart.php">Cart (<?= array_sum($_SESSION['cart'] ?? []) ?>)</a>
    <a href="/bobmart/item/my_orders.php">My Orders</a>
    <a href="/bobmart/users/profile.php">My Profile</a>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
      <a href="/bobmart/item/index.php">Inventory</a>
      <a href="/bobmart/item/create.php">Add Product</a>
      <a href="/bobmart/admin/orders.php">Orders</a>
      <a href="/bobmart/admin/manage_users.php">Users</a>
    <?php endif; ?>
    <a href="/bobmart/users/logout.php">Logout</a>
  <?php endif; ?>
</nav>
        <main class="main-content">