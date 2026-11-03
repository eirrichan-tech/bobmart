<?php
session_start();
require_once __DIR__ . '/includes/config.php';

echo "<h3>Login diagnostic</h3>";

// 1. Which database and folder is this really using?
$db = mysqli_fetch_row(mysqli_query($conn, "SELECT DATABASE()"))[0];
echo "Database in use: <b>" . htmlspecialchars($db) . "</b><br>";
echo "This folder: " . htmlspecialchars(__DIR__) . "<br>";

// 2. Is admin/login.php the corrected version?
$f = @file_get_contents(__DIR__ . '/admin/login.php');
if ($f === false) {
    echo "admin/login.php: <b style='color:red'>NOT FOUND in this folder</b><br>";
} else {
    echo "admin/login.php selects <code>id</code>: " . (strpos($f, 'SELECT id,') !== false ? "<b style='color:green'>yes</b>" : "<b style='color:red'>NO (old file)</b>") . "<br>";
    echo "admin/login.php has the admin-only check: " . (strpos($f, 'administrators only') !== false ? "<b style='color:green'>yes</b>" : "<b style='color:red'>NO (old file)</b>") . "<br>";
    echo "admin/login.php uses password_verify: " . (strpos($f, 'password_verify') !== false ? "<b style='color:green'>yes</b>" : "<b style='color:red'>NO</b>") . "<br>";
}

// 3. Sessions working?
$_SESSION['t'] = 'ok';
echo "Session save path writable: " . (is_writable(session_save_path() ?: sys_get_temp_dir()) ? "<b style='color:green'>yes</b>" : "<b style='color:red'>NO</b>") . "<br><hr>";

// 4. Test an email + password exactly like the login does
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    echo "Email typed: [" . htmlspecialchars($email) . "] (length " . strlen($email) . ")<br>";
    echo "Password typed length: " . strlen($pass) . "<br><br>";

    $st = mysqli_prepare($conn, "SELECT id, email, password, role, status FROM users WHERE email = ?");
    mysqli_stmt_bind_param($st, "s", $email);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);

    if (!$rows) {
        echo "<b style='color:red'>No user has exactly this email.</b> Check spelling, spaces and which admin address you use.<br>";
    }
    foreach ($rows as $u) {
        echo "Found id {$u['id']} | role: {$u['role']} | status: {$u['status']}<br>";
        echo "Stored hash length: " . strlen($u['password']) . " | starts with: " . htmlspecialchars(substr($u['password'], 0, 4)) . "<br>";
        $ok = password_verify($pass, $u['password']);
        echo "password_verify: " . ($ok ? "<b style='color:green'>MATCH</b>" : "<b style='color:red'>NO MATCH</b>") . "<br>";
        if (!$ok && $pass === $u['password']) echo "<b style='color:red'>The stored password is plain text. It needs hashing.</b><br>";
        if (strlen($u['password']) < 60) echo "<b style='color:red'>The hash is cut off. The password column must be VARCHAR(255).</b><br>";
        echo "role is admin: " . ($u['role'] === 'admin' ? "yes" : "<b style='color:red'>no</b>") . " | active: " . ($u['status'] === 'active' ? "yes" : "<b style='color:red'>no</b>") . "<br><br>";
    }
}
?>
<form method="POST" style="margin-top:15px">
    Email: <input type="text" name="email" size="30"><br><br>
    Password: <input type="text" name="password" size="30"><br><br>
    <button>Test</button>
</form>