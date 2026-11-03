<?php
session_start();
include('../includes/auth.php'); include('../includes/config.php');
require_admin();
$id = (int)($_GET['id'] ?? 0);
$st = mysqli_prepare($conn, "UPDATE stock s JOIN items i ON i.id=s.item_id
    SET s.stock_cases = s.stock_cases - 1, s.quantity = s.quantity + i.boxes_per_case
    WHERE s.item_id=? AND s.stock_cases>0 AND i.status<>'Vaulted/Discontinued'");
mysqli_stmt_bind_param($st, "i", $id); mysqli_stmt_execute($st);
header("Location: index.php"); exit;