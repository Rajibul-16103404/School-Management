<?php
/**
 * Admin Panel Header
 * School Management Website
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Enforce authentication
check_auth();

$user_name = $_SESSION['user_name'] ?? 'ব্যবহারকারী';
$user_role = $_SESSION['user_role'] ?? 'staff';

// Map role to display text
$role_names = [
    'superadmin' => 'সুপার অ্যাডমিন',
    'headteacher' => 'প্রধান শিক্ষক',
    'staff' => 'স্টাফ'
];
$role_display = $role_names[$user_role] ?? 'স্টাফ';

// Get current script for active menu highlights
$active_script = basename($_SERVER['SCRIPT_NAME']);
$active_dir = basename(dirname($_SERVER['SCRIPT_NAME']));
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sonargaon High School</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    
    <!-- CSS Layout -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSRF Token Definition -->
    <script>
        const CSRF_TOKEN = '<?php echo generate_csrf_token(); ?>';
    </script>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="logo-box">🏫</div>
            <div class="logo-text">
                <h2>কন্ট্রোল প্যানেল</h2>
                <p>সোনারগাঁও উচ্চ বিদ্যালয়</p>
            </div>
        </div>
        
        <div class="user-profile">
            <div class="profile-avatar">
                <i class="fa fa-user-shield"></i>
            </div>
            <div class="profile-info">
                <h3><?php echo escape($user_name); ?></h3>
                <span class="badge badge-role"><?php echo escape($role_display); ?></span>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <ul>
                <li class="<?php echo ($active_script === 'index.php' && $active_dir === 'admin') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin">
                        <i class="fa-solid fa-gauge"></i> <span>ড্যাশবোর্ড</span>
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'students') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/students">
                        <i class="fa-solid fa-graduation-cap"></i> <span>শিক্ষার্থী ব্যবস্থাপনা</span>
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'teachers') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/teachers">
                        <i class="fa-solid fa-chalkboard-user"></i> <span>শিক্ষক ও কর্মচারী</span>
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'classes') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/classes">
                        <i class="fa-solid fa-school"></i> <span>শ্রেণি ও শাখা</span>
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'academic') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/academic">
                        <i class="fa-solid fa-book-open"></i> <span>পাঠদান ও নোটিশ</span>
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'gallery') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/gallery">
                        <i class="fa-solid fa-images"></i> <span>ফটো গ্যালারি</span>
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'committee') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/committee">
                        <i class="fa-solid fa-users-gear"></i> <span>ব্যবস্থাপনা কমিটি</span>
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'mpo') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/mpo">
                        <i class="fa-solid fa-file-invoice-dollar"></i> <span>এমপিও তথ্য</span>
                    </a>
                </li>
                
                <!-- Superadmin & Headteacher only access -->
                <?php if ($user_role === 'superadmin' || $user_role === 'headteacher'): ?>
                    <li class="<?php echo ($active_dir === 'menu') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/menu">
                            <i class="fa-solid fa-compass"></i> <span>ন্যাভিগেশন মেনু</span>
                        </a>
                    </li>
                    <li class="<?php echo ($active_dir === 'settings') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/settings">
                            <i class="fa-solid fa-gears"></i> <span>প্রতিষ্ঠান পরিচিতি</span>
                        </a>
                    </li>
                    <li class="<?php echo ($active_dir === 'cms') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/cms">
                            <i class="fa-solid fa-display"></i> <span>ওয়েবসাইট CMS</span>
                        </a>
                    </li>
                    <li class="<?php echo ($active_dir === 'backup') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/backup">
                            <i class="fa-solid fa-database"></i> <span>ডাটা ব্যাকআপ ও রিসেট</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Superadmin only access -->
                <?php if ($user_role === 'superadmin'): ?>
                    <li class="<?php echo ($active_dir === 'users') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/users">
                            <i class="fa-solid fa-user-group"></i> <span>অ্যাডমিন ইউজার্স</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="menu-divider"></li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/" target="_blank">
                        <i class="fa-solid fa-globe"></i> <span>মূল ওয়েবসাইট</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Workspace -->
    <div class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fa fa-bars"></i>
            </button>
            <div class="topbar-right">
                <span class="date-display"><i class="fa fa-calendar-alt"></i> <?php echo date('d M, Y'); ?></span>
                <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="btn-logout" title="লগআউট" onclick="return confirm('আপনি কি নিশ্চিতভাবে লগআউট করতে চান?');"><i class="fa fa-power-off"></i></a>
            </div>
        </header>

        <!-- Dashboard Content Inner -->
        <div class="admin-content">
            <!-- Flash Alerts -->
            <?php echo display_flash_alerts(); ?>
