<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';

$auth = new Auth($pdo);
$result = $auth->logout();

$_SESSION['success'] = 'You have been logged out successfully.';
header('Location: index.php');
exit;
?>