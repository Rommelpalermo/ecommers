<?php
require_once 'config/database.php';

try {
    // Check if categories table exists
    $stmt = $pdo->query('DESCRIBE categories');
    echo 'Categories table exists with columns:' . PHP_EOL;
    while ($row = $stmt->fetch()) {
        echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
    }
    
    // Check if there are any categories
    $count_stmt = $pdo->query('SELECT COUNT(*) as count FROM categories');
    $count = $count_stmt->fetch()['count'];
    echo PHP_EOL . 'Total categories: ' . $count . PHP_EOL;
    
    if ($count == 0) {
        echo 'No categories found. Creating default categories...' . PHP_EOL;
        $categories = [
            'Electronics',
            'Clothing & Apparel', 
            'Home & Garden',
            'Sports & Recreation',
            'Books & Media',
            'Jewelry & Accessories',
            'Automotive',
            'Health & Beauty',
            'Toys & Games',
            'Antiques & Collectibles'
        ];
        
        $insert_stmt = $pdo->prepare('INSERT INTO categories (name, created_at) VALUES (?, NOW())');
        foreach ($categories as $category) {
            $insert_stmt->execute([$category]);
            echo 'Added category: ' . $category . PHP_EOL;
        }
        echo 'All categories added successfully!' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        echo 'Creating categories table...' . PHP_EOL;
        $pdo->exec('
            CREATE TABLE categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                parent_id INT NULL,
                sort_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_parent (parent_id),
                INDEX idx_active (is_active)
            )
        ');
        echo 'Categories table created!' . PHP_EOL;
        
        // Insert default categories
        $categories = [
            'Electronics',
            'Clothing & Apparel', 
            'Home & Garden',
            'Sports & Recreation',
            'Books & Media',
            'Jewelry & Accessories',
            'Automotive',
            'Health & Beauty',
            'Toys & Games',
            'Antiques & Collectibles'
        ];
        
        $insert_stmt = $pdo->prepare('INSERT INTO categories (name, created_at) VALUES (?, NOW())');
        foreach ($categories as $category) {
            $insert_stmt->execute([$category]);
            echo 'Added category: ' . $category . PHP_EOL;
        }
        echo 'All categories added successfully!' . PHP_EOL;
    }
}
?>