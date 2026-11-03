<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please log in to continue.";
        header("Location: /bobmart/users/login.php"); exit;
    }
}
function require_admin() {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        $_SESSION['error'] = "Access denied. Administrator privileges required.";
        header("Location: /bobmart/item/shop.php"); exit;
    }
}
function is_admin() { return ($_SESSION['role'] ?? '') === 'admin'; }