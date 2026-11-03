<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// MP4: bad-word filter using ONE regex pattern (no array)
function filterFoulWords(string $text): string {
    $pattern = '/\b('
        . 'scam(s|mer|mers|med|ming)?|fake(s|d)?|fraud(s|ster)?|counterfeit|bootleg|knock-?off|rip-?off|'
        . 'trash(y)?|garbage|junk|worthless|useless|horrible|'
        . 'f[u*@]ck(s|ed|er|ers|ing)?|sh[i1!]t(s|ty|ting)?|b[i1!]tch(es|y)?|bastards?|a[s$]{2}holes?|dick(head)?s?|'
        . 'crap(py)?|damn(ed|it)?|piss(ed)?|slut(s|ty)?|whores?|idiots?|stupid|dumb(ass)?|moron(s|ic)?|jerks?|'
        . 'puta|put(a|ang)\s*ina(\s*mo)?|tangina|tang\s*ina|gago|gaga|tanga|bobo|ulol|tarantado|siraulo|'
        . 'leche|lintik|hayop|bwisit|buwisit|pakyu|pucha|punyeta|inutil|walanghiya'
        . ')\b/i';

    return preg_replace_callback($pattern, function ($m) {
        return str_repeat('*', strlen($m[0]));     // asterisks, same length as the word
    }, $text);
}

function item_image(int $id, ?string $dbPath = null): string {
    if ($dbPath) return $dbPath;
    foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
        if (file_exists(__DIR__ . "/../uploads/items/b{$id}.{$ext}")) {
            return "uploads/items/b{$id}.{$ext}";
        }
    }
    return 'uploads/items/default.png';
}

// Reusable image uploader (single or multiple files)
function save_uploads(array $f, string $subdir, array &$errors, int $max = 2097152): array {
    $saved = []; $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $dir = __DIR__ . '/../uploads/' . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    foreach ((array)$f['name'] as $i => $name) {
        $err  = is_array($f['error']) ? $f['error'][$i] : $f['error'];
        if ($err === UPLOAD_ERR_NO_FILE) continue;
        $tmp  = is_array($f['tmp_name']) ? $f['tmp_name'][$i] : $f['tmp_name'];
        $size = is_array($f['size']) ? $f['size'][$i] : $f['size'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($err !== UPLOAD_ERR_OK || !in_array($ext, $allowed) || $size > $max || !@getimagesize($tmp)) {
            $errors[] = "Invalid image: " . $name . " (jpg/png/webp, max 2MB)"; continue;
        }
        $new = uniqid('', true) . '.' . $ext;
        if (move_uploaded_file($tmp, $dir . $new)) $saved[] = "uploads/$subdir/$new";
    }
    return $saved;
}

// Cart rows with live price and stock from the DB
function cart_rows($conn): array {
    $rows = [];
    foreach (($_SESSION['cart'] ?? []) as $id => $qty) {
        $st = mysqli_prepare($conn, "SELECT i.id, i.name, i.sell_price, s.quantity AS stock,
            (SELECT img_path FROM item_images WHERE item_id=i.id ORDER BY image_id LIMIT 1) AS img
            FROM items i JOIN stock s ON s.item_id=i.id WHERE i.id=? AND i.status='Active'");
        mysqli_stmt_bind_param($st, "i", $id);
        mysqli_stmt_execute($st);
        if ($r = mysqli_fetch_assoc(mysqli_stmt_get_result($st))) {
            $r['qty'] = min((int)$qty, (int)$r['stock']);
            $r['subtotal'] = $r['qty'] * $r['sell_price'];
            $rows[] = $r;
        }
    }
    return $rows;
}