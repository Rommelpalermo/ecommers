<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);
$cart = new Cart($pdo, isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

// Get category ID
$categoryId = intval(isset($_GET['id']) ? $_GET['id'] : 0);

if ($categoryId <= 0) {
    $_SESSION['error'] = 'Invalid category ID';
    header('Location: products.php');
    exit;
}

// Get pagination parameters
$page = intval(isset($_GET['page']) ? $_GET['page'] : 1);
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    // Get category details
    $categoryStmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND is_active = 1");
    $categoryStmt->execute([$categoryId]);
    $category = $categoryStmt->fetch();
    
    if (!$category) {
        $_SESSION['error'] = 'Category not found';
        header('Location: products.php');
        exit;
    }
    
    // Get products in this category
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.is_active = 1 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$categoryId]);
    $products = $stmt->fetchAll();
    
    // Get total count for pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ? AND is_active = 1");
    $countStmt->execute([$categoryId]);
    $totalProducts = $countStmt->fetch()['total'];
    $totalPages = ceil($totalProducts / $limit);
    
    // Get all categories for sidebar
    $categoriesStmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
    $categoriesStmt->execute();
    $allCategories = $categoriesStmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error loading category products';
    header('Location: products.php');
    exit;
}

$pageTitle = htmlspecialchars($category['name']);
include 'includes/header.php';
?>

<div class="row">
    <!-- Categories Sidebar -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Categories</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="products.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-th-large me-2"></i> All Products
                    </a>
                    <?php foreach ($allCategories as $cat): ?>
                        <a href="category.php?id=<?php echo $cat['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $cat['id'] == $categoryId ? 'active' : ''; ?>">
                            <i class="fas fa-cube me-2"></i> <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Products -->
    <div class="col-md-9">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($category['name']); ?></li>
            </ol>
        </nav>
        
        <!-- Category Header -->
        <div class="category-header mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="h2"><?php echo htmlspecialchars($category['name']); ?></h1>
                    <?php if ($category['description']): ?>
                        <p class="text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-muted">
                    <?php echo $totalProducts; ?> product<?php echo $totalProducts != 1 ? 's' : ''; ?>
                </div>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <!-- No Products Found -->
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4>No products found</h4>
                <p class="text-muted">This category doesn't have any products yet.</p>
                <a href="products.php" class="btn btn-primary">Browse All Products</a>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
            <div class="row product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
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
                <nav aria-label="Category products pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>">Next</a>
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