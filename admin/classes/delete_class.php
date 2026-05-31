<?php
/**
 * Admin Delete Class
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce auth
check_auth();

$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($class_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/classes/index.php");
    exit;
}

try {
    // Fetch class name for logging
    $stmt = $pdo->prepare("SELECT `name_en` FROM `classes` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$class_id]);
    $class = $stmt->fetch();

    if ($class) {
        $del_stmt = $pdo->prepare("DELETE FROM `classes` WHERE `id` = ?");
        $del_stmt->execute([$class_id]);

        log_activity($pdo, "Delete Class", "Deleted class: '{$class['name_en']}' (ID: $class_id)");
        $_SESSION['flash_success'] = "শ্রেণিটি সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "শ্রেণি পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/classes/index.php");
exit;
