<?php
// Quick Registration Test
require_once 'config/database.php';

echo "<h2>Current Users in Database</h2>";

try {
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Username</th><th>Email</th><th>First Name</th><th>Last Name</th><th>Created At</th>";
        echo "</tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>Total users found: " . count($users) . "</strong></p>";
    } else {
        echo "<p>No users found in the database.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3>Test Registration Form</h3>";
echo "<p><a href='register.php'>Go to Registration Form</a></p>";
echo "<p><a href='register.php?debug=1'>Go to Registration Form (Debug Mode)</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
table { margin: 20px 0; }
th, td { text-align: left; }
</style>