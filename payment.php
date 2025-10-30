<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';

$auth = new Auth($pdo);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    $_SESSION['error'] = 'Please login to access payment';
    header('Location: login.php');
    exit;
}

// Get order details
$orderNumber = isset($_GET['order']) ? $_GET['order'] : '';
$paymentMethod = isset($_GET['method']) ? $_GET['method'] : '';

if (empty($orderNumber) || empty($paymentMethod)) {
    $_SESSION['error'] = 'Invalid payment request';
    header('Location: cart.php');
    exit;
}

// Get order from database
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$orderNumber, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = 'Order not found';
    header('Location: orders.php');
    exit;
}

// If order is already paid, redirect to success
if ($order['payment_status'] === 'paid') {
    $_SESSION['success'] = 'Payment completed successfully!';
    header('Location: order-success.php?order=' . $orderNumber);
    exit;
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentSuccess = false;
    $errorMessage = '';
    
    try {
        if ($paymentMethod === 'stripe') {
            // Stripe payment processing
            $paymentSuccess = processStripePayment($order, $_POST);
        } elseif ($paymentMethod === 'paypal') {
            // PayPal payment processing  
            $paymentSuccess = processPayPalPayment($order, $_POST);
        }
        
        if ($paymentSuccess) {
            // Update order status
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing', paid_at = NOW() WHERE id = ?");
            $stmt->execute([$order['id']]);
            
            $_SESSION['success'] = 'Payment completed successfully!';
            header('Location: order-success.php?order=' . $orderNumber);
            exit;
        }
        
    } catch (Exception $e) {
        $errorMessage = 'Payment failed: ' . $e->getMessage();
    }
}

function processStripePayment($order, $postData) {
    // For demo purposes, simulate successful payment
    // In production, integrate with Stripe API
    
    // Validate card details (basic validation for demo)
    $cardNumber = isset($postData['card_number']) ? $postData['card_number'] : '';
    $expiryMonth = isset($postData['expiry_month']) ? $postData['expiry_month'] : '';
    $expiryYear = isset($postData['expiry_year']) ? $postData['expiry_year'] : '';
    $cvv = isset($postData['cvv']) ? $postData['cvv'] : '';
    
    if (empty($cardNumber) || empty($expiryMonth) || empty($expiryYear) || empty($cvv)) {
        throw new Exception('Please fill in all card details');
    }
    
    // For demo - accept test card numbers
    $testCards = ['4111111111111111', '4242424242424242', '5555555555554444'];
    if (!in_array(str_replace(' ', '', $cardNumber), $testCards)) {
        throw new Exception('Please use test card: 4111 1111 1111 1111');
    }
    
    // Simulate successful payment
    return true;
}

function processPayPalPayment($order, $postData) {
    // For demo purposes, simulate successful PayPal payment
    // In production, integrate with PayPal SDK
    
    $paypalEmail = isset($postData['paypal_email']) ? $postData['paypal_email'] : '';
    
    if (empty($paypalEmail)) {
        throw new Exception('PayPal email is required');
    }
    
    if (!filter_var($paypalEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }
    
    // Simulate successful PayPal payment
    return true;
}

$pageTitle = 'Payment - ' . $orderNumber;
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card"></i> 
                        Complete Payment - Order <?php echo htmlspecialchars($orderNumber); ?>
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- Order Summary -->
                    <div class="mb-4">
                        <h6>Order Summary</h6>
                        <div class="row">
                            <div class="col-sm-6">Total Amount:</div>
                            <div class="col-sm-6 text-end"><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <?php if ($paymentMethod === 'stripe'): ?>
                        <!-- Stripe Credit Card Payment -->
                        <div id="stripe-payment">
                            <h5><i class="fab fa-cc-stripe"></i> Credit Card Payment</h5>
                            <p class="text-muted">Powered by Stripe - Secure SSL Encryption</p>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="card_number" class="form-label">Card Number</label>
                                        <input type="text" class="form-control" id="card_number" name="card_number" 
                                               placeholder="4111 1111 1111 1111" maxlength="19" required>
                                        <small class="text-muted">Test card: 4111 1111 1111 1111</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_month" class="form-label">Expiry Month</label>
                                        <select class="form-control" id="expiry_month" name="expiry_month" required>
                                            <option value="">Select Month</option>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                                <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_year" class="form-label">Expiry Year</label>
                                        <select class="form-control" id="expiry_year" name="expiry_year" required>
                                            <option value="">Select Year</option>
                                            <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" maxlength="4" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="card_name" class="form-label">Cardholder Name</label>
                                        <input type="text" class="form-control" id="card_name" name="card_name" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-lock"></i> Pay ₱<?php echo number_format($order['total_amount'], 2); ?>
                                    </button>
                                    <a href="checkout.php" class="btn btn-outline-secondary">Back to Checkout</a>
                                </div>
                            </form>
                        </div>
                        
                    <?php elseif ($paymentMethod === 'paypal'): ?>
                        <!-- PayPal Payment -->
                        <div id="paypal-payment">
                            <h5><i class="fab fa-paypal"></i> PayPal Payment</h5>
                            <p class="text-muted">You will be redirected to PayPal to complete your payment</p>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="paypal_email" class="form-label">PayPal Email</label>
                                    <input type="email" class="form-control" id="paypal_email" name="paypal_email" 
                                           placeholder="your-email@example.com" required>
                                    <small class="text-muted">Use any valid email for demo</small>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg" style="background-color: #0070ba;">
                                        <i class="fab fa-paypal"></i> Pay with PayPal ₱<?php echo number_format($order['total_amount'], 2); ?>
                                    </button>
                                    <a href="checkout.php" class="btn btn-outline-secondary">Back to Checkout</a>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="card mt-3">
                <div class="card-body bg-light text-center">
                    <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                    <h6>Secure Payment</h6>
                    <small class="text-muted">
                        Your payment information is encrypted and secure. 
                        We never store your credit card details.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format credit card number input
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
            }
        });
    }
    
    // CVV input validation
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>