<?php
session_start();
include('../includes/auth.php');
require_admin(); // Protect editing for admins only
include('../includes/config.php');
include('../includes/functions.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

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

// Fetch existing item and stock details first to prepopulate form/validation
$stmt = mysqli_prepare($conn, "SELECT i.*, s.quantity, s.stock_cases FROM items i LEFT JOIN stock s ON i.id = s.item_id WHERE i.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);

if (!$item) {
    header("Location: index.php");
    exit;
}

// Pre-fill $v with existing item data on initial load
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $v['name'] = $item['name'];
    $v['series_name'] = $item['series_name'];
    $v['rarity'] = $item['rarity'];
    $v['cost_price'] = $item['cost_price'] ?? '0';
    $v['sell_price'] = $item['sell_price'] ?? '0';
    $v['stock_boxes'] = $item['quantity'] ?? '0';
    $v['stock_cases'] = $item['stock_cases'] ?? '0';
}

// Handle Form Submission for Update
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

    if (!$errors) {
        mysqli_begin_transaction($conn);
        try {
            // 1. Update item details in items table
            $st = mysqli_prepare($conn, "UPDATE items SET name = ?, series_name = ?, rarity = ?, cost_price = ?, sell_price = ? WHERE id = ?");
            $cost = (float)$v['cost_price']; 
            $sell = (float)$v['sell_price'];
            mysqli_stmt_bind_param($st, "sssddi", $v['name'], $v['series_name'], $v['rarity'], $cost, $sell, $id);
            mysqli_stmt_execute($st);

            // 2. Update stock quantity in stock table
            $st = mysqli_prepare($conn, "UPDATE stock SET quantity = ?, stock_cases = ? WHERE item_id = ?");
            $qty = (int)$v['stock_boxes']; 
            $cases = (int)$v['stock_cases'];
            mysqli_stmt_bind_param($st, "iii", $qty, $cases, $id);
            mysqli_stmt_execute($st);

            // 3. Remove ticked photos
            foreach (($_POST['delete_img'] ?? []) as $img_id) {
                $img_id = (int)$img_id;
                $st = mysqli_prepare($conn, "SELECT img_path FROM item_images WHERE image_id=? AND item_id=?");
                mysqli_stmt_bind_param($st, "ii", $img_id, $id); 
                mysqli_stmt_execute($st);
                if ($row = mysqli_fetch_assoc(mysqli_stmt_get_result($st))) {
                    @unlink(__DIR__ . '/../' . $row['img_path']);
                    $d = mysqli_prepare($conn, "DELETE FROM item_images WHERE image_id=?");
                    mysqli_stmt_bind_param($d, "i", $img_id); 
                    mysqli_stmt_execute($d);
                }
            }

            // 4. Add new photos
            $imgErrors = [];
            $new = isset($_FILES['images']) ? save_uploads($_FILES['images'], 'items', $imgErrors) : [];
            if ($imgErrors) {
                $errors['images'] = implode(' ', $imgErrors);
            } else {
                $st = mysqli_prepare($conn, "INSERT INTO item_images (item_id, img_path) VALUES (?,?)");
                foreach ($new as $p) { 
                    mysqli_stmt_bind_param($st, "is", $id, $p); 
                    mysqli_stmt_execute($st); 
                }
            }

            if (!$errors) {
                mysqli_commit($conn);
                $_SESSION['success'] = "Product updated successfully.";
                header("Location: index.php");
                exit;
            } else {
                mysqli_rollback($conn);
            }
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            $errors['general'] = "Could not update product: " . $ex->getMessage();
        }
    }
}

include('../includes/header.php');
?>

<div class="card-container" style="max-width: 600px; margin: 40px auto; background: rgba(15, 23, 42, 0.95); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); color: #f8fafc;">
    <h2 class="header-title" style="margin-bottom: 20px; font-size: 24px; color: #f8fafc;">Edit Product: <?= e($item['name']) ?></h2>

    <?php if (isset($errors['general'])): ?>
        <div style="color: #f43f5e; background: rgba(244, 63, 94, 0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; border: 1px solid rgba(244,63,94,0.2);">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form action="edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" novalidate>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Character & Series Name</label>
            <input type="text" name="name" value="<?= e($v['name']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['name'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['name']) ?></small><?php endif; ?>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Series Line</label>
            <input type="text" name="series_name" value="<?= e($v['series_name']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['series_name'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['series_name']) ?></small><?php endif; ?>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Rarity</label>
            <input type="text" name="rarity" value="<?= e($v['rarity']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['rarity'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['rarity']) ?></small><?php endif; ?>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Cost Price ($)</label>
                <input type="text" name="cost_price" value="<?= e($v['cost_price']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['cost_price'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['cost_price']) ?></small><?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Sell Price ($)</label>
                <input type="text" name="sell_price" value="<?= e($v['sell_price']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['sell_price'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['sell_price']) ?></small><?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="margin-bottom: 20px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Stock Quantity (Boxes)</label>
                <input type="text" name="stock_boxes" value="<?= e($v['stock_boxes']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['stock_boxes'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['stock_boxes']) ?></small><?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom: 20px; flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Stock Cases</label>
                <input type="text" name="stock_cases" value="<?= e($v['stock_cases']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
                <?php if (isset($errors['stock_cases'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['stock_cases']) ?></small><?php endif; ?>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #cbd5e1;">Existing Gallery Photos (Tick to remove)</label>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                <?php
                $im = mysqli_prepare($conn, "SELECT image_id, img_path FROM item_images WHERE item_id=?");
                mysqli_stmt_bind_param($im, "i", $id); 
                mysqli_stmt_execute($im);
                $imgs = mysqli_stmt_get_result($im);
                while ($g = mysqli_fetch_assoc($imgs)): 
                ?>
                    <label style="display:inline-block; text-align:center; background:#1e293b; padding:8px; border-radius:8px; border:1px solid #334155;">
                        <img src="/bobmart/<?= e($g['img_path']) ?>" width="80" height="80" style="object-fit:cover; border-radius:6px;"><br>
                        <input type="checkbox" name="delete_img[]" value="<?= $g['image_id'] ?>" style="margin-top:5px;"> <span style="font-size:12px; color:#f43f5e;">remove</span>
                    </label>
                <?php endwhile; ?>
            </div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #cbd5e1;">Add More Photos</label>
            <input type="file" name="images[]" multiple accept="image/*" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff;">
            <?php if (isset($errors['images'])): ?><small style="color:#f43f5e; display:block; margin-top:5px;"><?= e($errors['images']) ?></small><?php endif; ?>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary" style="flex: 1; padding: 12px; border: none; border-radius: 6px; font-weight: bold; background: #38bdf8; color: #0f172a; cursor: pointer;">Save Changes</button>
            <a href="index.php" style="flex: 1; text-align: center; padding: 12px; border-radius: 6px; background: #334155; color: #f8fafc; text-decoration: none; font-weight: bold;">Cancel</a>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>