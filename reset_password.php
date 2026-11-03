<?php
require_once __DIR__ . '/includes/config.php';

$id  = 1;                       // your admin row (admin@bobmart.com)
$new = 'Admin@12345';           // the password you will type at login

$hash = password_hash($new, PASSWORD_DEFAULT);
$up = mysqli_prepare($conn, "UPDATE users SET password = ?, role = 'admin', status = 'active' WHERE id = ?");
mysqli_stmt_bind_param($up, "si", $hash, $id);
mysqli_stmt_execute($up);

// read it back and test it, the same way the login does
$st = mysqli_prepare($conn, "SELECT email, password, role, status FROM users WHERE id = ?");
mysqli_stmt_bind_param($st, "i", $id);
mysqli_stmt_execute($st);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($st));

if (!$u) { die("No user with id $id."); }
echo "Email: " . htmlspecialchars($u['email']) . "<br>";
echo "Role: {$u['role']} | Status: {$u['status']}<br>";
echo "Stored hash length: " . strlen($u['password']) . " (should be 60)<br>";
echo "Password check: " . (password_verify($new, $u['password'])
     ? "<b style='color:green'>OK - log in with $new</b>"
     : "<b style='color:red'>FAILED</b>");