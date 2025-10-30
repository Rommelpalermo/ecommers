<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';

$auth = new Auth($pdo);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    $_SESSION['error'] = 'Please login to view order details';
    header('Location: login.php');
    exit;
}

// Get order number
$orderNumber = isset($_GET['order']) ? $_GET['order'] : '';

if (empty($orderNumber)) {
    $_SESSION['error'] = 'Invalid order number';
    header('Location: orders.php');
    exit;
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, oi.product_name, oi.quantity, oi.unit_price, oi.total_price
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->execute([$orderNumber, $_SESSION['user_id']]);
$orderData = $stmt->fetchAll();

if (empty($orderData)) {
    $_SESSION['error'] = 'Order not found';
    header('Location: orders.php');
    exit;
}

$order = $orderData[0];
$orderItems = $orderData;

$pageTitle = 'Order Confirmation';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Success Message -->
            <div class="card border-success mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                    <h2 class="text-success">Order Confirmed!</h2>
                    <p class="lead">Thank you for your purchase. Your order has been successfully placed.</p>
                    <p class="text-muted">Order Number: <strong><?php echo htmlspecialchars($orderNumber); ?></strong></p>
                </div>
            </div>
            
            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Order Number:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($order['order_number']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Order Date:</strong></div>
                        <div class="col-sm-8"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Payment Method:</strong></div>
                        <div class="col-sm-8">
                            <?php 
                            echo $order['payment_method'] === 'stripe' ? 'Credit Card (Stripe)' : 'PayPal';
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Payment Status:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-success">Paid</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Order Status:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-info">Processing</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td>₱<?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Order Total -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-8"><strong>Subtotal:</strong></div>
                        <div class="col-sm-4 text-end">₱<?php echo number_format($order['total_amount'] - $order['tax_amount'] - $order['shipping_amount'], 2); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-8"><strong>Shipping:</strong></div>
                        <div class="col-sm-4 text-end">₱<?php echo number_format($order['shipping_amount'], 2); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-8"><strong>Tax:</strong></div>
                        <div class="col-sm-4 text-end">₱<?php echo number_format($order['tax_amount'], 2); ?></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-8"><strong>Total:</strong></div>
                        <div class="col-sm-4 text-end"><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <?php if (!empty($order['shipping_address'])): ?>
                <?php $shippingAddress = json_decode($order['shipping_address'], true); ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-truck"></i> Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <address>
                            <?php echo htmlspecialchars($shippingAddress['first_name'] . ' ' . $shippingAddress['last_name']); ?><br>
                            <?php echo htmlspecialchars($shippingAddress['address']); ?><br>
                            <?php echo htmlspecialchars($shippingAddress['city'] . ', ' . $shippingAddress['state'] . ' ' . $shippingAddress['zip']); ?><br>
                            <?php echo htmlspecialchars($shippingAddress['country']); ?>
                        </address>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Next Steps -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> What's Next?</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Your order is being processed</li>
                        <li><i class="fas fa-check text-success"></i> You will receive an email confirmation shortly</li>
                        <li><i class="fas fa-check text-success"></i> We'll notify you when your order ships</li>
                        <li><i class="fas fa-check text-success"></i> Track your order anytime in your account</li>
                    </ul>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center mb-5">
                <a href="/" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-home"></i> Continue Shopping
                </a>
                <a href="orders.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-list"></i> View All Orders
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>