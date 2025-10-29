<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';

$auth = new Auth($pdo);

// Debug: Track if form was submitted
$formSubmitted = false;
$debugInfo = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    $debugInfo[] = "Form submitted via POST";
    
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $firstName = trim(isset($_POST['first_name']) ? $_POST['first_name'] : '');
    $lastName = trim(isset($_POST['last_name']) ? $_POST['last_name'] : '');
    
    $debugInfo[] = "Data extracted from POST";
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    $debugInfo[] = "Validation completed. Errors: " . count($errors);
    
    if (empty($errors)) {
        $debugInfo[] = "Attempting registration...";
        $result = $auth->register($username, $email, $password, $firstName, $lastName);
        
        $debugInfo[] = "Registration result: " . ($result['success'] ? 'SUCCESS' : 'FAILED');
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'] . ' You can now login with your credentials.';
            $debugInfo[] = "Success message set in session";
            
            // Show success message immediately, then redirect
            $showSuccessMessage = true;
            $successMessage = $result['message'];
            
            // Add a small delay to see the success message before redirect
            if (!isset($_GET['debug'])) {
                // Use JavaScript redirect with delay to show success message
                $redirectDelay = 3; // 3 seconds
            } else {
                $debugInfo[] = "DEBUG MODE: Redirect disabled";
                $redirectDelay = 0; // No redirect in debug mode
            }
        } else {
            $errors[] = $result['message'];
            $debugInfo[] = "Registration failed: " . $result['message'];
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        $debugInfo[] = "Errors set in session: " . count($errors) . " errors";
    }
}

$pageTitle = 'Register';
include 'includes/header.php';
?>

<!-- Debug Information (only shown when ?debug=1 is in URL) -->
<?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
    <div class="alert alert-info">
        <h5>Debug Information:</h5>
        <ul>
            <li><strong>Form Submitted:</strong> <?php echo $formSubmitted ? 'YES' : 'NO'; ?></li>
            <li><strong>Request Method:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?></li>
            <?php if ($formSubmitted): ?>
                <?php foreach ($debugInfo as $info): ?>
                    <li><?php echo htmlspecialchars($info); ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <?php if ($formSubmitted && !empty($_POST)): ?>
            <strong>POST Data:</strong>
            <pre><?php print_r(array_map(function($v) { return is_string($v) && strlen($v) > 20 ? substr($v, 0, 20) . '...' : $v; }, $_POST)); ?></pre>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">
                    <i class="fas fa-user-plus"></i> Create Account
                </h3>
                
                <!-- Registration Status Display -->
                <?php if ($formSubmitted): ?>
                    <?php if (isset($showSuccessMessage) && $showSuccessMessage): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle"></i> 
                            <strong><?php echo htmlspecialchars($successMessage); ?></strong>
                            <br>
                            <?php if (isset($redirectDelay) && $redirectDelay > 0): ?>
                                <small>Redirecting to login page in <span id="countdown"><?php echo $redirectDelay; ?></span> seconds...</small>
                                <script>
                                let countdown = <?php echo $redirectDelay; ?>;
                                const countdownElement = document.getElementById('countdown');
                                const timer = setInterval(function() {
                                    countdown--;
                                    countdownElement.textContent = countdown;
                                    if (countdown <= 0) {
                                        clearInterval(timer);
                                        window.location.href = 'login.php?registered=1';
                                    }
                                }, 1000);
                                </script>
                            <?php else: ?>
                                <br><a href="login.php?registered=1" class="btn btn-primary">Go to Login</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> 
                            Processing registration... Please wait.
                            <?php if (isset($_GET['debug'])): ?>
                                <br><small>Debug mode active - redirect delayed</small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars(isset($_POST['first_name']) ? $_POST['first_name'] : ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars(isset($_POST['last_name']) ? $_POST['last_name'] : ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars(isset($_POST['username']) ? $_POST['username'] : ''); ?>" required>
                        <div class="form-text">Must be at least 3 characters long</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Must be at least 6 characters long</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and 
                            <a href="privacy.php" target="_blank">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Already have an account?</p>
                    <a href="login.php" class="btn btn-outline-primary">Login Here</a>
                </div>
            </div>
        </div>
        
        <!-- Benefits Section -->
        <div class="card mt-3">
            <div class="card-body bg-light">
                <h6 class="card-title">Why Create an Account?</h6>
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-check text-success me-2"></i> Faster checkout process</li>
                    <li><i class="fas fa-check text-success me-2"></i> Order history and tracking</li>
                    <li><i class="fas fa-check text-success me-2"></i> Exclusive offers and discounts</li>
                    <li><i class="fas fa-check text-success me-2"></i> Wishlist and favorites</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced form validation
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const form = document.querySelector('form');
    
    // Password strength indicator
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        return strength;
    }
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
            confirmPassword.style.borderColor = '#dc3545';
        } else {
            confirmPassword.setCustomValidity('');
            confirmPassword.style.borderColor = '#28a745';
        }
    }
    
    function updatePasswordStrength() {
        const strength = checkPasswordStrength(password.value);
        let strengthText = '';
        let strengthColor = '';
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Weak';
                strengthColor = '#dc3545';
                break;
            case 2:
            case 3:
                strengthText = 'Medium';
                strengthColor = '#ffc107';
                break;
            case 4:
            case 5:
                strengthText = 'Strong';
                strengthColor = '#28a745';
                break;
        }
        
        let strengthIndicator = document.getElementById('password-strength');
        if (!strengthIndicator && password.value.length > 0) {
            strengthIndicator = document.createElement('div');
            strengthIndicator.id = 'password-strength';
            strengthIndicator.style.fontSize = '0.875rem';
            strengthIndicator.style.marginTop = '5px';
            password.parentNode.appendChild(strengthIndicator);
        }
        
        if (strengthIndicator && password.value.length > 0) {
            strengthIndicator.innerHTML = `Password strength: <span style="color: ${strengthColor}; font-weight: bold;">${strengthText}</span>`;
        } else if (strengthIndicator && password.value.length === 0) {
            strengthIndicator.remove();
        }
    }
    
    password.addEventListener('input', function() {
        updatePasswordStrength();
        validatePassword();
    });
    confirmPassword.addEventListener('input', validatePassword);
    
    // Form submission enhancement
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        submitBtn.disabled = true;
    });
});
</script>

<?php include 'includes/footer.php'; ?>