<?php
/**
 * Admin Gallery Management Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Restrict to superadmin, headteacher or staff
check_role(['superadmin', 'headteacher', 'staff']);

$error = null;

// Fetch school settings for gallery
$school = null;
try {
    $stmt = $pdo->query("SELECT * FROM `schools` WHERE `id` = 1 LIMIT 1");
    $school = $stmt->fetch();
} catch (PDOException $e) {}

// Add gallery photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gallery_photo'])) {
    if (!isset($_FILES['gallery_file']) || $_FILES['gallery_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "অনুগ্রহ করে একটি ছবি নির্বাচন করুন।";
    } else {
        try {
            // Upload limit: 10MB (10485760 bytes)
            $photo_path = upload_file($_FILES['gallery_file'], 'photos', ['jpg', 'jpeg', 'png'], 10485760);
            
            // Get current gallery photos
            $current_gallery = [];
            if (!empty($school['gallery'])) {
                $current_gallery = json_decode($school['gallery'], true) ?: [];
            }
            $current_gallery[] = $photo_path;
            
            $update_stmt = $pdo->prepare("UPDATE `schools` SET `gallery` = ? WHERE `id` = 1");
            $update_stmt->execute([json_encode($current_gallery)]);
            
            log_activity($pdo, "Add Gallery Photo", "Uploaded a new photo to school gallery.");
            $_SESSION['flash_success'] = "গ্যালারিতে নতুন ছবি সফলভাবে যুক্ত করা হয়েছে।";
            
            header("Location: " . BASE_URL . "/admin/gallery");
            exit;
        } catch (Exception $e) {
            $error = "ছবি আপলোড ত্রুটি: " . $e->getMessage();
        }
    }
}

// Delete gallery photo
if (isset($_GET['delete_photo'])) {
    $photo_to_delete = $_GET['delete_photo'];
    $photo_to_delete = basename($photo_to_delete);
    $full_photo_name = 'photos/' . $photo_to_delete;
    
    try {
        $current_gallery = [];
        if (!empty($school['gallery'])) {
            $current_gallery = json_decode($school['gallery'], true) ?: [];
        }
        
        if (in_array($full_photo_name, $current_gallery)) {
            $current_gallery = array_values(array_diff($current_gallery, [$full_photo_name]));
            
            $update_stmt = $pdo->prepare("UPDATE `schools` SET `gallery` = ? WHERE `id` = 1");
            $update_stmt->execute([json_encode($current_gallery)]);
            
            $physical_file = UPLOAD_DIR . '/' . $full_photo_name;
            if (file_exists($physical_file)) {
                unlink($physical_file);
            }
            
            log_activity($pdo, "Delete Gallery Photo", "Deleted a photo from school gallery.");
            $_SESSION['flash_success'] = "ছবিটি গ্যালারি থেকে মুছে ফেলা হয়েছে।";
        }
    } catch (Exception $e) {
        $error = "ছবি মুছতে ত্রুটি: " . $e->getMessage();
    }
    header("Location: " . BASE_URL . "/admin/gallery");
    exit;
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-images"></i> ফটো গ্যালারি ব্যবস্থাপনা (Photo Gallery Management)</span>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Photo Gallery Management Card -->
<div class="admin-card">
    <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa fa-images" style="color:var(--accent);"></i> নতুন ছবি আপলোড করুন</span>
    </div>
    
    <!-- Upload Form -->
    <form method="POST" enctype="multipart/form-data" style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <h3 style="font-size: 14px; color: var(--primary); margin-bottom: 15px;"><i class="fa fa-plus-circle"></i> নতুন ছবি যুক্ত করুন:</h3>
        <div style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
            <div style="flex:1; min-width:250px;">
                <input type="file" id="gallery_file" name="gallery_file" class="form-control" accept="image/png, image/jpeg" required>
                <small style="color:var(--text-muted); display:block; margin-top:5px;">অনূর্ধ্ব ১০ মেগাবাইট, JPG/PNG ফর্ম্যাট।</small>
            </div>
            <div>
                <button type="submit" name="add_gallery_photo" class="btn-admin btn-accent"><i class="fa fa-upload"></i> আপলোড করুন</button>
            </div>
        </div>
    </form>

    <!-- Existing Gallery Images -->
    <h3 style="font-size: 14px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-images"></i> গ্যালারির বর্তমান ছবিসমূহ:</h3>
    <?php
    $gallery_photos = [];
    if (!empty($school['gallery'])) {
        $gallery_photos = json_decode($school['gallery'], true) ?: [];
    }
    ?>
    <?php if (!empty($gallery_photos)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
            <?php foreach ($gallery_photos as $photo): ?>
                <?php $basename = basename($photo); ?>
                <div style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; background: white; text-align: center; display: flex; flex-direction: column;">
                    <div style="height: 110px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; padding: 5px;">
                        <img src="<?php echo UPLOAD_URL . '/' . escape($photo); ?>" alt="Gallery Image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <div style="padding: 8px; border-top: 1px solid var(--border-color); background: #f8fafc;">
                        <a href="?delete_photo=<?php echo urlencode($basename); ?>" class="btn-action delete btn-admin btn-danger" style="padding: 4px 8px; font-size: 11px; width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 4px;" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই ছবিটি গ্যালারি থেকে মুছে ফেলতে চান?');">
                            <i class="fa fa-trash"></i> মুছুন
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); font-style: italic; text-align: center; padding: 20px;">গ্যালারিতে কোনো ছবি আপলোড করা হয়নি।</p>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
