<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cms_db');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function hasPermission($requiredRole) {
    if (!isLoggedIn()) return false;
    
    $userRole = getUserRole();
    $hierarchy = ['client' => 1, 'livreur' => 2, 'admin' => 3];
    
    return $hierarchy[$userRole] >= $hierarchy[$requiredRole];
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: /cms/auth/login.php');
        exit();
    }
}

function redirectIfNotAuthorized($requiredRole) {
    redirectIfNotLoggedIn();
    if (!hasPermission($requiredRole)) {
        header('Location: /cms/dashboard/index.php');
        exit();
    }
}

function generateTrackingCode() {
    return 'TRK-' . date('Y-m') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}
?>
