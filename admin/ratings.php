<?php
require_once '../config/database.php';

// Simple admin check (in a real app, you'd have proper admin authentication)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle status updates
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $rating_id = (int)$_POST['rating_id'];
    $new_status = $_POST['status'];
    
    if (in_array($new_status, ['pending', 'approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE product_ratings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $rating_id]);
        $_SESSION['success'] = 'Rating status updated successfully';
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$product_filter = (int)($_GET['product'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "pr.status = ?";
    $params[] = $status_filter;
}

if ($product_filter > 0) {
    $where_conditions[] = "pr.product_id = ?";
    $params[] = $product_filter;
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Sort options
$order_by = '';
switch ($sort) {
    case 'oldest':
        $order_by = 'ORDER BY pr.created_at ASC';
        break;
    case 'rating_high':
        $order_by = 'ORDER BY pr.rating DESC, pr.created_at DESC';
        break;
    case 'rating_low':
        $order_by = 'ORDER BY pr.rating ASC, pr.created_at DESC';
        break;
    case 'product':
        $order_by = 'ORDER BY p.name, pr.created_at DESC';
        break;
    default:
        $order_by = 'ORDER BY pr.created_at DESC';
}

// Get ratings
$stmt = $pdo->prepare("
    SELECT pr.*, p.name as product_name, p.image_url as product_image,
           u.username as user_username
    FROM product_ratings pr
    LEFT JOIN products p ON pr.product_id = p.id
    LEFT JOIN users u ON pr.user_id = u.id
    $where_clause
    $order_by
");
$stmt->execute($params);
$ratings = $stmt->fetchAll();

// Get products for filter dropdown
$products_stmt = $pdo->query("SELECT id, name FROM products WHERE is_active = 1 ORDER BY name");
$products = $products_stmt->fetchAll();

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_ratings,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_ratings,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_ratings,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_ratings,
        AVG(CASE WHEN status = 'approved' THEN rating ELSE NULL END) as avg_rating
    FROM product_ratings
");
$stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Product Ratings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #ffffff;
            min-height: 100vh;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .table-dark {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }
        
        .rating-stars i {
            color: #ffc107;
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        .review-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .admin-header {
            background: rgba(255, 107, 53, 0.1);
            border-bottom: 2px solid #ff6b35;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark admin-header mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-star me-2"></i>Product Ratings Management
            </a>
            <div class="d-flex">
                <a href="add_item.php" class="btn btn-outline-success me-2">
                    <i class="fas fa-plus me-1"></i>Add Item
                </a>
                <a href="manage_items.php" class="btn btn-outline-info me-2">
                    <i class="fas fa-boxes me-1"></i>Manage Items
                </a>
                <a href="feedback.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-comments me-1"></i>Feedback
                </a>
                <a href="../index.php" class="btn btn-outline-warning">
                    <i class="fas fa-store me-1"></i>View Store
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning"><?php echo $stats['total_ratings']; ?></h3>
                        <p class="mb-0">Total Ratings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?php echo $stats['pending_ratings']; ?></h3>
                        <p class="mb-0">Pending Review</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?php echo $stats['approved_ratings']; ?></h3>
                        <p class="mb-0">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning"><?php echo number_format($stats['avg_rating'], 1); ?></h3>
                        <p class="mb-0">Average Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select bg-dark text-light border-secondary">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product</label>
                        <select name="product" class="form-select bg-dark text-light border-secondary">
                            <option value="0">All Products</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" <?php echo $product_filter === $product['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select bg-dark text-light border-secondary">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="rating_high" <?php echo $sort === 'rating_high' ? 'selected' : ''; ?>>Highest Rating</option>
                            <option value="rating_low" <?php echo $sort === 'rating_low' ? 'selected' : ''; ?>>Lowest Rating</option>
                            <option value="product" <?php echo $sort === 'product' ? 'selected' : ''; ?>>Product Name</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning me-2">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-light">
                            <i class="fas fa-refresh me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ratings Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Product Ratings (<?php echo count($ratings); ?> results)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ratings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No ratings found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ratings as $rating): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($rating['product_image']): ?>
                                                    <img src="../<?php echo htmlspecialchars($rating['product_image']); ?>" 
                                                         alt="Product" class="rounded me-2" width="40" height="40">
                                                <?php endif; ?>
                                                <small><?php echo htmlspecialchars($rating['product_name']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($rating['user_username'] ?: $rating['user_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($rating['user_email']); ?></small>
                                                <?php if ($rating['verified_purchase']): ?>
                                                    <br><span class="badge bg-success small">Verified Purchase</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $rating['rating'] ? '' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted"><?php echo $rating['rating']; ?>/5</small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($rating['review_title']); ?></strong>
                                                <p class="review-text small text-muted mb-1" title="<?php echo htmlspecialchars($rating['review_text']); ?>">
                                                    <?php echo htmlspecialchars($rating['review_text']); ?>
                                                </p>
                                                <?php if ($rating['helpful_count'] > 0): ?>
                                                    <small class="text-info">
                                                        <i class="fas fa-thumbs-up"></i> <?php echo $rating['helpful_count']; ?> helpful
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?php echo date('M j, Y', strtotime($rating['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success', 
                                                'rejected' => 'danger'
                                            ];
                                            $color = $statusColors[$rating['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?> status-badge">
                                                <?php echo ucfirst($rating['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-dark">
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $rating['id']; ?>, 'approved')">
                                                            <i class="fas fa-check text-success me-2"></i>Approve
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $rating['id']; ?>, 'rejected')">
                                                            <i class="fas fa-times text-danger me-2"></i>Reject
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $rating['id']; ?>, 'pending')">
                                                            <i class="fas fa-clock text-warning me-2"></i>Mark Pending
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="viewFullReview(<?php echo $rating['id']; ?>)">
                                                            <i class="fas fa-eye text-info me-2"></i>View Full Review
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for status updates -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="rating_id" id="statusRatingId">
        <input type="hidden" name="status" id="statusValue">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(ratingId, status) {
            if (confirm(`Are you sure you want to ${status} this rating?`)) {
                document.getElementById('statusRatingId').value = ratingId;
                document.getElementById('statusValue').value = status;
                document.getElementById('statusForm').submit();
            }
        }

        function viewFullReview(ratingId) {
            // You could implement a modal or redirect to a detailed view
            alert('Full review view feature - would show complete review details');
        }

        // Show success message if any
        <?php if (isset($_SESSION['success'])): ?>
            alert('<?php echo $_SESSION['success']; unset($_SESSION['success']); ?>');
        <?php endif; ?>
    </script>
</body>
</html>