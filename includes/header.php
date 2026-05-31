<?php
/**
 * Public Page Header
 * School Management Website
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Load school settings
$school = null;
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `schools` WHERE `id` = 1");
        $school = $stmt->fetch();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fallbacks
$sch_name_bn = $school['name_bn'] ?? 'সোনারগাঁও উচ্চ বিদ্যালয়';
$sch_name_en = $school['name_en'] ?? 'Sonargaon High School';
$sch_eiin = $school['eiin'] ?? '১২৩৪৫৬';
$sch_logo = $school['logo'] ?? '';
$sch_phone = $school['phone'] ?? '+৮৮০২১২৩৪৫৬';
$sch_mobile = $school['mobile'] ?? '+৮৮০১৭১২৩৪৫৬৭৮';
$sch_email = $school['email'] ?? 'info@school.gov.bd';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($sch_name_bn); ?> | Sonargaon High School</title>
    
    <!-- Google Fonts for Bengali and English -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Top Info Bar -->
<div class="top-bar">
    <div class="container top-bar-content">
        <div>
            <span><i class="fa fa-info-circle"></i> ইআইআইএন (EIIN): <strong><?php echo escape($sch_eiin); ?></strong></span>
            <span class="desktop-only" style="margin-left: 20px;"><i class="fa fa-phone"></i> <?php echo escape($sch_phone); ?></span>
        </div>
    </div>
</div>

<!-- Header Section -->
<header class="main-header">
    <div class="container header-container">
        <div class="logo-area">
            <?php if (!empty($sch_logo) && file_exists(UPLOAD_DIR . '/' . $sch_logo)): ?>
                <img src="<?php echo UPLOAD_URL . '/' . escape($sch_logo); ?>" alt="School Logo" class="school-logo">
            <?php else: ?>
                <div class="logo-placeholder">🏫</div>
            <?php endif; ?>
            <div class="school-titles">
                <h1><?php echo escape($sch_name_bn); ?></h1>
                <h2><?php echo escape($sch_name_en); ?></h2>
            </div>
        </div>
        <button class="nav-toggle" aria-label="Toggle navigation">
            <i class="fa fa-bars"></i>
        </button>
    </div>
</header>

<!-- Navigation Menu -->
<nav class="main-nav">
    <div class="container">
        <ul class="nav-list">
            <li><a href="<?php echo BASE_URL; ?>/"><i class="fa fa-home"></i> হোম</a></li>
            <li><a href="<?php echo BASE_URL; ?>/profile">পরিচিতি</a></li>
            <li><a href="<?php echo BASE_URL; ?>/recognition">অনুমতি ও স্বীকৃতি</a></li>
            <li><a href="<?php echo BASE_URL; ?>/students">শিক্ষার্থীর তথ্য</a></li>
            <li><a href="<?php echo BASE_URL; ?>/sections">অনুমোদিত শাখা</a></li>
            <li><a href="<?php echo BASE_URL; ?>/academics">পাঠদান তথ্য</a></li>
            <li><a href="<?php echo BASE_URL; ?>/mpo">এমপিও ও জাতীয়করণ</a></li>
            <li><a href="<?php echo BASE_URL; ?>/teachers">শিক্ষক-কর্মচারী</a></li>
            <li><a href="<?php echo BASE_URL; ?>/committee">ব্যবস্থাপনা কমিটি</a></li>
            <li><a href="<?php echo BASE_URL; ?>/contact">যোগাযোগ</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content Container -->
<main class="page-content container">
