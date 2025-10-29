<?php
// T-Shirt image uploader - saves as everything-will-be-ok-tshirt.jpg
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['tshirt_image']) && $_FILES['tshirt_image']['error'] == 0) {
        $uploadFile = 'philippine-traditional-tshirt.jpg';
        
        if (move_uploaded_file($_FILES['tshirt_image']['tmp_name'], $uploadFile)) {
            // Update the T-Shirt Basic product with the new image
            require_once '../config/database.php';
            $sql = "UPDATE products SET main_image = ? WHERE id = 3";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$uploadFile]);
            
            echo "<div class='alert alert-success'>✅ T-Shirt image updated successfully!</div>";
            echo "<p><a href='../product.php?id=3' class='btn btn-primary'>View Updated T-Shirt Basic</a></p>";
            echo "<p><img src='$uploadFile?" . time() . "' style='max-width: 300px; border: 1px solid #ddd; padding: 10px;'></p>";
        } else {
            echo "<div class='alert alert-danger'>❌ Failed to upload image.</div>";
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload T-Shirt Design</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>🇵🇭 Add Philippine Traditional T-Shirt Design</h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4>Current T-Shirt Status:</h4>
                <div class="alert alert-warning">
                    📋 T-Shirt Basic currently has no image assigned
                </div>
                <p><strong>Product:</strong> T-Shirt Basic (ID: 3)</p>
                <p><strong>Status:</strong> Ready for image upload</p>
            </div>
            <div class="col-md-6">
                <h4>Upload T-Shirt Design:</h4>
                <form method="post" enctype="multipart/form-data" style="border: 2px dashed #28a745; padding: 30px; text-align: center;">
                    <div class="mb-3">
                        <label for="tshirt_image" class="form-label">Select Philippine Traditional T-Shirt Design</label>
                        <input type="file" class="form-control" id="tshirt_image" name="tshirt_image" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg">🇵🇭 Add Philippine T-Shirt Design</button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">📋 What This Will Do:</h5>
                    <ul class="list-unstyled">
                        <li>✅ Save the t-shirt image as <code>philippine-traditional-tshirt.jpg</code></li>
                        <li>✅ Update T-Shirt Basic product to use this image</li>
                        <li>✅ Apply optimized CSS styling for product display</li>
                        <li>✅ Make the t-shirt visible on all pages (product, listings, cart, homepage)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="../" class="btn btn-secondary">Back to Store</a>
            <a href="../admin/" class="btn btn-outline-primary">Admin Panel</a>
        </div>
    </div>

    <script>
        // Add preview functionality
        document.getElementById('tshirt_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create preview element if it doesn't exist
                    let preview = document.getElementById('preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'preview';
                        preview.innerHTML = '<h5>Preview:</h5><img id="previewImg" style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">';
                        document.querySelector('form').appendChild(preview);
                    }
                    document.getElementById('previewImg').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>