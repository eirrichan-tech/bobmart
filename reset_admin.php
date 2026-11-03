<?php
require_once __DIR__ . '/includes/config.php';

$new  = 'Admin@12345';
$hash = password_hash($new, PASSWORD_DEFAULT);

$up = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = 1");
mysqli_stmt_bind_param($up, "s", $hash);
mysqli_stmt_execute($up);
echo "Rows changed: " . mysqli_stmt_affected_rows($up) . "<br>";

$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id = 1"));
echo "Stored length now: " . strlen($r['password']) . " (should be 60)<br>";
echo "Starts with: " . htmlspecialchars(substr($r['password'], 0, 4)) . " (should be \$2y\$)<br>";
echo password_verify($new, $r['password'])
    ? "<b style='color:green'>OK. Log in with admin@bobmart.com and $new</b>"
    : "<b style='color:red'>Still failing</b>";