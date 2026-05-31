<?php
/**
 * Admin Delete Routine
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce auth
check_auth();

$routine_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($routine_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/academic/index.php");
    exit;
}

try {
    // Fetch file path for deletion
    $stmt = $pdo->prepare("SELECT `file_path`, `class_id` FROM `routines` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$routine_id]);
    $routine = $stmt->fetch();

    if ($routine) {
        // Delete file
        if (file_exists(UPLOAD_DIR . '/' . $routine['file_path'])) {
            unlink(UPLOAD_DIR . '/' . $routine['file_path']);
        }

        // Delete from database
        $del_stmt = $pdo->prepare("DELETE FROM `routines` WHERE `id` = ?");
        $del_stmt->execute([$routine_id]);

        log_activity($pdo, "Delete Routine", "Deleted class routine (Class ID: {$routine['class_id']})");
        $_SESSION['flash_success'] = "ক্লাস রুটিনটি সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "রুটিন পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/academic/index.php");
exit;
