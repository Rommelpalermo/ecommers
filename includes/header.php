<?php
// Include necessary classes for header functionality
if (!class_exists('Cart')) {
    require_once __DIR__ . '/Cart.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Top Social Bar -->
    <div class="bg-light py-2 d-none d-md-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-phone"></i> +63 123 456 7890 
                        <span class="mx-2">|</span>
                        <i class="fas fa-envelope"></i> info@ecommers.com
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted me-3">Follow us:</small>
                    <a href="https://www.facebook.com/" target="_blank" class="text-muted me-2" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/" target="_blank" class="text-muted me-2" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.tiktok.com/" target="_blank" class="text-muted" title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-store"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/feedback.php">Feedback</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            try {
                                $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
                                $stmt->execute();
                                $categories = $stmt->fetchAll();
                                foreach ($categories as $category):
                            ?>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/category.php?id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                            <?php endforeach; } catch(Exception $e) {} ?>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Search Form -->
                    <li class="nav-item me-3">
                        <form class="d-flex" action="<?php echo SITE_URL; ?>/search.php" method="GET">
                            <input class="form-control form-control-sm me-2" type="search" name="q" placeholder="Search products..." style="width: 200px;">
                            <button class="btn btn-outline-light btn-sm" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </li>
                    
                    <!-- Cart -->
                    <li class="nav-item me-3">
                        <a class="nav-link position-relative" href="<?php echo SITE_URL; ?>/cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                                <?php
                                try {
                                    if (isset($pdo) && $pdo instanceof PDO) {
                                        $cart = new Cart($pdo, isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
                                        echo $cart->getItemCount();
                                    } else {
                                        echo '0';
                                    }
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </span>
                        </a>
                    </li>
                    
                    <!-- User Menu -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php">My Orders</a></li>
                                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/add_item.php">
                                        <i class="fas fa-plus me-2"></i>Add Auction Item
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/manage_items.php">
                                        <i class="fas fa-boxes me-2"></i>Manage Items
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/feedback.php">
                                        <i class="fas fa-comments me-2"></i>Manage Feedback
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/ratings.php">
                                        <i class="fas fa-star me-2"></i>Manage Ratings
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="container my-4">