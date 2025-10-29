<?php
// This file will help save the uploaded book image
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['book_image'])) {
    $uploadDir = '../uploads/';
    $fileName = 'html-css-programming-book.jpg';
    $uploadFile = $uploadDir . $fileName;
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (move_uploaded_file($_FILES['book_image']['tmp_name'], $uploadFile)) {
        echo json_encode(['success' => true, 'message' => 'Book image uploaded successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Replace Book Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-container {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .preview-image {
            max-width: 300px;
            max-height: 400px;
            border: 1px solid #ddd;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <h3>Current Book Image</h3>
                <img src="../uploads/html-css-programming-book.jpg?<?php echo time(); ?>" alt="Current Book" class="preview-image">
            </div>
            <div class="col-md-6">
                <h3>Upload New Book Image</h3>
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="book_image" class="form-label">Select HTML & CSS Book Image</label>
                        <input type="file" class="form-control" id="book_image" name="book_image" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Replace Book Image</button>
                </form>
                
                <div id="preview" class="preview-container" style="display: none;">
                    <h5>Preview:</h5>
                    <img id="previewImg" src="" alt="Preview" class="preview-image">
                </div>
                
                <div id="result" class="mt-3"></div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="../product.php?id=4" class="btn btn-success">View Programming Book Product</a>
            <a href="../" class="btn btn-secondary">Back to Store</a>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('book_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle form submission
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const fileInput = document.getElementById('book_image');
            formData.append('book_image', fileInput.files[0]);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data.success) {
                    resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    // Refresh the current image
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<div class="alert alert-danger">Error uploading image</div>';
            });
        });
    </script>
</body>
</html>