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
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    
    <!-- CSS Layout -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                        <i class="fa-solid fa-gauge"></i> ড্যাশবোর্ড
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'students') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/students">
                        <i class="fa-solid fa-graduation-cap"></i> শিক্ষার্থী ব্যবস্থাপনা
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'teachers') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/teachers">
                        <i class="fa-solid fa-chalkboard-user"></i> শিক্ষক ও কর্মচারী
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'classes') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/classes">
                        <i class="fa-solid fa-school"></i> শ্রেণি ও শাখা
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'academic') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/academic">
                        <i class="fa-solid fa-book-open"></i> পাঠদান ও নোটিশ
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'committee') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/committee">
                        <i class="fa-solid fa-users-gear"></i> ব্যবস্থাপনা কমিটি
                    </a>
                </li>
                <li class="<?php echo ($active_dir === 'mpo') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/mpo">
                        <i class="fa-solid fa-file-invoice-dollar"></i> এমপিও তথ্য
                    </a>
                </li>
                
                <!-- Superadmin & Headteacher only access -->
                <?php if ($user_role === 'superadmin' || $user_role === 'headteacher'): ?>
                    <li class="<?php echo ($active_dir === 'settings') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/settings">
                            <i class="fa-solid fa-gears"></i> প্রতিষ্ঠান পরিচিতি
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Superadmin only access -->
                <?php if ($user_role === 'superadmin'): ?>
                    <li class="<?php echo ($active_dir === 'users') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/users">
                            <i class="fa-solid fa-user-group"></i> অ্যাডমিন ইউজার্স
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="menu-divider"></li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/" target="_blank">
                        <i class="fa-solid fa-globe"></i> মূল ওয়েবসাইট
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
