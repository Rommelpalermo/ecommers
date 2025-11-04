<?php
// Rating display functions
function displayStars($rating, $size = 'normal') {
    $sizeClass = $size === 'large' ? 'fs-4' : ($size === 'small' ? 'fs-6' : '');
    $html = '<div class="rating-stars ' . $sizeClass . '">';
    
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

function getProductRatingStats($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM product_ratings 
        WHERE product_id = ? AND status = 'approved'
    ");
    
    $stmt->execute([$product_id]);
    $stats = $stmt->fetch();
    
    if ($stats['total_reviews'] > 0) {
        $stats['average_rating'] = round($stats['average_rating'], 1);
    } else {
        $stats['average_rating'] = 0;
    }
    
    return $stats;
}

function getProductReviews($pdo, $product_id, $limit = 10, $offset = 0, $sort = 'newest') {
    $orderBy = '';
    switch ($sort) {
        case 'oldest':
            $orderBy = 'ORDER BY pr.created_at ASC';
            break;
        case 'highest':
            $orderBy = 'ORDER BY pr.rating DESC, pr.created_at DESC';
            break;
        case 'lowest':
            $orderBy = 'ORDER BY pr.rating ASC, pr.created_at DESC';
            break;
        case 'helpful':
            $orderBy = 'ORDER BY pr.helpful_count DESC, pr.created_at DESC';
            break;
        default:
            $orderBy = 'ORDER BY pr.created_at DESC';
    }
    
    $stmt = $pdo->prepare("
        SELECT pr.*, u.username 
        FROM product_ratings pr 
        LEFT JOIN users u ON pr.user_id = u.id 
        WHERE pr.product_id = ? AND pr.status = 'approved' 
        $orderBy 
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$product_id, $limit, $offset]);
    return $stmt->fetchAll();
}

function displayRatingOverview($pdo, $product_id) {
    $stats = getProductRatingStats($pdo, $product_id);
    
    if ($stats['total_reviews'] == 0) {
        return '<div class="text-center py-4">
                    <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                </div>';
    }
    
    $html = '
    <div class="rating-overview mb-4">
        <div class="row align-items-center">
            <div class="col-md-4 text-center">
                <div class="average-rating">
                    <h2 class="display-4 mb-0 text-warning">' . $stats['average_rating'] . '</h2>
                    ' . displayStars($stats['average_rating'], 'large') . '
                    <p class="text-muted mt-2">' . $stats['total_reviews'] . ' review' . ($stats['total_reviews'] != 1 ? 's' : '') . '</p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="rating-breakdown">';
    
    for ($i = 5; $i >= 1; $i--) {
        $count = $stats[$i == 5 ? 'five_star' : ($i == 4 ? 'four_star' : ($i == 3 ? 'three_star' : ($i == 2 ? 'two_star' : 'one_star')))];
        $percentage = $stats['total_reviews'] > 0 ? round(($count / $stats['total_reviews']) * 100) : 0;
        
        $html .= '
                    <div class="row align-items-center mb-2">
                        <div class="col-2 text-end">
                            <small>' . $i . ' star</small>
                        </div>
                        <div class="col-8">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: ' . $percentage . '%"></div>
                            </div>
                        </div>
                        <div class="col-2">
                            <small class="text-muted">' . $count . '</small>
                        </div>
                    </div>';
    }
    
    $html .= '
                </div>
            </div>
        </div>
    </div>';
    
    return $html;
}

function displayReviews($pdo, $product_id, $sort = 'newest') {
    $reviews = getProductReviews($pdo, $product_id, 10, 0, $sort);
    
    if (empty($reviews)) {
        return '<div class="text-center py-4">
                    <p class="text-muted">No reviews yet.</p>
                </div>';
    }
    
    $html = '<div class="reviews-list">';
    
    foreach ($reviews as $review) {
        $timeAgo = time_elapsed_string($review['created_at']);
        $userName = $review['username'] ?: $review['user_name'];
        
        $html .= '
        <div class="review-item mb-4 p-4 rounded" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="review-header d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="d-flex align-items-center mb-1">
                        ' . displayStars($review['rating']) . '
                        ' . ($review['verified_purchase'] ? '<span class="badge bg-success ms-2 small">Verified Purchase</span>' : '') . '
                    </div>
                    <h6 class="review-title mb-1">' . htmlspecialchars($review['review_title']) . '</h6>
                    <small class="text-muted">By ' . htmlspecialchars($userName) . ' • ' . $timeAgo . '</small>
                </div>
            </div>
            
            <div class="review-content">
                <p class="mb-3">' . nl2br(htmlspecialchars($review['review_text'])) . '</p>';
        
        if (!empty($review['pros'])) {
            $html .= '
                <div class="pros-cons mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-thumbs-up me-2"></i>Pros</h6>
                            <p class="small text-muted">' . nl2br(htmlspecialchars($review['pros'])) . '</p>
                        </div>';
            
            if (!empty($review['cons'])) {
                $html .= '
                        <div class="col-md-6">
                            <h6 class="text-danger"><i class="fas fa-thumbs-down me-2"></i>Cons</h6>
                            <p class="small text-muted">' . nl2br(htmlspecialchars($review['cons'])) . '</p>
                        </div>';
            }
            
            $html .= '
                    </div>
                </div>';
        }
        
        $html .= '
                <div class="review-actions">
                    <button class="btn btn-sm btn-outline-light me-2" onclick="markHelpful(' . $review['id'] . ')">
                        <i class="fas fa-thumbs-up me-1"></i>Helpful (' . $review['helpful_count'] . ')
                    </button>
                    <button class="btn btn-sm btn-outline-light" onclick="reportReview(' . $review['id'] . ')">
                        <i class="fas fa-flag me-1"></i>Report
                    </button>
                </div>
            </div>
        </div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = array();
    
    if ($diff->y) $string[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
    if ($diff->m) $string[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
    if ($diff->d >= 7) {
        $weeks = floor($diff->d / 7);
        $string[] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
    } elseif ($diff->d) {
        $string[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
    }
    if ($diff->h) $string[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
    if ($diff->i) $string[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
    if ($diff->s) $string[] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>