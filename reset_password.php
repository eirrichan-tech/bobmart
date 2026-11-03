<?php
include('includes/config.php');

// Change this to the email of the account you want to reset
$email = "admin@bobmart.com"; 

// Change this to whatever new password you want to use
$new_plain_password = "password123"; 

$hashed_password = password_hash($new_plain_password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);

if (mysqli_stmt_execute($stmt)) {
    echo "Password successfully reset for <b>{$email}</b>! New password is: <b>{$new_plain_password}</b><br>";
    echo "<a href='users/login.php'>Go to Login</a>";
} else {
    echo "Error updating password: " . mysqli_error($conn);
}
?>