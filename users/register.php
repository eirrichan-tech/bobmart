<?php
session_start();
include('../includes/config.php');
include('../includes/functions.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($name === '' || $email === '' || $password === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $imgErrors = [];
        $profile_img = 'bobmart/includes/estetik/default.png';          // Make sure default.png is in this folder
        if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] !== UPLOAD_ERR_NO_FILE) {
            $saved = save_uploads($_FILES['profile_img'], 'users', $imgErrors);
            if ($saved) $profile_img = $saved[0];
        }
        if ($imgErrors) {
            $error = implode(' ', $imgErrors);
        }

        if (empty($error)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO users (email, password, name, role, profile_img, status) VALUES (?, ?, ?, 'customer', ?, 'active')");
            mysqli_stmt_bind_param($stmt, "ssss", $email, $hashed_password, $name, $profile_img);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Registration successful! Please log in.";
                header("Location: login.php");
                exit;
            } else {
                $error = "Email address is already registered.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration - Bobmart</title>
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
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
    <div class="card-container" style="width: 100%; max-width: 420px; text-align: left;">
        <h2 class="header-title" style="text-align: center; font-size: 26px; margin-bottom: 20px;">Create Account</h2>
        
        <?php if (!empty($error)): ?>
            <div style="color: #f43f5e; background: rgba(244, 63, 94, 0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Full Name</label>
                <input type="text" name="name" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Email Address</label>
                <input type="email" name="email" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Password</label>
                <input type="password" name="password" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Profile Photo</label>
                <input type="file" name="profile_img" accept="image/*">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Register</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #38bdf8; text-decoration: none; font-size: 14px;">Already have an account? Sign in here</a>
        </div>
    </div>
</body>
</html>