<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

$auth = new Auth($pdo);

// Simple admin check (you can enhance this)
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Get feedback statistics
$stats = [];
try {
    // Total feedback count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM feedback");
    $stats['total'] = $stmt->fetch()['total'];
    
    // Pending feedback
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM feedback WHERE status = 'pending'");
    $stats['pending'] = $stmt->fetch()['pending'];
    
    // Average rating
    $stmt = $pdo->query("SELECT AVG(rating) as avg_rating FROM feedback");
    $stats['avg_rating'] = round($stmt->fetch()['avg_rating'], 1);
    
    // Recent feedback
    $stmt = $pdo->query("SELECT COUNT(*) as recent FROM feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)");
    $stats['recent'] = $stmt->fetch()['recent'];
    
} catch (Exception $e) {
    $stats = ['total' => 0, 'pending' => 0, 'avg_rating' => 0, 'recent' => 0];
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $feedbackId = (int)$_POST['feedback_id'];
    $newStatus = $_POST['status'];
    
    if (in_array($newStatus, ['pending', 'reviewed', 'resolved'])) {
        $stmt = $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $feedbackId]);
        $_SESSION['success'] = 'Feedback status updated successfully!';
        header('Location: feedback.php');
        exit;
    }
}

// Get all feedback with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($filterStatus)) {
    $whereClause .= " AND status = ?";
    $params[] = $filterStatus;
}

if (!empty($filterType)) {
    $whereClause .= " AND feedback_type = ?";
    $params[] = $filterType;
}

$stmt = $pdo->prepare("SELECT * FROM feedback $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM feedback $whereClause");
$countParams = array_slice($params, 0, -2); // Remove limit and offset
$countStmt->execute($countParams);
$totalFeedback = $countStmt->fetch()['total'];
$totalPages = ceil($totalFeedback / $limit);

$pageTitle = 'Feedback Management';
include '../includes/header.php';
?>

<div class="container-fluid mt-3">
    <div class="row">
        <!-- Sidebar (simple) -->
        <div class="col-md-2 bg-dark text-white min-vh-100">
            <div class="p-3">
                <h5><i class="fas fa-cogs"></i> Admin Panel</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../">
                            <i class="fas fa-home"></i> Back to Store
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="feedback.php">
                            <i class="fas fa-comments"></i> Feedback
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="ratings.php">
                            <i class="fas fa-star"></i> Product Ratings
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10">
            <div class="p-4">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-comments"></i> Feedback Management</h2>
                    <a href="../feedback.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> View Feedback Form
                    </a>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['total']; ?></h4>
                                        <p class="mb-0">Total Feedback</p>
                                    </div>
                                    <i class="fas fa-comments fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['pending']; ?></h4>
                                        <p class="mb-0">Pending Review</p>
                                    </div>
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['avg_rating']; ?> <small>/5</small></h4>
                                        <p class="mb-0">Average Rating</p>
                                    </div>
                                    <i class="fas fa-star fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['recent']; ?></h4>
                                        <p class="mb-0">This Week</p>
                                    </div>
                                    <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Filter by Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="reviewed" <?php echo $filterStatus === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                    <option value="resolved" <?php echo $filterStatus === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="type" class="form-label">Filter by Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="">All Types</option>
                                    <option value="general" <?php echo $filterType === 'general' ? 'selected' : ''; ?>>General</option>
                                    <option value="product" <?php echo $filterType === 'product' ? 'selected' : ''; ?>>Product</option>
                                    <option value="service" <?php echo $filterType === 'service' ? 'selected' : ''; ?>>Service</option>
                                    <option value="website" <?php echo $filterType === 'website' ? 'selected' : ''; ?>>Website</option>
                                    <option value="complaint" <?php echo $filterType === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                                    <option value="suggestion" <?php echo $filterType === 'suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="feedback.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Feedback List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Feedback List (<?php echo $totalFeedback; ?> total)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($feedbacks)): ?>
                            <div class="text-center p-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No feedback found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Customer</th>
                                            <th>Subject</th>
                                            <th>Type</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($feedbacks as $feedback): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($feedback['name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($feedback['email']); ?></small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($feedback['subject']); ?></strong><br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars(substr($feedback['feedback_text'], 0, 100)) . (strlen($feedback['feedback_text']) > 100 ? '...' : ''); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo ucfirst($feedback['feedback_type']); ?></span>
                                                </td>
                                                <td>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $feedback['status'] === 'pending' ? 'warning' : 
                                                            ($feedback['status'] === 'reviewed' ? 'info' : 'success'); 
                                                    ?>">
                                                        <?php echo ucfirst($feedback['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewFeedback(<?php echo $feedback['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <div class="dropdown">
                                                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $feedback['id']; ?>, 'pending')">Mark Pending</a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $feedback['id']; ?>, 'reviewed')">Mark Reviewed</a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $feedback['id']; ?>, 'resolved')">Mark Resolved</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $filterStatus; ?>&type=<?php echo $filterType; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for status updates -->
<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="update_status" value="1">
    <input type="hidden" name="feedback_id" id="statusFeedbackId">
    <input type="hidden" name="status" id="statusValue">
</form>

<script>
function updateStatus(feedbackId, status) {
    if (confirm('Are you sure you want to update the status to "' + status + '"?')) {
        document.getElementById('statusFeedbackId').value = feedbackId;
        document.getElementById('statusValue').value = status;
        document.getElementById('statusForm').submit();
    }
}

function viewFeedback(feedbackId) {
    // You can implement a modal or redirect to a detail view
    alert('View feedback #' + feedbackId + ' - Feature can be enhanced with a modal or detail page');
}
</script>

<?php include '../includes/footer.php'; ?>