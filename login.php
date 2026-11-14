<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';
$page_title = 'Login';
$error = '';

if (!empty($_SESSION['login_notice'])) {
    $error = $_SESSION['login_notice'];
    unset($_SESSION['login_notice']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = db_fetch_one($conn, "SELECT * FROM users WHERE email = ?", 's', [$email]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];
        redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
    } else {
        $error = 'Invalid email or password.';
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="auth-card">
    <h1 class="page-title">Log in</h1>
    <?php if ($error): ?><div class="form-error"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>
    <p class="muted-link">No account yet? <a href="register.php">Register</a></p>
    <p class="muted-link" style="font-size:12px;">Demo admin: admin@example.com / admin123</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
