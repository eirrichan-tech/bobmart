<?php
session_start();
include('../includes/auth.php');
include('../includes/config.php');
require_admin();

if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$errors = [];
$v = ['name' => '', 'email' => '', 'role' => 'customer'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $tid    = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'];

    $sqls = [
        'toggle_status' => "UPDATE users SET status = IF(status='active','deactivated','active') WHERE id = ?",
        'delete'        => "DELETE FROM users WHERE id = ?",
    ];

    if ($tid === (int)$_SESSION['user_id']) {
        $_SESSION['error'] = "You can't change your own account.";
    } elseif ($action === 'change_role') {
        // Customers are locked. Only staff/admin accounts can move between the staff roles.
        $new = $_POST['new_role'] ?? '';
        if (!in_array($new, ['admin', 'inventory_staff', 'order_staff'], true)) {
            $_SESSION['error'] = "Choose a valid staff role.";
        } else {
            $s = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ? AND status = 'active' AND role <> 'customer'");
            mysqli_stmt_bind_param($s, "si", $new, $tid);
            mysqli_stmt_execute($s);
            $_SESSION[mysqli_stmt_affected_rows($s) > 0 ? 'success' : 'error'] = mysqli_stmt_affected_rows($s) > 0
                ? "Role updated."
                : "No change made. Customer roles are locked, and only active staff roles can be changed.";
        }
    } elseif (!isset($sqls[$action])) {
        $_SESSION['error'] = "Unknown action.";
    } else {
        try {
            $s = mysqli_prepare($conn, $sqls[$action]);
            mysqli_stmt_bind_param($s, "i", $tid);
            mysqli_stmt_execute($s);
            if (mysqli_stmt_affected_rows($s) > 0) {
                $_SESSION['success'] = "User updated.";
            } else {
                $_SESSION['error'] = "No change made. Roles can only be changed for active users.";
            }
        } catch (mysqli_sql_exception $ex) {
            $_SESSION['error'] = "Could not complete that. A user with orders can't be deleted, so deactivate them instead.";
        }
    }
    header("Location: manage_users.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $v['name']  = trim($_POST['name'] ?? '');
    $v['email'] = trim($_POST['email'] ?? '');
    $v['role']  = $_POST['role'] ?? 'customer';
    $password   = $_POST['password'] ?? '';

    if (strlen($v['name']) < 2 || strlen($v['name']) > 60) $errors['name'] = "Name must be 2 to 60 characters.";
    if ($v['email'] === '') $errors['email'] = "Email is required.";
    elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = "Enter a valid email address.";
    if (strlen($password) < 6) $errors['password'] = "Password must be at least 6 characters.";
    if (!in_array($v['role'], ['customer', 'inventory_staff', 'order_staff', 'admin'], true)) $errors['role'] = "Choose a valid role.";

    if (!isset($errors['email'])) {
        $chk = mysqli_prepare($conn, "SELECT 1 FROM users WHERE email = ?");
        mysqli_stmt_bind_param($chk, "s", $v['email']);
        mysqli_stmt_execute($chk);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) $errors['email'] = "That email already exists.";
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $ins = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
            mysqli_stmt_bind_param($ins, "ssss", $v['name'], $v['email'], $hash, $v['role']);
            mysqli_stmt_execute($ins);
            $_SESSION['success'] = "User added.";
            header("Location: manage_users.php");
            exit;
        } catch (mysqli_sql_exception $ex) {
            $errors['email'] = "Could not add user. Check that the email is unique.";
        }
    }
}

// "id AS user_id" so the rest of the page can keep using $u['user_id']
$users = mysqli_query($conn, "SELECT id AS user_id, name, email, role, status FROM users ORDER BY id");
include('../includes/header.php');
?>
<style>
        .msg-ok{color:#15803d;background:#dcfce7;padding:10px;border-radius:6px;margin-bottom:15px;font-size:14px}
        .msg-err{color:#be123c;background:#ffe4e6;padding:10px;border-radius:6px;margin-bottom:15px;font-size:14px}
        .field-error{display:block;color:#f43f5e;font-size:12px;margin-top:3px;font-weight:600}
        .dashboard-container .styled-table, .dashboard-container .styled-table td{color:#0f172a}
        .dashboard-container .styled-table th{color:#0284c7}
        .link-btn{background:none;border:none;color:#0284c7;font-weight:600;font-size:13px;cursor:pointer;padding:0;margin-right:10px}
        .link-btn.danger{color:#f43f5e}
        .add-box{background:#fff;padding:15px;margin-bottom:20px;border:1px solid #e2e8f0;border-radius:8px}
        .add-box form{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start}
        .add-box input,.add-box select{padding:8px;width:auto}
    </style>
<div class="dashboard-container">
    <h2 class="header-title">User Management</h2>
    <div class="back-row"><a href="../item/index.php" class="back-btn">&larr; Back to Inventory</a><a href="orders.php" class="back-btn">Orders</a><a href="../item/shop.php" class="back-btn">View Shop</a></div>

    <?php if (isset($_SESSION['success'])): ?><div class="msg-ok"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?><div class="msg-err"><?= e($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

    <div class="add-box">
        <h3 style="margin-bottom:4px">Add New User</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:10px">Accounts created on the public Register page are always customers and their role is locked. Staff accounts are created here.</p>
        <form method="POST" action="manage_users.php" novalidate>
            <input type="hidden" name="add_user" value="1">
            <div>
                <input type="text" name="name" placeholder="Full name" value="<?= e($v['name']) ?>">
                <?php if (isset($errors['name'])): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>
            </div>
            <div>
                <input type="text" name="email" placeholder="Email address" value="<?= e($v['email']) ?>">
                <?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
            </div>
            <div>
                <input type="password" name="password" placeholder="Password">
                <?php if (isset($errors['password'])): ?><small class="field-error"><?= e($errors['password']) ?></small><?php endif; ?>
            </div>
            <div>
                <select name="role">
                    <option value="customer" <?= $v['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                    <option value="inventory_staff" <?= $v['role'] === 'inventory_staff' ? 'selected' : '' ?>>Inventory Staff</option>
                    <option value="order_staff" <?= $v['role'] === 'order_staff' ? 'selected' : '' ?>>Order Staff</option>
                    <option value="admin" <?= $v['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <?php if (isset($errors['role'])): ?><small class="field-error"><?= e($errors['role']) ?></small><?php endif; ?>
            </div>
            <button type="submit" class="btn-primary">Add User</button>
        </form>
    </div>

    <div style="overflow-x:auto">
    <table class="styled-table">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td>#<?= (int)$u['user_id'] ?></td>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><b><?= e($u['role']) ?></b><?= $u['role'] === 'customer' ? ' <small>(locked)</small>' : '' ?></td>
                <td><?= e($u['status']) ?></td>
                <td>
                    <?php if ((int)$u['user_id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="POST" action="manage_users.php" style="display:inline">
                            <input type="hidden" name="id" value="<?= (int)$u['user_id'] ?>">
                            <button class="link-btn" name="action" value="toggle_status">
                                <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </button>
                            <?php if ($u['role'] !== 'customer'): ?>
                                <select name="new_role" style="width:auto;padding:4px;font-size:12px">
                                    <?php foreach (['inventory_staff' => 'Inventory Staff', 'order_staff' => 'Order Staff', 'admin' => 'Admin'] as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $u['role'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="link-btn" name="action" value="change_role">Save Role</button>
                            <?php endif; ?>
                            <button class="link-btn danger" name="action" value="delete"
                                    onclick="return confirm('Permanently delete this user?');">Delete</button>
                        </form>
                    <?php else: ?>
                        <small>(you)</small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include('../includes/footer.php'); ?>
