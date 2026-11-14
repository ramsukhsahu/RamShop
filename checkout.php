<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';
$page_title = 'Checkout';
require_login();

$items = cart_items($conn);
if (empty($items)) {
    redirect('cart.php');
}

// Defensive check: if the logged-in session refers to a user id that no
// longer exists (e.g. the database was reset/re-imported after login),
// the orders.user_id foreign key would fail. Catch that here with a clear
// message instead of a raw SQL error, and force a fresh login.
$currentUser = db_fetch_one($conn, "SELECT id, name FROM users WHERE id = ?", 'i', [$_SESSION['user_id']]);
if (!$currentUser) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['login_notice'] = 'Your session expired. Please log in again to continue checkout.';
    redirect('login.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $payment_status = $_POST['payment_status'] ?? 'pending';
    $transaction_id = $_POST['transaction_id'] ?? null;
    $card_last4     = $_POST['card_last4'] ?? null;

    $allowedMethods = ['cod', 'card', 'paypal'];
    if (!in_array($payment_method, $allowedMethods, true)) $payment_method = 'cod';
    if (!in_array($payment_status, ['pending', 'paid'], true)) $payment_status = 'pending';

    // Server-side safety net: card/paypal orders must have already been
    // confirmed by ajax/process_payment.php (payment_status = paid) before
    // we accept them here. This stops someone from bypassing the AJAX step
    // and submitting the form directly.
    if (in_array($payment_method, ['card', 'paypal'], true) && $payment_status !== 'paid') {
        $error = 'Payment was not completed. Please try again.';
    } elseif ($full_name === '' || $email === '' || $address === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $conn->begin_transaction();
        try {
            $total = cart_total($conn);

            $stmt = $conn->prepare(
                "INSERT INTO orders (user_id, full_name, email, phone, address, total, status, payment_method, payment_status, transaction_id, card_last4)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)"
            );
            $userId = (int) $_SESSION['user_id'];
            $stmt->bind_param(
                'issssdssss',
                $userId, $full_name, $email, $phone, $address, $total,
                $payment_method, $payment_status, $transaction_id, $card_last4
            );
            $stmt->execute();
            $orderId = $conn->insert_id;
            $stmt->close();

            $itemStmt  = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            $stockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($items as $item) {
                $itemStmt->bind_param('iisdi', $orderId, $item['id'], $item['name'], $item['price'], $item['qty']);
                $itemStmt->execute();

                $stockStmt->bind_param('iii', $item['qty'], $item['id'], $item['qty']);
                $stockStmt->execute();
            }
            $itemStmt->close();
            $stockStmt->close();

            $conn->commit();
            $_SESSION['cart'] = [];
            redirect('order_success.php?order_id=' . $orderId);
        } catch (Exception $ex) {
            $conn->rollback();
            error_log('Checkout failed: ' . $ex->getMessage());
            // Shows the real DB/PHP error inline so it's easy to diagnose locally.
            // Remove the getMessage() part (or gate it behind an env check) before
            // deploying this to a public production server.
            $error = 'Something went wrong while placing your order: ' . $ex->getMessage();
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="checkout-card">
    <h1 class="page-title">Checkout</h1>
    <?php if ($error): ?><div class="form-error"><?php echo e($error); ?></div><?php endif; ?>

    <div class="cart-summary" style="margin-bottom:20px; max-width:none;">
        <?php foreach ($items as $item): ?>
            <div class="row"><span><?php echo e($item['name']); ?> &times; <?php echo $item['qty']; ?></span><span><?php echo format_inr($item['subtotal']); ?></span></div>
        <?php endforeach; ?>
        <div class="row total"><span>Total</span><span><?php echo format_inr(cart_total($conn)); ?></span></div>
    </div>

    <form method="post">
        <div class="form-group">
            <label>Full name *</label>
            <input type="text" name="full_name" value="<?php echo e($_POST['full_name'] ?? $_SESSION['user_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo e($_POST['phone'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Shipping address *</label>
            <textarea name="address" rows="3" required><?php echo e($_POST['address'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label>Payment method *</label>
            <div class="payment-methods">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cod" checked>
                    <span class="payment-option-label">💵 Cash on Delivery</span>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="card">
                    <span class="payment-option-label">💳 Credit / Debit Card</span>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="paypal">
                    <span class="payment-option-label">🅿️ PayPal</span>
                </label>
            </div>
        </div>

        <div id="card-fields" class="card-fields" hidden>
            <div class="form-group">
                <label>Name on card</label>
                <input type="text" id="card_name" autocomplete="cc-name" placeholder="Jane Doe">
            </div>
            <div class="form-group">
                <label>Card number</label>
                <input type="text" id="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="4242 4242 4242 4242" maxlength="19">
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label>Expiry (MM/YY)</label>
                    <input type="text" id="card_expiry" autocomplete="cc-exp" placeholder="12/28" maxlength="5">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>CVV</label>
                    <input type="text" id="card_cvv" inputmode="numeric" autocomplete="cc-csc" placeholder="123" maxlength="4">
                </div>
            </div>
            <p class="muted-link" style="text-align:left; margin:-4px 0 4px;">Demo gateway — any valid-looking card works. Card ending in 0002 simulates a decline.</p>
        </div>

        <div id="paypal-fields" class="paypal-fields" hidden>
            <p class="muted-link" style="text-align:left;">You'll be redirected to PayPal to approve this payment. (Simulated in this demo — clicking "Place Order" completes it instantly.)</p>
        </div>

        <div id="payment-error" class="form-error" style="display:none;"></div>

        <!-- Populated by JS after a successful AJAX payment confirmation -->
        <input type="hidden" name="payment_status" id="payment_status" value="pending">
        <input type="hidden" name="transaction_id" id="transaction_id" value="">
        <input type="hidden" name="card_last4" id="card_last4" value="">

        <button type="submit" id="place-order-btn" class="btn btn-primary btn-block">Place Order</button>
    </form>
</div>

<script>
window.SITE_BASE = '';
window.CART_TOTAL = <?php echo json_encode(cart_total($conn)); ?>;
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
