<?php
/**
 * Admin Delete Student
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce auth
check_auth();

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/students/index.php");
    exit;
}

try {
    // Fetch profile photo path for cleanup
    $stmt = $pdo->prepare("SELECT `name_en`, `photo` FROM `students` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if ($student) {
        // Delete photo from disk if exists
        if (!empty($student['photo']) && file_exists(UPLOAD_DIR . '/' . $student['photo'])) {
            unlink(UPLOAD_DIR . '/' . $student['photo']);
        }

        // Delete database row
        $del_stmt = $pdo->prepare("DELETE FROM `students` WHERE `id` = ?");
        $del_stmt->execute([$student_id]);

        log_activity($pdo, "Delete Student", "Deleted student: '{$student['name_en']}' (ID: $student_id)");
        $_SESSION['flash_success'] = "শিক্ষার্থীর প্রোফাইল সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "শিক্ষার্থীর প্রোফাইল পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/students/index.php");
exit;
