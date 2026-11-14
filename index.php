<?php
require_once __DIR__ . '/includes/functions.php';
$base = '';
$page_title = 'Home';

$cat = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
$q   = trim($_GET['q'] ?? '');

$categories = db_fetch_all($conn, "SELECT * FROM categories ORDER BY name");

$sql = "SELECT * FROM products WHERE 1=1";
$types = '';
$params = [];
if ($cat > 0) {
    $sql .= " AND category_id = ?";
    $types .= 'i';
    $params[] = $cat;
}
if ($q !== '') {
    $sql .= " AND name LIKE ?";
    $types .= 's';
    $params[] = "%$q%";
}
$sql .= " ORDER BY created_at DESC";
$products = db_fetch_all($conn, $sql, $types, $params);

require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><?php echo $q ? 'Search results for "' . e($q) . '"' : 'All Products'; ?></h1>

<div class="filters">
    <a href="index.php" class="<?php echo $cat === 0 ? 'active' : ''; ?>">All</a>
    <?php foreach ($categories as $c): ?>
        <a href="index.php?cat=<?php echo $c['id']; ?>" class="<?php echo $cat === (int)$c['id'] ? 'active' : ''; ?>">
            <?php echo e($c['name']); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state">
        <h3>No products found</h3>
        <p>Try a different search or category.</p>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="product-thumb">
                    <img src="assets/uploads/<?php echo e($p['image']); ?>"
                         onerror="this.src='https://placehold.co/300x300?text=<?php echo urlencode($p['name']); ?>'"
                         alt="<?php echo e($p['name']); ?>">
                </a>
                <div class="product-body">
                    <h3><a href="product.php?slug=<?php echo e($p['slug']); ?>"><?php echo e($p['name']); ?></a></h3>
                    <div class="product-price"><?php echo format_inr($p['price']); ?></div>
                    <div class="stock-note"><?php echo $p['stock'] > 0 ? $p['stock'] . ' in stock' : 'Out of stock'; ?></div>

                    <form class="add-to-cart-form" data-product-id="<?php echo $p['id']; ?>">
                        <input type="hidden" class="qty-field" value="1">
                        <button type="submit" class="btn btn-primary btn-block" <?php echo $p['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <?php echo $p['stock'] <= 0 ? 'Out of stock' : 'Add to Cart'; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
