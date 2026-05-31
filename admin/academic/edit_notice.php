<?php
/**
 * Admin Edit Notice Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;
$notice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($notice_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/academic/index.php");
    exit;
}

// Fetch notice record
$notice = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `notices` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$notice_id]);
    $notice = $stmt->fetch();
} catch (PDOException $e) {}

if (!$notice) {
    $_SESSION['flash_error'] = "নোটিশ পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/academic/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_notice'])) {
    $title_bn = sanitize_input($_POST['title_bn'] ?? '');
    $title_en = sanitize_input($_POST['title_en'] ?? '');
    $content_bn = sanitize_input($_POST['content_bn'] ?? '');
    $content_en = sanitize_input($_POST['content_en'] ?? '');
    $publish_date = sanitize_input($_POST['publish_date'] ?? date('Y-m-d'));
    $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1;

    if (empty($title_bn) || empty($title_en) || empty($publish_date)) {
        $error = "নোটিশের শিরোনাম এবং প্রকাশের তারিখ প্রদান করা আবশ্যক।";
    } else {
        try {
            // Attachment upload handling
            $attachment_path = $notice['attachment']; // Keep original by default
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $uploaded = upload_file($_FILES['attachment'], 'notices', ['jpg', 'jpeg', 'png', 'pdf'], 10485760);
                    
                    // Delete old file if exists
                    if (!empty($notice['attachment']) && file_exists(UPLOAD_DIR . '/' . $notice['attachment'])) {
                        unlink(UPLOAD_DIR . '/' . $notice['attachment']);
                    }
                    
                    $attachment_path = $uploaded;
                } catch (Exception $e) {
                    $error = "সংযুক্তি ফাইল আপলোড ত্রুটি: " . $e->getMessage();
                }
            }

            if (!$error) {
                $update_sql = "
                    UPDATE `notices` 
                    SET `title_bn` = ?, `title_en` = ?, `content_bn` = ?, `content_en` = ?, `publish_date` = ?, `is_published` = ?, `attachment` = ? 
                    WHERE `id` = ?
                ";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute([
                    $title_bn,
                    $title_en,
                    $content_bn,
                    $content_en,
                    $publish_date,
                    $is_published,
                    $attachment_path,
                    $notice_id
                ]);

                log_activity($pdo, "Edit Notice", "Updated notice: '$title_en' (ID: $notice_id)");
                $_SESSION['flash_success'] = "নোটিশের বিবরণ সফলভাবে আপডেট করা হয়েছে।";
                
                header("Location: " . BASE_URL . "/admin/academic/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-bullhorn"></i> নোটিশ সম্পাদন করুন (Edit Notice)</span>
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
                <label for="title_bn">নোটিশের শিরোনাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="title_bn" name="title_bn" class="form-control" required value="<?php echo escape($notice['title_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="title_en">নোটিশের শিরোনাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="title_en" name="title_en" class="form-control" required value="<?php echo escape($notice['title_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="publish_date">প্রকাশের তারিখ <span style="color:var(--danger);">*</span></label>
                <input type="date" id="publish_date" name="publish_date" class="form-control" required value="<?php echo escape($notice['publish_date']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="is_published">প্রকাশনা অবস্থা (Status) <span style="color:var(--danger);">*</span></label>
                <select id="is_published" name="is_published" class="form-control" required>
                    <option value="1" <?php echo (int)$notice['is_published'] === 1 ? 'selected' : ''; ?>>সরাসরি প্রকাশ করুন (Published)</option>
                    <option value="0" <?php echo (int)$notice['is_published'] === 0 ? 'selected' : ''; ?>>খসড়া হিসেবে সংরক্ষণ করুন (Draft)</option>
                </select>
            </div>

            <div class="admin-form-group form-group-full">
                <label for="content_bn">বিস্তারিত বিবরণ (বাংলা)</label>
                <textarea id="content_bn" name="content_bn" class="form-control"><?php echo escape($notice['content_bn']); ?></textarea>
            </div>
            
            <div class="admin-form-group form-group-full">
                <label for="content_en">বিস্তারিত বিবরণ (ইংরেজি)</label>
                <textarea id="content_en" name="content_en" class="form-control"><?php echo escape($notice['content_en']); ?></textarea>
            </div>

            <div class="admin-form-group form-group-full" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                <?php if ($notice['attachment']): ?>
                    <div>
                        <p style="font-size:12px; font-weight:bold; margin-bottom:5px;">সংযুক্ত ফাইল:</p>
                        <a href="<?php echo UPLOAD_URL . '/' . escape($notice['attachment']); ?>" target="_blank" class="badge badge-success"><i class="fa fa-file-pdf"></i> ফাইল দেখুন</a>
                    </div>
                <?php endif; ?>
                <div style="flex:1;">
                    <label for="attachment">নতুন ফাইল সংযুক্ত করুন (সর্বোচ্চ ১০ মেগাবাইট, PDF/JPG/PNG - পরিবর্তন করতে চাইলে)</label>
                    <input type="file" id="attachment" name="attachment" class="form-control" accept="image/png, image/jpeg, application/pdf">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-admin btn-secondary">বাতিল করুন</a>
            <button type="submit" name="edit_notice" class="btn-admin btn-primary"><i class="fa fa-save"></i> আপডেট সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
