<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';

$auth = new Auth($pdo);
$message = '';
$messageType = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $feedback_text = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';
    $feedback_type = isset($_POST['feedback_type']) ? $_POST['feedback_type'] : 'general';
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($feedback_text)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'danger';
    } elseif ($rating < 1 || $rating > 5) {
        $message = 'Please select a rating between 1 and 5 stars.';
        $messageType = 'danger';
    } else {
        try {
            // Create feedback table if it doesn't exist
            $createTable = "CREATE TABLE IF NOT EXISTS feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                feedback_type ENUM('general', 'product', 'service', 'website', 'complaint', 'suggestion') DEFAULT 'general',
                rating INT NOT NULL DEFAULT 1,
                feedback_text TEXT NOT NULL,
                status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )";
            $pdo->exec($createTable);
            
            // Insert feedback
            $stmt = $pdo->prepare("
                INSERT INTO feedback (user_id, name, email, subject, feedback_type, rating, feedback_text) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $userId = $auth->isLoggedIn() ? $_SESSION['user_id'] : null;
            
            $stmt->execute([
                $userId,
                $name,
                $email,
                $subject,
                $feedback_type,
                $rating,
                $feedback_text
            ]);
            
            $message = 'Thank you for your feedback! We appreciate your input and will review it shortly.';
            $messageType = 'success';
            
            // Clear form data on success
            $name = $email = $subject = $feedback_text = '';
            $rating = 0;
            $feedback_type = 'general';
            
        } catch (Exception $e) {
            $message = 'Sorry, there was an error submitting your feedback. Please try again.';
            $messageType = 'danger';
        }
    }
}

// Pre-fill user data if logged in
if ($auth->isLoggedIn() && empty($name) && empty($email)) {
    $user = $auth->getCurrentUser();
    $name = $user['first_name'] . ' ' . $user['last_name'];
    $email = $user['email'];
}

$pageTitle = 'Customer Feedback';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-5">Customer Feedback</h1>
                <p class="lead text-muted">We value your opinion! Help us improve our service.</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Feedback Form -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-comments"></i> Share Your Feedback
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars(isset($name) ? $name : ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars(isset($email) ? $email : ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo htmlspecialchars(isset($subject) ? $subject : ''); ?>" 
                                       placeholder="Brief description of your feedback" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="feedback_type" class="form-label">Feedback Type</label>
                                <select class="form-control" id="feedback_type" name="feedback_type">
                                    <option value="general" <?php echo (isset($feedback_type) ? $feedback_type : '') === 'general' ? 'selected' : ''; ?>>General Feedback</option>
                                    <option value="product" <?php echo (isset($feedback_type) ? $feedback_type : '') === 'product' ? 'selected' : ''; ?>>Product Related</option>
                                    <option value="service" <?php echo (isset($feedback_type) ? $feedback_type : '') === 'service' ? 'selected' : ''; ?>>Customer Service</option>
                                    <option value="website" <?php echo (isset($feedback_type) ? $feedback_type : '') === 'website' ? 'selected' : ''; ?>>Website Experience</option>
                                    <option value="complaint" <?php echo (isset($feedback_type) ? $feedback_type : '') === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                                    <option value="suggestion" <?php echo (isset($feedback_type) ? $feedback_type : '') === 'suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Rating System -->
                        <div class="mb-3">
                            <label class="form-label">Overall Rating *</label>
                            <div class="rating-input">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" 
                                           <?php echo (isset($rating) ? $rating : 0) == $i ? 'checked' : ''; ?> required>
                                    <label for="star<?php echo $i; ?>" class="star-label">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                                <span class="rating-text ms-2">Click to rate</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Your Feedback *</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="6" 
                                      placeholder="Please share your detailed feedback, suggestions, or concerns..." required><?php echo htmlspecialchars(isset($feedback_text) ? $feedback_text : ''); ?></textarea>
                            <small class="text-muted">Minimum 10 characters required</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo"></i> Clear Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Why Feedback Matters -->
            <div class="card mt-4">
                <div class="card-body bg-light">
                    <h5 class="card-title">
                        <i class="fas fa-heart text-danger"></i> Why Your Feedback Matters
                    </h5>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                            <h6>Improve Our Service</h6>
                            <small class="text-muted">Your feedback helps us identify areas for improvement</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-users fa-2x text-success mb-2"></i>
                            <h6>Better Experience</h6>
                            <small class="text-muted">We use your input to enhance the customer experience</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-lightbulb fa-2x text-warning mb-2"></i>
                            <h6>New Ideas</h6>
                            <small class="text-muted">Your suggestions inspire new features and products</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Response Commitment -->
            <div class="alert alert-info mt-4">
                <i class="fas fa-clock"></i>
                <strong>Our Commitment:</strong> We typically respond to feedback within 24-48 hours. 
                For urgent matters, please contact us directly at <strong>support@ecommers.com</strong>.
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for Rating System -->
<style>
.rating-input {
    display: flex;
    align-items: center;
    gap: 5px;
}

.rating-input input[type="radio"] {
    display: none;
}

.star-label {
    cursor: pointer;
    font-size: 1.5rem;
    color: #ddd;
    transition: color 0.3s ease;
}

.star-label:hover,
.star-label:hover ~ .star-label {
    color: #ffc107;
}

.rating-input input[type="radio"]:checked ~ .star-label {
    color: #ffc107;
}

.rating-input input[type="radio"]:checked + .star-label {
    color: #ffc107;
}

/* Reverse the order for proper star rating */
.rating-input {
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input .star-label:hover,
.rating-input .star-label:hover ~ .star-label,
.rating-input input[type="radio"]:checked + .star-label,
.rating-input input[type="radio"]:checked + .star-label ~ .star-label {
    color: #ffc107;
}

.rating-text {
    order: 1;
    color: #6c757d;
    font-size: 0.9rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: box-shadow 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rating system functionality
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const ratingText = document.querySelector('.rating-text');
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            const ratingTexts = {
                1: 'Poor',
                2: 'Fair',
                3: 'Good',
                4: 'Very Good',
                5: 'Excellent'
            };
            ratingText.textContent = ratingTexts[rating] || 'Click to rate';
        });
    });
    
    // Form validation
    const form = document.querySelector('form');
    const feedbackTextarea = document.getElementById('feedback');
    
    form.addEventListener('submit', function(e) {
        if (feedbackTextarea.value.trim().length < 10) {
            e.preventDefault();
            alert('Please provide more detailed feedback (minimum 10 characters).');
            feedbackTextarea.focus();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>