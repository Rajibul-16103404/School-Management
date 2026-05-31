<?php
/**
 * Admin Add Committee Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
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
            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $photo_path = upload_file($_FILES['photo'], 'photos', ['jpg', 'jpeg', 'png'], 10485760); // Max 10MB
                } catch (Exception $e) {
                    $error = "ছবি আপলোড ত্রুটি: " . $e->getMessage();
                }
            }

            if (!$error) {
                $insert_sql = "
                    INSERT INTO `committee_members` (`name_bn`, `name_en`, `designation_bn`, `designation_en`, `profession_bn`, `profession_en`, `contact`, `photo`, `session_start`, `session_end`, `sort_order`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $pdo->prepare($insert_sql);
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
                    $sort_order
                ]);

                log_activity($pdo, "Add Committee Member", "Added committee member: '$name_en' as $designation_en");
                $_SESSION['flash_success'] = "কমিটির সদস্য সফলভাবে যুক্ত করা হয়েছে।";
                
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
    <span><i class="fa-solid fa-users-gear"></i> কমিটির নতুন সদস্য যুক্ত করুন</span>
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
                <input type="text" id="name_bn" name="name_bn" class="form-control" required placeholder="যেমন: হাজী মোফাজ্জল হোসেন">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">সদস্যের নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required placeholder="যেমন: Haji Mofazzal Hossain">
            </div>

            <div class="admin-form-group">
                <label for="designation_bn">কমিটিতে পদবী (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_bn" name="designation_bn" class="form-control" required placeholder="যেমন: সভাপতি / সাধারণ সদস্য">
            </div>
            
            <div class="admin-form-group">
                <label for="designation_en">কমিটিতে পদবী (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="designation_en" name="designation_en" class="form-control" required placeholder="যেমন: President / General Member">
            </div>

            <div class="admin-form-group">
                <label for="profession_bn">পেশা (বাংলা)</label>
                <input type="text" id="profession_bn" name="profession_bn" class="form-control" placeholder="যেমন: ব্যবসায়ী">
            </div>
            
            <div class="admin-form-group">
                <label for="profession_en">পেশা (ইংরেজি)</label>
                <input type="text" id="profession_en" name="profession_en" class="form-control" placeholder="যেমন: Businessman">
            </div>

            <div class="admin-form-group">
                <label for="contact">মোবাইল / যোগাযোগের নম্বর</label>
                <input type="text" id="contact" name="contact" class="form-control" placeholder="01xxxxxxxxx">
            </div>

            <div class="admin-form-group">
                <label for="sort_order">সর্টিং ক্রম নম্বর (Sort Order)</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control" value="0" min="0">
            </div>

            <div class="admin-form-group">
                <label for="session_start">মেয়াদকাল আরম্ভ বছর (Session Start) <span style="color:var(--danger);">*</span></label>
                <input type="number" id="session_start" name="session_start" class="form-control" required value="<?php echo date('Y'); ?>" placeholder="যেমন: 2025">
            </div>
            
            <div class="admin-form-group">
                <label for="session_end">মেয়াদকাল সমাপ্তি বছর (Session End) <span style="color:var(--danger);">*</span></label>
                <input type="number" id="session_end" name="session_end" class="form-control" required value="<?php echo date('Y') + 2; ?>" placeholder="যেমন: 2027">
            </div>

            <div class="admin-form-group form-group-full">
                <label for="photo">সদস্যের ছবি (অনূর্ধ্ব ১০ মেগাবাইট, JPG/PNG)</label>
                <input type="file" id="photo" name="photo" class="form-control" accept="image/png, image/jpeg">
            </div>
        </div>

        <div class="form-actions">
            <button type="reset" class="btn-admin btn-secondary">পুনরায় শুরু করুন</button>
            <button type="submit" name="add_member" class="btn-admin btn-primary"><i class="fa fa-save"></i> সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
