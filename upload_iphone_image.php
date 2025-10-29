<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = 'uploads/';
    $fileName = 'iphone-pro-purple.jpg';
    $uploadFile = $uploadDir . $fileName;
    
    // Check if file was uploaded without errors
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $success = "✅ Image uploaded successfully as: " . $fileName;
            } else {
                $error = "❌ Failed to move uploaded file.";
            }
        } else {
            $error = "❌ Invalid file type. Please upload JPG, PNG, GIF, or WebP.";
        }
    } else {
        $error = "❌ Upload error: " . $_FILES['image']['error'];
    }
}

// Get current product info
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = 1");
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload iPhone Image - Smartphone Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-mobile-alt"></i> Upload iPhone Image for Smartphone Pro</h3>
                    </div>
                    <div class="card-body">
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Current Product Info:</h5>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>ID:</strong> <?php echo $product['id']; ?></li>
                                    <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($product['name']); ?></li>
                                    <li class="list-group-item"><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></li>
                                    <li class="list-group-item"><strong>Current Image:</strong> 
                                        <?php if ($product['main_image']): ?>
                                            <?php echo $product['main_image']; ?>
                                            <?php if (file_exists('uploads/' . $product['main_image'])): ?>
                                                <span class="badge bg-success">File Exists</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">File Missing</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Upload New Image:</h5>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Select iPhone Image:</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                        <div class="form-text">Will be saved as: iphone-pro-purple.jpg</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload Image
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <?php if ($product['main_image'] && file_exists('uploads/' . $product['main_image'])): ?>
                            <div class="mt-4">
                                <h5>Current Image Preview:</h5>
                                <img src="uploads/<?php echo $product['main_image']; ?>" alt="Current Product Image" class="img-fluid" style="max-height: 300px;">
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <h5>Quick Links:</h5>
                            <a href="product.php?id=1" class="btn btn-outline-primary me-2">
                                <i class="fas fa-eye"></i> View Product Page
                            </a>
                            <a href="products.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-list"></i> All Products
                            </a>
                            <a href="index.php" class="btn btn-outline-success">
                                <i class="fas fa-home"></i> Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>