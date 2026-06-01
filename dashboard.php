<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers    = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalOrders   = $conn->query("SELECT SUM(quantity) FROM cart")->fetchColumn() ?? 0;
$revenue       = $conn->query("SELECT SUM(p.price * c.quantity) FROM cart c JOIN products p ON c.product_id = p.id")->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopNest Admin – Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">Shop<span>Nest</span> <span style="font-size:0.55em;font-weight:400;color:rgba(255,255,255,0.5);">Admin</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="manage_products.php">📦 Manage Products</a></li>
            <li><a href="add_product.php">➕ Add Product</a></li>
            <li><a href="../index.php" target="_blank">🏪 View Store</a></li>
            <li><a href="logout.php" class="danger">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <h1 class="admin-page-title">Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?= $totalProducts ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Registered Users</div>
                <div class="stat-value"><?= $totalUsers ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Items in Carts</div>
                <div class="stat-value"><?= $totalOrders ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Cart Value</div>
                <div class="stat-value">$<?= number_format($revenue, 0) ?></div>
            </div>
        </div>

        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <a href="add_product.php" class="btn-primary" style="width:auto;padding:12px 24px;text-decoration:none;display:inline-block;">➕ Add New Product</a>
            <a href="manage_products.php" class="btn-primary" style="width:auto;padding:12px 24px;text-decoration:none;display:inline-block;background:var(--accent);">📦 Manage Products</a>
        </div>
    </main>
</div>
</body>
</html>
