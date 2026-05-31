<?php
/**
 * Admin Edit Teacher/Staff Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/teachers/index.php");
    exit;
}

// Fetch member profile
$member = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `teachers` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
} catch (PDOException $e) {}

if (!$member) {
    $_SESSION['flash_error'] = "রেকর্ড পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/teachers/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $designation_bn = sanitize_input($_POST['designation_bn'] ?? '');
    $designation_en = sanitize_input($_POST['designation_en'] ?? '');
    $subject_bn = sanitize_input($_POST['subject_bn'] ?? '');
    $subject_en = sanitize_input($_POST['subject_en'] ?? '');
    $is_teacher = isset($_POST['is_teacher']) ? (int)$_POST['is_teacher'] : 1;
    $department = sanitize_input($_POST['department'] ?? '');
    $staff_type = sanitize_input($_POST['staff_type'] ?? '');
    
    // MPO parameters
    $mpo_index = sanitize_input($_POST['mpo_index'] ?? '');
    $mpo_scale = sanitize_input($_POST['mpo_scale'] ?? '');
    $mpo_date = sanitize_input($_POST['mpo_date'] ?? '');
    
    $nid = sanitize_input($_POST['nid'] ?? '');
    $qualification_bn = sanitize_input($_POST['qualification_bn'] ?? '');
    $qualification_en = sanitize_input($_POST['qualification_en'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $joining_date = sanitize_input($_POST['joining_date'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'Active');

    // Validation
    if (empty($name_bn) || empty($name_en) || empty($designation_bn) || empty($designation_en) || empty($nid) || empty($joining_date)) {
        $error = "প্রয়োজনীয় ক্ষেত্রসমূহ (নাম, পদবী, এনআইডি এবং যোগদানের তারিখ) পূরণ করা আবশ্যক।";
    } else {
        try {
            // Photo upload handling
            $photo_path = $member['photo']; // Keep original by default
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $uploaded = upload_file($_FILES['photo'], 'photos', ['jpg', 'jpeg', 'png'], 2097152); // Max 2MB
                    
                    // Delete old photo if exists
                    if (!empty($member['photo']) && file_exists(UPLOAD_DIR . '/' . $member['photo'])) {
                        unlink(UPLOAD_DIR . '/' . $member['photo']);
                    }
                    
                    $photo_path = $uploaded;
                } catch (Exception $e) {
                    $error = "ছবি আপলোড ত্রুটি: " . $e->getMessage();
                }
            }

            if (!$error) {
                // Adjust empty date fields for SQL
                $db_mpo_date = !empty($mpo_date) ? $mpo_date : null;
                $db_mpo_index = !empty($mpo_index) ? $mpo_index : null;
                $db_mpo_scale = !empty($mpo_scale) ? $mpo_scale : null;

                $update_sql = "
                    UPDATE `teachers` 
                    SET `name_bn` = ?, `name_en` = ?, `designation_bn` = ?, `designation_en` = ?, `subject_bn` = ?, `subject_en` = ?, `mpo_index` = ?, `mpo_scale` = ?, `mpo_date` = ?, `nid` = ?, `qualification_bn` = ?, `qualification_en` = ?, `photo` = ?, `joining_date` = ?, `is_teacher` = ?, `staff_type` = ?, `department` = ?, `phone` = ?, `email` = ?, `status` = ? 
                    WHERE `id` = ?
                ";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute([
                    $name_bn,
                    $name_en,
                    $designation_bn,
                    $designation_en,
                    $subject_bn,
                    $subject_en,
                    $db_mpo_index,
                    $db_mpo_scale,
                    $db_mpo_date,
                    $nid,
                    $qualification_bn,
                    $qualification_en,
                    $photo_path,
                    $joining_date,
                    $is_teacher,
                    $is_teacher === 0 ? $staff_type : null,
                    $is_teacher === 1 ? $department : null,
                    $phone,
                    $email,
                    $status,
                    $member_id
                ]);

                log_activity($pdo, "Edit Teacher/Staff", "Updated profile: '$name_en' (ID: $member_id)");
                $_SESSION['flash_success'] = "প্রোফাইল সফলভাবে আপডেট করা হয়েছে।";
                
                header("Location: " . BASE_URL . "/admin/teachers/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-chalkboard-user"></i> শিক্ষক/কর্মচারীর তথ্য সম্পাদন (Edit Teacher & Staff)</span>
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
                <label for="is_teacher">শ্রেণীবিভাগ (Type) <span style="color:var(--danger);">*</span></label>
                <select id="is_teacher" name="is_teacher" class="form-control" required>
                    <option value="1" <?php echo (int)$member['is_teacher'] === 1 ? 'selected' : ''; ?>>শিক্ষক (Teacher)</option>
                    <option value="0" <?php echo (int)$member['is_teacher'] === 0 ? 'selected' : ''; ?>>কর্মকর্তা / কর্মচারী (Staff)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="nid">জাতীয় পরিচয়পত্র নম্বর (NID) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="nid" name="nid" class="form-control" required value="<?php echo escape($member['nid']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="name_bn">নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required value="<?php echo escape($member['name_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required value="<?php echo escape($member['name_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="designation_bn">পদবী (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_bn" name="designation_bn" class="form-control" required value="<?php echo escape($member['designation_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="designation_en">পদবী (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_en" name="designation_en" class="form-control" required value="<?php echo escape($member['designation_en']); ?>">
            </div>

            <!-- Toggle based on type (Teacher fields) -->
            <div class="admin-form-group" id="dept-group" style="display: <?php echo (int)$member['is_teacher'] === 1 ? 'flex' : 'none'; ?>;">
                <label for="department">বিভাগ (Department)</label>
                <select id="department" name="department" class="form-control">
                    <option value="General" <?php echo $member['department'] === 'General' ? 'selected' : ''; ?>>General (সাধারণ)</option>
                    <option value="Science" <?php echo $member['department'] === 'Science' ? 'selected' : ''; ?>>Science (বিজ্ঞান)</option>
                    <option value="Humanities" <?php echo $member['department'] === 'Humanities' ? 'selected' : ''; ?>>Humanities (মানবিক)</option>
                    <option value="Commerce" <?php echo $member['department'] === 'Commerce' ? 'selected' : ''; ?>>Commerce (ব্যবসায় শিক্ষা)</option>
                </select>
            </div>

            <!-- Toggle based on type (Staff fields) -->
            <div class="admin-form-group" id="staff-group" style="display: <?php echo (int)$member['is_teacher'] === 0 ? 'flex' : 'none'; ?>;">
                <label for="staff_type">কর্মচারীর ধরন (Staff Category)</label>
                <select id="staff_type" name="staff_type" class="form-control">
                    <option value="3rd class" <?php echo $member['staff_type'] === '3rd class' ? 'selected' : ''; ?>>৩য় শ্রেণী (যেমন: হিসাবরক্ষক, কম্পিউটার অপারেটর)</option>
                    <option value="4th class" <?php echo $member['staff_type'] === '4th class' ? 'selected' : ''; ?>>৪র্থ শ্রেণী (যেমন: এমএলএসএস, পিয়ন, নৈশপ্রহরী)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="subject_bn">মূল পাঠদানের বিষয় (বাংলা)</label>
                <input type="text" id="subject_bn" name="subject_bn" class="form-control" value="<?php echo escape($member['subject_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="subject_en">মূল পাঠদানের বিষয় (ইংরেজি)</label>
                <input type="text" id="subject_en" name="subject_en" class="form-control" value="<?php echo escape($member['subject_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="phone">মোবাইল নম্বর</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo escape($member['phone']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="email">ইমেইল ঠিকানা</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo escape($member['email']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="joining_date">যোগদানের তারিখ <span style="color:var(--danger);">*</span></label>
                <input type="date" id="joining_date" name="joining_date" class="form-control" required value="<?php echo escape($member['joining_date']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="status">স্ট্যাটাস (Status)</label>
                <select id="status" name="status" class="form-control">
                    <option value="Active" <?php echo $member['status'] === 'Active' ? 'selected' : ''; ?>>Active (সক্রিয়)</option>
                    <option value="Retired" <?php echo $member['status'] === 'Retired' ? 'selected' : ''; ?>>Retired (অবসরপ্রাপ্ত)</option>
                    <option value="Suspended" <?php echo $member['status'] === 'Suspended' ? 'selected' : ''; ?>>Suspended (অব্যাহতিপ্রাপ্ত)</option>
                </select>
            </div>

            <!-- MPO Fields Box -->
            <div class="form-group-full" style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 20px; border-radius: 8px; margin: 10px 0;">
                <h3 style="font-size:15px; color:var(--primary); margin-bottom:15px;"><i class="fa fa-file-invoice-dollar"></i> সরকারি এমপিও সংক্রান্ত তথ্যাদি (ঐচ্ছিক)</h3>
                <div class="form-grid" style="margin-bottom:0;">
                    <div class="admin-form-group">
                        <label for="mpo_index">এমপিও ইনডেক্স নম্বর</label>
                        <input type="text" id="mpo_index" name="mpo_index" class="form-control" value="<?php echo escape($member['mpo_index']); ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="mpo_scale">বেতন গ্রেড / স্কেল</label>
                        <input type="text" id="mpo_scale" name="mpo_scale" class="form-control" value="<?php echo escape($member['mpo_scale']); ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="mpo_date">এমপিও প্রাপ্তির তারিখ</label>
                        <input type="date" id="mpo_date" name="mpo_date" class="form-control" value="<?php echo escape($member['mpo_date']); ?>">
                    </div>
                </div>
            </div>

            <div class="admin-form-group form-group-full" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                <?php if (!empty($member['photo']) && file_exists(UPLOAD_DIR . '/' . $member['photo'])): ?>
                    <div>
                        <p style="font-size:12px; font-weight:bold; margin-bottom:5px;">বর্তমান ছবি:</p>
                        <img src="<?php echo UPLOAD_URL . '/' . escape($member['photo']); ?>" alt="Teacher" style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                    </div>
                <?php endif; ?>
                <div style="flex: 1;">
                    <label for="photo">ছবি আপলোড (অনূর্ধ্ব ২ মেগাবাইট, JPG/PNG - পরিবর্তন করতে চাইলে)</label>
                    <input type="file" id="photo" name="photo" class="form-control" accept="image/png, image/jpeg">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-admin btn-secondary">বাতিল করুন</a>
            <button type="submit" name="edit_member" class="btn-admin btn-primary"><i class="fa fa-save"></i> আপডেট সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const isTeacherSelect = document.getElementById('is_teacher');
    const deptGroup = document.getElementById('dept-group');
    const staffGroup = document.getElementById('staff-group');
    
    isTeacherSelect.addEventListener('change', function() {
        if (this.value === '1') {
            deptGroup.style.display = 'flex';
            staffGroup.style.display = 'none';
        } else {
            deptGroup.style.display = 'none';
            staffGroup.style.display = 'flex';
        }
    });
});
</script>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
