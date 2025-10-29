<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/Auth.php';
require_once '../includes/Cart.php';

$auth = new Auth($pdo);
$cart = new Cart($pdo, isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $response['message'] = 'Invalid JSON data';
        echo json_encode($response);
        exit;
    }
    
    $action = isset($input['action']) ? $input['action'] : '';
    
    switch ($action) {
        case 'add':
            $productId = intval(isset($input['product_id']) ? $input['product_id'] : 0);
            $quantity = intval(isset($input['quantity']) ? $input['quantity'] : 1);
            
            if ($productId <= 0 || $quantity <= 0) {
                $response['message'] = 'Invalid product ID or quantity';
                break;
            }
            
            $result = $cart->addItem($productId, $quantity);
            $response = $result;
            break;
            
        case 'update':
            $productId = intval(isset($input['product_id']) ? $input['product_id'] : 0);
            $quantity = intval(isset($input['quantity']) ? $input['quantity'] : 0);
            
            if ($productId <= 0) {
                $response['message'] = 'Invalid product ID';
                break;
            }
            
            $result = $cart->updateItem($productId, $quantity);
            $response = $result;
            break;
            
        case 'remove':
            $productId = intval(isset($input['product_id']) ? $input['product_id'] : 0);
            
            if ($productId <= 0) {
                $response['message'] = 'Invalid product ID';
                break;
            }
            
            $result = $cart->removeItem($productId);
            $response = $result;
            break;
            
        case 'clear':
            $result = $cart->clear();
            $response = $result;
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    switch ($action) {
        case 'items':
            $items = $cart->getItems();
            $response = [
                'success' => true,
                'items' => $items,
                'count' => count($items),
                'total' => $cart->getTotal()
            ];
            break;
            
        case 'count':
            $count = $cart->getItemCount();
            $response = [
                'success' => true,
                'count' => $count
            ];
            break;
            
        case 'total':
            $total = $cart->getTotal();
            $response = [
                'success' => true,
                'total' => $total
            ];
            break;
    }
}

echo json_encode($response);
?>