<?php
// Simple book image uploader - saves as html-css-programming-book.jpg
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $uploadFile = 'html-css-programming-book.jpg';
        
        if (move_uploaded_file($_FILES['book_image']['tmp_name'], $uploadFile)) {
            echo "<div class='alert alert-success'>✅ Book image updated successfully!</div>";
            echo "<p><a href='../product.php?id=4' class='btn btn-primary'>View Updated Programming Book</a></p>";
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
    <title>Upload HTML & CSS Book Cover</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>📚 Replace Programming Book Cover</h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4>Current Image:</h4>
                <img src="html-css-programming-book.jpg?<?php echo time(); ?>" alt="Current Book" style="max-width: 100%; border: 1px solid #ddd; padding: 10px;">
            </div>
            <div class="col-md-6">
                <h4>Upload New Book Cover:</h4>
                <form method="post" enctype="multipart/form-data" style="border: 2px dashed #007bff; padding: 30px; text-align: center;">
                    <div class="mb-3">
                        <label for="book_image" class="form-label">Select HTML & CSS Book Image</label>
                        <input type="file" class="form-control" id="book_image" name="book_image" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">🔄 Replace Book Cover</button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <p class="text-muted">
                <strong>Instructions:</strong><br>
                1. Click "Choose File" and select your HTML & CSS book cover image<br>
                2. Click "Replace Book Cover" to upload<br>
                3. The Programming Book product will immediately use the new image
            </p>
        </div>
    </div>
</body>
</html>