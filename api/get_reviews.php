<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/rating_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $product_id = (int)$_GET['product_id'] ?? 0;
    $sort = $_GET['sort'] ?? 'newest';
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    // Validate sort parameter
    $validSorts = ['newest', 'oldest', 'highest', 'lowest', 'helpful'];
    if (!in_array($sort, $validSorts)) {
        $sort = 'newest';
    }
    
    $html = displayReviews($pdo, $product_id, $sort);
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    error_log("Get reviews error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>