<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

$auth = new Auth($pdo);

// Simple admin check (in a real app, you'd have proper admin authentication)
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    try {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description']);
        $starting_price = floatval($_POST['starting_price']);
        $reserve_price = floatval($_POST['reserve_price']);
        $category_id = intval($_POST['category_id']);
        $auction_date = $_POST['auction_date'];
        $auction_type = $_POST['auction_type'];
        $condition_item = $_POST['condition'];
        $keywords = trim($_POST['keywords']);
        
        // Validation
        $errors = [];
        if (empty($name)) $errors[] = "Item name is required";
        if (empty($description)) $errors[] = "Description is required";
        if ($starting_price <= 0) $errors[] = "Starting price must be greater than 0";
        if ($category_id <= 0) $errors[] = "Please select a category";
        if (empty($auction_date)) $errors[] = "Auction date is required";
        
        if (empty($errors)) {
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/';
                $file_extension = pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
                $file_name = 'auction_item_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $upload_path)) {
                    $image_path = 'uploads/' . $file_name;
                }
            }
            
            // Insert into products table (we'll use existing structure but adapt for auction)
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, description, short_description, price, sale_price, category_id, image_url, is_active, stock_quantity, sku, meta_keywords, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, ?, ?, NOW())
            ");
            
            $sku = 'AUC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $success = $stmt->execute([
                $name,
                $description,
                $short_description,
                $starting_price,
                $reserve_price > 0 ? $reserve_price : null,
                $category_id,
                $image_path,
                $sku,
                $keywords
            ]);
            
            if ($success) {
                $item_id = $pdo->lastInsertId();
                
                // Create auction_items table if it doesn't exist and insert auction-specific data
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS auction_items (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        product_id INT NOT NULL,
                        auction_date DATETIME NOT NULL,
                        auction_type ENUM('face_to_face', 'online', 'both') DEFAULT 'both',
                        item_condition ENUM('new', 'like_new', 'good', 'fair', 'poor') DEFAULT 'good',
                        starting_price DECIMAL(10,2) NOT NULL,
                        reserve_price DECIMAL(10,2),
                        current_bid DECIMAL(10,2) DEFAULT 0,
                        bid_count INT DEFAULT 0,
                        status ENUM('upcoming', 'active', 'sold', 'unsold') DEFAULT 'upcoming',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                    )
                ");
                
                $stmt = $pdo->prepare("
                    INSERT INTO auction_items 
                    (product_id, auction_date, auction_type, item_condition, starting_price, reserve_price) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $item_id,
                    $auction_date,
                    $auction_type,
                    $condition_item,
                    $starting_price,
                    $reserve_price > 0 ? $reserve_price : null
                ]);
                
                $_SESSION['success'] = "Auction item added successfully! Item ID: $sku";
                header('Location: add_item.php');
                exit;
            } else {
                $errors[] = "Failed to add item to database";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
}

// Get categories for dropdown
$categories_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categories_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Auction Item - J Brinces Trading Admin</title>
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
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.5);
            color: #d4edda;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #f8d7da;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark admin-header mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-gavel me-2"></i>Add Auction Item
            </a>
            <div class="d-flex">
                <a href="feedback.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-comments me-1"></i>Feedback
                </a>
                <a href="ratings.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-star me-1"></i>Ratings
                </a>
                <a href="../index.php" class="btn btn-outline-warning">
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

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Add New Auction Item
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
                                        <label for="name" class="form-label">Item Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter item name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
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
                                        <label for="condition" class="form-label">Condition *</label>
                                        <select class="form-select" id="condition" name="condition" required>
                                            <option value="">Select Condition</option>
                                            <option value="new" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'new') ? 'selected' : ''; ?>>New</option>
                                            <option value="like_new" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'like_new') ? 'selected' : ''; ?>>Like New</option>
                                            <option value="good" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'good') ? 'selected' : ''; ?>>Good</option>
                                            <option value="fair" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'fair') ? 'selected' : ''; ?>>Fair</option>
                                            <option value="poor" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'poor') ? 'selected' : ''; ?>>Poor</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="keywords" class="form-label">Keywords (for search)</label>
                                        <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Comma-separated keywords" value="<?php echo isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <!-- Auction Details -->
                                <div class="col-md-6">
                                    <h5 class="text-warning mb-3">
                                        <i class="fas fa-gavel me-2"></i>Auction Details
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="starting_price" class="form-label">Starting Price (₱) *</label>
                                        <input type="number" class="form-control" id="starting_price" name="starting_price" step="0.01" min="0" required placeholder="0.00" value="<?php echo isset($_POST['starting_price']) ? $_POST['starting_price'] : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="reserve_price" class="form-label">Reserve Price (₱)</label>
                                        <input type="number" class="form-control" id="reserve_price" name="reserve_price" step="0.01" min="0" placeholder="0.00 (optional)" value="<?php echo isset($_POST['reserve_price']) ? $_POST['reserve_price'] : ''; ?>">
                                        <small class="form-text text-muted">Minimum price you'll accept</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="auction_date" class="form-label">Auction Date *</label>
                                        <input type="datetime-local" class="form-control" id="auction_date" name="auction_date" required value="<?php echo isset($_POST['auction_date']) ? $_POST['auction_date'] : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="auction_type" class="form-label">Auction Type *</label>
                                        <select class="form-select" id="auction_type" name="auction_type" required>
                                            <option value="">Select Type</option>
                                            <option value="face_to_face" <?php echo (isset($_POST['auction_type']) && $_POST['auction_type'] == 'face_to_face') ? 'selected' : ''; ?>>Face-to-Face Only</option>
                                            <option value="online" <?php echo (isset($_POST['auction_type']) && $_POST['auction_type'] == 'online') ? 'selected' : ''; ?>>Online Only</option>
                                            <option value="both" <?php echo (isset($_POST['auction_type']) && $_POST['auction_type'] == 'both') ? 'selected' : ''; ?>>Both (Recommended)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="item_image" class="form-label">Item Image</label>
                                        <input type="file" class="form-control" id="item_image" name="item_image" accept="image/*" onchange="previewImage(this)">
                                        <small class="form-text text-muted">Upload a clear photo of the item</small>
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
                                        <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Provide detailed description of the item, including features, specifications, condition notes, etc."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
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
                                        <button type="submit" name="add_item" class="btn btn-warning btn-lg">
                                            <i class="fas fa-plus me-2"></i>Add Auction Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
        
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('auction_date').min = minDateTime;
        });
    </script>
</body>
</html>