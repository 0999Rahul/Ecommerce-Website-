<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
$user_id = $_SESSION['user_id'];

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch();
    if ($item) {
        $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?")->execute([$quantity, $user_id, $product_id]);
    } else {
        $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")->execute([$user_id, $product_id, $quantity]);
    }
    $redirect = $_POST['redirect'] ?? 'cart.php';
    header("Location: $redirect?added=1");
    exit();
}

// Remove from cart
if (isset($_POST['remove_from_cart'])) {
    $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$user_id, (int)$_POST['product_id']]);
    header("Location: cart.php");
    exit();
}

// Update quantity
if (isset($_POST['update_quantity'])) {
    $qty = max(1, (int)$_POST['quantity']);
    $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?")->execute([$qty, $user_id, (int)$_POST['product_id']]);
    header("Location: cart.php");
    exit();
}

// Fetch cart
$stmt = $conn->prepare("
    SELECT c.product_id, c.quantity, p.name, p.price, p.image, p.category
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));
$shipping = $subtotal > 0 ? 5.99 : 0;
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopNest – Your Cart</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <div class="header-inner">
        <a href="../index.php" class="logo">Shop<span>Nest</span></a>
        <nav>
            <a href="../index.php">← Continue Shopping</a>
        </nav>
    </div>
</header>

<div class="main-wrapper">
    <h1 class="admin-page-title">Your Cart <?php if (!empty($cart_items)): ?><span style="font-size:0.6em;color:var(--text-muted);">(<?= count($cart_items) ?> item<?= count($cart_items) !== 1 ? 's' : '' ?>)</span><?php endif; ?></h1>

    <?php if (empty($cart_items)): ?>
        <div class="empty-state">
            <div style="font-size:4em;">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything yet.</p>
            <a href="../index.php" class="btn-primary" style="display:inline-block;width:auto;padding:12px 28px;margin-top:10px;text-decoration:none;">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-container">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item-row">
                        <?php if (!empty($item['image']) && file_exists("../images/" . $item['image'])): ?>
                            <img src="../images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <?php else: ?>
                            <div style="width:90px;height:90px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:2em;flex-shrink:0;">📦</div>
                        <?php endif; ?>
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="cart-item-price">$<?= number_format($item['price'], 2) ?> each &nbsp;·&nbsp; <span style="color:var(--primary);font-weight:700;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span></div>
                        </div>
                        <div class="cart-item-actions">
                            <form method="POST" style="display:flex;align-items:center;gap:8px;">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                <button type="submit" name="update_quantity" class="btn-update">Update</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" name="remove_from_cart" class="btn-remove" onclick="return confirm('Remove this item?')">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal</span><span>$<?= number_format($subtotal, 2) ?></span></div>
                <div class="summary-row"><span>Shipping</span><span>$<?= number_format($shipping, 2) ?></span></div>
                <div class="summary-total"><span>Total</span><span>$<?= number_format($total, 2) ?></span></div>
                <a href="#" class="btn-checkout" onclick="alert('Checkout coming soon!');return false;">Proceed to Checkout</a>
                <a href="../index.php" class="btn-continue">← Continue Shopping</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> <strong>ShopNest</strong>. All rights reserved.</p>
</footer>
</body>
</html>
