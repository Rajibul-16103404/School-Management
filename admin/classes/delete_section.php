<?php
/**
 * Admin Delete Section
 * School Management Website
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce auth
check_auth();

$section_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($section_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/classes/index.php");
    exit;
}

try {
    // Fetch section name for logging
    $stmt = $pdo->prepare("SELECT `name_en` FROM `sections` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$section_id]);
    $section = $stmt->fetch();

    if ($section) {
        $del_stmt = $pdo->prepare("DELETE FROM `sections` WHERE `id` = ?");
        $del_stmt->execute([$section_id]);

        log_activity($pdo, "Delete Section", "Deleted section: '{$section['name_en']}' (ID: $section_id)");
        $_SESSION['flash_success'] = "শাখাটি সফলভাবে মুছে ফেলা হয়েছে।";
    } else {
        $_SESSION['flash_error'] = "শাখা পাওয়া যায়নি।";
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "মুছে ফেলা সম্ভব হয়নি। ডাটাবেজ ত্রুটি: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/admin/classes/index.php");
exit;
