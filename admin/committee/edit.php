<?php
/**
 * Admin Edit Committee Member Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/committee/index.php");
    exit;
}

// Fetch member record
$member = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `committee_members` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
} catch (PDOException $e) {}

if (!$member) {
    $_SESSION['flash_error'] = "সদস্যের তথ্য পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/committee/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $designation_bn = sanitize_input($_POST['designation_bn'] ?? '');
    $designation_en = sanitize_input($_POST['designation_en'] ?? '');
    $profession_bn = sanitize_input($_POST['profession_bn'] ?? '');
    $profession_en = sanitize_input($_POST['profession_en'] ?? '');
    $contact = sanitize_input($_POST['contact'] ?? '');
    $session_start = sanitize_input($_POST['session_start'] ?? '');
    $session_end = sanitize_input($_POST['session_end'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    // Validation
    if (empty($name_bn) || empty($name_en) || empty($designation_bn) || empty($designation_en) || empty($session_start) || empty($session_end)) {
        $error = "প্রয়োজনীয় ক্ষেত্রসমূহ (নাম, পদবী, মেয়াদকাল) পূরণ করা আবশ্যক।";
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
                $update_sql = "
                    UPDATE `committee_members` 
                    SET `name_bn` = ?, `name_en` = ?, `designation_bn` = ?, `designation_en` = ?, `profession_bn` = ?, `profession_en` = ?, `contact` = ?, `photo` = ?, `session_start` = ?, `session_end` = ?, `sort_order` = ? 
                    WHERE `id` = ?
                ";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute([
                    $name_bn,
                    $name_en,
                    $designation_bn,
                    $designation_en,
                    $profession_bn,
                    $profession_en,
                    $contact,
                    $photo_path,
                    $session_start,
                    $session_end,
                    $sort_order,
                    $member_id
                ]);

                log_activity($pdo, "Edit Committee Member", "Updated committee member: '$name_en' (ID: $member_id)");
                $_SESSION['flash_success'] = "সদস্যের তথ্য সফলভাবে আপডেট করা হয়েছে।";
                
                header("Location: " . BASE_URL . "/admin/committee/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-users-gear"></i> কমিটির সদস্যের তথ্য সম্পাদন করুন</span>
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
                <label for="name_bn">সদস্যের নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required value="<?php echo escape($member['name_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">সদস্যের নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required value="<?php echo escape($member['name_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="designation_bn">কমিটিতে পদবী (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_bn" name="designation_bn" class="form-control" required value="<?php echo escape($member['designation_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="designation_en">কমিটিতে পদবী (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_en" name="designation_en" class="form-control" required value="<?php echo escape($member['designation_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="profession_bn">পেশা (বাংলা)</label>
                <input type="text" id="profession_bn" name="profession_bn" class="form-control" value="<?php echo escape($member['profession_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="profession_en">পেশা (ইংরেজি)</label>
                <input type="text" id="profession_en" name="profession_en" class="form-control" value="<?php echo escape($member['profession_en']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="contact">মোবাইল / যোগাযোগের নম্বর</label>
                <input type="text" id="contact" name="contact" class="form-control" value="<?php echo escape($member['contact']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="sort_order">সর্টিং ক্রম নম্বর (Sort Order)</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo escape($member['sort_order']); ?>" min="0">
            </div>

            <div class="admin-form-group">
                <label for="session_start">মেয়াদকাল আরম্ভ বছর (Session Start) <span style="color:var(--danger);">*</span></label>
                <input type="number" id="session_start" name="session_start" class="form-control" required value="<?php echo escape($member['session_start']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="session_end">মেয়াদকাল সমাপ্তি বছর (Session End) <span style="color:var(--danger);">*</span></label>
                <input type="number" id="session_end" name="session_end" class="form-control" required value="<?php echo escape($member['session_end']); ?>">
            </div>

            <div class="admin-form-group form-group-full" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                <?php if (!empty($member['photo']) && file_exists(UPLOAD_DIR . '/' . $member['photo'])): ?>
                    <div>
                        <p style="font-size:12px; font-weight:bold; margin-bottom:5px;">বর্তমান ছবি:</p>
                        <img src="<?php echo UPLOAD_URL . '/' . escape($member['photo']); ?>" alt="Committee Member" style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                    </div>
                <?php endif; ?>
                <div style="flex:1;">
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

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
