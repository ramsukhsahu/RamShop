<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';
$page_title = 'My Orders';
require_login();

$orders = db_fetch_all($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", 'i', [$_SESSION['user_id']]);

require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">My Orders</h1>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        <h3>No orders yet</h3>
        <a class="btn btn-primary" href="index.php">Start shopping</a>
    </div>
<?php else: ?>
    <table class="admin-table">
        <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
        <tbody>
        <?php $methodLabels = ['cod' => 'Cash on Delivery', 'card' => 'Card', 'paypal' => 'PayPal']; ?>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?php echo $o['id']; ?></td>
                <td><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                <td><?php echo format_inr($o['total']); ?></td>
                <td>
                    <?php echo e($methodLabels[$o['payment_method']] ?? $o['payment_method']); ?>
                    <span class="badge badge-<?php echo $o['payment_status'] === 'paid' ? 'completed' : 'pending'; ?>" style="margin-left:4px;"><?php echo e($o['payment_status']); ?></span>
                </td>
                <td><span class="badge badge-<?php echo e($o['status']); ?>"><?php echo e($o['status']); ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
