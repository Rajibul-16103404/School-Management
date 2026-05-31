<?php
/**
 * Admin Upload Syllabus Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

// Fetch classes for dropdown selection
$classes = [];
try {
    $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_syllabus'])) {
    $class_id = (int)($_POST['class_id'] ?? 0);
    $subject_bn = sanitize_input($_POST['subject_bn'] ?? '');
    $subject_en = sanitize_input($_POST['subject_en'] ?? '');

    if ($class_id <= 0 || empty($subject_bn) || empty($subject_en)) {
        $error = "শ্রেণি এবং বিষয়ের নাম (বাংলা ও ইংরেজি) প্রদান করা আবশ্যক।";
    } elseif (!isset($_FILES['syllabus_file']) || $_FILES['syllabus_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "অনুগ্রহ করে সিলেবাস ফাইল (PDF) আপলোড করুন।";
    } else {
        try {
            // Upload the file
            $file_path = upload_file($_FILES['syllabus_file'], 'syllabi', ['pdf'], 10485760); // Limit to 10MB PDF

            // Insert database
            $insert_stmt = $pdo->prepare("INSERT INTO `syllabi` (`class_id`, `subject_bn`, `subject_en`, `file_path`) VALUES (?, ?, ?, ?)");
            $insert_stmt->execute([$class_id, $subject_bn, $subject_en, $file_path]);

            log_activity($pdo, "Upload Syllabus", "Uploaded syllabus: '$subject_en' for class ID: $class_id");
            $_SESSION['flash_success'] = "সিলেবাস সফলভাবে আপলোড করা হয়েছে।";
            
            header("Location: " . BASE_URL . "/admin/academic/index.php");
            exit;
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        } catch (Exception $e) {
            $error = "ফাইল আপলোড ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-book"></i> সিলেবাস আপলোড করুন (Upload Syllabus)</span>
    <a href="index.php" class="btn-admin btn-secondary"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST" enctype="multipart/form-data">
    <?php echo csrf_input(); ?>
        <div class="form-grid">
            <div class="admin-form-group">
                <label for="class_id">শ্রেণি নির্বাচন করুন <span style="color:var(--danger);">*</span></label>
                <div style="display: flex; gap: 8px;">
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">শ্রেণি নির্বাচন করুন</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo escape($c['name_bn']); ?> (<?php echo escape($c['name_en']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?php echo BASE_URL; ?>/admin/classes/add_class" class="btn-admin btn-accent" style="display: flex; align-items: center; justify-content: center; width: 42px; min-width: 42px; border-radius: 6px; text-decoration: none;" title="নতুন শ্রেণি যোগ করুন"><i class="fa fa-plus"></i></a>
                </div>
            </div>

            <div class="admin-form-group">
                <label for="subject_bn">বিষয় (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="subject_bn" name="subject_bn" class="form-control" required placeholder="যেমন: বাংলা / ইংরেজি / পদার্থবিজ্ঞান">
            </div>
            
            <div class="admin-form-group">
                <label for="subject_en">বিষয় (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="subject_en" name="subject_en" class="form-control" required placeholder="যেমন: Bangla / English / Physics">
            </div>

            <div class="admin-form-group">
                <label for="syllabus_file">সিলেবাস ফাইল (সর্বোচ্চ ১০ মেগাবাইট, শুধুমাত্র PDF) <span style="color:var(--danger);">*</span></label>
                <input type="file" id="syllabus_file" name="syllabus_file" class="form-control" accept="application/pdf" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="upload_syllabus" class="btn-admin btn-primary"><i class="fa fa-upload"></i> সিলেবাস সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
