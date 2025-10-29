<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['book_image'])) {
    $uploadDir = 'uploads/';
    $fileName = 'html-css-programming-book.jpg';
    $uploadFile = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['book_image']['tmp_name'], $uploadFile)) {
        // Update the Programming Book product with the new image
        $sql = "UPDATE products SET main_image = ? WHERE id = 4";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fileName]);
        
        echo "Book image uploaded successfully!";
        echo "<br><a href='product.php?id=4'>View Programming Book</a>";
    } else {
        echo "Failed to upload image.";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Book Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Upload HTML & CSS Book Image</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="book_image" class="form-label">Select Book Image</label>
                <input type="file" class="form-control" id="book_image" name="book_image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload Book Image</button>
        </form>
        
        <div class="mt-3">
            <small class="text-muted">This will upload the image as "html-css-programming-book.jpg" and update the Programming Book product.</small>
        </div>
    </div>
</body>
</html>