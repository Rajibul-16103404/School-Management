<?php
/**
 * Admin Delete Notice
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce auth
check_auth();

$notice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($notice_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/academic/index.php");
    exit;
}

try {
    // Fetch notice attachment for deletion
    $stmt = $pdo->prepare("SELECT `title_en`, `attachment` FROM `notices` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$notice_id]);
    $notice = $stmt->fetch();

    if ($notice) {
        // Delete attachment if exists
        if (!empty($notice['attachment']) && file_exists(UPLOAD_DIR . '/' . $notice['attachment'])) {
            unlink(UPLOAD_DIR . '/' . $notice['attachment']);
        }

        // Delete from database
        $del_stmt = $pdo->prepare("DELETE FROM `notices` WHERE `id` = ?");
        $del_stmt->execute([$notice_id]);

        log_activity($pdo, "Delete Notice", "Deleted notice: '{$notice['title_en']}' (ID: $notice_id)");
        $_SESSION['flash_success'] = "নোটিশটি সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "নোটিশ পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/academic/index.php");
exit;
