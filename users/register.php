<?php
/* users/register.php - complete replacement */
session_start();
include('../includes/config.php');
include('../includes/functions.php');

if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$errors = [];
$v = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['name']  = trim($_POST['name'] ?? '');
    $v['email'] = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';          // not trimmed on purpose
    $confirm    = $_POST['confirm'] ?? '';

    // ---------- PHP validation (no HTML5) ----------
    if (strlen($v['name']) < 2 || strlen($v['name']) > 60) {
        $errors['name'] = "Name must be 2 to 60 characters.";
    }
    if ($v['email'] === '') {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address.";
    }
    if (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    }
    if ($confirm !== $password) {
        $errors['confirm'] = "Passwords do not match.";
    }

    // email already taken?
    if (!isset($errors['email'])) {
        $chk = mysqli_prepare($conn, "SELECT 1 FROM users WHERE email = ?");
        mysqli_stmt_bind_param($chk, "s", $v['email']);
        mysqli_stmt_execute($chk);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            $errors['email'] = "That email is already registered.";
        }
    }

    // ---------- Optional profile photo (MP2) ----------
    $profile_img = 'uploads/users/default.png';      // put a default.png in bobmart/uploads/users/
    if (!$errors && isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imgErrors = [];
        $saved = save_uploads($_FILES['profile_img'], 'users', $imgErrors);
        if ($imgErrors) {
            $errors['profile_img'] = implode(' ', $imgErrors);
        } elseif ($saved) {
            $profile_img = $saved[0];
        }
    }

    // ---------- Save ----------
    if (!$errors) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO users (email, password, name, role, profile_img, status) VALUES (?, ?, ?, 'customer', ?, 'active')");
            mysqli_stmt_bind_param($stmt, "ssss", $v['email'], $hashed_password, $v['name'], $profile_img);
            mysqli_stmt_execute($stmt);
            $_SESSION['success'] = "Registration successful! Please log in.";
            header("Location: login.php");
            exit;
        } catch (mysqli_sql_exception $ex) {
            $errors['email'] = "Could not register. That email may already be in use.";
        }
    }
}

$errStyle = 'color:#f43f5e; display:block; margin-top:5px; font-size:12px; font-weight:600;';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - Bobmart</title>
    <link rel="stylesheet" href="/bobmart/includes/estetik/style.css">
    <style>
        body {
            background: url('/bobmart/includes/estetik/user_login_bg.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body>
    <div class="card-container" style="width: 100%; max-width: 420px; text-align: left;">
        <h2 class="header-title" style="text-align: center; font-size: 26px; margin-bottom: 20px;">Create Account</h2>

        <form action="register.php" method="POST" enctype="multipart/form-data" novalidate>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Full Name</label>
                <input type="text" name="name" value="<?= e($v['name']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                <?php if (isset($errors['name'])): ?><small style="<?= $errStyle ?>"><?= e($errors['name']) ?></small><?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Email Address</label>
                <input type="text" name="email" value="<?= e($v['email']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                <?php if (isset($errors['email'])): ?><small style="<?= $errStyle ?>"><?= e($errors['email']) ?></small><?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Password</label>
                <input type="password" name="password" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                <?php if (isset($errors['password'])): ?><small style="<?= $errStyle ?>"><?= e($errors['password']) ?></small><?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Confirm Password</label>
                <input type="password" name="confirm" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                <?php if (isset($errors['confirm'])): ?><small style="<?= $errStyle ?>"><?= e($errors['confirm']) ?></small><?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Profile Photo (optional)</label>
                <input type="file" name="profile_img">
                <?php if (isset($errors['profile_img'])): ?><small style="<?= $errStyle ?>"><?= e($errors['profile_img']) ?></small><?php endif; ?>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Register</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #38bdf8; text-decoration: none; font-size: 14px;">Already have an account? Sign in here</a>
        </div>
    </div>
</body>
</html>