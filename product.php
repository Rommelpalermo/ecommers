<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);
$cart = new Cart($pdo, isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

// Get product ID
$productId = intval(isset($_GET['id']) ? $_GET['id'] : 0);

if ($productId <= 0) {
    $_SESSION['error'] = 'Invalid product ID';
    header('Location: products.php');
    exit;
}

try {
    // Get product details
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error'] = 'Product not found';
        header('Location: products.php');
        exit;
    }
    
    // Get product images
    $imageStmt = $pdo->prepare("
        SELECT * FROM product_images 
        WHERE product_id = ? 
        ORDER BY sort_order, id
    ");
    $imageStmt->execute([$productId]);
    $productImages = $imageStmt->fetchAll();
    
    // Get related products (same category)
    $relatedStmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
        ORDER BY RAND() 
        LIMIT 4
    ");
    $relatedStmt->execute([$product['category_id'], $productId]);
    $relatedProducts = $relatedStmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error loading product details';
    header('Location: products.php');
    exit;
}

$pageTitle = htmlspecialchars($product['name']);
include 'includes/header.php';
?>

<div class="row">
    <!-- Product Images -->
    <div class="col-md-6">
        <div class="product-images">
            <!-- Main Image -->
            <div class="main-image mb-3">
                <img src="<?php echo $product['main_image'] ? UPLOAD_URL . $product['main_image'] : 'assets/images/no-image.jpg'; ?>" 
                     class="img-fluid rounded shadow" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     id="main-product-image">
            </div>
            
            <!-- Thumbnail Images -->
            <?php if (!empty($productImages)): ?>
                <div class="thumbnail-images">
                    <div class="row">
                        <?php foreach ($productImages as $index => $image): ?>
                            <div class="col-3 mb-2">
                                <img src="<?php echo UPLOAD_URL . $image['image_url']; ?>" 
                                     class="img-fluid rounded thumbnail-image cursor-pointer" 
                                     alt="<?php echo htmlspecialchars($image['alt_text'] ?: $product['name']); ?>"
                                     onclick="changeMainImage(this.src)">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-md-6">
        <div class="product-details">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <?php if ($product['category_name']): ?>
                        <li class="breadcrumb-item">
                            <a href="category.php?id=<?php echo $product['category_id']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>
            
            <!-- Product Title -->
            <h1 class="h3 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <!-- Product Rating & Reviews (placeholder) -->
            <div class="mb-3">
                <div class="d-flex align-items-center">
                    <div class="stars text-warning me-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <span class="text-muted">(4.2 out of 5 stars)</span>
                    <span class="text-muted ms-2">|</span>
                    <a href="#reviews" class="text-decoration-none ms-2">12 reviews</a>
                </div>
            </div>
            
            <!-- Price -->
            <div class="price-section mb-4">
                <?php if ($product['sale_price']): ?>
                    <div class="d-flex align-items-baseline">
                        <h2 class="text-primary me-3 mb-0">₱<?php echo number_format($product['sale_price'], 2); ?></h2>
                        <span class="h5 text-muted text-decoration-line-through">₱<?php echo number_format($product['price'], 2); ?></span>
                        <span class="badge bg-danger ms-2">
                            <?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>% OFF
                        </span>
                    </div>
                <?php else: ?>
                    <h2 class="text-primary mb-0">₱<?php echo number_format($product['price'], 2); ?></h2>
                <?php endif; ?>
            </div>
            
            <!-- Short Description -->
            <?php if ($product['short_description']): ?>
                <p class="lead mb-4"><?php echo htmlspecialchars($product['short_description']); ?></p>
            <?php endif; ?>
            
            <!-- Product Info -->
            <div class="product-info mb-4">
                <div class="row">
                    <div class="col-6">
                        <strong>SKU:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($product['sku']); ?></span>
                    </div>
                    <div class="col-6">
                        <strong>Category:</strong><br>
                        <?php if ($product['category_name']): ?>
                            <a href="category.php?id=<?php echo $product['category_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Uncategorized</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($product['weight'] || $product['dimensions']): ?>
                    <div class="row mt-3">
                        <?php if ($product['weight']): ?>
                            <div class="col-6">
                                <strong>Weight:</strong><br>
                                <span class="text-muted"><?php echo htmlspecialchars($product['weight']); ?> kg</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['dimensions']): ?>
                            <div class="col-6">
                                <strong>Dimensions:</strong><br>
                                <span class="text-muted"><?php echo htmlspecialchars($product['dimensions']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Stock Status -->
            <div class="stock-info mb-4">
                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span class="text-success">In Stock</span>
                        <span class="text-muted ms-2">(<?php echo $product['stock_quantity']; ?> available)</span>
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <span class="text-danger">Out of Stock</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quantity and Add to Cart -->
            <?php if ($product['stock_quantity'] > 0): ?>
                <div class="add-to-cart-section mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Quantity:</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <button class="btn btn-primary btn-lg w-100" id="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="action-buttons mb-4">
                <div class="d-grid gap-2 d-md-flex">
                    <button class="btn btn-outline-primary" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                        <i class="fas fa-heart"></i> Add to Wishlist
                    </button>
                    <button class="btn btn-outline-secondary" onclick="shareProduct()">
                        <i class="fas fa-share"></i> Share
                    </button>
                </div>
            </div>
            
            <!-- Features -->
            <div class="features">
                <div class="row text-center">
                    <div class="col-4">
                        <i class="fas fa-shipping-fast fa-2x text-primary mb-2"></i>
                        <p class="small mb-0">Fast Shipping</p>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-undo fa-2x text-primary mb-2"></i>
                        <p class="small mb-0">Easy Returns</p>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                        <p class="small mb-0">Secure Payment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Description & Details Tabs -->
<div class="row mt-5">
    <div class="col-12">
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                    Description
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab">
                    Specifications
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                    Reviews (12)
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="productTabsContent">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="description" role="tabpanel">
                <div class="p-4">
                    <?php if ($product['description']): ?>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No description available for this product.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Specifications Tab -->
            <div class="tab-pane fade" id="specifications" role="tabpanel">
                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>SKU:</strong></td>
                                    <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></td>
                                </tr>
                                <?php if ($product['weight']): ?>
                                <tr>
                                    <td><strong>Weight:</strong></td>
                                    <td><?php echo htmlspecialchars($product['weight']); ?> kg</td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($product['dimensions']): ?>
                                <tr>
                                    <td><strong>Dimensions:</strong></td>
                                    <td><?php echo htmlspecialchars($product['dimensions']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <div class="p-4">
                    <p class="text-muted">Product reviews feature coming soon!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<div class="row mt-5">
    <div class="col-12">
        <h3 class="mb-4">Related Products</h3>
        <div class="row">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="position-relative">
                            <img src="<?php echo $relatedProduct['main_image'] ? UPLOAD_URL . $relatedProduct['main_image'] : 'assets/images/no-image.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" 
                                 style="height: 200px; object-fit: cover;">
                            <?php if ($relatedProduct['sale_price']): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars($relatedProduct['name']); ?></h6>
                            <div class="price-section mt-auto">
                                <?php if ($relatedProduct['sale_price']): ?>
                                    <span class="h6 text-primary">₱<?php echo number_format($relatedProduct['sale_price'], 2); ?></span>
                                    <span class="text-muted text-decoration-line-through ms-2">₱<?php echo number_format($relatedProduct['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="h6 text-primary">₱<?php echo number_format($relatedProduct['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2">
                                <a href="product.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Quantity controls
function changeQuantity(change) {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    const max = parseInt(quantityInput.max);
    const min = parseInt(quantityInput.min);
    
    const newValue = currentValue + change;
    
    if (newValue >= min && newValue <= max) {
        quantityInput.value = newValue;
    }
}

// Change main product image
function changeMainImage(src) {
    document.getElementById('main-product-image').src = src;
    
    // Update thumbnail borders
    document.querySelectorAll('.thumbnail-img').forEach(img => {
        img.style.border = '2px solid transparent';
    });
    event.target.style.border = '2px solid #007bff';
}

// Add to cart functionality
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = document.getElementById('quantity').value;
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount();
                    showAlert('Product added to cart successfully!', 'success');
                } else {
                    showAlert(data.message || 'Failed to add product to cart', 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred', 'danger');
            });
        });
    }
});

// Wishlist functionality (placeholder)
function addToWishlist(productId) {
    showAlert('Wishlist feature coming soon!', 'info');
}

// Share functionality
function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($product['name']); ?>',
            text: '<?php echo htmlspecialchars($product['short_description']); ?>',
            url: window.location.href
        });
    } else {
        // Fallback - copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showAlert('Product URL copied to clipboard!', 'success');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>