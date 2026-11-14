<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';
$page_title = 'Order placed';
require_login();

$orderId = (int) ($_GET['order_id'] ?? 0);
$order = db_fetch_one(
    $conn,
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    'ii',
    [$orderId, $_SESSION['user_id']]
);

if (!$order) redirect('index.php');

require __DIR__ . '/includes/header.php';
?>
<div class="empty-state">
    <h1>🎉 Order placed successfully!</h1>
    <p>Your order #<?php echo $order['id']; ?> has been received and is now <strong><?php echo e($order['status']); ?></strong>.</p>
    <p>Total: <strong><?php echo format_inr($order['total']); ?></strong></p>
    <p>
        Payment: <strong><?php echo e(['cod' => 'Cash on Delivery', 'card' => 'Card', 'paypal' => 'PayPal'][$order['payment_method']] ?? $order['payment_method']); ?></strong>
        &mdash; <span class="badge badge-<?php echo $order['payment_status'] === 'paid' ? 'completed' : 'pending'; ?>"><?php echo e($order['payment_status']); ?></span>
        <?php if ($order['card_last4']): ?> (card ending <?php echo e($order['card_last4']); ?>)<?php endif; ?>
    </p>
    <?php if ($order['transaction_id']): ?>
        <p class="stock-note">Transaction ID: <?php echo e($order['transaction_id']); ?></p>
    <?php endif; ?>
    <a class="btn btn-primary" href="orders.php">View my orders</a>
    <a class="btn btn-outline" href="index.php" style="margin-left:10px;">Continue shopping</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
