<?php
/**
 * Admin Upload Routine Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

// Fetch classes for dropdown selection
$classes = [];
try {
    $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_routine'])) {
    $class_id = (int)($_POST['class_id'] ?? 0);

    if ($class_id <= 0) {
        $error = "অনুগ্রহ করে শ্রেণি নির্বাচন করুন।";
    } elseif (!isset($_FILES['routine_file']) || $_FILES['routine_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "অনুগ্রহ করে রুটিন ফাইল আপলোড করুন।";
    } else {
        try {
            // Check if class routine already exists
            $stmt = $pdo->prepare("SELECT * FROM `routines` WHERE `class_id` = ? LIMIT 1");
            $stmt->execute([$class_id]);
            $existing_routine = $stmt->fetch();

            // Upload the file
            $file_path = upload_file($_FILES['routine_file'], 'routines', ['jpg', 'jpeg', 'png', 'pdf'], 10485760);

            if ($existing_routine) {
                // Delete old file
                if (file_exists(UPLOAD_DIR . '/' . $existing_routine['file_path'])) {
                    unlink(UPLOAD_DIR . '/' . $existing_routine['file_path']);
                }

                // Update database
                $update_stmt = $pdo->prepare("UPDATE `routines` SET `file_path` = ?, `updated_at` = CURRENT_TIMESTAMP WHERE `class_id` = ?");
                $update_stmt->execute([$file_path, $class_id]);
            } else {
                // Insert database
                $insert_stmt = $pdo->prepare("INSERT INTO `routines` (`class_id`, `file_path`) VALUES (?, ?)");
                $insert_stmt->execute([$class_id, $file_path]);
            }

            log_activity($pdo, "Upload Routine", "Uploaded routine for class ID: $class_id");
            $_SESSION['flash_success'] = "ক্লাস রুটিন সফলভাবে আপলোড করা হয়েছে।";
            
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
    <span><i class="fa-solid fa-calendar-plus"></i> রুটিন আপলোড (Upload Class Routine)</span>
    <a href="index.php" class="btn-admin btn-secondary"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="admin-form-group">
                <label for="class_id">শ্রেণি নির্বাচন করুন <span style="color:var(--danger);">*</span></label>
                <select id="class_id" name="class_id" class="form-control" required>
                    <option value="">শ্রেণি নির্বাচন করুন</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo escape($c['name_bn']); ?> (<?php echo escape($c['name_en']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="routine_file">রুটিন ফাইল নির্বাচন করুন (সর্বোচ্চ ১০ মেগাবাইট, PDF/JPG/PNG) <span style="color:var(--danger);">*</span></label>
                <input type="file" id="routine_file" name="routine_file" class="form-control" accept="image/png, image/jpeg, application/pdf" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="upload_routine" class="btn-admin btn-primary"><i class="fa fa-upload"></i> রুটিন আপলোড করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
