<?php
include '../includes/db.php';
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials or insufficient permissions.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopNest Admin – Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page" style="background:linear-gradient(135deg, #1e293b 0%, #3730a3 100%);">
    <div class="auth-card">
        <div class="logo-text">Shop<span>Nest</span></div>
        <h2>Admin Portal</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" placeholder="admin@shopnest.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Admin password" required>
            </div>
            <button type="submit" name="login" class="btn-primary" style="background:var(--primary-dark);">Sign In as Admin</button>
        </form>

        <div class="auth-link">
            <a href="../pages/login.php">← Back to Store</a>
        </div>
    </div>
</body>
</html>
