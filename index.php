<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);
$pageTitle = 'Home';

// Get featured products
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_featured = 1 AND p.is_active = 1 
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll();
} catch (Exception $e) {
    $featuredProducts = [];
}

// Get categories
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY name LIMIT 6");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to <?php echo SITE_NAME; ?></h1>
                <p class="lead mb-4">Discover amazing products at unbeatable prices. Shop with confidence and enjoy fast, secure delivery.</p>
                <div class="d-flex gap-3">
                    <a href="products.php" class="btn btn-light btn-lg">Shop Now</a>
                    <a href="#featured" class="btn btn-outline-light btn-lg">Browse Featured</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/hero-shopping.jpg" alt="Shopping" class="img-fluid rounded shadow" style="max-height: 400px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Shop by Category</h2>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-4 col-lg-2 mb-4">
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="text-decoration-none">
                        <div class="card h-100 shadow-sm hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-cube fa-3x text-primary mb-3"></i>
                                <h6 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h6>
                                <?php if ($category['description']): ?>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($category['description'], 0, 50)) . '...'; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured" class="featured-products mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="position-relative">
                            <img src="<?php echo $product['main_image'] ? UPLOAD_URL . $product['main_image'] : 'assets/images/no-image.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 style="height: 200px; object-fit: cover;">
                            <?php if ($product['sale_price']): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo htmlspecialchars(substr($product['short_description'], 0, 80)) . '...'; ?>
                            </p>
                            <div class="mb-2">
                                <?php if ($product['category_name']): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($product['category_name']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="price-section mb-3">
                                <?php if ($product['sale_price']): ?>
                                    <span class="h6 text-primary">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                    <span class="text-muted text-decoration-line-through ms-2">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="h6 text-primary">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($featuredProducts) > 0): ?>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-outline-primary btn-lg">View All Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="features-section bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                <h5>Fast Shipping</h5>
                <p class="text-muted">Free shipping on orders over $50</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                <h5>Secure Payment</h5>
                <p class="text-muted">Your payment information is safe</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                <h5>Easy Returns</h5>
                <p class="text-muted">30-day return policy</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5>24/7 Support</h5>
                <p class="text-muted">Customer support available anytime</p>
            </div>
        </div>
    </div>
</section>

<script>
// Add to cart functionality
document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    updateCartCount();
                    // Show success message
                    showAlert('Product added to cart successfully!', 'success');
                } else {
                    showAlert(data.message || 'Failed to add product to cart', 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred', 'danger');
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>