<?php
/**
 * Admin MPO Management Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

// Fetch school MPO configuration
$school = null;
try {
    $stmt = $pdo->query("SELECT * FROM `schools` WHERE `id` = 1 LIMIT 1");
    $school = $stmt->fetch();
} catch (PDOException $e) {}

// Fetch teachers for individual MPO configurations
$teachers = [];
try {
    $teachers = $pdo->query("SELECT * FROM `teachers` WHERE `is_teacher` = 1 AND `status` = 'Active' ORDER BY `joining_date` ASC")->fetchAll();
} catch (PDOException $e) {}

// Update school-level MPO settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_school_mpo'])) {
    $mpo_status = sanitize_input($_POST['mpo_status'] ?? 'Non-MPO');
    $mpo_number = sanitize_input($_POST['mpo_number'] ?? '');
    $mpo_date = sanitize_input($_POST['mpo_date'] ?? '');
    $nationalization_status = sanitize_input($_POST['nationalization_status'] ?? '');
    $nationalization_date = sanitize_input($_POST['nationalization_date'] ?? '');

    try {
        $db_mpo_date = !empty($mpo_date) ? $mpo_date : null;
        $db_nationalization_date = !empty($nationalization_date) ? $nationalization_date : null;

        $update_sql = "
            UPDATE `schools` 
            SET `mpo_status` = ?, `mpo_number` = ?, `mpo_date` = ?, `nationalization_status` = ?, `nationalization_date` = ? 
            WHERE `id` = 1
        ";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$mpo_status, $mpo_number, $db_mpo_date, $nationalization_status, $db_nationalization_date]);

        log_activity($pdo, "Update School MPO Info", "Updated school-level MPO & nationalization status.");
        $_SESSION['flash_success'] = "প্রতিষ্ঠানের এমপিও ও জাতীয়করণ তথ্য সফলভাবে আপডেট করা হয়েছে।";
        
        header("Location: " . BASE_URL . "/admin/mpo/index.php");
        exit;
    } catch (PDOException $e) {
        $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-file-invoice-dollar"></i> এমপিও ও জাতীয়করণ ব্যবস্থাপনা (MPO & Nationalization)</span>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr; gap:25px; align-items: start;">
    <!-- 1. School-Level Settings -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title"><i class="fa fa-building" style="color:var(--accent);"></i> প্রতিষ্ঠানের এমপিও ও জাতীয়করণ প্রোফাইল</span>
        </div>
        
        <form method="POST">
            <div class="form-grid">
                <div class="admin-form-group">
                    <label for="mpo_status">এমপিও স্ট্যাটাস <span style="color:var(--danger);">*</span></label>
                    <select id="mpo_status" name="mpo_status" class="form-control" required>
                        <option value="Non-MPO" <?php echo $school['mpo_status'] === 'Non-MPO' ? 'selected' : ''; ?>>নন-এমপিও (Non-MPO)</option>
                        <option value="MPO" <?php echo $school['mpo_status'] === 'MPO' ? 'selected' : ''; ?>>এমপিওভুক্ত (MPO)</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label for="mpo_number">এমপিও কোড / নম্বর</label>
                    <input type="text" id="mpo_number" name="mpo_number" class="form-control" value="<?php echo escape($school['mpo_number']); ?>" placeholder="এমপিও নম্বর">
                </div>

                <div class="admin-form-group">
                    <label for="mpo_date">এমপিওভুক্তির তারিখ</label>
                    <input type="date" id="mpo_date" name="mpo_date" class="form-control" value="<?php echo escape($school['mpo_date']); ?>">
                </div>

                <div class="admin-form-group">
                    <label for="nationalization_status">জাতীয়করণ স্ট্যাটাস (যেমন: বেসরকারি / সরকারি)</label>
                    <input type="text" id="nationalization_status" name="nationalization_status" class="form-control" value="<?php echo escape($school['nationalization_status']); ?>" placeholder="যেমন: আংশিক সরকারি / জাতীয়করণকৃত">
                </div>

                <div class="admin-form-group">
                    <label for="nationalization_date">জাতীয়করণের তারিখ</label>
                    <input type="date" id="nationalization_date" name="nationalization_date" class="form-control" value="<?php echo escape($school['nationalization_date']); ?>">
                </div>
            </div>

            <div class="form-actions" style="margin-top:20px; padding-top:15px;">
                <button type="submit" name="update_school_mpo" class="btn-admin btn-primary"><i class="fa fa-save"></i> প্রতিষ্ঠান প্রোফাইল সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>

    <!-- 2. Teacher-wise Settings -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title"><i class="fa fa-users-line" style="color:var(--accent);"></i> শিক্ষক ভিত্তিক এমপিও কোড ও ইনডেক্স সমূহ</span>
        </div>
        
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>শিক্ষকের নাম</th>
                        <th>পদবী</th>
                        <th>এমপিও ইনডেক্স</th>
                        <th>বেতন গ্রেড / স্কেল</th>
                        <th>এমপিও ভুক্তির তারিখ</th>
                        <th class="actions-cell">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $t): ?>
                            <tr>
                                <td style="font-weight: bold; text-align: left;"><?php echo escape($t['name_bn']); ?></td>
                                <td><?php echo escape($t['designation_bn']); ?></td>
                                <td>
                                    <?php if (!empty($t['mpo_index'])): ?>
                                        <strong style="color: var(--primary);"><?php echo escape($t['mpo_index']); ?></strong>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size:12px;">নিবন্ধিত নয়</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo escape($t['mpo_scale'] ?: '-'); ?></td>
                                <td style="font-family: var(--font-en);"><?php echo !empty($t['mpo_date']) ? format_date($t['mpo_date']) : '-'; ?></td>
                                <td class="actions-cell">
                                    <a href="edit_teacher_mpo.php?id=<?php echo $t['id']; ?>" class="btn-action edit" title="এমপিও পরিবর্তন"><i class="fa fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="color: var(--text-muted);">কোনো শিক্ষক পাওয়া যায়নি।</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
