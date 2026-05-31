<?php
/**
 * Admin Academic Dashboard
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Fetch all classes
$classes = [];
try {
    $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
} catch (PDOException $e) {}

// Fetch notices
$notices = [];
try {
    $notices = $pdo->query("SELECT * FROM `notices` ORDER BY `publish_date` DESC, `id` DESC")->fetchAll();
} catch (PDOException $e) {}

// Fetch routines mapped by class_id
$routines = [];
try {
    $raw_routines = $pdo->query("
        SELECT r.*, c.name_bn AS class_name
        FROM `routines` r
        JOIN `classes` c ON r.class_id = c.id
        ORDER BY c.numeric_name ASC
    ")->fetchAll();
    foreach ($raw_routines as $r) {
        $routines[$r['class_id']] = $r;
    }
} catch (PDOException $e) {}

// Fetch syllabi
$syllabi = [];
try {
    $syllabi = $pdo->query("
        SELECT s.*, c.name_bn AS class_name
        FROM `syllabi` s
        JOIN `classes` c ON s.class_id = c.id
        ORDER BY c.numeric_name ASC, s.id DESC
    ")->fetchAll();
} catch (PDOException $e) {}
?>

<div class="page-title">
    <span><i class="fa-solid fa-book-open"></i> পাঠদান ও নোটিশ ব্যবস্থাপনা</span>
    <div style="display:flex; gap:10px;">
        <a href="<?php echo BASE_URL; ?>/admin/academic/upload_syllabus" class="btn-admin btn-secondary" style="background-color: var(--info);"><i class="fa fa-book"></i> সিলেবাস আপলোড</a>
        <a href="<?php echo BASE_URL; ?>/admin/academic/upload_routine" class="btn-admin btn-accent"><i class="fa fa-calendar-plus"></i> রুটিন আপলোড</a>
        <a href="<?php echo BASE_URL; ?>/admin/academic/add_notice" class="btn-admin btn-primary"><i class="fa fa-plus-circle"></i> নতুন নোটিশ</a>
    </div>
</div>

<!-- Grid Layout: Notices on the Left, Routines/Syllabi on the Right -->
<div style="display:grid; grid-template-columns: 1fr; gap:25px; align-items: start;">
    <!-- 1. Notices Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title"><i class="fa fa-bullhorn" style="color:var(--accent);"></i> নোটিশ বোর্ড (Notice Board)</span>
        </div>
        
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>তারিখ</th>
                        <th>নোটিশের শিরোনাম (বাংলা)</th>
                        <th>অবস্থা (Visibility)</th>
                        <th>সংযুক্তি (File)</th>
                        <th class="actions-cell">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($notices)): ?>
                        <?php foreach ($notices as $notice): ?>
                            <tr>
                                <td style="font-family: var(--font-en); font-size:13px;"><?php echo format_date($notice['publish_date']); ?></td>
                                <td style="font-weight: bold; text-align: left;"><?php echo escape($notice['title_bn']); ?></td>
                                <td>
                                    <?php echo $notice['is_published'] === 1 ? '<span class="badge badge-success">Published</span>' : '<span class="badge badge-danger">Draft</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($notice['attachment']): ?>
                                        <a href="<?php echo UPLOAD_URL . '/' . escape($notice['attachment']); ?>" target="_blank" class="badge badge-success"><i class="fa fa-file-pdf"></i> ফাইল</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size:12px;">নেই</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <a href="<?php echo BASE_URL; ?>/admin/academic/edit_notice?id=<?php echo $notice['id']; ?>" class="btn-action edit" title="সম্পাদনা"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo BASE_URL; ?>/admin/academic/delete_notice?id=<?php echo $notice['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই নোটিশটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="color: var(--text-muted);">কোনো নোটিশ পাওয়া যায়নি।</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Routines & Syllabi Grid -->
    <div style="display:grid; grid-template-columns: 1fr; gap:25px;">
        <!-- Routines Card -->
        <div class="admin-card">
            <div class="admin-card-header">
                <span class="admin-card-title"><i class="fa fa-calendar-days" style="color:var(--accent);"></i> শ্রেণি ভিত্তিক রুটিনসমূহ (Class Routines)</span>
            </div>
            
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>শ্রেণি</th>
                            <th>রুটিন ফাইল (PDF/Image)</th>
                            <th class="actions-cell">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($classes)): ?>
                            <?php foreach ($classes as $cls): ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php echo escape($cls['name_bn']); ?> (<?php echo escape($cls['name_en']); ?>)</td>
                                    <td>
                                        <?php if (isset($routines[$cls['id']])): ?>
                                            <a href="<?php echo UPLOAD_URL . '/' . escape($routines[$cls['id']]['file_path']); ?>" target="_blank" class="badge badge-success">
                                                <i class="fa fa-file-pdf"></i> রুটিন দেখুন / ডাউনলোড
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size:12px; font-style:italic;">কোনো রুটিন আপলোড করা হয়নি</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <?php if (isset($routines[$cls['id']])): ?>
                                            <a href="<?php echo BASE_URL; ?>/admin/academic/delete_routine?id=<?php echo $routines[$cls['id']]['id']; ?>" class="btn-action delete" title="রুটিন মুছুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই রুটিনটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size:12px;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Syllabi Card -->
        <div class="admin-card">
            <div class="admin-card-header">
                <span class="admin-card-title"><i class="fa fa-book-bookmark" style="color:var(--accent);"></i> আপলোডকৃত সিলেবাসসমূহ (Syllabi)</span>
            </div>
            
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>শ্রেণি</th>
                            <th>বিষয়</th>
                            <th>সিলেবাস ফাইল (PDF)</th>
                            <th class="actions-cell">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($syllabi)): ?>
                            <?php foreach ($syllabi as $sy): ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php echo escape($sy['class_name']); ?></td>
                                    <td><strong><?php echo escape($sy['subject_bn']); ?></strong> (<?php echo escape($sy['subject_en']); ?>)</td>
                                    <td>
                                        <a href="<?php echo UPLOAD_URL . '/' . escape($sy['file_path']); ?>" target="_blank" class="badge badge-success">
                                            <i class="fa fa-file-pdf"></i> সিলেবাস ডাউনলোড
                                        </a>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="<?php echo BASE_URL; ?>/admin/academic/delete_syllabus?id=<?php echo $sy['id']; ?>" class="btn-action delete" title="সিলেবাস মুছুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই সিলেবাসটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="color: var(--text-muted);">কোনো সিলেবাস আপলোড করা হয়নি।</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
