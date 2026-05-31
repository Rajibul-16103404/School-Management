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

<div class="admin-card" style="padding: 0; overflow: visible;">
    <!-- Tab Navigation -->
    <div style="background-color: var(--bg-sidebar); border-radius: var(--radius) var(--radius) 0 0; display: flex; overflow-x: auto; border-bottom: 3px solid var(--accent); padding: 0 10px;">
        <button class="tab-btn active" onclick="switchTab(event, 'notices-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid var(--accent); transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-bullhorn"></i> নোটিশ বোর্ড (Notice Board)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'routines-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-calendar-days"></i> শ্রেণি রুটিন (Class Routines)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'syllabi-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-book-bookmark"></i> সিলেবাস তালিকা (Syllabi)
        </button>
    </div>

    <!-- Tab Contents -->
    <div style="padding: 25px;">
        <!-- Tab 1: Notice Board -->
        <div id="notices-tab" class="tab-content active-content">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-bullhorn" style="color:var(--accent);"></i> প্রতিষ্ঠানের নোটিশ ও বিজ্ঞপ্তি</h3>
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

        <!-- Tab 2: Class Routines -->
        <div id="routines-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-calendar-days" style="color:var(--accent);"></i> শ্রেণি ভিত্তিক সাপ্তাহিক ক্লাস রুটিনসমূহ</h3>
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

        <!-- Tab 3: Syllabi -->
        <div id="syllabi-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-book-bookmark" style="color:var(--accent);"></i> আপলোডকৃত পাঠ্যসূচী ও সিলেবাস তালিকা</h3>
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

<script>
// Switch admin tabs utility
function switchTab(evt, tabId) {
    // Hide all tab contents
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
    }

    // Deactivate all tab buttons and reset colors
    const tabBtns = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < tabBtns.length; i++) {
        tabBtns[i].classList.remove("active");
        tabBtns[i].style.borderBottom = "3px solid transparent";
        tabBtns[i].style.color = "#94a3b8";
    }

    // Show selected tab content and active state
    document.getElementById(tabId).style.display = "block";
    evt.currentTarget.classList.add("active");
    evt.currentTarget.style.borderBottom = "3px solid var(--accent)";
    evt.currentTarget.style.color = "white";
}
</script>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
