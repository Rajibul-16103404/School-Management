<?php
/**
 * Admin Delete User Page
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Restrict to superadmin only
check_role('superadmin');

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/users/index.php");
    exit;
}

// Check if trying to delete self
if ($user_id === (int)$_SESSION['user_id']) {
    $_SESSION['flash_error'] = "দুঃখিত! আপনি নিজের অ্যাকাউন্ট নিজে মুছে ফেলতে পারবেন না।";
    header("Location: " . BASE_URL . "/admin/users/index.php");
    exit;
}

try {
    // Fetch username for logging
    $stmt = $pdo->prepare("SELECT `username` FROM `users` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $del_stmt = $pdo->prepare("DELETE FROM `users` WHERE `id` = ?");
        $del_stmt->execute([$user_id]);

        log_activity($pdo, "Delete User Account", "Deleted admin user: '{$user['username']}' (ID: $user_id)");
        $_SESSION['flash_success'] = "ইউজার অ্যাকাউন্টটি সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "অ্যাকাউন্ট পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/users/index.php");
exit;
