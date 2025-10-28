<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields';
    } else {
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">
                    <i class="fas fa-sign-in-alt"></i> Login
                </h3>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Don't have an account?</p>
                    <a href="register.php" class="btn btn-outline-primary">Create Account</a>
                </div>
                
                <div class="text-center mt-3">
                    <a href="forgot-password.php" class="text-muted">Forgot your password?</a>
                </div>
            </div>
        </div>
        
        <!-- Demo Account Info -->
        <div class="card mt-3">
            <div class="card-body bg-light">
                <h6 class="card-title">Demo Account</h6>
                <p class="card-text small mb-2">
                    <strong>Admin:</strong> admin / admin123<br>
                    <strong>User:</strong> Create a new account
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>