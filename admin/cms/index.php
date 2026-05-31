<?php
/**
 * Admin Website CMS Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Enforce superadmin or headteacher role
check_role(['superadmin', 'headteacher']);

$error = null;
$success = null;

// Fetch school data
$school = null;
try {
    $stmt = $pdo->query("SELECT * FROM `schools` WHERE `id` = 1 LIMIT 1");
    $school = $stmt->fetch();
} catch (PDOException $e) {}

// Decode slider JSON
$sliders = [];
if ($school && !empty($school['slider_data'])) {
    $sliders = json_decode($school['slider_data'], true) ?: [];
}

// 1. Handle Delete Slide (Using parameter 'delete_photo' so verify_csrf validates it)
if (isset($_GET['delete_photo'])) {
    $idx = (int)$_GET['delete_photo'];
    if (isset($sliders[$idx])) {
        $slide_to_delete = $sliders[$idx];
        
        // Delete slide image file if it exists
        if (!empty($slide_to_delete['image'])) {
            $file_path = UPLOAD_DIR . '/' . $slide_to_delete['image'];
            if (file_exists($file_path)) {
                // Keep default slides if they are in assets
                if (strpos($slide_to_delete['image'], 'slide_') === false) {
                    unlink($file_path);
                }
            }
        }
        
        unset($sliders[$idx]);
        $sliders = array_values($sliders); // Reindex array
        
        $slider_json = json_encode($sliders, JSON_UNESCAPED_UNICODE);
        try {
            $stmt = $pdo->prepare("UPDATE `schools` SET `slider_data` = ? WHERE `id` = 1");
            $stmt->execute([$slider_json]);
            
            log_activity($pdo, "Delete Slider Banner", "Deleted homepage banner index: $idx");
            $_SESSION['flash_success'] = "স্লাইডার ব্যানারটি সফলভাবে মুছে ফেলা হয়েছে।";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "ডাটাবেজ আপডেট ব্যর্থ হয়েছে।";
        }
        
        header("Location: " . BASE_URL . "/admin/cms");
        exit;
    }
}

// 2. Handle Add Slide
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slide'])) {
    $title_bn = sanitize_input($_POST['title_bn'] ?? '');
    $title_en = sanitize_input($_POST['title_en'] ?? '');
    $subtitle_bn = sanitize_input($_POST['subtitle_bn'] ?? '');
    $subtitle_en = sanitize_input($_POST['subtitle_en'] ?? '');

    try {
        if (!empty($_FILES['slide_image']['name'])) {
            $uploaded_file = upload_file($_FILES['slide_image'], 'photos', ['jpg', 'jpeg', 'png']);
            
            $new_slide = [
                'image' => $uploaded_file,
                'title_bn' => $title_bn,
                'title_en' => $title_en,
                'subtitle_bn' => $subtitle_bn,
                'subtitle_en' => $subtitle_en
            ];
            
            $sliders[] = $new_slide;
            $slider_json = json_encode($sliders, JSON_UNESCAPED_UNICODE);
            
            $stmt = $pdo->prepare("UPDATE `schools` SET `slider_data` = ? WHERE `id` = 1");
            $stmt->execute([$slider_json]);
            
            log_activity($pdo, "Add Slider Banner", "Added homepage banner: $title_bn");
            $_SESSION['flash_success'] = "নতুন স্লাইডার ব্যানার সফলভাবে যুক্ত করা হয়েছে।";
            header("Location: " . BASE_URL . "/admin/cms");
            exit;
        } else {
            $error = "অনুগ্রহ করে একটি ছবি আপলোড করুন।";
        }
    } catch (Exception $e) {
        $error = "ব্যানার আপলোড ব্যর্থ হয়েছে: " . $e->getMessage();
    }
}

// 3. Handle Headmaster Welcome message update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_headmaster'])) {
    $hm_name_bn = sanitize_input($_POST['headmaster_name_bn'] ?? '');
    $hm_name_en = sanitize_input($_POST['headmaster_name_en'] ?? '');
    $hm_quote_bn = sanitize_input($_POST['headmaster_quote_bn'] ?? '');
    $hm_quote_en = sanitize_input($_POST['headmaster_quote_en'] ?? '');
    
    $hm_photo = $school['headmaster_photo'] ?? 'teacher_1.png';

    try {
        if (!empty($_FILES['headmaster_photo_file']['name'])) {
            // Delete old photo if it exists on disk and isn't the default teacher_1.png
            if (!empty($hm_photo) && $hm_photo !== 'teacher_1.png' && file_exists(UPLOAD_DIR . '/photos/' . $hm_photo)) {
                unlink(UPLOAD_DIR . '/photos/' . $hm_photo);
            }
            
            $uploaded_file = upload_file($_FILES['headmaster_photo_file'], 'photos', ['jpg', 'jpeg', 'png']);
            $hm_photo = basename($uploaded_file);
        }
        
        $update_sql = "
            UPDATE `schools` SET 
            `headmaster_name_bn` = ?,
            `headmaster_name_en` = ?,
            `headmaster_photo` = ?,
            `headmaster_quote_bn` = ?,
            `headmaster_quote_en` = ?
            WHERE `id` = 1
        ";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$hm_name_bn, $hm_name_en, $hm_photo, $hm_quote_bn, $hm_quote_en]);
        
        log_activity($pdo, "Update Headmaster Welcome Info", "Updated Headmaster name and quotes.");
        $_SESSION['flash_success'] = "প্রধান শিক্ষকের বাণী ও তথ্য সফলভাবে আপডেট করা হয়েছে।";
        header("Location: " . BASE_URL . "/admin/cms");
        exit;
    } catch (Exception $e) {
        $error = "তথ্য পরিবর্তন ব্যর্থ হয়েছে: " . $e->getMessage();
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-display"></i> ওয়েবসাইট কন্টেন্ট ম্যানেজমেন্ট (Website CMS)</span>
</div>

<?php echo display_flash_alerts(); ?>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card" style="padding: 0; overflow: visible;">
    <!-- Tab Navigation -->
    <div style="background-color: var(--bg-sidebar); border-radius: var(--radius) var(--radius) 0 0; display: flex; overflow-x: auto; border-bottom: 3px solid var(--accent); padding: 0 10px;">
        <button class="tab-btn active" onclick="switchTab(event, 'slider-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid var(--accent); transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-images"></i> হোম স্লাইডার (Slider Banners)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'headmaster-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-user-tie"></i> প্রধান শিক্ষকের বাণী (Welcome Message)
        </button>
    </div>

    <!-- Tab Contents -->
    <div style="padding: 25px;">
        <!-- Tab 1: Sliders -->
        <div id="slider-tab" class="tab-content active-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 16px; color: var(--primary-dark);"><i class="fa fa-list"></i> স্লাইডার ব্যানারসমূহের তালিকা</h3>
                <button class="btn-admin btn-primary" onclick="toggleAddSlideForm()"><i class="fa fa-plus-circle"></i> নতুন ব্যানার যুক্ত করুন</button>
            </div>

            <!-- Add Slide Form (Collapsible) -->
            <div id="addSlideForm" style="display: none; background: rgba(0,0,0,0.02); border: 1px dashed var(--border-color); padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--primary);"><i class="fa fa-plus"></i> নতুন স্লাইড যোগ করুন</h4>
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrf_input(); ?>
                    <div class="form-grid">
                        <div class="admin-form-group">
                            <label for="title_bn">ব্যানার শিরোনাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                            <input type="text" id="title_bn" name="title_bn" class="form-control" required placeholder="যেমন: মানসম্মত শিক্ষা">
                        </div>
                        <div class="admin-form-group">
                            <label for="title_en">ব্যানার শিরোনাম (English) <span style="color:var(--danger);">*</span></label>
                            <input type="text" id="title_en" name="title_en" class="form-control" required placeholder="e.g. Quality Education">
                        </div>
                        <div class="admin-form-group" style="grid-column: span 2;">
                            <label for="subtitle_bn">ব্যানার উপ-শিরোনাম (বাংলা)</label>
                            <input type="text" id="subtitle_bn" name="subtitle_bn" class="form-control" placeholder="ব্যানারের ছোট বিবরণ বাংলায় লিখুন">
                        </div>
                        <div class="admin-form-group" style="grid-column: span 2;">
                            <label for="subtitle_en">ব্যানার উপ-শিরোনাম (English)</label>
                            <input type="text" id="subtitle_en" name="subtitle_en" class="form-control" placeholder="Write banner details in English">
                        </div>
                        <div class="admin-form-group">
                            <label for="slide_image">ব্যানার ইমেজ (Slider Image) <span style="color:var(--danger);">*</span></label>
                            <input type="file" id="slide_image" name="slide_image" class="form-control" required accept="image/*">
                            <small style="color: var(--text-muted);">অনুমোদিত ফাইল: JPG, PNG। সর্বোচ্চ সাইজ: ১০ মেগাবাইট।</small>
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top: 15px; border:none; padding-top:0;">
                        <button type="submit" name="add_slide" class="btn-admin btn-accent"><i class="fa fa-save"></i> ব্যানারটি সংরক্ষণ করুন</button>
                        <button type="button" class="btn-admin btn-secondary" onclick="toggleAddSlideForm()">বাতিল</button>
                    </div>
                </form>
            </div>

            <!-- List Slider Table -->
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ছবি (Preview)</th>
                            <th>ব্যানার শিরোনাম (বাংলা / English)</th>
                            <th>বিবরণ (Subtitles)</th>
                            <th class="actions-cell">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sliders)): ?>
                            <?php foreach ($sliders as $index => $slide): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $image_url = '';
                                        if (strpos($slide['image'], 'slide_') !== false) {
                                            $image_url = BASE_URL . '/assets/images/' . $slide['image'];
                                        } else {
                                            $image_url = UPLOAD_URL . '/' . $slide['image'];
                                        }
                                        ?>
                                        <img src="<?php echo escape($image_url); ?>" alt="Banner Preview" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);">
                                    </td>
                                    <td style="text-align: left; font-weight: bold;">
                                        <?php echo escape($slide['title_bn']); ?>
                                        <span style="display: block; font-size: 11px; color: var(--text-muted); font-weight: normal;"><?php echo escape($slide['title_en']); ?></span>
                                    </td>
                                    <td style="text-align: left; font-size: 13px;">
                                        <?php echo escape($slide['subtitle_bn']); ?>
                                        <span style="display: block; font-size: 11px; color: var(--text-muted);"><?php echo escape($slide['subtitle_en']); ?></span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="?delete_photo=<?php echo $index; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই ব্যানার স্লাইডটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="color: var(--text-muted); font-style: italic;">কোনো স্লাইডার ব্যানার পাওয়া যায়নি।</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Headmaster Welcome message -->
        <div id="headmaster-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 20px;"><i class="fa fa-user-tie"></i> প্রধান শিক্ষকের তথ্য ও বাণী পরিবর্তন করুন</h3>
            
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_input(); ?>
                <div class="form-grid">
                    <div class="admin-form-group">
                        <label for="headmaster_name_bn">নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="headmaster_name_bn" name="headmaster_name_bn" class="form-control" required value="<?php echo escape($school['headmaster_name_bn'] ?? ''); ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="headmaster_name_en">নাম (English) <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="headmaster_name_en" name="headmaster_name_en" class="form-control" required value="<?php echo escape($school['headmaster_name_en'] ?? ''); ?>">
                    </div>
                    
                    <div class="admin-form-group" style="grid-column: span 2;">
                        <label for="headmaster_quote_bn">স্বাগত বাণী / উদ্ধৃতি (বাংলা) <span style="color:var(--danger);">*</span></label>
                        <textarea id="headmaster_quote_bn" name="headmaster_quote_bn" class="form-control" rows="5" required><?php echo escape($school['headmaster_quote_bn'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="admin-form-group" style="grid-column: span 2;">
                        <label for="headmaster_quote_en">স্বাগত বাণী / উদ্ধৃতি (English) <span style="color:var(--danger);">*</span></label>
                        <textarea id="headmaster_quote_en" name="headmaster_quote_en" class="form-control" rows="5" required><?php echo escape($school['headmaster_quote_en'] ?? ''); ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label for="headmaster_photo_file">প্রধান শিক্ষকের ছবি</label>
                        <input type="file" id="headmaster_photo_file" name="headmaster_photo_file" class="form-control" accept="image/*">
                        <small style="color: var(--text-muted);">খালি রাখলে আগের ছবিটিই বহাল থাকবে। ফাইল সাইজ অনধিক ১০ মেগাবাইট।</small>
                        
                        <?php if (!empty($school['headmaster_photo'])): ?>
                            <div style="margin-top: 15px;">
                                <span style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px;">বর্তমান ছবি:</span>
                                <img src="<?php echo UPLOAD_URL . '/photos/' . escape($school['headmaster_photo']); ?>" alt="Headmaster Photo" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:20px; padding-top:15px;">
                    <button type="submit" name="update_headmaster" class="btn-admin btn-accent"><i class="fa fa-save"></i> প্রধান শিক্ষকের তথ্য সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle Add Banner form panel
function toggleAddSlideForm() {
    const formPanel = document.getElementById("addSlideForm");
    if (formPanel.style.display === "none") {
        formPanel.style.display = "block";
    } else {
        formPanel.style.display = "none";
    }
}

// Tab switcher logic
function switchTab(evt, tabId) {
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
    }

    const tabBtns = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < tabBtns.length; i++) {
        tabBtns[i].classList.remove("active");
        tabBtns[i].style.borderBottom = "3px solid transparent";
        tabBtns[i].style.color = "#94a3b8";
    }

    document.getElementById(tabId).style.display = "block";
    evt.currentTarget.classList.add("active");
    evt.currentTarget.style.borderBottom = "3px solid var(--accent)";
    evt.currentTarget.style.color = "white";
}
</script>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
