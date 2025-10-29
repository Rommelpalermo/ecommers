<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);
$cart = new Cart($pdo, isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

// Get search query
$query = trim(isset($_GET['q']) ? $_GET['q'] : '');
$page = intval(isset($_GET['page']) ? $_GET['page'] : 1);
$limit = 12;
$offset = ($page - 1) * $limit;

$products = [];
$totalProducts = 0;
$totalPages = 1;

if (!empty($query)) {
    try {
        // Search products
        $searchQuery = "%$query%";
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)
            ORDER BY p.name ASC 
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute([$searchQuery, $searchQuery, $searchQuery]);
        $products = $stmt->fetchAll();
        
        // Get total count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM products p 
            WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)
        ");
        $countStmt->execute([$searchQuery, $searchQuery, $searchQuery]);
        $totalProducts = $countStmt->fetch()['total'];
        $totalPages = ceil($totalProducts / $limit);
        
    } catch (Exception $e) {
        $products = [];
        $totalProducts = 0;
    }
}

$pageTitle = 'Search Results';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <!-- Search Header -->
        <div class="search-header mb-4">
            <h1 class="h2">Search Results</h1>
            <?php if (!empty($query)): ?>
                <p class="text-muted">
                    <?php if ($totalProducts > 0): ?>
                        Found <?php echo $totalProducts; ?> product<?php echo $totalProducts != 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($query); ?>"
                    <?php else: ?>
                        No products found for "<?php echo htmlspecialchars($query); ?>"
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p class="text-muted">Please enter a search term to find products.</p>
            <?php endif; ?>
        </div>
        
        <!-- Search Form -->
        <div class="search-form mb-4">
            <form method="GET" action="">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="q" 
                           placeholder="Search for products..." 
                           value="<?php echo htmlspecialchars($query); ?>" required>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (empty($query)): ?>
            <!-- No Search Query -->
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4>Start Your Search</h4>
                <p class="text-muted">Enter keywords to find products you're looking for.</p>
            </div>
            
        <?php elseif (empty($products)): ?>
            <!-- No Results -->
            <div class="text-center py-5">
                <i class="fas fa-search-minus fa-4x text-muted mb-3"></i>
                <h4>No Products Found</h4>
                <p class="text-muted">Try different keywords or browse our categories.</p>
                <div class="mt-3">
                    <a href="products.php" class="btn btn-primary">Browse All Products</a>
                    <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Search Results -->
            <div class="row product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm hover-shadow">
                            <div class="position-relative">
                                <img src="<?php echo $product['main_image'] ? UPLOAD_URL . $product['main_image'] : 'assets/images/no-image.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                                <?php if ($product['sale_price']): ?>
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
                                <?php endif; ?>
                                <?php if ($product['is_featured']): ?>
                                    <span class="badge bg-warning position-absolute top-0 start-0 m-2">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                <p class="card-text text-muted small flex-grow-1">
                                    <?php echo htmlspecialchars(substr($product['short_description'], 0, 80)) . '...'; ?>
                                </p>
                                <div class="mb-2">
                                    <?php if ($product['category_name']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                        </small>
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
                                <div class="mb-2">
                                    <small class="text-muted">
                                        Stock: <?php echo $product['stock_quantity']; ?> available
                                    </small>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-times"></i> Out of Stock
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Search results pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

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
    });
});
</script>

<?php include 'includes/footer.php'; ?>