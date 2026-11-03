<?php
session_start();
include('../includes/config.php');
include('../includes/functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';
$error = '';

// Fetch existing user details using 'id' instead of 'user_id'
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    header("Location: login.php");
    exit;
}

$profile_img = $user['profile_img'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    // Server-side validation (Name length check)
    if (strlen($name) < 2) {
        $error = "Name must be at least 2 characters long.";
    } else {
        $imgErrors = [];
        if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] !== UPLOAD_ERR_NO_FILE) {
            $saved = save_uploads($_FILES['profile_img'], 'users', $imgErrors);
            if ($saved) {
                $profile_img = $saved[0];
            }
        }
        
        if ($imgErrors) {
            $error = implode(' ', $imgErrors);
        } else {
            // Prepared statement update using 'id'
            $st = mysqli_prepare($conn, "UPDATE users SET name = ?, profile_img = ? WHERE id = ?");
            mysqli_stmt_bind_param($st, "ssi", $name, $profile_img, $user_id);
            mysqli_stmt_execute($st);
            
            $_SESSION['name'] = $name;
            $msg = "Profile updated successfully!";
            
            // Refresh user data array using 'id'
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>My Profile</title><link rel="stylesheet" href="../includes/estetik/style.css"></head>
<body>
    <div class="card-container" style="text-align: center;">
        <h2 class="header-title">Customer Profile</h2>
        <?php if (!empty($msg)): ?><div style="color:#16a34a; background:#dcfce7; padding:10px; border-radius:6px; margin-bottom:15px; font-size:13px;"><?= e($msg) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div style="color:#f43f5e; background:rgba(244,63,94,0.1); padding:10px; border-radius:6px; margin-bottom:15px; font-size:13px;"><?= e($error) ?></div><?php endif; ?>
        
        <img src="/bobmart/<?= e($user['profile_img']) ?>" width="100" height="100" style="border-radius: 50%; object-fit: cover; margin-bottom: 15px;">
        
        <form action="profile.php" method="POST" enctype="multipart/form-data" novalidate style="text-align: left;">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= e($user['name']) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" value="<?= e($user['email']) ?>" disabled style="background:#e2e8f0;">
            </div>
            <div class="form-group">
                <label>New Profile Photo</label>
                <input type="file" name="profile_img">
            </div>
            <button type="submit" class="btn-submit">Update Profile</button>
        </form>
        <a href="logout.php" class="back-link" style="color: #ef4444;">Log Out</a>
    </div>
</body>
</html>