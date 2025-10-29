<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    $_SESSION['error'] = 'Please login to checkout';
    header('Location: login.php');
    exit;
}

$cart = new Cart($pdo, $_SESSION['user_id']);
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty';
    header('Location: cart.php');
    exit;
}

// Get user information
$user = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process checkout
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    $billingAddress = [
        'first_name' => isset($_POST['billing_first_name']) ? $_POST['billing_first_name'] : '',
        'last_name' => isset($_POST['billing_last_name']) ? $_POST['billing_last_name'] : '',
        'address' => isset($_POST['billing_address']) ? $_POST['billing_address'] : '',
        'city' => isset($_POST['billing_city']) ? $_POST['billing_city'] : '',
        'state' => isset($_POST['billing_state']) ? $_POST['billing_state'] : '',
        'zip' => isset($_POST['billing_zip']) ? $_POST['billing_zip'] : '',
        'country' => isset($_POST['billing_country']) ? $_POST['billing_country'] : 'PH'
    ];
    
    $shippingAddress = [
        'first_name' => isset($_POST['shipping_first_name']) ? $_POST['shipping_first_name'] : $billingAddress['first_name'],
        'last_name' => isset($_POST['shipping_last_name']) ? $_POST['shipping_last_name'] : $billingAddress['last_name'],
        'address' => isset($_POST['shipping_address']) ? $_POST['shipping_address'] : $billingAddress['address'],
        'city' => isset($_POST['shipping_city']) ? $_POST['shipping_city'] : $billingAddress['city'],
        'state' => isset($_POST['shipping_state']) ? $_POST['shipping_state'] : $billingAddress['state'],
        'zip' => isset($_POST['shipping_zip']) ? $_POST['shipping_zip'] : $billingAddress['zip'],
        'country' => isset($_POST['shipping_country']) ? $_POST['shipping_country'] : $billingAddress['country']
    ];
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'address', 'city', 'state', 'zip'];
    $isValid = true;
    
    foreach ($requiredFields as $field) {
        if (empty($billingAddress[$field])) {
            $isValid = false;
            break;
        }
    }
    
    if (!$isValid) {
        $_SESSION['error'] = 'Please fill in all required fields';
    } elseif (empty($paymentMethod)) {
        $_SESSION['error'] = 'Please select a payment method';
    } else {
        // Create order
        try {
            $pdo->beginTransaction();
            
            // Generate order number
            $orderNumber = 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -8));
            
            // Calculate totals
            $subtotal = $cartTotal;
            $taxAmount = $subtotal * 0.08; // 8% tax
            $shippingAmount = $subtotal >= 2500 ? 0 : 500; // Free shipping over ₱2,500
            $totalAmount = $subtotal + $taxAmount + $shippingAmount;
            
            // Insert order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, tax_amount, shipping_amount,
                                  payment_method, billing_address, shipping_address, status, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $orderNumber,
                $totalAmount,
                $taxAmount,
                $shippingAmount,
                $paymentMethod,
                json_encode($billingAddress),
                json_encode($shippingAddress)
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Insert order items
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($cartItems as $item) {
                $unitPrice = $item['sale_price'] ?: $item['price'];
                $totalPrice = $unitPrice * $item['quantity'];
                
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    'SKU-' . $item['product_id'],
                    $item['quantity'],
                    $unitPrice,
                    $totalPrice
                ]);
                
                // Update product stock
                $updateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $updateStock->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $cart->clear();
            
            $pdo->commit();
            
            // Redirect to payment processing
            $_SESSION['success'] = 'Order placed successfully!';
            header('Location: payment.php?order=' . $orderNumber . '&method=' . $paymentMethod);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Failed to create order: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
$additionalCSS = ['https://js.stripe.com/v3/'];
include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <h2 class="mb-4">
            <i class="fas fa-credit-card"></i> Checkout
        </h2>
        
        <form method="POST" action="" id="checkout-form">
            <!-- Billing Address -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Billing Address</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="billing_first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="billing_first_name" name="billing_first_name" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="billing_last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="billing_last_name" name="billing_last_name" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="billing_address" class="form-label">Address *</label>
                        <input type="text" class="form-control" id="billing_address" name="billing_address" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="billing_city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="billing_city" name="billing_city" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="billing_state" class="form-label">State *</label>
                            <input type="text" class="form-control" id="billing_state" name="billing_state" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="billing_zip" class="form-label">ZIP Code *</label>
                            <input type="text" class="form-control" id="billing_zip" name="billing_zip" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Shipping Address</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="same_as_billing" checked>
                            <label class="form-check-label" for="same_as_billing">
                                Same as billing address
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="shipping-fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shipping_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="shipping_first_name" name="shipping_first_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shipping_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="shipping_last_name" name="shipping_last_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="shipping_address" name="shipping_address">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shipping_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="shipping_city" name="shipping_city">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="shipping_state" class="form-label">State</label>
                            <input type="text" class="form-control" id="shipping_state" name="shipping_state">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="shipping_zip" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" id="shipping_zip" name="shipping_zip">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="payment-method" data-method="stripe">
                                <div class="text-center">
                                    <i class="fab fa-cc-stripe fa-2x text-primary mb-2"></i>
                                    <h6>Credit Card</h6>
                                    <small class="text-muted">Powered by Stripe</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="payment-method" data-method="paypal">
                                <div class="text-center">
                                    <i class="fab fa-paypal fa-2x text-primary mb-2"></i>
                                    <h6>PayPal</h6>
                                    <small class="text-muted">Pay with PayPal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="payment_method" required>
                </div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-credit-card"></i> Place Order
                </button>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <!-- Order Summary -->
        <div class="card order-summary">
            <div class="card-header">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <?php foreach ($cartItems as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <small><?php echo htmlspecialchars($item['name']); ?></small>
                            <br><small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                        </div>
                        <small>₱<?php echo number_format(($item['sale_price'] ?: $item['price']) * $item['quantity'], 2); ?></small>
                    </div>
                <?php endforeach; ?>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <div class="d-flex justify-content-between">
                    <span>Shipping:</span>
                    <span><?php echo $cartTotal >= 2500 ? 'Free' : '₱500.00'; ?></span>
                </div>
                
                <div class="d-flex justify-content-between">
                    <span>Tax (8%):</span>
                    <span>₱<?php echo number_format($cartTotal * 0.08, 2); ?></span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total:</span>
                    <span>₱<?php echo number_format($cartTotal + ($cartTotal >= 2500 ? 0 : 500) + ($cartTotal * 0.08), 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Security Info -->
        <div class="card mt-3">
            <div class="card-body bg-light">
                <h6 class="card-title">
                    <i class="fas fa-lock text-success"></i> Secure Checkout
                </h6>
                <small class="text-muted">
                    SSL encrypted checkout. Your information is safe and secure.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Same as billing checkbox
    const sameAsBilling = document.getElementById('same_as_billing');
    const shippingFields = document.getElementById('shipping-fields');
    
    sameAsBilling.addEventListener('change', function() {
        if (this.checked) {
            shippingFields.style.display = 'none';
        } else {
            shippingFields.style.display = 'block';
        }
    });
    
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            // Remove selected class from all methods
            document.querySelectorAll('.payment-method').forEach(m => {
                m.classList.remove('selected');
            });
            
            // Add selected class to clicked method
            this.classList.add('selected');
            
            // Update hidden input
            document.getElementById('payment_method').value = this.dataset.method;
        });
    });
    
    // Form validation
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        const paymentMethod = document.getElementById('payment_method').value;
        
        if (!paymentMethod) {
            e.preventDefault();
            alert('Please select a payment method');
            return false;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>