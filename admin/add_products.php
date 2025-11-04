<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

$auth = new Auth($pdo);

// Simple admin check
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    try {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description']);
        $price = floatval($_POST['price']);
        $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
        $category_id = intval($_POST['category_id']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $keywords = trim($_POST['keywords']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validation
        $errors = [];
        if (empty($name)) $errors[] = "Product name is required";
        if (empty($description)) $errors[] = "Description is required";
        if ($price <= 0) $errors[] = "Price must be greater than 0";
        if ($category_id <= 0) $errors[] = "Please select a category";
        if ($stock_quantity < 0) $errors[] = "Stock quantity cannot be negative";
        
        // Validate sale price
        if ($sale_price !== null && $sale_price >= $price) {
            $errors[] = "Sale price must be less than regular price";
        }
        
        if (empty($errors)) {
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array(strtolower($file_extension), $allowed_extensions)) {
                    $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/' . $file_name;
                    } else {
                        $errors[] = "Failed to upload image";
                    }
                } else {
                    $errors[] = "Invalid image format. Allowed: JPG, PNG, GIF, WebP";
                }
            }
            
            if (empty($errors)) {
                // Insert into products table
                $stmt = $pdo->prepare("
                    INSERT INTO products 
                    (name, description, short_description, price, sale_price, category_id, image_url, 
                     stock_quantity, is_featured, is_active, sku, meta_keywords, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $sku = 'PROD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $success = $stmt->execute([
                    $name,
                    $description,
                    $short_description,
                    $price,
                    $sale_price,
                    $category_id,
                    $image_path,
                    $stock_quantity,
                    $is_featured,
                    $is_active,
                    $sku,
                    $keywords
                ]);
                
                if ($success) {
                    $product_id = $pdo->lastInsertId();
                    $_SESSION['success'] = "Product added successfully! Product SKU: $sku";
                    header('Location: add_products.php');
                    exit;
                } else {
                    $errors[] = "Failed to add product to database";
                }
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
}

// Get categories for dropdown
$categories_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categories_stmt->fetchAll();

// Get recent products for display
$recent_stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$recent_products = $recent_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - J Brinces Trading Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #ffffff;
            min-height: 100vh;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 107, 53, 0.3);
            color: #ffffff;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #ff6b35;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .admin-header {
            background: rgba(255, 107, 53, 0.1);
            border-bottom: 2px solid #ff6b35;
            backdrop-filter: blur(10px);
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #ff6b35;
        }
        
        .recent-products {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .product-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .product-item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark admin-header mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-box me-2"></i>Add Product
            </a>
            <div class="d-flex">
                <a href="add_item.php" class="btn btn-outline-warning me-2">
                    <i class="fas fa-gavel me-1"></i>Add Auction Item
                </a>
                <a href="manage_items.php" class="btn btn-outline-info me-2">
                    <i class="fas fa-boxes me-1"></i>Manage Items
                </a>
                <a href="feedback.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-comments me-1"></i>Feedback
                </a>
                <a href="ratings.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-star me-1"></i>Ratings
                </a>
                <a href="../index.php" class="btn btn-outline-success">
                    <i class="fas fa-store me-1"></i>View Store
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Add Product Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h5 class="text-warning mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Basic Information
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter product name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="short_description" class="form-label">Short Description</label>
                                        <input type="text" class="form-control" id="short_description" name="short_description" placeholder="Brief description for listings" value="<?php echo isset($_POST['short_description']) ? htmlspecialchars($_POST['short_description']) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="keywords" class="form-label">Keywords (for search)</label>
                                        <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Comma-separated keywords" value="<?php echo isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <!-- Pricing & Inventory -->
                                <div class="col-md-6">
                                    <h5 class="text-warning mb-3">
                                        <i class="fas fa-peso-sign me-2"></i>Pricing & Inventory
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Regular Price (₱) *</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required placeholder="0.00" value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="sale_price" class="form-label">Sale Price (₱)</label>
                                        <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" placeholder="0.00 (optional)" value="<?php echo isset($_POST['sale_price']) ? $_POST['sale_price'] : ''; ?>">
                                        <small class="form-text text-muted">Must be less than regular price</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" required placeholder="0" value="<?php echo isset($_POST['stock_quantity']) ? $_POST['stock_quantity'] : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="product_image" class="form-label">Product Image</label>
                                        <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*" onchange="previewImage(this)">
                                        <small class="form-text text-muted">Upload a clear photo of the product</small>
                                    </div>
                                    
                                    <div id="image_preview" class="mt-3" style="display: none;">
                                        <label class="form-label">Preview:</label><br>
                                        <img id="preview_img" src="" alt="Preview" class="preview-image">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Full Width Description -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5 class="text-warning mb-3">
                                        <i class="fas fa-file-alt me-2"></i>Detailed Description
                                    </h5>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Full Description *</label>
                                        <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Provide detailed description of the product, including features, specifications, benefits, etc."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Product Settings -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5 class="text-warning mb-3">
                                        <i class="fas fa-cog me-2"></i>Product Settings
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo (isset($_POST['is_featured']) && $_POST['is_featured']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_featured">
                                                    <i class="fas fa-star text-warning me-2"></i>Featured Product
                                                </label>
                                                <small class="form-text text-muted d-block">Show on homepage featured section</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_active">
                                                    <i class="fas fa-eye text-success me-2"></i>Active Product
                                                </label>
                                                <small class="form-text text-muted d-block">Make product visible to customers</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <hr class="text-muted">
                                    <div class="d-flex justify-content-between">
                                        <a href="../index.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Cancel
                                        </a>
                                        <button type="submit" name="add_product" class="btn btn-warning btn-lg">
                                            <i class="fas fa-plus me-2"></i>Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Recent Products Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Products
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="recent-products">
                            <?php if (empty($recent_products)): ?>
                                <p class="text-muted text-center">No products yet</p>
                            <?php else: ?>
                                <?php foreach ($recent_products as $product): ?>
                                    <div class="product-item d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if ($product['image_url']): ?>
                                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product" class="rounded">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-light"><?php echo htmlspecialchars(substr($product['name'], 0, 30)) . (strlen($product['name']) > 30 ? '...' : ''); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?></small>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-warning">₱<?php echo number_format($product['price'], 2); ?></small>
                                                <small class="text-muted"><?php echo $product['stock_quantity']; ?> in stock</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <a href="manage_items.php" class="btn btn-outline-light btn-sm w-100">
                                <i class="fas fa-boxes me-2"></i>Manage All Products
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Quick Stats
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $stats_stmt = $pdo->query("
                            SELECT 
                                COUNT(*) as total_products,
                                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_products,
                                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_products,
                                SUM(stock_quantity) as total_stock
                            FROM products
                        ");
                        $stats = $stats_stmt->fetch();
                        ?>
                        <div class="row text-center">
                            <div class="col-6 mb-2">
                                <h5 class="text-warning mb-0"><?php echo $stats['total_products']; ?></h5>
                                <small class="text-muted">Total Products</small>
                            </div>
                            <div class="col-6 mb-2">
                                <h5 class="text-success mb-0"><?php echo $stats['active_products']; ?></h5>
                                <small class="text-muted">Active</small>
                            </div>
                            <div class="col-6">
                                <h5 class="text-info mb-0"><?php echo $stats['featured_products']; ?></h5>
                                <small class="text-muted">Featured</small>
                            </div>
                            <div class="col-6">
                                <h5 class="text-warning mb-0"><?php echo $stats['total_stock']; ?></h5>
                                <small class="text-muted">Total Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview_img').src = e.target.result;
                    document.getElementById('image_preview').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Price validation
        document.getElementById('sale_price').addEventListener('input', function() {
            const regularPrice = parseFloat(document.getElementById('price').value) || 0;
            const salePrice = parseFloat(this.value) || 0;
            
            if (salePrice > 0 && salePrice >= regularPrice) {
                this.setCustomValidity('Sale price must be less than regular price');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('price').addEventListener('input', function() {
            const salePriceInput = document.getElementById('sale_price');
            const regularPrice = parseFloat(this.value) || 0;
            const salePrice = parseFloat(salePriceInput.value) || 0;
            
            if (salePrice > 0 && salePrice >= regularPrice) {
                salePriceInput.setCustomValidity('Sale price must be less than regular price');
            } else {
                salePriceInput.setCustomValidity('');
            }
        });
    </script>
</body>
</html>