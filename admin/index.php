<?php
/**
 * Admin Panel Dashboard
 * School Management Website
 */

require_once __DIR__ . '/../includes/admin_header.php';

// Fetch summary metrics
$count_students = 0;
$count_teachers = 0;
$count_staff = 0;
$count_notices = 0;

if ($pdo) {
    try {
        $count_students = $pdo->query("SELECT COUNT(*) FROM `students`")->fetchColumn();
        $count_teachers = $pdo->query("SELECT COUNT(*) FROM `teachers` WHERE `is_teacher` = 1")->fetchColumn();
        $count_staff = $pdo->query("SELECT COUNT(*) FROM `teachers` WHERE `is_teacher` = 0")->fetchColumn();
        $count_notices = $pdo->query("SELECT COUNT(*) FROM `notices`")->fetchColumn();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fetch 10 recent activity logs
$logs = [];
if ($pdo) {
    try {
        $logs = $pdo->query("
            SELECT l.*, u.username, u.name_en AS user_full_name
            FROM `activity_logs` l
            LEFT JOIN `users` u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT 10
        ")->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-gauge"></i> ড্যাশবোর্ড (Dashboard Summary)</span>
</div>

<!-- Stats Counter Grid -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-info">
            <h3>মোট ছাত্র-ছাত্রী</h3>
            <p><?php echo escape($count_students); ?> জন</p>
        </div>
        <div class="admin-stat-icon" style="color: var(--primary);"><i class="fa fa-graduation-cap"></i></div>
    </div>
    
    <div class="admin-stat-card blue">
        <div class="admin-stat-info">
            <h3>মোট শিক্ষক</h3>
            <p><?php echo escape($count_teachers); ?> জন</p>
        </div>
        <div class="admin-stat-icon" style="color: var(--info);"><i class="fa fa-chalkboard-user"></i></div>
    </div>
    
    <div class="admin-stat-card orange">
        <div class="admin-stat-info">
            <h3>কর্মকর্তা ও কর্মচারী</h3>
            <p><?php echo escape($count_staff); ?> জন</p>
        </div>
        <div class="admin-stat-icon" style="color: var(--warning);"><i class="fa fa-user-tie"></i></div>
    </div>
    
    <div class="admin-stat-card red">
        <div class="admin-stat-info">
            <h3>মোট নোটিশ</h3>
            <p><?php echo escape($count_notices); ?> টি</p>
        </div>
        <div class="admin-stat-icon" style="color: var(--danger);"><i class="fa fa-bullhorn"></i></div>
    </div>
</div>

<!-- Quick Actions Area -->
<div class="admin-card" style="margin-bottom: 25px;">
    <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa fa-wand-magic-sparkles" style="color: var(--accent);"></i> কুইক অ্যাকশন (Quick Actions)</span>
    </div>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="<?php echo BASE_URL; ?>/admin/students/add" class="btn-admin btn-primary"><i class="fa fa-user-plus"></i> শিক্ষার্থী যুক্ত করুন</a>
        <a href="<?php echo BASE_URL; ?>/admin/teachers/add" class="btn-admin btn-accent"><i class="fa fa-user-tie"></i> শিক্ষক/স্টাফ যুক্ত করুন</a>
        <a href="<?php echo BASE_URL; ?>/admin/academic/add_notice" class="btn-admin btn-secondary" style="background-color: var(--info);"><i class="fa fa-plus-circle"></i> নোটিশ প্রকাশ করুন</a>
        <a href="<?php echo BASE_URL; ?>/admin/classes" class="btn-admin btn-secondary" style="background-color: var(--primary);"><i class="fa fa-layer-group"></i> শ্রেণি/শাখা ব্যবস্থাপনা</a>
    </div>
</div>

<!-- Activity Log Area -->
<div class="admin-card">
    <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa fa-list-check" style="color: var(--accent);"></i> সাম্প্রতিক কার্য বিবরণী (Recent Activity Logs)</span>
        <span style="font-size:12px; color:var(--text-muted);">সর্বশেষ ১০টি অ্যাক্টিভিটি</span>
    </div>
    
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>সময় ও তারিখ</th>
                    <th>অপারেটর (ইউজার)</th>
                    <th>ধাপ (অ্যাকশন)</th>
                    <th>বিস্তারিত বিবরণ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="font-family: var(--font-en); font-size:12px; color: var(--text-muted);"><?php echo date('d-m-Y H:i:s', strtotime($log['created_at'])); ?></td>
                            <td><strong><?php echo escape($log['username'] ?: 'System'); ?></strong></td>
                            <td><span class="badge" style="background-color: #cbd5e1; color:#0f172a;"><?php echo escape($log['action']); ?></span></td>
                            <td style="text-align: left; font-size: 13px;"><?php echo escape($log['details'] ?: '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="color: var(--text-muted);">কোনো কার্য বিবরণী পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';
?>
