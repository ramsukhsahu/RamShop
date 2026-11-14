<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';
$page_title = 'Register';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $existing = db_fetch_one($conn, "SELECT id FROM users WHERE email = ?", 's', [$email]);
        if ($existing) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db_query(
                $conn,
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')",
                'sss',
                [$name, $email, $hash]
            );

            $_SESSION['user_id']   = $conn->insert_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['role']      = 'customer';
            $stmt->close();
            redirect('index.php');
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="auth-card">
    <h1 class="page-title">Create an account</h1>
    <?php if ($error): ?><div class="form-error"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Full name</label>
            <input type="text" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
    <p class="muted-link">Already have an account? <a href="login.php">Log in</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
