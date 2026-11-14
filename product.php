<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';

$slug = $_GET['slug'] ?? '';
$product = db_fetch_one(
    $conn,
    "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ?",
    's',
    [$slug]
);

if (!$product) {
    http_response_code(404);
    $page_title = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><h3>Product not found</h3><a class="btn btn-primary" href="index.php">Back to shop</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = $product['name'];
require __DIR__ . '/includes/header.php';
?>

<div class="product-detail">
    <div class="image">
        <img src="assets/uploads/<?php echo e($product['image']); ?>"
             onerror="this.src='https://placehold.co/500x500?text=<?php echo urlencode($product['name']); ?>'"
             alt="<?php echo e($product['name']); ?>">
    </div>
    <div class="info">
        <div class="product-cat"><?php echo e($product['category_name'] ?? 'Uncategorized'); ?></div>
        <h1><?php echo e($product['name']); ?></h1>
        <p><?php echo nl2br(e($product['description'])); ?></p>
        <div class="price-large"><?php echo format_inr($product['price']); ?></div>
        <p class="stock-note"><?php echo $product['stock'] > 0 ? $product['stock'] . ' in stock' : 'Out of stock'; ?></p>

        <form class="add-to-cart-form" data-product-id="<?php echo $product['id']; ?>" style="margin-top:20px;">
            <input type="number" min="1" max="<?php echo max(1,(int)$product['stock']); ?>" value="1" class="qty-input qty-field">
            <button type="submit" class="btn btn-primary" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                <?php echo $product['stock'] <= 0 ? 'Out of stock' : 'Add to Cart'; ?>
            </button>
        </form>
        <p style="margin-top:16px;"><a href="index.php">&larr; Continue shopping</a></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
