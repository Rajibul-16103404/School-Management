<?php
/**
 * Admin Delete Teacher/Staff
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce auth
check_auth();

$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/teachers/index.php");
    exit;
}

try {
    // Fetch photo path for cleanup
    $stmt = $pdo->prepare("SELECT `name_en`, `photo` FROM `teachers` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();

    if ($member) {
        // Delete photo if exists
        if (!empty($member['photo']) && file_exists(UPLOAD_DIR . '/' . $member['photo'])) {
            unlink(UPLOAD_DIR . '/' . $member['photo']);
        }

        // Delete from database
        $del_stmt = $pdo->prepare("DELETE FROM `teachers` WHERE `id` = ?");
        $del_stmt->execute([$member_id]);

        log_activity($pdo, "Delete Teacher/Staff", "Deleted teacher/staff member: '{$member['name_en']}' (ID: $member_id)");
        $_SESSION['flash_success'] = "প্রোফাইলটি সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "রেকর্ড পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/teachers/index.php");
exit;
