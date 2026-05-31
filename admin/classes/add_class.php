<?php
/**
 * Admin Add Class Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

// Fetch teachers for class teacher dropdown
$teachers = [];
try {
    $teachers = $pdo->query("SELECT * FROM `teachers` WHERE `is_teacher` = 1 AND `status` = 'Active' ORDER BY `name_bn` ASC")->fetchAll();
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $numeric_name = (int)($_POST['numeric_name'] ?? 0);
    $class_teacher_id = isset($_POST['class_teacher_id']) && $_POST['class_teacher_id'] !== '' ? (int)$_POST['class_teacher_id'] : null;

    if (empty($name_bn) || empty($name_en) || $numeric_name <= 0) {
        $error = "শ্রেণির নাম এবং নম্বর প্রদান করা আবশ্যক।";
    } else {
        try {
            // Check duplication of numeric name
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `classes` WHERE `numeric_name` = ?");
            $stmt->execute([$numeric_name]);
            if ($stmt->fetchColumn() > 0) {
                $error = "এই শ্রেণি নম্বরটি (Numeric Name) ইতিমধ্যেই নিবন্ধিত আছে।";
            } else {
                $insert_sql = "INSERT INTO `classes` (`name_bn`, `name_en`, `numeric_name`, `class_teacher_id`) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($insert_sql);
                $stmt->execute([$name_bn, $name_en, $numeric_name, $class_teacher_id]);

                log_activity($pdo, "Add Class", "Added class: '$name_en' (Numeric: $numeric_name)");
                $_SESSION['flash_success'] = "শ্রেণি সফলভাবে তৈরি করা হয়েছে।";
                
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
    <span><i class="fa-solid fa-school"></i> নতুন শ্রেণি তৈরি করুন</span>
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
                <label for="name_bn">শ্রেণির নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required placeholder="যেমন: ষষ্ঠ শ্রেণি">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">শ্রেণির নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required placeholder="যেমন: Class Six">
            </div>

            <div class="admin-form-group">
                <label for="numeric_name">শ্রেণির ক্রমিক নম্বর (Numeric Rank) <span style="color:var(--danger);">*</span></label>
                <input type="number" id="numeric_name" name="numeric_name" class="form-control" required min="1" max="12" placeholder="যেমন: 6, 7, 8, 9, 10">
            </div>

            <div class="admin-form-group">
                <label for="class_teacher_id">শ্রেণি শিক্ষক (Class Teacher)</label>
                <select id="class_teacher_id" name="class_teacher_id" class="form-control">
                    <option value="">কোনো শিক্ষক নির্ধারিত নয়</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo escape($t['name_bn']); ?> (<?php echo escape($t['designation_bn']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="add_class" class="btn-admin btn-primary"><i class="fa fa-save"></i> সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
