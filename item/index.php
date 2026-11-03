<?php
session_start();
include('../includes/auth.php');
include('../includes/config.php');
include('../includes/header.php');

$sql = "SELECT i.*, s.quantity, s.stock_cases FROM items i JOIN stock s ON i.id = s.item_id";
$result = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 class="header-title">Inventory Catalog</h2>
            <p class="header-subtitle">Manage dual-layer SKU stock, series, and statuses.</p>
        </div>
        <a href="create.php" class="btn-primary">+ Add New Product</a>
    </div>

    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Character & Series</th>
                <th>Rarity</th>
                <th>Price</th>
                <th>Cases</th>
                <th>Boxes</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= $item['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                    <span style="color: #64748b; font-size: 12px;"><?= htmlspecialchars($item['series_name']) ?></span>
                </td>
                <td>
                    <span class="badge badge-<?= strtolower($item['rarity'] == 'Secret/Chase' ? 'secret' : $item['rarity']) ?>">
                        <?= $item['rarity'] ?>
                    </span>
                </td>
                <td>$<?= number_format($item['sell_price'], 2) ?></td>
                <td><?= $item['stock_cases'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= htmlspecialchars($item['status']) ?></td>
                <td class="action-links">
                    <?php if ($item['stock_cases'] > 0): ?>
                        <a href="break_case.php?id=<?= $item['id'] ?>">Break</a>
                    <?php endif; ?>
                    <a href="edit.php?id=<?= $item['id'] ?>">Edit</a>
                    <a href="delete.php?id=<?= $item['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php 
include('../includes/footer.php'); 
?>