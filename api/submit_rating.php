<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get form data
    $product_id = (int)$_POST['product_id'] ?? 0;
    $rating = (int)$_POST['rating'] ?? 0;
    $review_title = trim($_POST['review_title'] ?? '');
    $review_text = trim($_POST['review_text'] ?? '');
    $pros = trim($_POST['pros'] ?? '');
    $cons = trim($_POST['cons'] ?? '');
    $verified_purchase = isset($_POST['verified_purchase']) ? 1 : 0;
    
    // User data (from session if logged in, or from form)
    $user_id = $_SESSION['user_id'] ?? null;
    $user_name = '';
    $user_email = '';
    
    if ($user_id) {
        // Get user info from database
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $user_name = $user['username'];
            $user_email = $user['email'];
        }
    } else {
        // Get from form
        $user_name = trim($_POST['reviewer_name'] ?? '');
        $user_email = trim($_POST['reviewer_email'] ?? '');
    }
    
    // Validation
    $errors = [];
    
    if ($product_id <= 0) {
        $errors[] = 'Invalid product ID';
    }
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Rating must be between 1 and 5 stars';
    }
    
    if (empty($review_title)) {
        $errors[] = 'Review title is required';
    }
    
    if (empty($review_text)) {
        $errors[] = 'Review text is required';
    }
    
    if (empty($user_name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        $errors[] = 'Product not found';
    }
    
    // Check for duplicate review (same email/user for same product)
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT id FROM product_ratings WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$product_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM product_ratings WHERE product_id = ? AND user_email = ?");
        $stmt->execute([$product_id, $user_email]);
    }
    
    if ($stmt->fetch()) {
        $errors[] = 'You have already reviewed this product';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    // Insert the rating
    $stmt = $pdo->prepare("
        INSERT INTO product_ratings 
        (product_id, user_id, user_name, user_email, rating, review_title, review_text, pros, cons, verified_purchase, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $success = $stmt->execute([
        $product_id,
        $user_id,
        $user_name,
        $user_email,
        $rating,
        $review_title,
        $review_text,
        $pros,
        $cons,
        $verified_purchase
    ]);
    
    if ($success) {
        // Send notification email to admin (optional)
        $review_id = $pdo->lastInsertId();
        
        // Log the submission
        error_log("New review submitted: ID $review_id, Product $product_id, Rating $rating");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Review submitted successfully! It will be visible after approval.',
            'review_id' => $review_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
    }
    
} catch (PDOException $e) {
    error_log("Rating submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Rating submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your review']);
}
?>