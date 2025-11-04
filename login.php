<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';
require_once 'includes/Cart.php';

$auth = new Auth($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
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

// Check if user just registered
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    if (!isset($_SESSION['success'])) {
        $_SESSION['success'] = 'Registration successful! Please login with your new account.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .pixel-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #1a1a1a;
            display: grid;
            grid-template-columns: repeat(20, 1fr);
            grid-template-rows: repeat(15, 1fr);
            gap: 2px;
            animation: pixelShift 10s infinite linear;
        }

        .pixel {
            background: linear-gradient(45deg, #2d2d2d 0%, #1a1a1a 100%);
            transition: all 0.5s ease;
        }

        .pixel.orange-1 { background: linear-gradient(45deg, #ff6b35 0%, #d4441c 100%); }
        .pixel.orange-2 { background: linear-gradient(45deg, #ff8856 0%, #e6522b 100%); }
        .pixel.orange-3 { background: linear-gradient(45deg, #ffaa7a 0%, #ff7043 100%); }
        .pixel.dark-1 { background: linear-gradient(45deg, #3d3d3d 0%, #2a2a2a 100%); }
        .pixel.dark-2 { background: linear-gradient(45deg, #4d4d4d 0%, #3a3a3a 100%); }

        @keyframes pixelShift {
            0% { transform: translateX(0) translateY(0); }
            25% { transform: translateX(-5px) translateY(-5px); }
            50% { transform: translateX(5px) translateY(-10px); }
            75% { transform: translateX(-5px) translateY(5px); }
            100% { transform: translateX(0) translateY(0); }
        }

        .login-container {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: rgba(30, 30, 30, 0.95);
            border: 2px solid #ff6b35;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.2);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 107, 53, 0.1), transparent);
            animation: shine 3s infinite;
            pointer-events: none;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .login-title {
            color: #ff6b35;
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
        }

        .form-label {
            color: #cccccc;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 107, 53, 0.3);
            border-radius: 8px;
            color: #ffffff;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #ff6b35;
            box-shadow: 0 0 15px rgba(255, 107, 53, 0.3);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-login {
            background: linear-gradient(45deg, #ff6b35 0%, #ff8856 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #ff8856 0%, #ffaa7a 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
            color: white;
        }

        .link-signup {
            color: #ff6b35;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .link-signup:hover {
            color: #ff8856;
            text-shadow: 0 0 5px rgba(255, 107, 53, 0.5);
        }

        .forgot-password {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #ff6b35;
        }

        .alert {
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid #ff6b35;
            color: #ffffff;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .text-white {
            color: #ffffff !important;
        }

        .demo-info {
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid rgba(255, 107, 53, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            color: #cccccc;
            font-size: 0.85rem;
        }

        .back-to-store {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 20;
        }

        .btn-back {
            background: rgba(255, 107, 53, 0.2);
            border: 1px solid #ff6b35;
            color: #ff6b35;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 107, 53, 0.3);
            color: #ff8856;
        }

        @media (max-width: 768px) {
            .pixel-background {
                grid-template-columns: repeat(15, 1fr);
                grid-template-rows: repeat(20, 1fr);
            }
            
            .login-card {
                padding: 30px 25px;
                margin: 10px;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Pixel Background -->
    <div class="pixel-background">
        <?php for ($i = 0; $i < 300; $i++): ?>
            <?php 
            $classes = ['', 'orange-1', 'orange-2', 'orange-3', 'dark-1', 'dark-2'];
            $randomClass = $classes[array_rand($classes)];
            ?>
            <div class="pixel <?php echo $randomClass; ?>" style="animation-delay: <?php echo rand(0, 5); ?>s;"></div>
        <?php endfor; ?>
    </div>

    <!-- Back to Store Button -->
    <div class="back-to-store">
        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Store
        </a>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <h1 class="login-title">Sign In</h1>
            
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Enter your username or email"
                           value="<?php echo htmlspecialchars(isset($_POST['username']) ? $_POST['username'] : ''); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>
            
            <div class="text-center">
                <div class="mb-2">
                    <a href="forgot-password.php" class="forgot-password">Forgot Password</a>
                </div>
                <div class="text-white">
                    Don't have an account? 
                    <a href="register.php" class="link-signup">SignUp</a>
                </div>
            </div>

            <!-- Demo Account Info -->
            <div class="demo-info">
                <strong>Demo Accounts:</strong><br>
                <small>
                    Admin: admin / admin123<br>
                    Or create a new account
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add dynamic pixel animation
        document.addEventListener('DOMContentLoaded', function() {
            const pixels = document.querySelectorAll('.pixel');
            
            setInterval(() => {
                pixels.forEach(pixel => {
                    if (Math.random() > 0.95) {
                        const classes = ['', 'orange-1', 'orange-2', 'orange-3', 'dark-1', 'dark-2'];
                        const randomClass = classes[Math.floor(Math.random() * classes.length)];
                        pixel.className = `pixel ${randomClass}`;
                    }
                });
            }, 2000);
        });
    </script>
</body>
</html>