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

// Roles: customer (self-registered, locked), inventory_staff, order_staff, admin (created by an admin)
// Admin is always allowed. Pass the extra roles a page accepts: require_role(['inventory_staff']);
function require_role(array $allowed) {
    require_login();
    $role = $_SESSION['role'] ?? '';
    if ($role !== 'admin' && !in_array($role, $allowed, true)) {
        $_SESSION['error'] = "Access denied. Your account role does not have permission to open that page.";
        header("Location: /bobmart/item/shop.php"); exit;
    }
}
function role_home($role) {
    if ($role === 'admin' || $role === 'inventory_staff') return "/bobmart/item/index.php";
    if ($role === 'order_staff') return "/bobmart/admin/orders.php";
    return "/bobmart/item/shop.php";
}
