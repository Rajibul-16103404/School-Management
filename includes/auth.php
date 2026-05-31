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
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    
    // Check if remember_me cookie is set for device recognition
    if (isset($_COOKIE['remember_me'])) {
        global $pdo;
        $db = $pdo;
        if (!$db) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $db = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                return false;
            }
        }
        
        $parts = explode(':', $_COOKIE['remember_me'], 2);
        if (count($parts) === 2) {
            $user_id = (int)$parts[0];
            $token = $parts[1];
            
            try {
                $stmt = $db->prepare("SELECT * FROM `users` WHERE `id` = ? AND `remember_token` = ? LIMIT 1");
                $stmt->execute([$user_id, $token]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Auto-renew session details
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name_bn'];
                    $_SESSION['last_activity'] = time();
                    return true;
                }
            } catch (PDOException $e) {
                return false;
            }
        }
    }
    
    return false;
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
