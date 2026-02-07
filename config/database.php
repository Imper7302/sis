<?php
// Base konfiqurasiyanı daxil et
require_once 'config.php';

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sis_db');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Function to sanitize input (yalnız burada olacaq)
function clean_input($data) {
    if ($data === null) return null;
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: ../login.php');
        exit();
    }
}

// Check if user is superadmin
function is_superadmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}

// Check if user is employee
function is_employee() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'isci';
}
?>