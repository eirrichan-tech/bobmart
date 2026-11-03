<?php
session_start();
include('../includes/auth.php');
require_login();
$id = (int)($_POST['item_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);
if (($_POST['action'] ?? '') === 'remove' || $qty < 1) unset($_SESSION['cart'][$id]);
else $_SESSION['cart'][$id] = $qty;      // stock is re-checked in cart_rows() and checkout
header("Location: view_cart.php"); exit;