<?php
/**
 * Admin Global Settings Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Restrict to superadmin or headteacher
check_role(['superadmin', 'headteacher']);

$error = null;

// Fetch school settings
$school = null;
try {
    $stmt = $pdo->query("SELECT * FROM `schools` WHERE `id` = 1 LIMIT 1");
    $school = $stmt->fetch();
} catch (PDOException $e) {}

// Fetch recognition documents
$docs = [];
try {
    $docs = $pdo->query("SELECT * FROM `recognition_docs` ORDER BY `recognition_date` DESC")->fetchAll();
} catch (PDOException $e) {}

// Update school settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_school'])) {
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $eiin = sanitize_input($_POST['eiin'] ?? '');
    $founding_year = (int)($_POST['founding_year'] ?? 1971);
    $phone = sanitize_input($_POST['phone'] ?? '');
    $mobile = sanitize_input($_POST['mobile'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $fax = sanitize_input($_POST['fax'] ?? '');
    $address_bn = sanitize_input($_POST['address_bn'] ?? '');
    $address_en = sanitize_input($_POST['address_en'] ?? '');
    
    $mission_bn = sanitize_input($_POST['mission_bn'] ?? '');
    $mission_en = sanitize_input($_POST['mission_en'] ?? '');
    $vision_bn = sanitize_input($_POST['vision_bn'] ?? '');
    $vision_en = sanitize_input($_POST['vision_en'] ?? '');
    $objectives_bn = sanitize_input($_POST['objectives_bn'] ?? '');
    $objectives_en = sanitize_input($_POST['objectives_en'] ?? '');
    $map_embed = $_POST['map_embed'] ?? ''; // Do not sanitize raw map iframe code

    if (empty($name_bn) || empty($name_en) || empty($eiin)) {
        $error = "প্রতিষ্ঠানের নাম এবং ইআইআইএন (EIIN) নম্বর প্রদান করা আবশ্যক।";
    } else {
        try {
            // Logo upload handling
            $logo_path = $school['logo']; // Keep original by default
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $uploaded = upload_file($_FILES['logo'], 'photos', ['jpg', 'jpeg', 'png'], 10485760); // Max 10MB
                    
                    // Delete old logo
                    if (!empty($school['logo']) && file_exists(UPLOAD_DIR . '/' . $school['logo'])) {
                        unlink(UPLOAD_DIR . '/' . $school['logo']);
                    }
                    
                    $logo_path = $uploaded;
                } catch (Exception $e) {
                    $error = "লোগো আপলোড ত্রুটি: " . $e->getMessage();
                }
            }

            if (!$error) {
                $update_sql = "
                    UPDATE `schools` 
                    SET `name_bn` = ?, `name_en` = ?, `eiin` = ?, `founding_year` = ?, `phone` = ?, `mobile` = ?, `email` = ?, `fax` = ?, `address_bn` = ?, `address_en` = ?, `mission_bn` = ?, `mission_en` = ?, `vision_bn` = ?, `vision_en` = ?, `objectives_bn` = ?, `objectives_en` = ?, `logo` = ?, `map_embed` = ? 
                    WHERE `id` = 1
                ";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute([
                    $name_bn, $name_en, $eiin, $founding_year, $phone, $mobile, $email, $fax, $address_bn, $address_en,
                    $mission_bn, $mission_en, $vision_bn, $vision_en, $objectives_bn, $objectives_en, $logo_path, $map_embed
                ]);                log_activity($pdo, "Update School Settings", "Updated school profile configurations.");
                $_SESSION['flash_success'] = "প্রতিষ্ঠানের পরিচিতি সফলভাবে আপডেট করা হয়েছে।";
                
                header("Location: " . BASE_URL . "/admin/settings");
                exit;
            }
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}

// Add recognition document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doc'])) {
    $permission_date = sanitize_input($_POST['permission_date'] ?? '');
    $recognition_date = sanitize_input($_POST['recognition_date'] ?? '');
    $recognition_number = sanitize_input($_POST['recognition_number'] ?? '');
    $authority_bn = sanitize_input($_POST['authority_bn'] ?? '');
    $authority_en = sanitize_input($_POST['authority_en'] ?? '');

    if (empty($permission_date) || empty($recognition_date) || empty($recognition_number) || empty($authority_bn)) {
        $error = "সবগুলো ক্ষেত্র (তারিখ, স্বীকৃতি নম্বর এবং অনুমোদনকারী কর্তৃপক্ষ) পূরণ করা আবশ্যক।";
    } elseif (!isset($_FILES['doc_file']) || $_FILES['doc_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "অনুগ্রহ করে অনুমোদনের মূল ফাইল (PDF) আপলোড করুন।";
    } else {
        try {
            // Upload PDF
            $doc_path = upload_file($_FILES['doc_file'], 'documents', ['pdf'], 10485760); // Limit to 10MB

            $insert_sql = "
                INSERT INTO `recognition_docs` (`permission_date`, `recognition_date`, `recognition_number`, `issuing_authority_bn`, `issuing_authority_en`, `document_path`) 
                VALUES (?, ?, ?, ?, ?, ?)
            ";
            $stmt = $pdo->prepare($insert_sql);
            $stmt->execute([
                $permission_date,
                $recognition_date,
                $recognition_number,
                $authority_bn,
                $authority_en,
                $doc_path
            ]);

            log_activity($pdo, "Add Recognition Doc", "Added recognition document No: $recognition_number");
            $_SESSION['flash_success'] = "পাঠদানের অনুমতি ও স্বীকৃতি নথিপত্র সফলভাবে যুক্ত করা হয়েছে।";
            
            header("Location: " . BASE_URL . "/admin/settings");
            exit;
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        } catch (Exception $e) {
            $error = "ফাইল আপলোড ত্রুটি: " . $e->getMessage();
        }
    }
}

// Delete recognition document
if (isset($_GET['delete_doc_id'])) {
    $del_id = (int)$_GET['delete_doc_id'];
    if ($del_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT `document_path` FROM `recognition_docs` WHERE `id` = ? LIMIT 1");
            $stmt->execute([$del_id]);
            $doc = $stmt->fetch();

            if ($doc) {
                if (file_exists(UPLOAD_DIR . '/' . $doc['document_path'])) {
                    unlink(UPLOAD_DIR . '/' . $doc['document_path']);
                }

                $del_stmt = $pdo->prepare("DELETE FROM `recognition_docs` WHERE `id` = ?");
                $del_stmt->execute([$del_id]);

                log_activity($pdo, "Delete Recognition Doc", "Deleted document ID: $del_id");
                $_SESSION['flash_success'] = "নথিপত্রটি সফলভাবে মুছে ফেলা হয়েছে।";
            }
        } catch (PDOException $e) {
            $error = "মুছে ফেলা সম্ভব হয়নি: " . $e->getMessage();
        }
        header("Location: " . BASE_URL . "/admin/settings");
        exit;
    }
}

?>

<div class="page-title">
    <span><i class="fa-solid fa-gears"></i> গ্লোবাল সেটিংস ও পরিচিতি ব্যবস্থাপনা</span>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card" style="padding: 0; overflow: visible;">
    <!-- Tab Navigation -->
    <div style="background-color: var(--bg-sidebar); border-radius: var(--radius) var(--radius) 0 0; display: flex; overflow-x: auto; border-bottom: 3px solid var(--accent); padding: 0 10px;">
        <button class="tab-btn active" onclick="switchTab(event, 'profile-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid var(--accent); transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-school"></i> সাধারণ সেটিংস (General Settings)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'recognition-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-stamp"></i> অনুমতি ও স্বীকৃতি (Permission & Recognition)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'gallery-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-images"></i> গ্যালারি লিংক (Gallery Link)
        </button>
    </div>

    <!-- Tab Contents -->
    <div style="padding: 25px;">
        <!-- Tab 1: Profile Info -->
        <div id="profile-tab" class="tab-content active-content">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-school" style="color:var(--accent);"></i> প্রতিষ্ঠানের সাধারণ তথ্যাবলী</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="admin-form-group">
                        <label for="name_bn">প্রতিষ্ঠানের নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="name_bn" name="name_bn" class="form-control" required value="<?php echo escape($school['name_bn']); ?>">
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="name_en">প্রতিষ্ঠানের নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="name_en" name="name_en" class="form-control" required value="<?php echo escape($school['name_en']); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="eiin">ইআইআইএন (EIIN) কোড <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="eiin" name="eiin" class="form-control" required value="<?php echo escape($school['eiin']); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="founding_year">প্রতিষ্ঠা বছর</label>
                        <input type="number" id="founding_year" name="founding_year" class="form-control" value="<?php echo escape($school['founding_year']); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="phone">ফোন নম্বর (অফিস)</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo escape($school['phone']); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="mobile">মোবাইল নম্বর (প্রধান)</label>
                        <input type="text" id="mobile" name="mobile" class="form-control" value="<?php echo escape($school['mobile']); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="email">ইমেইল ঠিকানা</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo escape($school['email']); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="fax">ফ্যাক্স (Fax)</label>
                        <input type="text" id="fax" name="fax" class="form-control" value="<?php echo escape($school['fax']); ?>">
                    </div>

                    <div class="admin-form-group form-group-full">
                        <label for="address_bn">পূর্ণ ঠিকানা (বাংলা)</label>
                        <input type="text" id="address_bn" name="address_bn" class="form-control" value="<?php echo escape($school['address_bn']); ?>">
                    </div>
                    
                    <div class="admin-form-group form-group-full">
                        <label for="address_en">পূর্ণ ঠিকানা (ইংরেজি)</label>
                        <input type="text" id="address_en" name="address_en" class="form-control" value="<?php echo escape($school['address_en']); ?>">
                    </div>

                    <div class="admin-form-group form-group-full">
                        <label for="mission_bn">লক্ষ্য - Mission (বাংলা)</label>
                        <textarea id="mission_bn" name="mission_bn" class="form-control"><?php echo escape($school['mission_bn']); ?></textarea>
                    </div>
                    
                    <div class="admin-form-group form-group-full">
                        <label for="mission_en">লক্ষ্য - Mission (ইংরেজি)</label>
                        <textarea id="mission_en" name="mission_en" class="form-control"><?php echo escape($school['mission_en']); ?></textarea>
                    </div>

                    <div class="admin-form-group form-group-full">
                        <label for="vision_bn">উদ্দেশ্য - Vision (বাংলা)</label>
                        <textarea id="vision_bn" name="vision_bn" class="form-control"><?php echo escape($school['vision_bn']); ?></textarea>
                    </div>
                    
                    <div class="admin-form-group form-group-full">
                        <label for="vision_en">উদ্দেশ্য - Vision (ইংরেজি)</label>
                        <textarea id="vision_en" name="vision_en" class="form-control"><?php echo escape($school['vision_en']); ?></textarea>
                    </div>

                    <div class="admin-form-group form-group-full">
                        <label for="objectives_bn">অবজেক্টিভস (বাংলা)</label>
                        <textarea id="objectives_bn" name="objectives_bn" class="form-control"><?php echo escape($school['objectives_bn']); ?></textarea>
                    </div>
                    
                    <div class="admin-form-group form-group-full">
                        <label for="objectives_en">অবজেক্টিভস (ইংরেজি)</label>
                        <textarea id="objectives_en" name="objectives_en" class="form-control"><?php echo escape($school['objectives_en']); ?></textarea>
                    </div>

                    <div class="admin-form-group form-group-full">
                        <label for="map_embed">গুগল ম্যাপ এমবেড কোড (Google Maps iframe Code)</label>
                        <textarea id="map_embed" name="map_embed" class="form-control" style="font-family: monospace; font-size:13px;"><?php echo escape($school['map_embed']); ?></textarea>
                    </div>

                    <div class="admin-form-group form-group-full" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                        <?php if ($school['logo']): ?>
                            <div>
                                <p style="font-size:12px; font-weight:bold; margin-bottom:5px;">বর্তমান লোগো:</p>
                                <img src="<?php echo UPLOAD_URL . '/' . escape($school['logo']); ?>" alt="Logo" style="width:80px; height:80px; object-fit:contain; border:1px solid var(--border-color); padding: 5px; background:white;">
                            </div>
                        <?php endif; ?>
                        <div style="flex:1;">
                            <label for="logo">প্রতিষ্ঠানের লোগো আপলোড (অনূর্ধ্ব ১০ মেগাবাইট, JPG/PNG)</label>
                            <input type="file" id="logo" name="logo" class="form-control" accept="image/png, image/jpeg">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_school" class="btn-admin btn-primary"><i class="fa fa-save"></i> সেটিংস সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>

        <!-- Tab 2: Recognition Documents -->
        <div id="recognition-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-stamp" style="color:var(--accent);"></i> পাঠদানের অনুমতি ও স্বীকৃতি নথিপত্র ব্যবস্থাপনা</h3>
            
            <!-- Upload Form -->
            <form method="POST" enctype="multipart/form-data" style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <h3 style="font-size: 14px; color: var(--primary); margin-bottom: 15px;"><i class="fa fa-plus-circle"></i> নতুন স্বীকৃতিপত্র যুক্ত করুন:</h3>
                <div class="form-grid">
                    <div class="admin-form-group">
                        <label for="permission_date">পাঠদানের অনুমতি লাভের তারিখ <span style="color:var(--danger);">*</span></label>
                        <input type="date" id="permission_date" name="permission_date" class="form-control" required>
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="recognition_date">একাডেমিক স্বীকৃতির তারিখ <span style="color:var(--danger);">*</span></label>
                        <input type="date" id="recognition_date" name="recognition_date" class="form-control" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="recognition_number">অনুমতি / স্বীকৃতি পত্র নম্বর <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="recognition_number" name="recognition_number" class="form-control" required placeholder="যেমন: স্বী-১২৩৪/৯৫">
                    </div>

                    <div class="admin-form-group">
                        <label for="authority_bn">অনুমোদনকারী কর্তৃপক্ষ (বাংলা) <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="authority_bn" name="authority_bn" class="form-control" required placeholder="যেমন: ঢাকা শিক্ষা বোর্ড">
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="authority_en">অনুমোদনকারী কর্তৃপক্ষ (ইংরেজি)</label>
                        <input type="text" id="authority_en" name="authority_en" class="form-control" placeholder="যেমন: Board of Intermediate and Secondary Education, Dhaka">
                    </div>

                    <div class="admin-form-group">
                        <label for="doc_file">অনুমোদনের মূল ফাইল (শুধুমাত্র PDF, সর্বোচ্চ ১০ মেগাবাইট) <span style="color:var(--danger);">*</span></label>
                        <input type="file" id="doc_file" name="doc_file" class="form-control" accept="application/pdf" required>
                    </div>
                </div>
                <div class="form-actions" style="margin-top:15px; padding-top:10px; border-top:none;">
                    <button type="submit" name="add_doc" class="btn-admin btn-accent"><i class="fa fa-save"></i> নথি সংরক্ষণ করুন</button>
                </div>
            </form>

            <!-- Documents List Table -->
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ক্রম</th>
                            <th>অনুমতির তারিখ</th>
                            <th>স্বীকৃতির তারিখ</th>
                            <th>স্বীকৃতি নম্বর</th>
                            <th>অনুমোদনকারী কর্তৃপক্ষ</th>
                            <th>সংযুক্ত ফাইল</th>
                            <th class="actions-cell">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($docs)): ?>
                            <?php $i = 1; foreach ($docs as $doc): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo format_date($doc['permission_date']); ?></td>
                                    <td><?php echo format_date($doc['recognition_date']); ?></td>
                                    <td><strong><?php echo escape($doc['recognition_number']); ?></strong></td>
                                    <td style="text-align: left;"><?php echo escape($doc['issuing_authority_bn']); ?></td>
                                    <td>
                                        <a href="<?php echo UPLOAD_URL . '/' . escape($doc['document_path']); ?>" target="_blank" class="badge badge-success"><i class="fa fa-file-pdf"></i> দেখুন</a>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="?delete_doc_id=<?php echo $doc['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই নথিটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="color: var(--text-muted); font-style:italic;">কোনো স্বীকৃতিপত্র আপলোড করা হয়নি।</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Photo Gallery Link Card -->
        <div id="gallery-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-images" style="color:var(--accent);"></i> প্রতিষ্ঠানের ছবি গ্যালারি (Photo Gallery)</h3>
            <div style="padding: 20px; text-align: center; background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px;">
                <p style="margin-bottom: 15px; color: var(--text-muted);">প্রতিষ্ঠানের ছবি গ্যালারির ছবিসমূহ আপলোড ও মুছার জন্য গ্যালারি ব্যবস্থাপনা পেজে যান।</p>
                <a href="<?php echo BASE_URL; ?>/admin/gallery" class="btn-admin btn-accent" style="display: inline-flex; align-items: center; gap: 8px;"><i class="fa fa-images"></i> গ্যালারি ব্যবস্থাপনা পেজে যান</a>
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
