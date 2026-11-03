<?php
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $series_name = trim($_POST['series_name'] ?? '');
    $rarity = trim($_POST['rarity'] ?? '');
    $stock_boxes = intval($_POST['stock_boxes'] ?? 0);

    if ($id) {
        // 1. Update the item details in the items table
        $stmt = mysqli_prepare($conn, "UPDATE items SET name = ?, series_name = ?, rarity = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $series_name, $rarity, $id);
        mysqli_stmt_execute($stmt);

        // 2. Update the stock quantity in the stock table
        $stmt_stock = mysqli_prepare($conn, "UPDATE stock SET quantity = ? WHERE item_id = ?");
        mysqli_stmt_bind_param($stmt_stock, "ii", $stock_boxes, $id);
        mysqli_stmt_execute($stmt_stock);
    }

    header('Location: index.php');
    exit;
}
?>