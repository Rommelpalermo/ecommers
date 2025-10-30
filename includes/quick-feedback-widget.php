<?php
// Quick Feedback Widget - Can be included on any page
$productId = isset($productId) ? $productId : null;
$widgetTitle = isset($widgetTitle) ? $widgetTitle : 'Quick Feedback';
?>

<!-- Quick Feedback Widget -->
<div class="card mt-4" id="quickFeedbackWidget">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-star text-warning"></i> <?php echo $widgetTitle; ?>
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">How was your experience? Let us know!</p>
        
        <form id="quickFeedbackForm">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            
            <!-- Quick Rating -->
            <div class="mb-3">
                <label class="form-label">Quick Rating:</label>
                <div class="quick-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" id="quick-star<?php echo $i; ?>" name="quick_rating" value="<?php echo $i; ?>">
                        <label for="quick-star<?php echo $i; ?>" class="quick-star">
                            <i class="fas fa-star"></i>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Quick Feedback Text -->
            <div class="mb-3">
                <textarea class="form-control form-control-sm" name="quick_feedback" placeholder="Brief feedback (optional)" rows="2"></textarea>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-lock"></i> Anonymous feedback
                </small>
                <div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                    <a href="feedback.php" class="btn btn-link btn-sm">Detailed Feedback</a>
                </div>
            </div>
        </form>
        
        <!-- Success Message -->
        <div id="quickFeedbackSuccess" class="alert alert-success mt-3" style="display: none;">
            <i class="fas fa-check-circle"></i> Thank you for your feedback!
        </div>
    </div>
</div>

<style>
.quick-rating {
    display: flex;
    gap: 5px;
    align-items: center;
}

.quick-rating input[type="radio"] {
    display: none;
}

.quick-star {
    cursor: pointer;
    font-size: 1.2rem;
    color: #ddd;
    transition: color 0.3s ease;
}

.quick-star:hover,
.quick-rating input[type="radio"]:checked + .quick-star,
.quick-rating input[type="radio"]:checked ~ .quick-star {
    color: #ffc107;
}

/* Reverse order for proper rating */
.quick-rating {
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.quick-rating input[type="radio"]:checked + .quick-star,
.quick-rating input[type="radio"]:checked + .quick-star ~ .quick-star {
    color: #ffc107;
}

#quickFeedbackWidget .card-body {
    padding: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quickForm = document.getElementById('quickFeedbackForm');
    const successDiv = document.getElementById('quickFeedbackSuccess');
    
    if (quickForm) {
        quickForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const rating = formData.get('quick_rating');
            
            if (!rating) {
                alert('Please select a rating');
                return;
            }
            
            // Simulate AJAX submission (you can implement actual AJAX here)
            setTimeout(() => {
                quickForm.style.display = 'none';
                successDiv.style.display = 'block';
                
                // Optional: Store in localStorage for demo
                const feedback = {
                    rating: rating,
                    feedback: formData.get('quick_feedback'),
                    product_id: formData.get('product_id'),
                    timestamp: new Date().toISOString()
                };
                
                let quickFeedbacks = JSON.parse(localStorage.getItem('quickFeedbacks') || '[]');
                quickFeedbacks.push(feedback);
                localStorage.setItem('quickFeedbacks', JSON.stringify(quickFeedbacks));
                
                console.log('Quick feedback submitted:', feedback);
            }, 500);
        });
    }
});
</script>