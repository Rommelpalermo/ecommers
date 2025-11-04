<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $rating_id = (int)$_POST['rating_id'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? null;
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if ($rating_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating ID']);
        exit;
    }
    
    // Check if rating exists
    $stmt = $pdo->prepare("SELECT id FROM product_ratings WHERE id = ? AND status = 'approved'");
    $stmt->execute([$rating_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Rating not found']);
        exit;
    }
    
    // Check if user already voted
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT id FROM rating_helpful WHERE rating_id = ? AND user_id = ?");
        $stmt->execute([$rating_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM rating_helpful WHERE rating_id = ? AND user_ip = ?");
        $stmt->execute([$rating_id, $user_ip]);
    }
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already marked this review as helpful']);
        exit;
    }
    
    // Insert helpful vote
    $stmt = $pdo->prepare("INSERT INTO rating_helpful (rating_id, user_id, user_ip) VALUES (?, ?, ?)");
    $stmt->execute([$rating_id, $user_id, $user_ip]);
    
    // Update helpful count
    $stmt = $pdo->prepare("UPDATE product_ratings SET helpful_count = helpful_count + 1 WHERE id = ?");
    $stmt->execute([$rating_id]);
    
    // Get updated count
    $stmt = $pdo->prepare("SELECT helpful_count FROM product_ratings WHERE id = ?");
    $stmt->execute([$rating_id]);
    $count = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your feedback!',
        'new_count' => $count
    ]);
    
} catch (PDOException $e) {
    error_log("Helpful vote error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Helpful vote error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>