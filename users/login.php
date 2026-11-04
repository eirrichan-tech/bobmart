<?php
session_start();
include('../includes/config.php');
include_once('../includes/auth.php');
include('../includes/functions.php');

$error = $_SESSION['error'] ?? ''; 
unset($_SESSION['error']);   // Shows "Please log in..." if redirected
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email.";
    }

    if ($password === '') {
        $errors['password'] = "Password is required.";
    }

    if (!$errors) {
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role, status FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Invalid email or password.";
        } elseif ($user['status'] !== 'active') {
            $error = "Your account has been deactivated. Contact the administrator.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            
            // Redirect based on role
            header("Location: " . role_home($user['role']));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Bobmart</title>
    <link rel="stylesheet" href="/bobmart/includes/estetik/style.css">
    <style>
        body {
            background: url('/bobmart/includes/estetik/user_login_bg.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body>
    <div class="card-container" style="width: 100%; max-width: 420px; text-align: left; background: rgba(15, 23, 42, 0.9); padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.1); color: #f8fafc;">
        <h2 class="header-title" style="text-align: center; font-size: 26px; margin-bottom: 20px; color: #f8fafc;">Sign In</h2>
        
        <?php if (!empty($error)): ?>
            <div style="color: #f43f5e; background: rgba(244, 63, 94, 0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; border: 1px solid rgba(244,63,94,0.2);">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Email Address</label>
                <input type="text" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['email'])): ?>
                    <small style="color:#f43f5e; display:block; margin-top:5px; font-size:12px;"><?= htmlspecialchars($errors['email']) ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Password</label>
                <input type="password" name="password" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['password'])): ?>
                    <small style="color:#f43f5e; display:block; margin-top:5px; font-size:12px;"><?= htmlspecialchars($errors['password']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; border: none; border-radius: 6px; font-weight: bold; background: #38bdf8; color: #0f172a; cursor: pointer;">Login</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="register.php" style="color: #38bdf8; text-decoration: none; font-size: 14px;">Don't have an account? Register here</a>
        </div>
        <div style="text-align: center; margin-top: 12px;"><a href="/bobmart/item/shop.php" style="color: #38bdf8; text-decoration: none; font-size: 14px;">&larr; Back to Shop</a></div>
    </div>
</body>
</html>