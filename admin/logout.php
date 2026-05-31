<?php
/**
 * Admin Logout Page
 * School Management Website
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'] ?? '';
    // Audit Log Entry
    log_activity($pdo, "Admin Logout", "User '$username' successfully logged out.");
    
    // Clear and destroy session
    session_unset();
    session_destroy();
}

header("Location: " . BASE_URL . "/admin/login.php");
exit;
