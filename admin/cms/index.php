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
        header("Location: " . BASE_URL . "/admin/cms?tab=headmaster");
        exit;
    } catch (Exception $e) {
        $error = "তথ্য পরিবর্তন ব্যর্থ হয়েছে: " . $e->getMessage();
    }
}

// 4. Handle About settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    $about_bn = sanitize_input($_POST['about_text_bn'] ?? '');
    $about_en = sanitize_input($_POST['about_text_en'] ?? '');

    try {
        $update_sql = "
            UPDATE `schools` SET 
            `about_text_bn` = ?,
            `about_text_en` = ?
            WHERE `id` = 1
        ";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$about_bn, $about_en]);
        
        log_activity($pdo, "Update About Content", "Updated school about content.");
        $_SESSION['flash_success'] = "প্রতিষ্ঠানের পরিচিতি সফলভাবে আপডেট করা হয়েছে।";
        header("Location: " . BASE_URL . "/admin/cms?tab=about");
        exit;
    } catch (PDOException $e) {
        $error = "পরিচিতি পরিবর্তন ব্যর্থ হয়েছে: " . $e->getMessage();
    }
}

// 5. Handle Footer settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_footer'])) {
    $footer_text_bn = sanitize_input($_POST['footer_text_bn'] ?? '');
    $footer_text_en = sanitize_input($_POST['footer_text_en'] ?? '');
    $footer_copyright_bn = sanitize_input($_POST['footer_copyright_bn'] ?? '');
    $footer_copyright_en = sanitize_input($_POST['footer_copyright_en'] ?? '');
    
    // Parse links array
    $link_titles_bn = $_POST['link_title_bn'] ?? [];
    $link_titles_en = $_POST['link_title_en'] ?? [];
    $link_urls = $_POST['link_url'] ?? [];
    
    $links = [];
    for ($i = 0; $i < count($link_titles_bn); $i++) {
        $title_bn = sanitize_input($link_titles_bn[$i] ?? '');
        $title_en = sanitize_input($link_titles_en[$i] ?? '');
        $url = sanitize_input($link_urls[$i] ?? '');
        
        if (!empty($title_bn) && !empty($url)) {
            $links[] = [
                'title_bn' => $title_bn,
                'title_en' => $title_en,
                'url' => $url
            ];
        }
    }
    
    $links_json = json_encode($links, JSON_UNESCAPED_UNICODE);

    try {
        $update_sql = "
            UPDATE `schools` SET 
            `footer_text_bn` = ?,
            `footer_text_en` = ?,
            `footer_copyright_bn` = ?,
            `footer_copyright_en` = ?,
            `footer_links` = ?
            WHERE `id` = 1
        ";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$footer_text_bn, $footer_text_en, $footer_copyright_bn, $footer_copyright_en, $links_json]);
        
        log_activity($pdo, "Update Footer Settings", "Updated school footer info and links.");
        $_SESSION['flash_success'] = "ফুটার সেটিংস ও লিংকসমূহ সফলভাবে আপডেট করা হয়েছে।";
        header("Location: " . BASE_URL . "/admin/cms?tab=footer");
        exit;
    } catch (PDOException $e) {
        $error = "ফুটার পরিবর্তন ব্যর্থ হয়েছে: " . $e->getMessage();
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
        <button class="tab-btn" onclick="switchTab(event, 'about-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-address-card"></i> প্রতিষ্ঠান পরিচিতি (About Info)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'footer-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-anchor"></i> ফুটার সেটিংস (Footer Settings)
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

        <!-- Tab 3: About Text -->
        <div id="about-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 20px;"><i class="fa fa-address-card"></i> প্রতিষ্ঠান পরিচিতি বিবরণ সম্পাদন করুন</h3>
            
            <form method="POST">
                <?php echo csrf_input(); ?>
                <div class="form-grid">
                    <div class="admin-form-group" style="grid-column: span 2;">
                        <label for="about_text_bn">পরিচিতি বিবরণ (বাংলা) <span style="color:var(--danger);">*</span></label>
                        <textarea id="about_text_bn" name="about_text_bn" class="form-control" rows="8" required><?php echo escape($school['about_text_bn'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="admin-form-group" style="grid-column: span 2;">
                        <label for="about_text_en">পরিচিতি বিবরণ (English) <span style="color:var(--danger);">*</span></label>
                        <textarea id="about_text_en" name="about_text_en" class="form-control" rows="8" required><?php echo escape($school['about_text_en'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:20px; padding-top:15px;">
                    <button type="submit" name="update_about" class="btn-admin btn-accent"><i class="fa fa-save"></i> পরিচিতি সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>

        <!-- Tab 4: Footer Settings -->
        <div id="footer-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 20px;"><i class="fa fa-anchor"></i> ফুটার সেটিংস ও কাস্টমাইজেশন</h3>
            
            <form method="POST">
                <?php echo csrf_input(); ?>
                <div class="form-grid">
                    <div class="admin-form-group" style="grid-column: span 2;">
                        <label for="footer_text_bn">ফুটার সংক্ষিপ্ত বিবরণ (বাংলা) <span style="color:var(--danger);">*</span></label>
                        <textarea id="footer_text_bn" name="footer_text_bn" class="form-control" rows="3" required><?php echo escape($school['footer_text_bn'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="admin-form-group" style="grid-column: span 2;">
                        <label for="footer_text_en">ফুটার সংক্ষিপ্ত বিবরণ (English) <span style="color:var(--danger);">*</span></label>
                        <textarea id="footer_text_en" name="footer_text_en" class="form-control" rows="3" required><?php echo escape($school['footer_text_en'] ?? ''); ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label for="footer_copyright_bn">কপিরাইট নোটিশ (বাংলা) <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="footer_copyright_bn" name="footer_copyright_bn" class="form-control" required value="<?php echo escape($school['footer_copyright_bn'] ?? ''); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="footer_copyright_en">কপিরাইট নোটিশ (English) <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="footer_copyright_en" name="footer_copyright_en" class="form-control" required value="<?php echo escape($school['footer_copyright_en'] ?? ''); ?>">
                    </div>

                    <div class="admin-form-group form-group-full" style="margin-top: 15px;">
                        <label style="display: block; margin-bottom: 10px; font-weight: bold; color: var(--text-color);">গুরুত্বপূর্ণ লিঙ্কসমূহ (Footer Links)</label>
                        <div class="admin-table-responsive">
                            <table class="admin-table" id="links-table">
                                <thead>
                                    <tr>
                                        <th>লিংকের শিরোনাম (বাংলা)</th>
                                        <th>লিংকের শিরোনাম (English)</th>
                                        <th>ইউআরএল (URL)</th>
                                        <th style="width: 80px; text-align: center;">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $links_list = [];
                                    if (!empty($school['footer_links'])) {
                                        $links_list = json_decode($school['footer_links'], true) ?: [];
                                    }
                                    if (empty($links_list)) {
                                        $links_list = [
                                            ["title_bn" => "মাধ্যমিক ও উচ্চশিক্ষা অধিদপ্তর", "title_en" => "Directorate of Secondary and Higher Education", "url" => "https://dshe.gov.bd"],
                                            ["title_bn" => "শিক্ষা মন্ত্রণালয়", "title_en" => "Ministry of Education", "url" => "https://moedu.gov.bd"],
                                            ["title_bn" => "ঢাকা শিক্ষা বোর্ড", "title_en" => "Board of Intermediate and Secondary Education, Dhaka", "url" => "https://dhakaeducationboard.gov.bd"],
                                            ["title_bn" => "জাতীয় তথ্য বাতায়ন", "title_en" => "National Web Portal", "url" => "https://www.bangladesh.gov.bd"]
                                        ];
                                    }
                                    foreach ($links_list as $link): 
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="text" name="link_title_bn[]" class="form-control" value="<?php echo escape($link['title_bn'] ?? ''); ?>" placeholder="যেমন: শিক্ষা বোর্ড" required>
                                        </td>
                                        <td>
                                            <input type="text" name="link_title_en[]" class="form-control" value="<?php echo escape($link['title_en'] ?? ''); ?>" placeholder="e.g. Education Board">
                                        </td>
                                        <td>
                                            <input type="url" name="link_url[]" class="form-control" value="<?php echo escape($link['url'] ?? ''); ?>" placeholder="https://..." required>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-admin btn-danger" style="padding: 6px 12px;" onclick="removeLinkRow(this)"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 10px; text-align: right;">
                            <button type="button" class="btn-admin btn-primary" onclick="addLinkRow()"><i class="fa fa-plus-circle"></i> নতুন লিঙ্ক যোগ করুন</button>
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:20px; padding-top:15px;">
                    <button type="submit" name="update_footer" class="btn-admin btn-accent"><i class="fa fa-save"></i> ফুটার সেটিংস সংরক্ষণ করুন</button>
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
    if (evt) {
        evt.currentTarget.classList.add("active");
        evt.currentTarget.style.borderBottom = "3px solid var(--accent)";
        evt.currentTarget.style.color = "white";
    }
}

// Add/Remove dynamic footer links row
function addLinkRow() {
    const tableBody = document.querySelector("#links-table tbody");
    const row = document.createElement("tr");
    row.innerHTML = `
        <td>
            <input type="text" name="link_title_bn[]" class="form-control" placeholder="যেমন: শিক্ষা বোর্ড" required>
        </td>
        <td>
            <input type="text" name="link_title_en[]" class="form-control" placeholder="e.g. Education Board">
        </td>
        <td>
            <input type="url" name="link_url[]" class="form-control" placeholder="https://..." required>
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn-admin btn-danger" style="padding: 6px 12px;" onclick="removeLinkRow(this)"><i class="fa fa-trash"></i></button>
        </td>
    `;
    tableBody.appendChild(row);
}

function removeLinkRow(button) {
    const row = button.closest("tr");
    if (document.querySelectorAll("#links-table tbody tr").length > 1) {
        row.remove();
    } else {
        alert("অবশ্যই অন্তত একটি লিংক থাকতে হবে।");
    }
}

// Parse url parameters to auto-activate stored tab
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab === 'about') {
        const btn = document.querySelector('button[onclick*="about-tab"]');
        if (btn) btn.click();
    } else if (activeTab === 'footer') {
        const btn = document.querySelector('button[onclick*="footer-tab"]');
        if (btn) btn.click();
    } else if (activeTab === 'headmaster') {
        const btn = document.querySelector('button[onclick*="headmaster-tab"]');
        if (btn) btn.click();
    }
});
</script>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
