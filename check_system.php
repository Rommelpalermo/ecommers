<?php
// PHP Version Compatibility Check
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP Version ID: " . PHP_VERSION_ID . "\n";

if (PHP_VERSION_ID < 50400) {
    die("Error: PHP 5.4.0 or higher is required for this application.\n");
}

if (PHP_VERSION_ID >= 70000) {
    echo "✓ PHP 7.0+ detected - All features supported\n";
} else {
    echo "⚠ PHP 5.4-6.x detected - Using compatibility mode\n";
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'session', 'json'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "Error: Missing required PHP extensions: " . implode(', ', $missing_extensions) . "\n";
} else {
    echo "✓ All required PHP extensions are loaded\n";
}

echo "System check completed.\n";
?>