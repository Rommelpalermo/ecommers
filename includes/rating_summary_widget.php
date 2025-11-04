<?php
// Rating summary widget - shows overall site rating statistics
require_once 'config/database.php';

// Include rating functions for time_elapsed_string function
if (!function_exists('time_elapsed_string')) {
    require_once __DIR__ . '/rating_functions.php';
}

try {
    // Get overall rating statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating,
            COUNT(DISTINCT product_id) as rated_products
        FROM product_ratings 
        WHERE status = 'approved'
    ");
    $overall_stats = $stmt->fetch();
    
    // Get recent reviews
    $stmt = $pdo->query("
        SELECT pr.rating, pr.review_title, pr.user_name, p.name as product_name, pr.created_at
        FROM product_ratings pr
        LEFT JOIN products p ON pr.product_id = p.id
        WHERE pr.status = 'approved'
        ORDER BY pr.created_at DESC
        LIMIT 3
    ");
    $recent_reviews = $stmt->fetchAll();
    
    // Get top rated products
    $stmt = $pdo->query("
        SELECT p.name, p.id, AVG(pr.rating) as avg_rating, COUNT(pr.id) as review_count
        FROM products p
        LEFT JOIN product_ratings pr ON p.id = pr.product_id AND pr.status = 'approved'
        WHERE p.is_active = 1
        GROUP BY p.id, p.name
        HAVING review_count > 0
        ORDER BY avg_rating DESC, review_count DESC
        LIMIT 3
    ");
    $top_products = $stmt->fetchAll();
    
} catch (Exception $e) {
    $overall_stats = ['total_reviews' => 0, 'avg_rating' => 0, 'rated_products' => 0];
    $recent_reviews = [];
    $top_products = [];
}

function displayStarsSmall($rating) {
    $html = '<div class="rating-stars-small">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-muted"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>

<div class="rating-summary-widget">
    <div class="container-fluid">
        <div class="row">
            <!-- Overall Statistics -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 bg-dark border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Customer Satisfaction</h6>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($overall_stats['total_reviews'] > 0): ?>
                            <h2 class="text-warning mb-1"><?php echo number_format($overall_stats['avg_rating'], 1); ?></h2>
                            <?php echo displayStarsSmall($overall_stats['avg_rating']); ?>
                            <p class="small text-muted mt-2 mb-0">
                                Based on <?php echo number_format($overall_stats['total_reviews']); ?> reviews<br>
                                across <?php echo $overall_stats['rated_products']; ?> products
                            </p>
                        <?php else: ?>
                            <p class="text-muted">No reviews yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Reviews -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 bg-dark border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Latest Reviews</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_reviews)): ?>
                            <?php foreach ($recent_reviews as $review): ?>
                                <div class="border-bottom border-secondary pb-2 mb-2 last:border-0 last:pb-0 last:mb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <?php echo displayStarsSmall($review['rating']); ?>
                                        <small class="text-muted"><?php echo time_elapsed_string($review['created_at']); ?></small>
                                    </div>
                                    <p class="small mb-1 text-truncate"><?php echo htmlspecialchars($review['review_title']); ?></p>
                                    <small class="text-muted">
                                        by <?php echo htmlspecialchars($review['user_name']); ?> 
                                        on <?php echo htmlspecialchars($review['product_name']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="small text-muted">No recent reviews</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Top Rated Products -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 bg-dark border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Rated Products</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($top_products)): ?>
                            <?php foreach ($top_products as $product): ?>
                                <div class="border-bottom border-secondary pb-2 mb-2 last:border-0 last:pb-0 last:mb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="small text-warning fw-bold"><?php echo number_format($product['avg_rating'], 1); ?></span>
                                        <?php echo displayStarsSmall($product['avg_rating']); ?>
                                    </div>
                                    <p class="small mb-1">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-light">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </p>
                                    <small class="text-muted"><?php echo $product['review_count']; ?> review<?php echo $product['review_count'] != 1 ? 's' : ''; ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="small text-muted">No rated products yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-summary-widget .rating-stars-small {
    font-size: 0.8rem;
}

.rating-summary-widget .card {
    background: rgba(255, 255, 255, 0.05) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 107, 53, 0.3) !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.rating-summary-widget .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 107, 53, 0.2);
}

.rating-summary-widget .card-header {
    background: linear-gradient(135deg, #ff6b35, #ffa726) !important;
    border-bottom: 1px solid rgba(255, 107, 53, 0.3);
}

.rating-summary-widget .text-truncate {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rating-summary-widget .border-bottom:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}
</style>