<?php
/**
 * Admin Edit Student Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/students/index.php");
    exit;
}

// Fetch student profile
$student = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `students` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
} catch (PDOException $e) {}

if (!$student) {
    $_SESSION['flash_error'] = "শিক্ষার্থীর প্রোফাইল পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/students/index.php");
    exit;
}

// Fetch classes for dropdown
$classes = [];
try {
    $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $class_id = (int)($_POST['class_id'] ?? 0);
    $section_id = (int)($_POST['section_id'] ?? 0);
    $roll = (int)($_POST['roll'] ?? 0);
    $gender = sanitize_input($_POST['gender'] ?? '');
    $dob = sanitize_input($_POST['dob'] ?? '');
    $guardian_bn = sanitize_input($_POST['guardian_bn'] ?? '');
    $guardian_en = sanitize_input($_POST['guardian_en'] ?? '');
    $mobile = sanitize_input($_POST['mobile'] ?? '');

    // Validation
    if (empty($name_bn) || empty($name_en) || $class_id <= 0 || $section_id <= 0 || $roll <= 0 || empty($gender) || empty($dob) || empty($guardian_bn) || empty($guardian_en) || empty($mobile)) {
        $error = "সবগুলো ঘর পূরণ করা আবশ্যক।";
    } else {
        try {
            // Check roll duplication excluding this student
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `students` WHERE `class_id` = ? AND `section_id` = ? AND `roll` = ? AND `id` != ?");
            $stmt->execute([$class_id, $section_id, $roll, $student_id]);
            $roll_dup = $stmt->fetchColumn();

            if ($roll_dup > 0) {
                $error = "দুঃখিত! এই শ্রেণির এই শাখায় রোল নম্বর <b>{$roll}</b> ইতিমধ্যেই নিবন্ধিত আছে।";
            } else {
                // Photo upload handling
                $photo_path = $student['photo']; // Keep original photo path by default
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $uploaded = upload_file($_FILES['photo'], 'photos', ['jpg', 'jpeg', 'png'], 2097152);
                        
                        // Delete old photo if it exists
                        if (!empty($student['photo']) && file_exists(UPLOAD_DIR . '/' . $student['photo'])) {
                            unlink(UPLOAD_DIR . '/' . $student['photo']);
                        }
                        
                        $photo_path = $uploaded;
                    } catch (Exception $e) {
                        $error = "ছবি আপলোড ত্রুটি: " . $e->getMessage();
                    }
                }

                if (!$error) {
                    // Update record
                    $update_sql = "
                        UPDATE `students` 
                        SET `name_bn` = ?, `name_en` = ?, `class_id` = ?, `section_id` = ?, `roll` = ?, `gender` = ?, `dob` = ?, `guardian_name_bn` = ?, `guardian_name_en` = ?, `mobile` = ?, `photo` = ? 
                        WHERE `id` = ?
                    ";
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute([
                        $name_bn,
                        $name_en,
                        $class_id,
                        $section_id,
                        $roll,
                        $gender,
                        $dob,
                        $guardian_bn,
                        $guardian_en,
                        $mobile,
                        $photo_path,
                        $student_id
                    ]);

                    log_activity($pdo, "Edit Student", "Updated student: '$name_en' (ID: $student_id, Roll: $roll)");
                    $_SESSION['flash_success'] = "শিক্ষার্থীর তথ্য সফলভাবে আপডেট করা হয়েছে।";
                    
                    header("Location: " . BASE_URL . "/admin/students/index.php");
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-graduation-cap"></i> শিক্ষার্থীর তথ্য সম্পাদন (Edit Student Profile)</span>
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
                <label for="name_bn">শিক্ষার্থীর নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required value="<?php echo escape($student['name_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">শিক্ষार्थियों নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required value="<?php echo escape($student['name_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="class_id">শ্রেণি <span style="color:var(--danger);">*</span></label>
                <select id="class_id" name="class_id" class="form-control" required>
                    <option value="">শ্রেণি নির্বাচন করুন</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo (int)$student['class_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo escape($c['name_bn']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="section_id">শাখা <span style="color:var(--danger);">*</span></label>
                <select id="section_id" name="section_id" class="form-control" required>
                    <option value="">শাখা নির্বাচন করুন</option>
                    <?php
                    // Pre-fill active class sections
                    try {
                        $secs = $pdo->prepare("SELECT * FROM `sections` WHERE `class_id` = ?");
                        $secs->execute([$student['class_id']]);
                        foreach ($secs->fetchAll() as $sc) {
                            $sel = (int)$student['section_id'] === (int)$sc['id'] ? 'selected' : '';
                            echo "<option value='{$sc['id']}' {$sel}>{$sc['name_bn']}</option>";
                        }
                    } catch (PDOException $e) {}
                    ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="roll">রোল নম্বর <span style="color:var(--danger);">*</span></label>
                <input type="number" id="roll" name="roll" class="form-control" required min="1" value="<?php echo escape($student['roll']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="gender">লিঙ্গ (Gender) <span style="color:var(--danger);">*</span></label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>ছাত্র (Male)</option>
                    <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>ছাত্রী (Female)</option>
                    <option value="Other" <?php echo $student['gender'] === 'Other' ? 'selected' : ''; ?>>অন্যান্য (Other)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="dob">জন্ম তারিখ <span style="color:var(--danger);">*</span></label>
                <input type="date" id="dob" name="dob" class="form-control" required value="<?php echo escape($student['dob']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="mobile">মোবাইল নম্বর <span style="color:var(--danger);">*</span></label>
                <input type="text" id="mobile" name="mobile" class="form-control" required value="<?php echo escape($student['mobile']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="guardian_bn">অভিভাবকের নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="guardian_bn" name="guardian_bn" class="form-control" required value="<?php echo escape($student['guardian_name_bn']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="guardian_en">অভিভাবকের নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="guardian_en" name="guardian_en" class="form-control" required value="<?php echo escape($student['guardian_name_en']); ?>">
            </div>

            <div class="admin-form-group form-group-full" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                <?php if (!empty($student['photo']) && file_exists(UPLOAD_DIR . '/' . $student['photo'])): ?>
                    <div>
                        <p style="font-size:12px; font-weight:bold; margin-bottom:5px;">বর্তমান ছবি:</p>
                        <img src="<?php echo UPLOAD_URL . '/' . escape($student['photo']); ?>" alt="Student" style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                    </div>
                <?php endif; ?>
                <div style="flex:1;">
                    <label for="photo">নতুন ছবি আপলোড (অনূর্ধ্ব ২ মেগাবাইট, JPG/PNG - পরিবর্তন করতে চাইলে)</label>
                    <input type="file" id="photo" name="photo" class="form-control" accept="image/png, image/jpeg">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-admin btn-secondary">বাতিল করুন</a>
            <button type="submit" name="edit_student" class="btn-admin btn-primary"><i class="fa fa-save"></i> আপডেট সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
