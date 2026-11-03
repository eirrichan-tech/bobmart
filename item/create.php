<?php
session_start();
include('../includes/auth.php');
include('../includes/config.php');
include('../includes/functions.php');
require_admin();

$errors = []; 
$v = [
    'name' => '',
    'series_name' => '',
    'rarity' => '',
    'cost_price' => '',
    'sell_price' => '',
    'stock_boxes' => '0',
    'stock_cases' => '0'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) {
        $v[$k] = trim($_POST[$k] ?? '');
    }

    if ($v['name'] === '') {
        $errors['name'] = "Name is required.";
    }
    if ($v['series_name'] === '') {
        $errors['series_name'] = "Series is required.";
    }
    if ($v['rarity'] === '') {
        $errors['rarity'] = "Rarity is required.";
    }
    if (!is_numeric($v['cost_price']) || $v['cost_price'] < 0) {
        $errors['cost_price'] = "Cost must be a number ≥ 0.";
    }
    if (!is_numeric($v['sell_price']) || $v['sell_price'] <= 0) {
        $errors['sell_price'] = "Price must be greater than 0.";
    }
    if (!ctype_digit($v['stock_boxes'])) {
        $errors['stock_boxes'] = "Boxes must be a whole number.";
    }
    if (!ctype_digit($v['stock_cases'])) {
        $errors['stock_cases'] = "Cases must be a whole number.";
    }

    $imgErrors = [];
    $paths = isset($_FILES['images']) ? save_uploads($_FILES['images'], 'items', $imgErrors) : [];
    if ($imgErrors) {
        $errors['images'] = implode(' ', $imgErrors);
    }
    if (!$paths && !$errors) {
        $errors['images'] = "Upload at least one photo.";
    }

    if (!$errors) {
        mysqli_begin_transaction($conn);
        try {
            $st = mysqli_prepare($conn, "INSERT INTO items (name, series_name, rarity, cost_price, sell_price, status) VALUES (?,?,?,?,?,'Active')");
            $cost = (float)$v['cost_price']; 
            $sell = (float)$v['sell_price'];
            mysqli_stmt_bind_param($st, "sssdd", $v['name'], $v['series_name'], $v['rarity'], $cost, $sell);
            mysqli_stmt_execute($st);
            
            $item_id = mysqli_insert_id($conn);
            
            $st = mysqli_prepare($conn, "INSERT INTO stock (item_id, quantity, stock_cases) VALUES (?,?,?)");
            $qty = (int)$v['stock_boxes']; 
            $cases = (int)$v['stock_cases'];
            mysqli_stmt_bind_param($st, "iii", $item_id, $qty, $cases);
            mysqli_stmt_execute($st);
            
            $st = mysqli_prepare($conn, "INSERT INTO item_images (item_id, img_path) VALUES (?,?)");
            foreach ($paths as $p) { 
                mysqli_stmt_bind_param($st, "is", $item_id, $p); 
                mysqli_stmt_execute($st); 
            }
            
            mysqli_commit($conn);
            $_SESSION['success'] = "Product added.";
            header("Location: index.php"); 
            exit;
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            $errors['general'] = "Could not save product: " . $ex->getMessage();
        }
    }
}
include('../includes/header.php');
?>

<div class="card-container" style="max-width: 600px; margin: 40px auto; background: rgba(15, 23, 42, 0.95); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); color: #f8fafc;">
    <h2 class="header-title" style="margin-bottom: 20px; font-size: 24px; color: #f8fafc;">Add New Product</h2>

    <?php if (isset($errors['general'])): ?>
        <div style="color: #f43f5e; background: rgba(244, 63, 94, 0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; border: 1px solid rgba(244,63,94,0.2);">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form action="create.php" method="POST" enctype="multipart/form-data" novalidate>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Character & Series Name</label>
            <input type="text" name="name" value="<?= e($v['name']) ?>" placeholder="e.g., Hirono: The Other One" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['name'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['name']) ?></small><?php endif; ?>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Series Line</label>
            <input type="text" name="series_name" value="<?= e($v['series_name']) ?>" placeholder="e.g., Hirono Series 01" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['series_name'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['series_name']) ?></small><?php endif; ?>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Rarity</label>
            <input type="text" name="rarity" value="<?= e($v['rarity']) ?>" placeholder="e.g., Regular / Secret" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['rarity'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['rarity']) ?></small><?php endif; ?>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Cost Price ($)</label>
                <input type="text" name="cost_price" value="<?= e($v['cost_price']) ?>" placeholder="450.00" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['cost_price'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['cost_price']) ?></small><?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Sell Price ($)</label>
                <input type="text" name="sell_price" value="<?= e($v['sell_price']) ?>" placeholder="600.00" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['sell_price'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['sell_price']) ?></small><?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="margin-bottom: 20px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Initial Stock (Boxes)</label>
                <input type="text" name="stock_boxes" value="<?= e($v['stock_boxes']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['stock_boxes'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['stock_boxes']) ?></small><?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom: 20px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Initial Cases</label>
                <input type="text" name="stock_cases" value="<?= e($v['stock_cases']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['stock_cases'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['stock_cases']) ?></small><?php endif; ?>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Photos (select one or more)</label>
            <input type="file" name="images[]" multiple accept="image/*" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['images'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['images']) ?></small><?php endif; ?>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary" style="flex: 1; padding: 12px; border: none; border-radius: 6px; font-weight: bold; background: #38bdf8; color: #0f172a; cursor: pointer;">Add Product</button>
            <a href="index.php" style="flex: 1; text-align: center; padding: 12px; border-radius: 6px; background: #334155; color: #f8fafc; text-decoration: none; font-weight: bold;">Cancel</a>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>