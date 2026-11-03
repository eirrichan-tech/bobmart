<?php
session_start();
include('../includes/auth.php'); include('../includes/config.php');
require_login();
$id = (int)($_POST['item_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);
if ($qty < 1) { $_SESSION['error'] = "Quantity must be at least 1."; header("Location: details.php?id=$id"); exit; }

$st = mysqli_prepare($conn, "SELECT s.quantity FROM items i JOIN stock s ON s.item_id=i.id WHERE i.id=? AND i.status='Active'");
mysqli_stmt_bind_param($st, "i", $id); mysqli_stmt_execute($st);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (!$row) { $_SESSION['error'] = "Product unavailable."; header("Location: shop.php"); exit; }

$_SESSION['cart'][$id] = min(($_SESSION['cart'][$id] ?? 0) + $qty, (int)$row['quantity']);
$_SESSION['success'] = "Added to cart.";
header("Location: view_cart.php"); exit;