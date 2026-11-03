<?php
session_start();
include('../includes/auth.php'); 
include('../includes/config.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    
    if ($_GET['action'] === 'toggle_status') {
        mysqli_query($conn, "UPDATE users SET status = IF(status='active', 'deactivated', 'active') WHERE user_id = $target_id");
    } elseif ($_GET['action'] === 'make_admin') {
        mysqli_query($conn, "UPDATE users SET role = 'admin' WHERE user_id = $target_id");
    } elseif ($_GET['action'] === 'make_customer') {
        mysqli_query($conn, "UPDATE users SET role = 'customer' WHERE user_id = $target_id");
    } elseif ($_GET['action'] === 'delete') {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $target_id");
    }
    
    header("Location: manage_users.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = 'active';

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        $query = "INSERT INTO users (name, email, password, role, status) VALUES ('$name', '$email', '$password', '$role', '$status')";
        if (mysqli_query($conn, $query)) {
            header("Location: manage_users.php");
            exit;
        } else {
            $error = "Error adding user: " . mysqli_error($conn);
        }
    }
}

$users = mysqli_query($conn, "SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../includes/estetik/style.css">
</head>
<body>
    <div class="dashboard-container">
        <h2 class="header-title">User Management (Full CRUD)</h2>
        <p class="header-subtitle"><a href="../item/index.php">&larr; Back to Inventory</a></p>

        <?php if (!empty($error)): ?>
            <p style="color: red; font-weight: bold;"><?= $error ?></p>
        <?php endif; ?>

        <!-- CREATE SECTION: Add New User Form -->
        <div style="background: #f9f9f9; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Add New User</h3>
            <form method="POST" action="manage_users.php" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <input type="hidden" name="add_user" value="1">
                <input type="text" name="name" placeholder="Full Name" required style="padding: 5px;">
                <input type="email" name="email" placeholder="Email Address" required style="padding: 5px;">
                <input type="password" name="password" placeholder="Password" required style="padding: 5px;">
                <select name="role" style="padding: 5px;">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" style="padding: 6px 12px; background: #28a745; color: white; border: none; cursor: pointer;">Add User</button>
            </form>
        </div>

        <!-- READ, UPDATE, DELETE SECTION: Users Table -->
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td>#<?= $u['user_id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><b><?= $u['role'] ?></b></td>
                    <td><?= $u['status'] ?></td>
                    <td class="action-links">
                        <a href="manage_users.php?action=toggle_status&id=<?= $u['user_id'] ?>">Toggle Status</a>
                        <?php if($u['role'] === 'customer'): ?>
                            <a href="manage_users.php?action=make_admin&id=<?= $u['user_id'] ?>">Make Admin</a>
                        <?php else: ?>
                            <a href="manage_users.php?action=make_customer&id=<?= $u['user_id'] ?>">Demote</a>
                        <?php endif; ?>
                        <a href="manage_users.php?action=delete&id=<?= $u['user_id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this user?');" style="color: red; margin-left: 5px;">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>