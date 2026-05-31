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
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    
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
            <?php elseif (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="School Logo" class="school-logo">
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

<?php
// Fetch menus from database
$menu_tree = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `menus` ORDER BY `sort_order` ASC");
        $all_menus = $stmt->fetchAll();
        
        $root_menus = [];
        $sub_menus = [];
        
        foreach ($all_menus as $m) {
            if ($m['parent_id'] === null) {
                $root_menus[] = $m;
            } else {
                $sub_menus[$m['parent_id']][] = $m;
            }
        }
        
        $menu_tree = [
            'roots' => $root_menus,
            'subs' => $sub_menus
        ];
    } catch (PDOException $e) {
        // Fallback to static
    }
}

// Fallbacks
if (empty($menu_tree)) {
    $menu_tree = [
        'roots' => [
            ['id' => 1, 'title_bn' => 'হোম', 'title_en' => 'Home', 'url' => '/'],
            ['id' => 2, 'title_bn' => 'আমাদের সম্পর্কে', 'title_en' => 'About Us', 'url' => '#'],
            ['id' => 5, 'title_bn' => 'একাডেমিক', 'title_en' => 'Academics', 'url' => '#'],
            ['id' => 9, 'title_bn' => 'জনবল', 'title_en' => 'Personnel', 'url' => '#'],
            ['id' => 12, 'title_bn' => 'এমপিও ও জাতীয়করণ', 'title_en' => 'MPO & Nationalization', 'url' => '/mpo'],
            ['id' => 13, 'title_bn' => 'যোগাযোগ', 'title_en' => 'Contact', 'url' => '/contact']
        ],
        'subs' => [
            2 => [
                ['title_bn' => 'পরিচিতি', 'title_en' => 'Profile', 'url' => '/profile'],
                ['title_bn' => 'অনুমতি ও স্বীকৃতি', 'title_en' => 'Recognition', 'url' => '/recognition']
            ],
            5 => [
                ['title_bn' => 'শিক্ষার্থীর তথ্য', 'title_en' => 'Students Info', 'url' => '/students'],
                ['title_bn' => 'অনুমোদিত শাখা', 'title_en' => 'Approved Sections', 'url' => '/sections'],
                ['title_bn' => 'পাঠদান তথ্য', 'title_en' => 'Academics Info', 'url' => '/academics']
            ],
            9 => [
                ['title_bn' => 'শিক্ষক-কর্মচারী', 'title_en' => 'Teachers & Staff', 'url' => '/teachers'],
                ['title_bn' => 'ব্যবস্থাপনা কমিটি', 'title_en' => 'Management Committee', 'url' => '/committee']
            ]
        ]
    ];
}
?>

<!-- Navigation Menu -->
<nav class="main-nav">
    <div class="container">
        <ul class="nav-list">
            <?php foreach ($menu_tree['roots'] as $root): 
                $has_sub = isset($menu_tree['subs'][$root['id']]) && count($menu_tree['subs'][$root['id']]) > 0;
                $url = $root['url'];
                
                if ($url !== '#' && !str_starts_with($url, 'http') && !str_starts_with($url, 'mailto:')) {
                    $url = BASE_URL . $url;
                }
            ?>
                <?php if ($has_sub): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle"><?php echo escape($root['title_bn']); ?> <i class="fa fa-chevron-down" style="font-size: 11px; margin-left: 4px;"></i></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($menu_tree['subs'][$root['id']] as $sub): 
                                $sub_url = $sub['url'];
                                if ($sub_url !== '#' && !str_starts_with($sub_url, 'http') && !str_starts_with($sub_url, 'mailto:')) {
                                    $sub_url = BASE_URL . $sub_url;
                                }
                            ?>
                                <li><a href="<?php echo $sub_url; ?>"><?php echo escape($sub['title_bn']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo $url; ?>">
                            <?php if ($root['id'] == 1 || $root['url'] === '/'): ?><i class="fa fa-home"></i> <?php endif; ?>
                            <?php echo escape($root['title_bn']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<!-- Main Content Container -->
<main class="page-content container">
