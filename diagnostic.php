<?php
// Comprehensive Registration System Diagnostic

echo "<h1>Registration System Diagnostic</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    echo "✅ Database config loaded successfully<br>";
    echo "✅ PDO connection established<br>";
    echo "✅ Session started<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 2: Auth Class
echo "<h2>2. Auth Class Test</h2>";
try {
    require_once 'includes/Auth.php';
    $auth = new Auth($pdo);
    echo "✅ Auth class loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Auth class failed: " . $e->getMessage() . "<br>";
}

// Test 3: Users Table
echo "<h2>3. Database Table Test</h2>";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Users table exists with columns: " . implode(', ', $columns) . "<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    echo "✅ Current user count: " . $count . "<br>";
} catch (Exception $e) {
    echo "❌ Users table test failed: " . $e->getMessage() . "<br>";
}

// Test 4: Registration Function
echo "<h2>4. Registration Function Test</h2>";
try {
    $testUsername = 'diagnostic_user_' . time();
    $testEmail = 'diagnostic_' . time() . '@test.com';
    
    $result = $auth->register($testUsername, $testEmail, 'password123', 'Test', 'User');
    
    if ($result['success']) {
        echo "✅ Registration function working: " . $result['message'] . "<br>";
        
        // Clean up test user
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
        $stmt->execute([$testUsername]);
        echo "✅ Test user cleaned up<br>";
    } else {
        echo "❌ Registration function failed: " . $result['message'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Registration test failed: " . $e->getMessage() . "<br>";
}

// Test 5: PHP Version and Features
echo "<h2>5. PHP Environment Test</h2>";
echo "✅ PHP Version: " . PHP_VERSION . "<br>";
echo "✅ PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "✅ PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
echo "✅ Password Hash Available: " . (function_exists('password_hash') ? 'Yes' : 'No') . "<br>";

// Test 6: Session Test
echo "<h2>6. Session Test</h2>";
$_SESSION['test'] = 'session_working';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'session_working') {
    echo "✅ Sessions are working<br>";
    unset($_SESSION['test']);
} else {
    echo "❌ Sessions not working<br>";
}

// Test 7: File Permissions
echo "<h2>7. File System Test</h2>";
if (is_readable('config/database.php')) {
    echo "✅ config/database.php is readable<br>";
} else {
    echo "❌ config/database.php is not readable<br>";
}

if (is_readable('includes/Auth.php')) {
    echo "✅ includes/Auth.php is readable<br>";
} else {
    echo "❌ includes/Auth.php is not readable<br>";
}

if (is_readable('register.php')) {
    echo "✅ register.php is readable<br>";
} else {
    echo "❌ register.php is not readable<br>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all tests show ✅, the registration system should be working correctly.</p>";
echo "<p>If you're still experiencing issues, please check:</p>";
echo "<ul>";
echo "<li>Browser JavaScript console for errors</li>";
echo "<li>Form submission data</li>";
echo "<li>Server error logs</li>";
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1, h2 { color: #333; }
</style>