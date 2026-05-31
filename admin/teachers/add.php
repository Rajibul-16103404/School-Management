<?php
/**
 * Admin Add Teacher/Staff Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
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
            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $photo_path = upload_file($_FILES['photo'], 'photos', ['jpg', 'jpeg', 'png'], 2097152); // Max 2MB
                } catch (Exception $e) {
                    $error = "ছবি আপলোড ত্রুটি: " . $e->getMessage();
                }
            }

            if (!$error) {
                // Adjust empty date fields for SQL
                $db_mpo_date = !empty($mpo_date) ? $mpo_date : null;
                $db_mpo_index = !empty($mpo_index) ? $mpo_index : null;
                $db_mpo_scale = !empty($mpo_scale) ? $mpo_scale : null;

                $insert_sql = "
                    INSERT INTO `teachers` (`name_bn`, `name_en`, `designation_bn`, `designation_en`, `subject_bn`, `subject_en`, `mpo_index`, `mpo_scale`, `mpo_date`, `nid`, `qualification_bn`, `qualification_en`, `photo`, `joining_date`, `is_teacher`, `staff_type`, `department`, `phone`, `email`, `status`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $pdo->prepare($insert_sql);
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
                    $status
                ]);

                $type_label = $is_teacher === 1 ? 'Teacher' : 'Staff';
                log_activity($pdo, "Add Teacher/Staff", "Added $type_label: '$name_en' with designation: $designation_en");
                $_SESSION['flash_success'] = "শিক্ষক/স্টাফ প্রোফাইল সফলভাবে সংরক্ষণ করা হয়েছে।";
                
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
    <span><i class="fa-solid fa-chalkboard-user"></i> শিক্ষক/কর্মচারী যুক্ত করুন (Add Teacher & Staff)</span>
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
                    <option value="1">শিক্ষক (Teacher)</option>
                    <option value="0">কর্মকর্তা / কর্মচারী (Staff)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="nid">জাতীয় পরিচয়পত্র নম্বর (NID) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="nid" name="nid" class="form-control" required placeholder="এনআইডি নম্বর লিখুন">
            </div>

            <div class="admin-form-group">
                <label for="name_bn">নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required placeholder="যেমন: রফিকুল ইসলাম">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required placeholder="যেমন: Md. Rafiqul Islam">
            </div>

            <div class="admin-form-group">
                <label for="designation_bn">পদবী (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_bn" name="designation_bn" class="form-control" required placeholder="যেমন: সহকারী শিক্ষক / অফিস সহকারী">
            </div>
            
            <div class="admin-form-group">
                <label for="designation_en">পদবী (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_en" name="designation_en" class="form-control" required placeholder="যেমন: Assistant Teacher / Office Assistant">
            </div>

            <!-- Toggle based on type (Teacher fields) -->
            <div class="admin-form-group" id="dept-group">
                <label for="department">বিভাগ (Department)</label>
                <select id="department" name="department" class="form-control">
                    <option value="General">General (সাধারণ)</option>
                    <option value="Science">Science (বিজ্ঞান)</option>
                    <option value="Humanities">Humanities (মানবিক)</option>
                    <option value="Commerce">Commerce (ব্যবসায় শিক্ষা)</option>
                </select>
            </div>

            <!-- Toggle based on type (Staff fields) -->
            <div class="admin-form-group" id="staff-group" style="display: none;">
                <label for="staff_type">কর্মচারীর ধরন (Staff Category)</label>
                <select id="staff_type" name="staff_type" class="form-control">
                    <option value="3rd class">৩য় শ্রেণী (যেমন: হিসাবরক্ষক, কম্পিউটার অপারেটর)</option>
                    <option value="4th class">৪র্থ শ্রেণী (যেমন: এমএলএসএস, পিয়ন, নৈশপ্রহরী)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="subject_bn">মূল পাঠদানের বিষয় (বাংলা)</label>
                <input type="text" id="subject_bn" name="subject_bn" class="form-control" placeholder="যেমন: ইংরেজি / গণিত">
            </div>
            
            <div class="admin-form-group">
                <label for="subject_en">মূল পাঠদানের বিষয় (ইংরেজি)</label>
                <input type="text" id="subject_en" name="subject_en" class="form-control" placeholder="যেমন: English / Mathematics">
            </div>

            <div class="admin-form-group">
                <label for="phone">মোবাইল নম্বর</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="01xxxxxxxxx">
            </div>

            <div class="admin-form-group">
                <label for="email">ইমেইল ঠিকানা</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="teacher@email.com">
            </div>

            <div class="admin-form-group">
                <label for="joining_date">যোগদানের তারিখ <span style="color:var(--danger);">*</span></label>
                <input type="date" id="joining_date" name="joining_date" class="form-control" required>
            </div>

            <div class="admin-form-group">
                <label for="status">স্ট্যাটাস (Status)</label>
                <select id="status" name="status" class="form-control">
                    <option value="Active">Active (সক্রিয়)</option>
                    <option value="Retired">Retired (অবসরপ্রাপ্ত)</option>
                    <option value="Suspended">Suspended (অব্যাহতিপ্রাপ্ত)</option>
                </select>
            </div>

            <!-- MPO Fields Box -->
            <div class="form-group-full" style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 20px; border-radius: 8px; margin: 10px 0;">
                <h3 style="font-size:15px; color:var(--primary); margin-bottom:15px;"><i class="fa fa-file-invoice-dollar"></i> সরকারি এমপিও সংক্রান্ত তথ্যাদি (ঐচ্ছিক)</h3>
                <div class="form-grid" style="margin-bottom:0;">
                    <div class="admin-form-group">
                        <label for="mpo_index">এমপিও ইনডেক্স নম্বর</label>
                        <input type="text" id="mpo_index" name="mpo_index" class="form-control" placeholder="যেমন: M-123456">
                    </div>
                    <div class="admin-form-group">
                        <label for="mpo_scale">বেতন গ্রেড / স্কেল</label>
                        <input type="text" id="mpo_scale" name="mpo_scale" class="form-control" placeholder="যেমন: Grade 9 (Scale: 22000-53060)">
                    </div>
                    <div class="admin-form-group">
                        <label for="mpo_date">এমপিও প্রাপ্তির তারিখ</label>
                        <input type="date" id="mpo_date" name="mpo_date" class="form-control">
                    </div>
                </div>
            </div>

            <div class="admin-form-group form-group-full">
                <label for="photo">শিক্ষক/কর্মচারীর ছবি (অনূর্ধ্ব ২ মেগাবাইট, JPG/PNG)</label>
                <input type="file" id="photo" name="photo" class="form-control" accept="image/png, image/jpeg">
            </div>
        </div>

        <div class="form-actions">
            <button type="reset" class="btn-admin btn-secondary">পুনরায় শুরু করুন</button>
            <button type="submit" name="add_member" class="btn-admin btn-primary"><i class="fa fa-save"></i> সংরক্ষণ করুন</button>
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
