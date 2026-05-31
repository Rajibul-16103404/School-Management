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
    
    // Clear remember token in DB
    $user_id = $_SESSION['user_id'];
    try {
        $update_stmt = $pdo->prepare("UPDATE `users` SET `remember_token` = NULL WHERE `id` = ?");
        $update_stmt->execute([$user_id]);
    } catch (PDOException $e) {}

    // Delete remember_me cookie
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    // Clear and destroy session
    session_unset();
    session_destroy();
}

header("Location: " . BASE_URL . "/admin/login.php");
exit;
