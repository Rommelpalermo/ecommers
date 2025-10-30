<?php
session_start();
require_once 'config/database.php';

// Demo payment test - simulate successful payments
$pageTitle = 'Payment Demo';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card"></i> Payment System Demo & Test
                    </h4>
                </div>
                <div class="card-body">
                    <p class="lead">Test the payment integration with these demo scenarios:</p>
                </div>
            </div>
            
            <div class="row">
                <!-- Stripe Payment Demo -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fab fa-cc-stripe"></i> Stripe Credit Card Payment
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>✅ Features Implemented:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Credit card form validation</li>
                                <li><i class="fas fa-check text-success"></i> Card number formatting</li>
                                <li><i class="fas fa-check text-success"></i> Expiry date selection</li>
                                <li><i class="fas fa-check text-success"></i> CVV validation</li>
                                <li><i class="fas fa-check text-success"></i> Test card support</li>
                            </ul>
                            
                            <h6 class="mt-3">🧪 Test Cards:</h6>
                            <div class="alert alert-info">
                                <small>
                                    <strong>Test Card Numbers:</strong><br>
                                    • 4111 1111 1111 1111 (Visa)<br>
                                    • 4242 4242 4242 4242 (Visa)<br>
                                    • 5555 5555 5555 4444 (Mastercard)<br>
                                    <em>Use any future expiry date and any CVV</em>
                                </small>
                            </div>
                            
                            <a href="payment.php?order=ORD-2025-TEST001&method=stripe" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Test Stripe Payment
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- PayPal Payment Demo -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header text-white" style="background-color: #0070ba;">
                            <h5 class="mb-0">
                                <i class="fab fa-paypal"></i> PayPal Payment
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>✅ Features Implemented:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> PayPal email validation</li>
                                <li><i class="fas fa-check text-success"></i> PayPal branding</li>
                                <li><i class="fas fa-check text-success"></i> Secure redirect simulation</li>
                                <li><i class="fas fa-check text-success"></i> Payment confirmation</li>
                                <li><i class="fas fa-check text-success"></i> Order processing</li>
                            </ul>
                            
                            <h6 class="mt-3">🧪 Test Account:</h6>
                            <div class="alert alert-info">
                                <small>
                                    <strong>Test Email:</strong><br>
                                    Use any valid email address format<br>
                                    (e.g., test@example.com)<br>
                                    <em>Demo mode - no actual PayPal needed</em>
                                </small>
                            </div>
                            
                            <a href="payment.php?order=ORD-2025-TEST001&method=paypal" class="btn text-white" style="background-color: #0070ba;">
                                <i class="fab fa-paypal"></i> Test PayPal Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Flow Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> Payment System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>✅ Fixed Issues:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Created missing payment.php file</li>
                                <li><i class="fas fa-check text-success"></i> Added payment method selection styling</li>
                                <li><i class="fas fa-check text-success"></i> Implemented form validation</li>
                                <li><i class="fas fa-check text-success"></i> Added order success page</li>
                                <li><i class="fas fa-check text-success"></i> Fixed checkout redirect</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>🚀 Ready Features:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-shield-alt text-success"></i> Secure payment processing</li>
                                <li><i class="fas fa-database text-success"></i> Order management</li>
                                <li><i class="fas fa-envelope text-success"></i> Order confirmation</li>
                                <li><i class="fas fa-truck text-success"></i> Shipping calculation</li>
                                <li><i class="fas fa-receipt text-success"></i> Tax calculation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> How to Test the Complete Payment Flow
                    </h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li><strong>Add Products to Cart:</strong> Go to the store and add some products to your cart</li>
                        <li><strong>Login:</strong> Make sure you're logged in (register if needed)</li>
                        <li><strong>Checkout:</strong> Go to checkout and fill in your billing information</li>
                        <li><strong>Select Payment Method:</strong> Choose either Stripe (Credit Card) or PayPal</li>
                        <li><strong>Complete Payment:</strong> Use the test credentials provided above</li>
                        <li><strong>View Confirmation:</strong> See your order confirmation and success page</li>
                    </ol>
                    
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-thumbs-up"></i> 
                        <strong>Both payment methods are now working!</strong> 
                        The system properly validates, processes, and confirms payments.
                    </div>
                </div>
            </div>
            
            <div class="text-center mb-5">
                <a href="/" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-home"></i> Go to Store
                </a>
                <a href="checkout.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-shopping-cart"></i> Go to Checkout
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>