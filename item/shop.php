<?php
session_start();
include('../includes/auth.php');
include('../includes/config.php');
include('../includes/functions.php');

$q = trim($_GET['q'] ?? ''); $rarity = trim($_GET['rarity'] ?? ''); $sort = $_GET['sort'] ?? 'new';
$orderBy = ['new'=>'i.id DESC','low'=>'i.sell_price ASC','high'=>'i.sell_price DESC','top'=>'avg_rating DESC'][$sort] ?? 'i.id DESC';
$page = max(1, (int)($_GET['page'] ?? 1)); $per = 12; $off = ($page - 1) * $per;

$sql = "SELECT i.id, i.name, i.series_name, i.rarity, i.sell_price, s.quantity,
        (SELECT img_path FROM item_images WHERE item_id=i.id ORDER BY image_id LIMIT 1) AS img,
        (SELECT ROUND(AVG(rating),1) FROM reviews WHERE item_id=i.id) AS avg_rating,
        (SELECT COALESCE(SUM(quantity),0) FROM order_items WHERE item_id=i.id) AS sold
        FROM items i JOIN stock s ON s.item_id=i.id
        WHERE i.status='Active' AND (i.name LIKE ? OR i.series_name LIKE ?)";
$like = "%$q%"; $types = "ss"; $params = [$like, $like];
if ($rarity !== '') { $sql .= " AND i.rarity=?"; $types .= "s"; $params[] = $rarity; }
$sql .= " ORDER BY $orderBy LIMIT $per OFFSET $off";      // $orderBy is whitelisted above

$st = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($st, $types, ...$params);
mysqli_stmt_execute($st);
$items = mysqli_stmt_get_result($st);

include('../includes/header.php');
?>
<div class="dashboard-container">
  <h2 class="header-title">Shop</h2>
  <form method="GET" novalidate style="display:flex;gap:8px;margin-bottom:20px">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search character or series">
    <input type="text" name="rarity" value="<?= e($rarity) ?>" placeholder="Rarity (e.g. Secret)">
    <select name="sort">
      <option value="new">Newest</option><option value="low">Price: Low to High</option>
      <option value="high">Price: High to Low</option><option value="top">Top Rated</option>
    </select>
    <button class="btn-primary">Search</button>
  </form>

  <div class="product-grid">
  <?php while ($p = mysqli_fetch_assoc($items)): ?>
    <a class="product-card" href="details.php?id=<?= $p['id'] ?>">
      <img src="/bobmart/<?= e(item_image((int)$p['id'], $p['img'])) ?>" alt="<?= e($p['name']) ?>">
      <div class="pc-body">
        <div class="pc-name"><?= e($p['name']) ?></div>
        <div class="pc-series"><?= e($p['series_name']) ?> · <?= e($p['rarity']) ?></div>
        <div class="pc-price">$<?= number_format($p['sell_price'], 2) ?></div>
        <div class="pc-meta">★ <?= $p['avg_rating'] ?? '–' ?> · <?= (int)$p['sold'] ?> sold
          <?= $p['quantity'] < 1 ? ' · <b style="color:#f43f5e">Sold out</b>' : '' ?></div>
      </div>
    </a>
  <?php endwhile; ?>
  </div>

  <div style="text-align:center;margin-top:20px">
    <?php if ($page > 1): ?><a href="?<?= http_build_query(['q'=>$q,'rarity'=>$rarity,'sort'=>$sort,'page'=>$page-1]) ?>">← Prev</a><?php endif; ?>
    <a href="?<?= http_build_query(['q'=>$q,'rarity'=>$rarity,'sort'=>$sort,'page'=>$page+1]) ?>">Next →</a>
  </div>
</div>
<?php include('../includes/footer.php'); ?>