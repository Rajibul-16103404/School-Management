<?php
/**
 * Admin Edit Class Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($class_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/classes/index.php");
    exit;
}

// Fetch class record
$class = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `classes` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$class_id]);
    $class = $stmt->fetch();
} catch (PDOException $e) {}

if (!$class) {
    $_SESSION['flash_error'] = "শ্রেণি পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/classes/index.php");
    exit;
}

// Fetch teachers for class teacher dropdown
$teachers = [];
try {
    $teachers = $pdo->query("SELECT * FROM `teachers` WHERE `is_teacher` = 1 AND `status` = 'Active' ORDER BY `name_bn` ASC")->fetchAll();
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_class'])) {
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $numeric_name = (int)($_POST['numeric_name'] ?? 0);
    $class_teacher_id = isset($_POST['class_teacher_id']) && $_POST['class_teacher_id'] !== '' ? (int)$_POST['class_teacher_id'] : null;

    if (empty($name_bn) || empty($name_en) || $numeric_name <= 0) {
        $error = "শ্রেণির নাম এবং নম্বর প্রদান করা আবশ্যক।";
    } else {
        try {
            // Check duplication of numeric name excluding this class
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `classes` WHERE `numeric_name` = ? AND `id` != ?");
            $stmt->execute([$numeric_name, $class_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "এই শ্রেণি নম্বরটি (Numeric Name) ইতিমধ্যেই অন্য শ্রেণিতে নিবন্ধিত আছে।";
            } else {
                $update_sql = "UPDATE `classes` SET `name_bn` = ?, `name_en` = ?, `numeric_name` = ?, `class_teacher_id` = ? WHERE `id` = ?";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute([$name_bn, $name_en, $numeric_name, $class_teacher_id, $class_id]);

                log_activity($pdo, "Edit Class", "Updated class: '$name_en' (ID: $class_id)");
                $_SESSION['flash_success'] = "শ্রেণির বিবরণ সফলভাবে আপডেট করা হয়েছে।";
                
                header("Location: " . BASE_URL . "/admin/classes/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-school"></i> শ্রেণির তথ্য সম্পাদন করুন</span>
    <a href="index.php" class="btn-admin btn-secondary"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST">
    <?php echo csrf_input(); ?>
        <div class="form-grid">
            <div class="admin-form-group">
                <label for="name_bn">শ্রেণির নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required value="<?php echo escape($class['name_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">শ্রেণির নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required value="<?php echo escape($class['name_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="numeric_name">শ্রেণির ক্রমিক নম্বর (Numeric Rank) <span style="color:var(--danger);">*</span></label>
                <input type="number" id="numeric_name" name="numeric_name" class="form-control" required min="1" max="12" value="<?php echo escape($class['numeric_name']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="class_teacher_id">শ্রেণি শিক্ষক (Class Teacher)</label>
                <select id="class_teacher_id" name="class_teacher_id" class="form-control">
                    <option value="">কোনো শিক্ষক নির্ধারিত নয়</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo (int)$class['class_teacher_id'] === (int)$t['id'] ? 'selected' : ''; ?>><?php echo escape($t['name_bn']); ?> (<?php echo escape($t['designation_bn']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="edit_class" class="btn-admin btn-primary"><i class="fa fa-save"></i> আপডেট সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
