<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

$success = '';
$error = '';

if (isset($_POST['add_product'])) {
    $name        = trim($_POST['name']);
    $price       = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $category    = trim($_POST['category']);
    $imageName   = '';

    if (empty($name) || $price <= 0) {
        $error = "Product name and a valid price are required.";
    } else {
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Only image files (jpg, png, gif, webp) are allowed.";
            } else {
                $imageName = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], "../images/$imageName");
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, description, category, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $description, $category, $imageName]);
            header("Location: manage_products.php?saved=1");
            exit();
        }
    }
}

$categories = ['Electronics', 'Footwear', 'Accessories', 'Lifestyle', 'Clothing', 'Books', 'General'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopNest Admin – Add Product</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">Shop<span>Nest</span> <span style="font-size:0.55em;font-weight:400;color:rgba(255,255,255,0.5);">Admin</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="manage_products.php">📦 Manage Products</a></li>
            <li><a href="add_product.php" class="active">➕ Add Product</a></li>
            <li><a href="../index.php" target="_blank">🏪 View Store</a></li>
            <li><a href="logout.php" class="danger">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <h1 class="admin-page-title">Add New Product</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="max-width:600px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="admin-form">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" placeholder="e.g. Wireless Earbuds" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Price ($) *</label>
                    <input type="number" name="price" step="0.01" min="0.01" placeholder="29.99" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Describe the product…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*">
                    <small style="color:var(--text-muted);">JPG, PNG, GIF, or WEBP. Optional.</small>
                </div>
                <div style="display:flex;gap:14px;align-items:center;margin-top:8px;">
                    <button type="submit" name="add_product" class="btn-submit">Add Product</button>
                    <a href="manage_products.php" style="color:var(--text-muted);font-size:0.9em;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
