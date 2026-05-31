<?php
/**
 * Admin Edit Teacher MPO Details
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;
$teacher_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($teacher_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/mpo/index.php");
    exit;
}

// Fetch teacher profile
$teacher = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `teachers` WHERE `id` = ? AND `is_teacher` = 1 LIMIT 1");
    $stmt->execute([$teacher_id]);
    $teacher = $stmt->fetch();
} catch (PDOException $e) {}

if (!$teacher) {
    $_SESSION['flash_error'] = "শিক্ষক পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/mpo/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_mpo'])) {
    $mpo_index = sanitize_input($_POST['mpo_index'] ?? '');
    $mpo_scale = sanitize_input($_POST['mpo_scale'] ?? '');
    $mpo_date = sanitize_input($_POST['mpo_date'] ?? '');

    try {
        $db_mpo_date = !empty($mpo_date) ? $mpo_date : null;
        $db_mpo_index = !empty($mpo_index) ? $mpo_index : null;
        $db_mpo_scale = !empty($mpo_scale) ? $mpo_scale : null;

        $update_sql = "
            UPDATE `teachers` 
            SET `mpo_index` = ?, `mpo_scale` = ?, `mpo_date` = ? 
            WHERE `id` = ?
        ";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$db_mpo_index, $db_mpo_scale, $db_mpo_date, $teacher_id]);

        log_activity($pdo, "Edit Teacher MPO Info", "Updated MPO info for teacher: '{$teacher['name_en']}' (ID: $teacher_id)");
        $_SESSION['flash_success'] = "শিক্ষকের এমপিও তথ্য সফলভাবে আপডেট করা হয়েছে।";
        
        header("Location: " . BASE_URL . "/admin/mpo/index.php");
        exit;
    } catch (PDOException $e) {
        $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-file-invoice-dollar"></i> শিক্ষকের এমপিও তথ্য সংশোধন</span>
    <a href="index.php" class="btn-admin btn-secondary"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
        <h3 style="font-size: 16px; color: var(--primary-dark);"><?php echo escape($teacher['name_bn']); ?> (<?php echo escape($teacher['name_en']); ?>)</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-top:4px;">পদবী: <?php echo escape($teacher['designation_bn']); ?> | বিভাগ: <?php echo escape($teacher['department'] ?: 'General'); ?></p>
    </div>

    <form method="POST">
    <?php echo csrf_input(); ?>
        <div class="form-grid">
            <div class="admin-form-group">
                <label for="mpo_index">এমপিও ইনডেক্স নম্বর</label>
                <input type="text" id="mpo_index" name="mpo_index" class="form-control" value="<?php echo escape($teacher['mpo_index']); ?>" placeholder="যেমন: T-12345">
            </div>
            
            <div class="admin-form-group">
                <label for="mpo_scale">সরকারি বেতন স্কেল / গ্রেড</label>
                <input type="text" id="mpo_scale" name="mpo_scale" class="form-control" value="<?php echo escape($teacher['mpo_scale']); ?>" placeholder="যেমন: Grade 9 (Scale: 22000-53060)">
            </div>
            
            <div class="admin-form-group">
                <label for="mpo_date">ইনডেক্স প্রাপ্তির তারিখ</label>
                <input type="date" id="mpo_date" name="mpo_date" class="form-control" value="<?php echo escape($teacher['mpo_date']); ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-admin btn-secondary">বাতিল করুন</a>
            <button type="submit" name="edit_mpo" class="btn-admin btn-primary"><i class="fa fa-save"></i> এমপিও বিবরণ সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
