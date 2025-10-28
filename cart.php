<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);
$cart = new Cart($pdo, isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

// Get cart items
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty';
    header('Location: index.php');
    exit;
}

$pageTitle = 'Shopping Cart';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <h2 class="mb-4">
            <i class="fas fa-shopping-cart"></i> Shopping Cart
        </h2>
        
        <div class="card">
            <div class="card-body">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="<?php echo $item['main_image'] ? UPLOAD_URL . $item['main_image'] : 'assets/images/no-image.jpg'; ?>" 
                                     class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="max-height: 100px; object-fit: cover;">
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted mb-0">SKU: <?php echo htmlspecialchars($item['product_id']); ?></p>
                                <p class="text-muted mb-0">Stock: <?php echo $item['stock_quantity']; ?> available</p>
                            </div>
                            <div class="col-md-2">
                                <div class="price-section">
                                    <?php if ($item['sale_price']): ?>
                                        <span class="fw-bold text-primary">$<?php echo number_format($item['sale_price'], 2); ?></span>
                                        <br><small class="text-muted text-decoration-line-through">$<?php echo number_format($item['price'], 2); ?></small>
                                    <?php else: ?>
                                        <span class="fw-bold text-primary">$<?php echo number_format($item['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control form-control-sm mx-2 text-center quantity-input" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['stock_quantity']; ?>"
                                           data-product-id="<?php echo $item['product_id']; ?>"
                                           onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <div class="mb-2">
                                    <strong>$<?php echo number_format(($item['sale_price'] ?: $item['price']) * $item['quantity'], 2); ?></strong>
                                </div>
                                <button class="btn btn-danger btn-sm remove-from-cart" data-product-id="<?php echo $item['product_id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="text-end mt-3">
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Clear Cart
                    </button>
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card order-summary">
            <div class="card-body">
                <h5 class="card-title">Order Summary</h5>
                
                <div class="d-flex justify-content-between">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal">$<?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <div class="d-flex justify-content-between">
                    <span>Shipping:</span>
                    <span class="text-muted">Free</span>
                </div>
                
                <div class="d-flex justify-content-between">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($cartTotal * 0.08, 2); ?></span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total:</span>
                    <span id="cart-total">$<?php echo number_format($cartTotal * 1.08, 2); ?></span>
                </div>
                
                <div class="d-grid mt-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login to Checkout
                        </a>
                        <small class="text-muted text-center mt-2">You need to login to complete your purchase</small>
                    <?php endif; ?>
                </div>
                
                <!-- Coupon Code -->
                <div class="mt-3">
                    <label for="couponCode" class="form-label">Coupon Code</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="couponCode" placeholder="Enter coupon code">
                        <button class="btn btn-outline-secondary" type="button">Apply</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Info -->
        <div class="card mt-3">
            <div class="card-body bg-light">
                <h6 class="card-title">
                    <i class="fas fa-shield-alt text-success"></i> Secure Checkout
                </h6>
                <small class="text-muted">
                    Your payment information is encrypted and secure. We accept all major credit cards and PayPal.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function updateQuantity(productId, quantity) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update cart');
        }
    })
    .catch(error => {
        alert('An error occurred');
        console.error('Error:', error);
    });
}

function removeFromCart(productId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        alert('An error occurred');
        console.error('Error:', error);
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'clear'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            alert(data.message || 'Failed to clear cart');
        }
    })
    .catch(error => {
        alert('An error occurred');
        console.error('Error:', error);
    });
}

// Remove from cart event listeners
document.querySelectorAll('.remove-from-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        removeFromCart(productId);
    });
});
</script>

<?php include 'includes/footer.php'; ?>