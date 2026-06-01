<?php
session_start();

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: pages/login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit();
}

include 'includes/db.php';

// Search & filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories
$cats = $conn->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Cart item count
$cartCount = 0;
$cstmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
$cstmt->execute([$_SESSION['user_id']]);
$cartCount = (int)$cstmt->fetchColumn();

// Get username
$ustmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$ustmt->execute([$_SESSION['user_id']]);
$currentUser = $ustmt->fetchColumn();

$added = isset($_GET['added']) ? $_GET['added'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopNest – Browse Products</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="header-inner">
        <a href="index.php" class="logo">Shop<span>Nest</span></a>
        <nav>
            <span class="user-greeting">Hi, <?= htmlspecialchars($currentUser) ?>!</span>
            <a href="pages/cart.php">
                🛒 Cart <?php if ($cartCount > 0): ?><span class="nav-badge"><?= $cartCount ?></span><?php endif; ?>
            </a>
            <form method="POST" style="display:inline;">
                <button type="submit" name="logout" class="btn-logout">Logout</button>
            </form>
        </nav>
    </div>
</header>

<div class="search-bar-wrapper">
    <div class="search-bar-inner">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex:1;">
            <input type="text" name="search" class="search-input" placeholder="Search products…" value="<?= htmlspecialchars($search) ?>">
            <select name="category" class="filter-select">
                <option value="">All Categories</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary" style="width:auto;padding:10px 22px;margin:0;">Search</button>
            <?php if ($search || $category): ?>
                <a href="index.php" style="color:var(--text-muted);font-size:0.9em;white-space:nowrap;">Clear filters</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="main-wrapper">
    <div class="section-header">
        <h2 class="section-title">
            <?php if ($search || $category): ?>
                <?= count($products) ?> result<?= count($products) !== 1 ? 's' : '' ?> found
            <?php else: ?>
                All Products
            <?php endif; ?>
        </h2>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <h3>No products found</h3>
            <p>Try adjusting your search or <a href="index.php">browse everything</a>.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="product-img-wrap">
                        <?php if (!empty($p['image']) && file_exists("images/" . $p['image'])): ?>
                            <img src="images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <?php else: ?>
                            <div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:3em;">📦</div>
                        <?php endif; ?>
                        <span class="product-category"><?= htmlspecialchars($p['category']) ?></span>
                    </div>
                    <div class="product-body">
                        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="product-desc"><?= htmlspecialchars($p['description']) ?></div>
                        <div class="product-price">$<?= number_format($p['price'], 2) ?></div>
                        <form method="POST" action="pages/cart.php">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="redirect" value="../index.php">
                            <button type="submit" name="add_to_cart" class="btn-add-cart">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> <strong>ShopNest</strong>. All rights reserved.</p>
</footer>

<?php if ($added): ?>
<div class="toast" id="toast">✓ Item added to cart!</div>
<script>
    const t = document.getElementById('toast');
    setTimeout(() => t.classList.add('show'), 100);
    setTimeout(() => t.classList.remove('show'), 3000);
</script>
<?php endif; ?>

</body>
</html>
