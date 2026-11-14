<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';
$page_title = 'Your Cart';

$items = cart_items($conn);
$total = cart_total($conn);

require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Your Cart</h1>

<div id="cart-wrapper">
<?php if (empty($items)): ?>
    <div class="empty-state">
        <h3>Your cart is empty</h3>
        <p>Browse products and add something you like.</p>
        <a class="btn btn-primary" href="index.php">Continue shopping</a>
    </div>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="cart-table-body">
            <?php foreach ($items as $item): ?>
                <tr data-product-id="<?php echo $item['id']; ?>">
                    <td>
                        <div class="cart-prod">
                            <img src="assets/uploads/<?php echo e($item['image']); ?>"
                                 onerror="this.src='https://placehold.co/60x60?text=%20'" alt="">
                            <span><?php echo e($item['name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo format_inr($item['price']); ?></td>
                    <td>
                        <div class="qty-controls">
                            <button type="button" data-action="decrease">&minus;</button>
                            <input type="text" class="qty-input" value="<?php echo $item['qty']; ?>">
                            <button type="button" data-action="increase">&plus;</button>
                        </div>
                    </td>
                    <td class="line-subtotal"><?php echo format_inr($item['subtotal']); ?></td>
                    <td><span class="remove-link" data-action="remove">Remove</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="cart-summary">
        <div class="row"><span>Subtotal</span><span id="cart-total"><?php echo format_inr($total); ?></span></div>
        <div class="row total"><span>Total</span><span id="cart-total-display"><?php echo format_inr($total); ?></span></div>
        <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top:14px;">Proceed to Checkout</a>
    </div>
<?php endif; ?>
</div>

<script>
// Keep the displayed total in sync with the AJAX-updated subtotal element
const observer = new MutationObserver(() => {
    const totalEl = document.getElementById('cart-total');
    const displayEl = document.getElementById('cart-total-display');
    if (totalEl && displayEl) displayEl.textContent = totalEl.textContent;
});
const totalTarget = document.getElementById('cart-total');
if (totalTarget) observer.observe(totalTarget, { childList: true, characterData: true, subtree: true });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
