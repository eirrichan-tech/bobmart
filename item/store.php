<?php
session_start();
include('../includes/auth.php');
include('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $series = trim($_POST['series_name'] ?? '');
    $rarity = trim($_POST['rarity'] ?? '');
    $cost = trim($_POST['cost_price'] ?? '');
    $sell = trim($_POST['sell_price'] ?? '');
    $cases = trim($_POST['stock_cases'] ?? '');
    $qty = trim($_POST['quantity'] ?? '');
    $hasErrors = false;

    if ($name === '' || $series === '' || !is_numeric($cost) || !is_numeric($sell) || !is_numeric($cases) || !is_numeric($qty)) {
        $_SESSION['error'] = 'All fields are required, and numeric fields must contain valid numbers.';
        $hasErrors = true;
    } elseif ((float)$cost < 0 || (float)$sell < 0 || (int)$cases < 0 || (int)$qty < 0) {
        $_SESSION['error'] = 'Stock and price values cannot be negative numbers.';
        $hasErrors = true;
    }

    if ($hasErrors) {
        $_SESSION['old_name'] = $name;
        $_SESSION['old_series'] = $series;
        $_SESSION['old_cost'] = $cost;
        $_SESSION['old_sell'] = $sell;
        $_SESSION['old_cases'] = $cases;
        $_SESSION['old_qty'] = $qty;
        header("Location: create.php");
        exit;
    }

    $uploadedImages = [];
    if (isset($_FILES['images'])) {
        $fileCount = count($_FILES['images']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['images']['tmp_name'][$i];
                $fileName = time() . '_' . basename($_FILES['images']['name'][$i]);
                $destination = 'images/' . $fileName;
                if (move_uploaded_file($tmpName, $destination)) {
                    $uploadedImages[] = $destination;
                }
            }
        }
    }
    $primaryImage = !empty($uploadedImages) ? $uploadedImages[0] : 'images/default.jpg';

    mysqli_begin_transaction($conn);
    try {
        $stmt1 = mysqli_prepare($conn, "INSERT INTO items (name, series_name, rarity, cost_price, sell_price, img_path, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        mysqli_stmt_bind_param($stmt1, "sssdds", $name, $series, $rarity, $cost, $sell, $primaryImage);
        mysqli_stmt_execute($stmt1);
        $item_id = mysqli_insert_id($conn);

        $stmt2 = mysqli_prepare($conn, "INSERT INTO stock (item_id, quantity, stock_cases) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "iii", $item_id, $qty, $cases);
        mysqli_stmt_execute($stmt2);

        mysqli_commit($conn);
        header("Location: index.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conn);
        die("Transaction failed and rolled back: " . $e->getMessage());
    }
}
?>