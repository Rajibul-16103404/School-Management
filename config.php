<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();
/**
 * Global Configuration File
 * School Management System
 */

// Set Timezone (Bangladesh standard time)
date_default_timezone_set('Asia/Dhaka');

// Enable error reporting for debugging during setup (should be turned off in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration defaults
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'school_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    // 15 minutes session life time (900 seconds)
    ini_set('session.gc_maxlifetime', 900);
    session_set_cookie_params([
        'lifetime' => 900,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Session timeout check helper
define('SESSION_TIMEOUT_SECONDS', 900);

// Base URLs and directory paths
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
// Automatically determine script subdirectory
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptDir = rtrim($scriptDir, '/');
if (str_contains($_SERVER['REQUEST_URI'], '/admin')) {
    // If inside admin subfolder, strip /admin from the end of base path
    $scriptDir = preg_replace('/\/admin(\/.*)?$/', '', $scriptDir);
}
if (str_contains($_SERVER['REQUEST_URI'], '/api')) {
    $scriptDir = preg_replace('/\/api(\/.*)?$/', '', $scriptDir);
}

define('BASE_URL', $protocol . '://' . $host . ($scriptDir === '' ? '' : $scriptDir));
define('ROOT_DIR', __DIR__);
define('UPLOAD_DIR', ROOT_DIR . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Create upload directory if it does not exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    // Create subfolders for better organization
    mkdir(UPLOAD_DIR . '/notices', 0755, true);
    mkdir(UPLOAD_DIR . '/routines', 0755, true);
    mkdir(UPLOAD_DIR . '/syllabi', 0755, true);
    mkdir(UPLOAD_DIR . '/photos', 0755, true);
    mkdir(UPLOAD_DIR . '/documents', 0755, true);
}
