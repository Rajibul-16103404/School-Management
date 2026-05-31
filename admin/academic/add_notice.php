<?php
/**
 * Admin Add Notice Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice'])) {
    $title_bn = sanitize_input($_POST['title_bn'] ?? '');
    $title_en = sanitize_input($_POST['title_en'] ?? '');
    $content_bn = sanitize_input($_POST['content_bn'] ?? '');
    $content_en = sanitize_input($_POST['content_en'] ?? '');
    $publish_date = sanitize_input($_POST['publish_date'] ?? date('Y-m-d'));
    $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1;

    if (empty($title_bn) || empty($title_en) || empty($publish_date)) {
        $error = "নোটিশের শিরোনাম (বাংলা ও ইংরেজি) এবং প্রকাশের তারিখ প্রদান করা আবশ্যক।";
    } else {
        try {
            // Attachment upload handling
            $attachment_path = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $attachment_path = upload_file($_FILES['attachment'], 'notices', ['jpg', 'jpeg', 'png', 'pdf'], 10485760); // Max 10MB
                } catch (Exception $e) {
                    $error = "সংযুক্তি ফাইল আপলোড ত্রুটি: " . $e->getMessage();
                }
            }

            if (!$error) {
                $insert_sql = "
                    INSERT INTO `notices` (`title_bn`, `title_en`, `content_bn`, `content_en`, `publish_date`, `is_published`, `attachment`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $pdo->prepare($insert_sql);
                $stmt->execute([
                    $title_bn,
                    $title_en,
                    $content_bn,
                    $content_en,
                    $publish_date,
                    $is_published,
                    $attachment_path
                ]);

                log_activity($pdo, "Add Notice", "Published notice: '$title_en'");
                $_SESSION['flash_success'] = "নোটিশ সফলভাবে সংরক্ষণ করা হয়েছে।";
                
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
    <span><i class="fa-solid fa-bullhorn"></i> নতুন নোটিশ তৈরি করুন (Create Notice)</span>
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
                <label for="title_bn">নোটিশের শিরোনাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="title_bn" name="title_bn" class="form-control" required placeholder="যেমন: অর্ধবার্ষিক পরীক্ষার রুটিন ও নোটিশ">
            </div>
            
            <div class="admin-form-group">
                <label for="title_en">নোটিশের শিরোনাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="title_en" name="title_en" class="form-control" required placeholder="যেমন: Half Yearly Exam Notice and Routine">
            </div>

            <div class="admin-form-group">
                <label for="publish_date">প্রকাশের তারিখ <span style="color:var(--danger);">*</span></label>
                <input type="date" id="publish_date" name="publish_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="admin-form-group">
                <label for="is_published">প্রকাশনা অবস্থা (Status) <span style="color:var(--danger);">*</span></label>
                <select id="is_published" name="is_published" class="form-control" required>
                    <option value="1">সরাসরি প্রকাশ করুন (Published)</option>
                    <option value="0">খসড়া হিসেবে সংরক্ষণ করুন (Draft)</option>
                </select>
            </div>

            <div class="admin-form-group form-group-full">
                <label for="content_bn">বিস্তারিত বিবরণ (বাংলা)</label>
                <textarea id="content_bn" name="content_bn" class="form-control" placeholder="নোটিশের বিস্তারিত বিবরণ বাংলায় লিখুন..."></textarea>
            </div>
            
            <div class="admin-form-group form-group-full">
                <label for="content_en">বিস্তারিত বিবরণ (ইংরেজি)</label>
                <textarea id="content_en" name="content_en" class="form-control" placeholder="Enter notice details in English..."></textarea>
            </div>

            <div class="admin-form-group form-group-full">
                <label for="attachment">সংযুক্ত ফাইল (ঐচ্ছিক, সর্বোচ্চ ১০ মেগাবাইট, PDF/JPG/PNG)</label>
                <input type="file" id="attachment" name="attachment" class="form-control" accept="image/png, image/jpeg, application/pdf">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="add_notice" class="btn-admin btn-primary"><i class="fa fa-save"></i> নোটিশ সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
