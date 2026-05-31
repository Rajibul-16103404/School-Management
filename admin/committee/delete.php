<?php
/**
 * Admin Delete Committee Member
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
    header("Location: " . BASE_URL . "/admin/committee/index.php");
    exit;
}

try {
    // Fetch member image for deletion
    $stmt = $pdo->prepare("SELECT `name_en`, `photo` FROM `committee_members` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();

    if ($member) {
        // Delete image file if exists
        if (!empty($member['photo']) && file_exists(UPLOAD_DIR . '/' . $member['photo'])) {
            unlink(UPLOAD_DIR . '/' . $member['photo']);
        }

        // Delete from database
        $del_stmt = $pdo->prepare("DELETE FROM `committee_members` WHERE `id` = ?");
        $del_stmt->execute([$member_id]);

        log_activity($pdo, "Delete Committee Member", "Deleted committee member: '{$member['name_en']}' (ID: $member_id)");
        $_SESSION['flash_success'] = "কমিটির সদস্যের প্রোফাইল সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "সদস্যের তথ্য পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/committee/index.php");
exit;
