<?php
/**
 * Authentication Helper
 * School Management Website
 */

require_once __DIR__ . '/../config.php';

// Manage Session Timeouts
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
        // Destroy session on inactivity
        $username = $_SESSION['username'] ?? 'User';
        session_unset();
        session_destroy();
        
        // Redirect to login page with timeout notice
        header("Location: " . BASE_URL . "/admin/login.php?timeout=1");
        exit;
    }
    // Update last active activity
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Enforce authentication for protected pages
 */
function check_auth() {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'অনুগ্রহ করে প্রথমে লগইন করুন।';
        header("Location: " . BASE_URL . "/admin/login.php");
        exit;
    }
}

/**
 * Enforce role-based access control
 * @param array|string $allowed_roles Allowed user types
 */
function check_role($allowed_roles) {
    check_auth();
    
    $user_role = $_SESSION['user_role'] ?? '';
    
    if (is_array($allowed_roles)) {
        if (!in_array($user_role, $allowed_roles)) {
            $_SESSION['flash_error'] = 'এই পেজটিতে আপনার প্রবেশাধিকার নেই।';
            header("Location: " . BASE_URL . "/admin/index.php");
            exit;
        }
    } else {
        if ($user_role !== $allowed_roles) {
            $_SESSION['flash_error'] = 'এই পেজটিতে আপনার প্রবেশাধিকার নেই।';
            header("Location: " . BASE_URL . "/admin/index.php");
            exit;
        }
    }
}
