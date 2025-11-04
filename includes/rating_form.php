<?php
// Rating form component
function displayRatingForm($product_id, $user_logged_in = false) {
    $html = '
    <div class="rating-form-container mb-4">
        <div class="card bg-dark border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Write a Review</h5>
            </div>
            <div class="card-body">
                <form id="ratingForm" class="rating-form">
                    <input type="hidden" name="product_id" value="' . $product_id . '">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Overall Rating *</label>
                                <div class="rating-input">
                                    <div class="star-rating">
                                        <input type="radio" id="star5" name="rating" value="5">
                                        <label for="star5" class="star"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star4" name="rating" value="4">
                                        <label for="star4" class="star"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star3" name="rating" value="3">
                                        <label for="star3" class="star"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star2" name="rating" value="2">
                                        <label for="star2" class="star"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star1" name="rating" value="1">
                                        <label for="star1" class="star"><i class="fas fa-star"></i></label>
                                    </div>
                                    <small class="form-text text-muted">Click to rate</small>
                                </div>
                            </div>
                        </div>';
    
    if (!$user_logged_in) {
        $html .= '
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reviewer_name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" id="reviewer_name" name="reviewer_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="reviewer_email" class="form-label">Your Email *</label>
                                <input type="email" class="form-control bg-dark text-light border-secondary" id="reviewer_email" name="reviewer_email" required>
                                <small class="form-text text-muted">Will not be displayed publicly</small>
                            </div>
                        </div>';
    }
    
    $html .= '
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_title" class="form-label">Review Title *</label>
                        <input type="text" class="form-control bg-dark text-light border-secondary" id="review_title" name="review_title" placeholder="Summarize your experience" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_text" class="form-label">Your Review *</label>
                        <textarea class="form-control bg-dark text-light border-secondary" id="review_text" name="review_text" rows="4" placeholder="Share your thoughts about this product..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pros" class="form-label">What did you like? (Optional)</label>
                                <textarea class="form-control bg-dark text-light border-secondary" id="pros" name="pros" rows="3" placeholder="List the positive aspects..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cons" class="form-label">What could be improved? (Optional)</label>
                                <textarea class="form-control bg-dark text-light border-secondary" id="cons" name="cons" rows="3" placeholder="Any drawbacks or areas for improvement..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="verified_purchase" name="verified_purchase" value="1">
                            <label class="form-check-label" for="verified_purchase">
                                I purchased this product
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">* Required fields</small>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="resetRatingForm()">Reset</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-paper-plane me-2"></i>Submit Review
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        font-size: 1.5rem;
        margin-bottom: 5px;
    }
    
    .star-rating input {
        display: none;
    }
    
    .star-rating label {
        color: #666;
        cursor: pointer;
        transition: color 0.2s;
        padding: 0 2px;
    }
    
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label {
        color: #ffc107;
    }
    
    .star-rating input:checked + label {
        color: #ffc107;
    }
    
    .rating-form .form-control:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    .rating-form-container {
        animation: slideInUp 0.5s ease-out;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
    
    <script>
    document.getElementById("ratingForm").addEventListener("submit", function(e) {
        e.preventDefault();
        submitRating();
    });
    
    function submitRating() {
        const form = document.getElementById("ratingForm");
        const formData = new FormData(form);
        
        // Validate rating
        const rating = formData.get("rating");
        if (!rating) {
            alert("Please select a rating");
            return;
        }
        
        // Show loading state
        const submitBtn = form.querySelector(\'button[type="submit"]\');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = \'<i class="fas fa-spinner fa-spin me-2"></i>Submitting...\';
        submitBtn.disabled = true;
        
        fetch("api/submit_rating.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showMessage("Review submitted successfully! It will be visible after approval.", "success");
                resetRatingForm();
                
                // Optionally reload reviews section
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showMessage(data.message || "Error submitting review. Please try again.", "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showMessage("Error submitting review. Please try again.", "error");
        })
        .finally(() => {
            // Restore button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
    
    function resetRatingForm() {
        document.getElementById("ratingForm").reset();
        // Clear star selection
        document.querySelectorAll(\'input[name="rating"]\').forEach(input => {
            input.checked = false;
        });
    }
    
    function showMessage(message, type) {
        const alertClass = type === "success" ? "alert-success" : "alert-danger";
        const icon = type === "success" ? "check-circle" : "exclamation-triangle";
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert alert at the top of the form
        const formContainer = document.querySelector(".rating-form-container");
        formContainer.insertAdjacentHTML("beforebegin", alertHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector(".alert");
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
    </script>';
    
    return $html;
}
?>