<?php
/**
 * Admin Add Student Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

// Fetch classes for dropdown selection
$classes = [];
try {
    $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
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

    // Server-side validation
    if (empty($name_bn) || empty($name_en) || $class_id <= 0 || $section_id <= 0 || $roll <= 0 || empty($gender) || empty($dob) || empty($guardian_bn) || empty($guardian_en) || empty($mobile)) {
        $error = "সবগুলো ঘর পূরণ করা আবশ্যিক।";
    } elseif (!in_array($gender, ['Male', 'Female', 'Other'])) {
        $error = "ভুল জেন্ডার নির্বাচন করা হয়েছে।";
    } else {
        try {
            // Check roll duplication in the same class/section
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `students` WHERE `class_id` = ? AND `section_id` = ? AND `roll` = ?");
            $stmt->execute([$class_id, $section_id, $roll]);
            $roll_dup = $stmt->fetchColumn();

            if ($roll_dup > 0) {
                $error = "দুঃখিত! এই শ্রেণির এই শাখায় রোল নম্বর <b>{$roll}</b> ইতিমধ্যেই নিবন্ধিত আছে।";
            } else {
                // Photo upload handling
                $photo_path = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $photo_path = upload_file($_FILES['photo'], 'photos', ['jpg', 'jpeg', 'png'], 2097152); // Limit photos to 2MB
                    } catch (Exception $e) {
                        $error = "ছবি আপলোড ত্রুটি: " . $e->getMessage();
                    }
                }

                if (!$error) {
                    // Insert record
                    $insert_sql = "
                        INSERT INTO `students` (`name_bn`, `name_en`, `class_id`, `section_id`, `roll`, `gender`, `dob`, `guardian_name_bn`, `guardian_name_en`, `mobile`, `photo`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ";
                    $stmt = $pdo->prepare($insert_sql);
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
                        $photo_path
                    ]);

                    log_activity($pdo, "Add Student", "Added student: '$name_en' to class ID: $class_id (Roll: $roll)");
                    $_SESSION['flash_success'] = "শিক্ষার্থী সফলভাবে যুক্ত করা হয়েছে।";
                    
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
    <span><i class="fa-solid fa-graduation-cap"></i> শিক্ষার্থী ভর্তি করুন (Add Student Profile)</span>
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
                <input type="text" id="name_bn" name="name_bn" class="form-control" required placeholder="যেমন: হাসান আলী">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">শিক্ষার্থীর নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required placeholder="যেমন: Hasan Ali">
            </div>

            <div class="admin-form-group">
                <label for="class_id">শ্রেণি নির্বাচন করুন <span style="color:var(--danger);">*</span></label>
                <select id="class_id" name="class_id" class="form-control" required>
                    <option value="">শ্রেণি নির্বাচন করুন</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo escape($c['name_bn']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="section_id">শাখা নির্বাচন করুন <span style="color:var(--danger);">*</span></label>
                <select id="section_id" name="section_id" class="form-control" required>
                    <option value="">প্রথমে শ্রেণি নির্বাচন করুন</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="roll">রোল নম্বর <span style="color:var(--danger);">*</span></label>
                <input type="number" id="roll" name="roll" class="form-control" required min="1" placeholder="রোল নম্বর">
            </div>

            <div class="admin-form-group">
                <label for="gender">লিঙ্গ (Gender) <span style="color:var(--danger);">*</span></label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="">লিঙ্গ নির্বাচন করুন</option>
                    <option value="Male">ছাত্র (Male)</option>
                    <option value="Female">ছাত্রী (Female)</option>
                    <option value="Other">অন্যান্য (Other)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="dob">জন্ম তারিখ <span style="color:var(--danger);">*</span></label>
                <input type="date" id="dob" name="dob" class="form-control" required>
            </div>

            <div class="admin-form-group">
                <label for="mobile">মোবাইল নম্বর (অভিভাবকের) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="mobile" name="mobile" class="form-control" required placeholder="01xxxxxxxxx">
            </div>

            <div class="admin-form-group">
                <label for="guardian_bn">অভিভাবকের নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="guardian_bn" name="guardian_bn" class="form-control" required placeholder="পিতা / মাতার নাম">
            </div>

            <div class="admin-form-group">
                <label for="guardian_en">অভিভাবকের নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="guardian_en" name="guardian_en" class="form-control" required placeholder="Guardian's Name">
            </div>

            <div class="admin-form-group form-group-full">
                <label for="photo">শিক্ষার্থীর ছবি (অনূর্ধ্ব ২ মেগাবাইট, JPG/PNG)</label>
                <input type="file" id="photo" name="photo" class="form-control" accept="image/png, image/jpeg">
            </div>
        </div>

        <div class="form-actions">
            <button type="reset" class="btn-admin btn-secondary">পুনরায় শুরু করুন</button>
            <button type="submit" name="add_student" class="btn-admin btn-primary"><i class="fa fa-save"></i> সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
