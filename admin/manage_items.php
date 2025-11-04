<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

$auth = new Auth($pdo);

// Simple admin check
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Handle delete action
if (isset($_POST['delete_item'])) {
    $item_id = (int)$_POST['item_id'];
    try {
        // Delete from auction_items first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM auction_items WHERE product_id = ?");
        $stmt->execute([$item_id]);
        
        // Delete from products
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$item_id]);
        
        $_SESSION['success'] = "Item deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting item: " . $e->getMessage();
    }
    header('Location: manage_items.php');
    exit;
}

// Get all auction items
try {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, 
               ai.auction_date, ai.auction_type, ai.item_condition, 
               ai.starting_price as auction_starting_price, ai.reserve_price, 
               ai.current_bid, ai.bid_count, ai.status as auction_status
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN auction_items ai ON p.id = ai.product_id
        WHERE p.is_active = 1 
        ORDER BY p.created_at DESC
    ");
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    $items = [];
    $_SESSION['error'] = "Error loading items: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Auction Items - J Brinces Trading</title>
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
        
        .admin-header {
            background: rgba(255, 107, 53, 0.1);
            border-bottom: 2px solid #ff6b35;
            backdrop-filter: blur(10px);
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark admin-header mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes me-2"></i>Manage Auction Items
            </a>
            <div class="d-flex">
                <a href="add_item.php" class="btn btn-success me-2">
                    <i class="fas fa-plus me-1"></i>Add New Item
                </a>
                <a href="feedback.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-comments me-1"></i>Feedback
                </a>
                <a href="ratings.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-star me-1"></i>Ratings
                </a>
                <a href="../index.php" class="btn btn-outline-warning">
                    <i class="fas fa-store me-1"></i>View Store
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning"><?php echo count($items); ?></h3>
                        <p class="mb-0">Total Items</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?php echo count(array_filter($items, function($item) { return $item['auction_status'] === 'upcoming'; })); ?></h3>
                        <p class="mb-0">Upcoming Auctions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?php echo count(array_filter($items, function($item) { return $item['auction_status'] === 'active'; })); ?></h3>
                        <p class="mb-0">Active Auctions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning"><?php echo count(array_filter($items, function($item) { return $item['auction_status'] === 'sold'; })); ?></h3>
                        <p class="mb-0">Sold Items</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Auction Items (<?php echo count($items); ?>)</h5>
                <a href="add_item.php" class="btn btn-warning">
                    <i class="fas fa-plus me-2"></i>Add New Item
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Item Details</th>
                                <th>Category</th>
                                <th>Auction Info</th>
                                <th>Pricing</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No auction items found</p>
                                        <a href="add_item.php" class="btn btn-warning mt-2">Add Your First Item</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['image_url']): ?>
                                                <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                     alt="Item Image" class="item-image">
                                            <?php else: ?>
                                                <div class="item-image bg-secondary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                <br>
                                                <small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                                <br>
                                                <small class="text-muted">
                                                    Condition: <span class="text-warning"><?php echo ucfirst(str_replace('_', ' ', $item['item_condition'] ?? 'N/A')); ?></span>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($item['category_name'] ?? 'No Category'); ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-light">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo $item['auction_date'] ? date('M j, Y g:i A', strtotime($item['auction_date'])) : 'Not Set'; ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    Type: <?php echo ucfirst(str_replace('_', ' ', $item['auction_type'] ?? 'N/A')); ?>
                                                </small>
                                                <?php if ($item['bid_count'] > 0): ?>
                                                    <br>
                                                    <small class="text-info">
                                                        <i class="fas fa-gavel me-1"></i><?php echo $item['bid_count']; ?> bid(s)
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-light">Starting: ₱<?php echo number_format($item['auction_starting_price'] ?? $item['price'], 2); ?></small>
                                                <?php if ($item['reserve_price']): ?>
                                                    <br><small class="text-muted">Reserve: ₱<?php echo number_format($item['reserve_price'], 2); ?></small>
                                                <?php endif; ?>
                                                <?php if ($item['current_bid'] > 0): ?>
                                                    <br><small class="text-success">Current: ₱<?php echo number_format($item['current_bid'], 2); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $item['auction_status'] ?? 'upcoming';
                                            $statusColors = [
                                                'upcoming' => 'warning',
                                                'active' => 'success',
                                                'sold' => 'primary',
                                                'unsold' => 'secondary'
                                            ];
                                            $color = $statusColors[$status] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?> status-badge">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-dark">
                                                    <li>
                                                        <a class="dropdown-item" href="../product.php?id=<?php echo $item['id']; ?>" target="_blank">
                                                            <i class="fas fa-eye text-info me-2"></i>View Item
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="edit_item.php?id=<?php echo $item['id']; ?>">
                                                            <i class="fas fa-edit text-warning me-2"></i>Edit
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                                                            <i class="fas fa-trash text-danger me-2"></i>Delete
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header border-danger">
                    <h5 class="modal-title text-light">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-light">
                    <p>Are you sure you want to delete this auction item?</p>
                    <p><strong id="itemName"></strong></p>
                    <p class="text-warning small">
                        <i class="fas fa-warning me-1"></i>This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="item_id" id="deleteItemId">
                        <button type="submit" name="delete_item" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Item
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(itemId, itemName) {
            document.getElementById('deleteItemId').value = itemId;
            document.getElementById('itemName').textContent = itemName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>