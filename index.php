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
<section class="hero-section text-white py-5 mb-5" style="background: linear-gradient(135deg, #ff6b35 0%, #4a90e2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-3 fw-bold mb-3" style="color: #ffffff; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Welcome to <?php echo SITE_NAME; ?></h1>
                <div class="auction-info bg-dark bg-opacity-75 p-4 rounded mb-4">
                    <h2 class="h3 text-warning mb-3">
                        <i class="fas fa-gavel me-2"></i>FACE-TO-FACE AUCTION
                    </h2>
                    <div class="schedule mb-3">
                        <h4 class="text-info">Every Wednesday & Saturday</h4>
                        <h4 class="text-warning">10:00am - 6:00pm</h4>
                    </div>
                    <div class="location mb-3">
                        <h6 class="text-light">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            0261 D San Luis St. Purob 6, Landayan, San Pedro, Laguna
                        </h6>
                    </div>
                    <div class="contact mb-3">
                        <p class="mb-1 text-light">
                            <i class="fas fa-phone me-2"></i>For info call: <strong class="text-warning">09512723785</strong>
                        </p>
                        <p class="mb-0 text-light">
                            <i class="fas fa-envelope me-2"></i>Email: <strong class="text-warning">jbrincestrading0716@gmail.com</strong>
                        </p>
                    </div>
                    <div class="online-auction border-top pt-3">
                        <h5 class="text-info mb-2">
                            <i class="fas fa-globe me-2"></i>ONLINE AUCTION
                        </h5>
                        <p class="mb-1 text-light">
                            <strong class="text-warning">www.jbrincesbid.com</strong>
                        </p>
                        <p class="mb-0 text-light">
                            <strong class="text-warning">7:00pm - 9:00pm</strong>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <a href="products.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-gavel me-2"></i>View Auction Items
                    </a>
                    <a href="#featured" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-star me-2"></i>Featured Items
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="auction-gallery bg-dark bg-opacity-75 p-3 rounded shadow">
                    <div class="text-center mb-3">
                        <h4 class="text-warning">
                            <i class="fas fa-images me-2"></i>Auction Gallery
                        </h4>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="auction-placeholder bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                <div class="text-center">
                                    <i class="fas fa-box fa-2x text-warning mb-2"></i>
                                    <small class="text-light">Household Items</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="auction-placeholder bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                <div class="text-center">
                                    <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                                    <small class="text-light">Tools & Equipment</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="auction-placeholder bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                <div class="text-center">
                                    <i class="fas fa-laptop fa-2x text-warning mb-2"></i>
                                    <small class="text-light">Electronics</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="auction-placeholder bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                <div class="text-center">
                                    <i class="fas fa-gem fa-2x text-warning mb-2"></i>
                                    <small class="text-light">Collectibles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>Next Auction: Wednesday 10:00 AM
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section mb-5" style="background: rgba(0,0,0,0.05);">
    <div class="container py-5">
        <h2 class="text-center mb-2">
            <i class="fas fa-gavel text-warning me-2"></i>Auction Categories
        </h2>
        <p class="text-center text-muted mb-5">Explore our diverse collection of auction items</p>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-4 col-lg-2 mb-4">
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="text-decoration-none">
                        <div class="card h-100 shadow-sm hover-shadow border-warning" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                            <div class="card-body text-center">
                                <i class="fas fa-gavel fa-3x text-warning mb-3"></i>
                                <h6 class="card-title text-light"><?php echo htmlspecialchars($category['name']); ?></h6>
                                <?php if ($category['description']): ?>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($category['description'], 0, 50)) . '...'; ?></p>
                                <?php endif; ?>
                                <small class="text-warning">
                                    <i class="fas fa-clock me-1"></i>Live Bidding Available
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Customer Reviews Summary -->
<section class="rating-summary-section mb-5">
    <div class="container">
        <h2 class="text-center mb-4">What Our Customers Say</h2>
        <?php include 'includes/rating_summary_widget.php'; ?>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured" class="featured-products mb-5">
    <div class="container">
        <h2 class="text-center mb-2">
            <i class="fas fa-star text-warning me-2"></i>Featured Auction Items
        </h2>
        <p class="text-center text-muted mb-5">Hot items going under the hammer soon!</p>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="position-relative">
                            <img src="<?php echo $product['main_image'] ? UPLOAD_URL . $product['main_image'] : 'assets/images/no-image.jpg'; ?>" 
                                 class="card-img-top product-card-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
                                    <span class="h6 text-primary">₱<?php echo number_format($product['sale_price'], 2); ?></span>
                                    <span class="text-muted text-decoration-line-through ms-2">₱<?php echo number_format($product['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="h6 text-primary">₱<?php echo number_format($product['price'], 2); ?></span>
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
                <p class="text-muted">Free shipping on orders over ₱2,500</p>
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