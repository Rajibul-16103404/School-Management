<?php
/**
 * AJAX API: Get Class Sections
 * School Management Website
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Enforce basic validation
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

if ($class_id <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT `id`, `name_bn`, `name_en` FROM `sections` WHERE `class_id` = ? ORDER BY `id` ASC");
    $stmt->execute([$class_id]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($sections);
} catch (PDOException $e) {
    // Return empty array on error
    echo json_encode([]);
}
exit;
