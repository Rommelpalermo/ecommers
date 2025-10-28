<?php
class Cart {
    private $pdo;
    private $userId;
    private $sessionId;
    
    public function __construct($pdo, $userId = null) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->sessionId = session_id();
    }
    
    public function addItem($productId, $quantity = 1) {
        try {
            // Check if product exists and has sufficient stock
            $stmt = $this->pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return ['success' => false, 'message' => 'Product not found'];
            }
            
            if ($product['stock_quantity'] < $quantity) {
                return ['success' => false, 'message' => 'Insufficient stock'];
            }
            
            // Check if item already exists in cart
            $stmt = $this->pdo->prepare("
                SELECT id, quantity FROM cart 
                WHERE product_id = ? AND " . ($this->userId ? "user_id = ?" : "session_id = ?")
            );
            $stmt->execute($this->userId ? [$productId, $this->userId] : [$productId, $this->sessionId]);
            $existingItem = $stmt->fetch();
            
            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem['quantity'] + $quantity;
                if ($newQuantity > $product['stock_quantity']) {
                    return ['success' => false, 'message' => 'Insufficient stock'];
                }
                
                $stmt = $this->pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existingItem['id']]);
            } else {
                // Add new item
                $stmt = $this->pdo->prepare("
                    INSERT INTO cart (user_id, session_id, product_id, quantity) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$this->userId, $this->sessionId, $productId, $quantity]);
            }
            
            return ['success' => true, 'message' => 'Item added to cart'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to add item: ' . $e->getMessage()];
        }
    }
    
    public function updateItem($productId, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($productId);
            }
            
            // Check stock
            $stmt = $this->pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if ($product['stock_quantity'] < $quantity) {
                return ['success' => false, 'message' => 'Insufficient stock'];
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE cart SET quantity = ? 
                WHERE product_id = ? AND " . ($this->userId ? "user_id = ?" : "session_id = ?")
            );
            $stmt->execute($this->userId ? [$quantity, $productId, $this->userId] : [$quantity, $productId, $this->sessionId]);
            
            return ['success' => true, 'message' => 'Cart updated'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update cart: ' . $e->getMessage()];
        }
    }
    
    public function removeItem($productId) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM cart 
                WHERE product_id = ? AND " . ($this->userId ? "user_id = ?" : "session_id = ?")
            );
            $stmt->execute($this->userId ? [$productId, $this->userId] : [$productId, $this->sessionId]);
            
            return ['success' => true, 'message' => 'Item removed from cart'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to remove item: ' . $e->getMessage()];
        }
    }
    
    public function getItems() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, p.name, p.price, p.sale_price, p.main_image, p.stock_quantity
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE " . ($this->userId ? "c.user_id = ?" : "c.session_id = ?") . "
                ORDER BY c.added_at DESC
            ");
            $stmt->execute($this->userId ? [$this->userId] : [$this->sessionId]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getTotal() {
        $items = $this->getItems();
        $total = 0;
        
        foreach ($items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $total += $price * $item['quantity'];
        }
        
        return $total;
    }
    
    public function getItemCount() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(quantity) as count 
                FROM cart 
                WHERE " . ($this->userId ? "user_id = ?" : "session_id = ?")
            );
            $stmt->execute($this->userId ? [$this->userId] : [$this->sessionId]);
            $result = $stmt->fetch();
            
            return $result['count'] ?: 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function clear() {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM cart 
                WHERE " . ($this->userId ? "user_id = ?" : "session_id = ?")
            );
            $stmt->execute($this->userId ? [$this->userId] : [$this->sessionId]);
            
            return ['success' => true, 'message' => 'Cart cleared'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to clear cart: ' . $e->getMessage()];
        }
    }
}
?>