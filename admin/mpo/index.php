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
        
        header("Location: " . BASE_URL . "/admin/mpo");
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

<div class="admin-card" style="padding: 0; overflow: visible;">
    <!-- Tab Navigation -->
    <div style="background-color: var(--bg-sidebar); border-radius: var(--radius) var(--radius) 0 0; display: flex; overflow-x: auto; border-bottom: 3px solid var(--accent); padding: 0 10px;">
        <button class="tab-btn active" onclick="switchTab(event, 'school-mpo-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid var(--accent); transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-building"></i> প্রতিষ্ঠান এমপিও (School MPO)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'teacher-mpo-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-users-line"></i> শিক্ষক এমপিও সূচী (Teachers MPO)
        </button>
    </div>

    <!-- Tab Contents -->
    <div style="padding: 25px;">
        <!-- Tab 1: School MPO -->
        <div id="school-mpo-tab" class="tab-content active-content">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-building" style="color:var(--accent);"></i> প্রতিষ্ঠানের এমপিও ও জাতীয়করণ প্রোফাইল</h3>
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

        <!-- Tab 2: Teacher-wise MPO -->
        <div id="teacher-mpo-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-users-line" style="color:var(--accent);"></i> শিক্ষক ভিত্তিক এমপিও কোড ও ইনডেক্স সমূহ</h3>
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
                                        <a href="<?php echo BASE_URL; ?>/admin/mpo/edit_teacher_mpo?id=<?php echo $t['id']; ?>" class="btn-action edit" title="এমপিও পরিবর্তন"><i class="fa fa-edit"></i></a>
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
