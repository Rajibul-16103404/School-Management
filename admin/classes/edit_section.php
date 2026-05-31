<?php
/**
 * Admin Edit Section Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;
$section_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($section_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/classes/index.php");
    exit;
}

// Fetch section record
$section = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `sections` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$section_id]);
    $section = $stmt->fetch();
} catch (PDOException $e) {}

if (!$section) {
    $_SESSION['flash_error'] = "শাখা পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/classes/index.php");
    exit;
}

// Fetch classes for dropdown
$classes = [];
try {
    $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_section'])) {
    $class_id = (int)($_POST['class_id'] ?? 0);
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $approved_count = (int)($_POST['approved_sections_count'] ?? 1);
    $existing_count = (int)($_POST['existing_sections_count'] ?? 1);
    $remark = sanitize_input($_POST['remark'] ?? '');

    if ($class_id <= 0 || empty($name_bn) || empty($name_en)) {
        $error = "শ্রেণি এবং শাখার নাম প্রদান করা আবশ্যক।";
    } else {
        try {
            $update_sql = "
                UPDATE `sections` 
                SET `class_id` = ?, `name_bn` = ?, `name_en` = ?, `approved_sections_count` = ?, `existing_sections_count` = ?, `remark` = ? 
                WHERE `id` = ?
            ";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute([$class_id, $name_bn, $name_en, $approved_count, $existing_count, $remark, $section_id]);

            log_activity($pdo, "Edit Section", "Updated section: '$name_en' (ID: $section_id)");
            $_SESSION['flash_success'] = "শাখার বিবরণ সফলভাবে আপডেট করা হয়েছে।";
            
            header("Location: " . BASE_URL . "/admin/classes/index.php");
            exit;
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-sitemap"></i> শাখার তথ্য সম্পাদন করুন</span>
    <a href="index.php" class="btn-admin btn-secondary"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST">
        <div class="form-grid">
            <div class="admin-form-group">
                <label for="class_id">শ্রেণি <span style="color:var(--danger);">*</span></label>
                <select id="class_id" name="class_id" class="form-control" required>
                    <option value="">শ্রেণি নির্বাচন করুন</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo (int)$section['class_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo escape($c['name_bn']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="name_bn">শাখার নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required value="<?php echo escape($section['name_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">শাখার নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required value="<?php echo escape($section['name_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="approved_sections_count">অনুমোদিত শাখার সংখ্যা</label>
                <input type="number" id="approved_sections_count" name="approved_sections_count" class="form-control" min="1" value="<?php echo escape($section['approved_sections_count']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="existing_sections_count">চলমান শাখার সংখ্যা</label>
                <input type="number" id="existing_sections_count" name="existing_sections_count" class="form-control" min="1" value="<?php echo escape($section['existing_sections_count']); ?>">
            </div>

            <div class="admin-form-group form-group-full">
                <label for="remark">মন্তব্য (Remarks)</label>
                <textarea id="remark" name="remark" class="form-control"><?php echo escape($section['remark']); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-admin btn-secondary">বাতিল করুন</a>
            <button type="submit" name="edit_section" class="btn-admin btn-primary"><i class="fa fa-save"></i> আপডেট সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
