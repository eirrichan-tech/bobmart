<?php
session_start();
include('../includes/auth.php');
include('../includes/config.php');
include('../includes/functions.php');

$item_id = (int)($_GET['id'] ?? 0);
$st = mysqli_prepare($conn, "SELECT i.*, s.quantity FROM items i JOIN stock s ON i.id=s.item_id WHERE i.id=?");
mysqli_stmt_bind_param($st, "i", $item_id); mysqli_stmt_execute($st);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (!$product) die("Product not found.");

$uid = $_SESSION['user_id'] ?? 0;
$errors = [];

// Did this user buy it? (use o.status='Delivered' for a stricter rule)
$can_review = false; $my = null;
if ($uid) {
    $c = mysqli_prepare($conn, "SELECT 1 FROM orders o JOIN order_items oi ON oi.order_id=o.order_id
                                WHERE o.user_id=? AND oi.item_id=? AND o.status<>'Cancelled' LIMIT 1");
    mysqli_stmt_bind_param($c, "ii", $uid, $item_id); mysqli_stmt_execute($c);
    $can_review = (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($c));

    $m = mysqli_prepare($conn, "SELECT rating, comment FROM reviews WHERE item_id=? AND user_id=?");
    mysqli_stmt_bind_param($m, "ii", $item_id, $uid); mysqli_stmt_execute($m);
    $my = mysqli_fetch_assoc(mysqli_stmt_get_result($m));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_review'])) {
    require_login();
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if (!$can_review) $errors[] = "Only customers who purchased this product can review it.";
    if ($rating < 1 || $rating > 5) $errors[] = "Choose a rating from 1 to 5.";
    if ($comment === '') $errors[] = "Comment is required.";
    elseif (strlen($comment) > 500) $errors[] = "Comment must be 500 characters or fewer.";

    if (!$errors) {
        $r = mysqli_prepare($conn, "INSERT INTO reviews (item_id,user_id,rating,comment) VALUES (?,?,?,?)
                                    ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)");
        mysqli_stmt_bind_param($r, "iiis", $item_id, $uid, $rating, $comment);
        mysqli_stmt_execute($r);
        $_SESSION['success'] = $my ? "Review updated." : "Review posted.";
        header("Location: details.php?id=$item_id"); exit;
    }
}

$imgs = mysqli_prepare($conn, "SELECT img_path FROM item_images WHERE item_id=? ORDER BY image_id");
mysqli_stmt_bind_param($imgs, "i", $item_id); mysqli_stmt_execute($imgs);
$images = mysqli_fetch_all(mysqli_stmt_get_result($imgs), MYSQLI_ASSOC);

// Fixed join using u.id and order by created_at
$rv = mysqli_prepare($conn, "SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.item_id=? ORDER BY r.created_at DESC");
mysqli_stmt_bind_param($rv, "i", $item_id); mysqli_stmt_execute($rv);
$reviews = mysqli_stmt_get_result($rv);
include('../includes/header.php');
?>
<div class="card-container" style="max-width:800px;margin:auto">
  <a href="shop.php" class="back-btn">&larr; Back to Shop</a>
  <!-- gallery (MP1 view multiple photos) -->
  <?php if ($images): ?>
    <img id="mainImg" src="/bobmart/<?= e($images[0]['img_path']) ?>" style="width:100%;max-height:380px;object-fit:contain;border-radius:12px">
    <div style="display:flex;gap:8px;margin:10px 0">
      <?php foreach ($images as $im): ?>
        <img src="/bobmart/<?= e($im['img_path']) ?>" width="70" height="70" style="object-fit:cover;border-radius:8px;cursor:pointer"
             onclick="document.getElementById('mainImg').src=this.src">
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h2 class="header-title"><?= e($product['name']) ?></h2>
  <p class="header-subtitle"><?= e($product['series_name']) ?> | <?= e($product['rarity']) ?></p>
  <p style="font-size:24px;color:#ee4d2d;font-weight:800">$<?= number_format($product['sell_price'], 2) ?></p>
  <p>Stock: <?= (int)$product['quantity'] ?> boxes</p>

  <?php if ($product['quantity'] > 0 && $product['status'] === 'Active'): ?>
  <form action="cart_add.php" method="POST" novalidate style="display:flex;gap:8px;margin:12px 0">
    <input type="hidden" name="item_id" value="<?= $item_id ?>">
    <input type="text" name="qty" value="1" style="width:70px">
    <button class="btn-primary">Add to Cart</button>
  </form>
  <?php endif; ?>

  <hr>
  <h3>Customer Reviews</h3>
  <?php if ($errors): ?><div style="color:#f43f5e"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
  <?php if (mysqli_num_rows($reviews) === 0): ?><p>No reviews yet.</p><?php endif; ?>
  <?php while ($rev = mysqli_fetch_assoc($reviews)): ?>
    <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-top:10px">
      <strong><?= e($rev['name']) ?></strong> <span style="color:#f59e0b"><?= str_repeat('★', $rev['rating']) ?></span>
      <p><?= e(filterFoulWords($rev['comment'])) ?></p>
      <small><?= e($rev['created_at']) ?></small>
    </div>
  <?php endwhile; ?>

  <?php if ($uid && $can_review): ?>
    <h4><?= $my ? 'Edit your review' : 'Leave a review' ?></h4>
    <form method="POST" novalidate>
      <select name="rating">
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <option value="<?= $i ?>" <?= ($my['rating'] ?? 5) == $i ? 'selected' : '' ?>><?= $i ?> ★</option>
        <?php endfor; ?>
      </select>
      <textarea name="comment" rows="3"><?= e($my['comment'] ?? '') ?></textarea>
      <button name="save_review" value="1" class="btn-submit"><?= $my ? 'Update Review' : 'Submit Review' ?></button>
    </form>
  <?php elseif ($uid): ?>
    <p style="font-size:13px">Only customers who purchased this product can leave a review.</p>
  <?php else: ?>
    <p><a href="/bobmart/users/login.php">Sign in</a> to review.</p>
  <?php endif; ?>
</div>
<?php include('../includes/footer.php'); ?>