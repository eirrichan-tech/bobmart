<?php
require_once __DIR__ . '/includes/config.php';
function ok($b, $msg) { echo ($b ? "<span style='color:green'>PASS</span>" : "<span style='color:red'>FAIL</span>") . " - $msg<br>"; }
function head($t) { echo "<h3 style='margin-bottom:4px'>$t</h3>"; }
function count_q($conn, $sql) { try { return (int)mysqli_fetch_row(mysqli_query($conn, $sql))[0]; } catch (Throwable $e) { return -1; } }

echo "<h2>Bobmart audit</h2>";

head("Database (Unit 1)");
$need = [
  'users'       => ['id','name','email','password','role','profile_img','status'],
  'items'       => ['id','name','series_name','rarity','cost_price','sell_price','status'],
  'stock'       => ['item_id','quantity','stock_cases'],
  'item_images' => ['image_id','item_id','img_path'],
  'orders'      => ['order_id','user_id','total_amount','status','created_at','shipping_address'],
  'order_items' => ['order_id','item_id','quantity','price'],
  'reviews'     => ['item_id','user_id','rating','comment','created_at','updated_at'],
];
foreach ($need as $t => $cols) {
    try {
        $have = array_column(mysqli_fetch_all(mysqli_query($conn, "SHOW COLUMNS FROM `$t`"), MYSQLI_ASSOC), 'Field');
        $miss = array_diff($cols, $have);
        ok(!$miss, "table $t" . ($miss ? " is missing columns: " . implode(', ', $miss) : ""));
    } catch (Throwable $e) { ok(false, "table $t does not exist"); }
}
try {
    $v = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM order_details_view LIMIT 1"));
    $vc = $v ? array_keys($v) : [];
    $mv = array_diff(['order_id','customer_name','customer_email','item_name','quantity','price','subtotal','total_amount','status'], $vc);
    ok($v !== null && !$mv, "VIEW order_details_view works" . ($v === null ? " (no orders yet, place one)" : ($mv ? " but lacks: " . implode(', ', $mv) : "")));
} catch (Throwable $e) { ok(false, "VIEW order_details_view: " . $e->getMessage()); }

head("Data");
ok(count_q($conn, 'SELECT COUNT(*) FROM users WHERE password NOT LIKE \'$2y$%\'') === 0, "all passwords are hashed");
ok(count_q($conn, "SELECT COUNT(*) FROM items i LEFT JOIN stock s ON s.item_id=i.id WHERE s.item_id IS NULL") === 0, "every item has a stock row");
ok(count_q($conn, "SELECT COUNT(*) FROM (SELECT item_id FROM item_images GROUP BY item_id HAVING COUNT(*)>1) t") > 0, "at least one product has 2+ photos in item_images (MP1 multiple photos)");
ok(count_q($conn, "SELECT COUNT(*) FROM users WHERE role='admin' AND status='active'") > 0, "an active admin exists");
ok(count_q($conn, "SELECT COUNT(*) FROM orders") > 0, "at least one order exists");
ok(count_q($conn, "SELECT COUNT(*) FROM reviews") > 0, "at least one review exists (MP4)");
ok(count_q($conn, "SELECT COUNT(*) FROM users WHERE status='deactivated'") > 0, "at least one user has been deactivated (MP2 demo)");

head("Files");
$files = ['item/shop.php','item/details.php','item/index.php','item/create.php','item/edit.php','item/delete.php',
 'item/view_cart.php','item/cart_add.php','item/cart_update.php','item/checkout.php','item/my_orders.php',
 'users/login.php','users/logout.php','users/register.php','users/profile.php',
 'admin/login.php','admin/orders.php','admin/manage_users.php',
 'includes/auth.php','includes/config.php','includes/header.php','includes/footer.php',
 'includes/mailer.php','includes/mail_config.php','includes/simple_pdf.php','PHPMailer'];
foreach ($files as $f) ok(file_exists(__DIR__ . '/' . $f), $f);
foreach (['uploads/items','uploads/users'] as $d) ok(is_dir(__DIR__ . "/$d") && is_writable(__DIR__ . "/$d"), "folder $d exists and is writable");
ok(extension_loaded('openssl'), "OpenSSL extension on");

head("Code rules");
function src($f) { $p = __DIR__ . '/' . $f; return file_exists($p) ? file_get_contents($p) : null; }
foreach (['item/index.php','item/create.php','item/edit.php','item/delete.php','item/break_case.php','admin/orders.php','admin/manage_users.php'] as $f) {
    $s = src($f); if ($s === null) continue;
    ok(strpos($s, 'require_admin') !== false, "MP3: $f is protected by require_admin()");
}
foreach (['users/login.php','admin/login.php','users/register.php','item/create.php','item/edit.php'] as $f) {
    $s = src($f); if ($s === null) { ok(false, "MP5: $f is missing"); continue; }
    $bad = preg_match('/\srequired(\s|>|=)/i', $s) || preg_match('/type\s*=\s*["\'](email|number)["\']/i', $s);
    ok(!$bad && stripos($s, 'novalidate') !== false, "MP5: $f has no HTML5 validation (no required/type=email/number, has novalidate)");
}
$s = src('item/checkout.php');
ok($s !== null && strpos($s, 'mysqli_prepare') !== false && strpos($s, 'begin_transaction') !== false, "Term test: checkout uses prepared statements and a transaction");
ok($s !== null && strpos($s, '$item_id = 1') === false, "Term test: checkout is no longer hardcoded to item 1");
$all = (src('includes/functions.php') ?? '') . (src('item/details.php') ?? '');
ok(strpos($all, 'preg_replace') !== false, "MP4: regex bad-word filter exists");
$d = src('item/details.php');
ok($d !== null && stripos($d, 'order_items') !== false, "MP4: details.php checks the user actually bought the item");
$m = src('includes/mailer.php');
ok($m !== null && (strpos($m, 'addStringAttachment') !== false), "Term test: PDF attachment code exists");
ok(strpos((src('includes/auth.php') ?? ''), 'Please log in') !== false, "MP3: auth.php sets a redirect message");

head("Broken links (Unit 2)");
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
$hits = [];
foreach ($it as $file) {
    $p = $file->getPathname();
    if (substr($p, -4) !== '.php' || stripos($p, 'PHPMailer') !== false || stripos($p, 'audit.php') !== false) continue;
    $c = file_get_contents($p);
    if (preg_match('#/bobmart/user/|\.\./user/#', $c)) $hits[] = str_replace(__DIR__, '', $p);
    if (strpos($c, 'manage.php') !== false) $hits[] = str_replace(__DIR__, '', $p) . " (links to manage.php)";
}
ok(!$hits, "no links to /user/ or manage.php" . ($hits ? ": " . implode(', ', $hits) : ""));