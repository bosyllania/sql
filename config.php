<?php
// config.php - Database Configuration File

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection using PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    // Log error to file (in production)
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Display user-friendly error (remove in production)
    die("Connection failed. Please try again later.");
}

// Optional: Set timezone
date_default_timezone_set('Asia/Manila');

// Optional: Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>