<?php
/**
 * Admin Delete Syllabus
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce auth
check_auth();

$syllabus_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($syllabus_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/academic/index.php");
    exit;
}

try {
    // Fetch file path for deletion
    $stmt = $pdo->prepare("SELECT `file_path`, `subject_en` FROM `syllabi` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$syllabus_id]);
    $syllabus = $stmt->fetch();

    if ($syllabus) {
        // Delete file
        if (file_exists(UPLOAD_DIR . '/' . $syllabus['file_path'])) {
            unlink(UPLOAD_DIR . '/' . $syllabus['file_path']);
        }

        // Delete from database
        $del_stmt = $pdo->prepare("DELETE FROM `syllabi` WHERE `id` = ?");
        $del_stmt->execute([$syllabus_id]);

        log_activity($pdo, "Delete Syllabus", "Deleted syllabus for subject: '{$syllabus['subject_en']}' (ID: $syllabus_id)");
        $_SESSION['flash_success'] = "সিলেবাসটি সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "সিলেবাস পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/academic/index.php");
exit;
