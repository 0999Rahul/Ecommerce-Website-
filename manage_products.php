<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Handle delete
if (isset($_POST['delete_product'])) {
    $id = (int)$_POST['product_id'];
    // Remove from carts first
    $conn->prepare("DELETE FROM cart WHERE product_id = ?")->execute([$id]);
    $conn->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header("Location: manage_products.php?deleted=1");
    exit();
}

$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopNest Admin – Manage Products</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;align-items:center;justify-content:center; }
        .modal-overlay.active { display:flex; }
        .modal { background:white;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.2); }
        .modal h3 { margin:0 0 10px;font-size:1.2em; }
        .modal p { color:var(--text-muted);font-size:0.95em;margin:0 0 24px; }
        .modal-btns { display:flex;gap:12px;justify-content:center; }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">Shop<span>Nest</span> <span style="font-size:0.55em;font-weight:400;color:rgba(255,255,255,0.5);">Admin</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="manage_products.php" class="active">📦 Manage Products</a></li>
            <li><a href="add_product.php">➕ Add Product</a></li>
            <li><a href="../index.php" target="_blank">🏪 View Store</a></li>
            <li><a href="logout.php" class="danger">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <h1 class="admin-page-title">Manage Products</h1>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success" style="max-width:500px;">Product deleted successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success" style="max-width:500px;">Product saved successfully.</div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <h3>No products yet</h3>
                <p><a href="add_product.php">Add your first product</a></p>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td>
                        <?php if (!empty($p['image']) && file_exists("../images/" . $p['image'])): ?>
                            <img src="../images/<?= htmlspecialchars($p['image']) ?>" alt="">
                        <?php else: ?>
                            <div style="width:55px;height:55px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.5em;">📦</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><small style="color:var(--text-muted);"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 60)) ?>…</small></td>
                    <td><?= htmlspecialchars($p['category']) ?></td>
                    <td><strong>$<?= number_format($p['price'], 2) ?></strong></td>
                    <td>
                        <div class="table-actions">
                            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-sm btn-edit">Edit</a>
                            <button class="btn-sm btn-delete" onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </main>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div style="font-size:2.5em;margin-bottom:10px;">🗑️</div>
        <h3>Delete Product?</h3>
        <p id="deleteModalText">This action cannot be undone.</p>
        <div class="modal-btns">
            <button class="btn-sm btn-edit" style="padding:10px 22px;font-size:0.9em;" onclick="closeModal()">Cancel</button>
            <form method="POST" style="display:inline;" id="deleteForm">
                <input type="hidden" name="product_id" id="deleteProductId">
                <button type="submit" name="delete_product" class="btn-sm btn-delete" style="padding:10px 22px;font-size:0.9em;">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteProductId').value = id;
    document.getElementById('deleteModalText').textContent = `Delete "${name}"? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.add('active');
}
function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
