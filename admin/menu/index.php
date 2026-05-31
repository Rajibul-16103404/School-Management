<?php
/**
 * Admin Navigation Menu Management Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Restrict to superadmin or headteacher
check_role(['superadmin', 'headteacher']);

$error = null;
$success = null;
$edit_item = null;

// Fetch all root menus for parent select lists (only items that do not have a parent)
$root_options = [];
try {
    $stmt = $pdo->query("SELECT * FROM `menus` WHERE `parent_id` IS NULL ORDER BY `sort_order` ASC, `id` ASC");
    $root_options = $stmt->fetchAll();
} catch (PDOException $e) {}

// 1. Edit Mode: Fetch the item if 'edit' ID is provided
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM `menus` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$edit_id]);
        $edit_item = $stmt->fetch();
    } catch (PDOException $e) {}
}

// 2. CREATE Menu Item Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu'])) {
    $title_bn = sanitize_input($_POST['title_bn'] ?? '');
    $title_en = sanitize_input($_POST['title_en'] ?? '');
    $url = sanitize_input($_POST['url'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($title_bn) || empty($title_en) || empty($url)) {
        $error = "মেনু শিরোনাম (বাংলা ও ইংরেজি) এবং ইউআরএল (URL) প্রদান করা আবশ্যক।";
    } else {
        try {
            $insert_sql = "
                INSERT INTO `menus` (`title_bn`, `title_en`, `url`, `parent_id`, `sort_order`) 
                VALUES (?, ?, ?, ?, ?)
            ";
            $stmt = $pdo->prepare($insert_sql);
            $stmt->execute([$title_bn, $title_en, $url, $parent_id, $sort_order]);

            log_activity($pdo, "Add Menu Item", "Added menu item: $title_bn ($title_en)");
            $_SESSION['flash_success'] = "নতুন ন্যাভিগেশন মেনু আইটেম সফলভাবে যুক্ত করা হয়েছে।";
            
            header("Location: " . BASE_URL . "/admin/menu");
            exit;
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}

// 3. UPDATE Menu Item Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_menu'])) {
    $id = (int)($_POST['id'] ?? 0);
    $title_bn = sanitize_input($_POST['title_bn'] ?? '');
    $title_en = sanitize_input($_POST['title_en'] ?? '');
    $url = sanitize_input($_POST['url'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($id <= 0 || empty($title_bn) || empty($title_en) || empty($url)) {
        $error = "সকল ক্ষেত্র সঠিকভাবে পূরণ করা আবশ্যক।";
    } elseif ($parent_id !== null && $parent_id === $id) {
        $error = "একটি মেনু আইটেম নিজের সাব-মেনু হতে পারে না।";
    } else {
        try {
            $update_sql = "
                UPDATE `menus` 
                SET `title_bn` = ?, `title_en` = ?, `url` = ?, `parent_id` = ?, `sort_order` = ? 
                WHERE `id` = ?
            ";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute([$title_bn, $title_en, $url, $parent_id, $sort_order, $id]);

            log_activity($pdo, "Update Menu Item", "Updated menu item ID: $id -> $title_bn");
            $_SESSION['flash_success'] = "ন্যাভিগেশন মেনু আইটেম সফলভাবে আপডেট করা হয়েছে।";
            
            header("Location: " . BASE_URL . "/admin/menu");
            exit;
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}

// 4. DELETE Menu Item Handler
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id > 0) {
        try {
            $del_stmt = $pdo->prepare("DELETE FROM `menus` WHERE `id` = ?");
            $del_stmt->execute([$delete_id]);

            log_activity($pdo, "Delete Menu Item", "Deleted menu item ID: $delete_id");
            $_SESSION['flash_success'] = "ন্যাভিগেশন মেনু আইটেমটি সফলভাবে মুছে ফেলা হয়েছে।";
        } catch (PDOException $e) {
            $error = "মুছে ফেলা সম্ভব হয়নি: " . $e->getMessage();
        }
        header("Location: " . BASE_URL . "/admin/menu");
        exit;
    }
}

// Fetch all menu items hierarchically for the list view
$menus_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `menus` ORDER BY `sort_order` ASC, `id` ASC");
        $all_items = $stmt->fetchAll();

        // Organize hierarchically
        $roots = [];
        $subs = [];
        foreach ($all_items as $item) {
            if ($item['parent_id'] === null) {
                $roots[] = $item;
            } else {
                $subs[$item['parent_id']][] = $item;
            }
        }
        $menus_list = [
            'roots' => $roots,
            'subs' => $subs
        ];
    } catch (PDOException $e) {}
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-compass"></i> ন্যাভিগেশন মেনু ব্যবস্থাপনা (Navigation Menu Manager)</span>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Check if in EDIT MODE -->
<?php if ($edit_item): ?>
    <div class="admin-card">
        <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <span class="admin-card-title"><i class="fa fa-edit" style="color:var(--accent);"></i> মেনু আইটেম সম্পাদনা (Edit Menu Item)</span>
            <a href="<?php echo BASE_URL; ?>/admin/menu" class="btn-admin btn-secondary" style="font-size:12px; padding:6px 12px;"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
        </div>
        
        <form method="POST" style="padding: 20px;">
            <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
            
            <div class="form-grid">
                <div class="admin-form-group">
                    <label for="title_bn">মেনুর নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                    <input type="text" id="title_bn" name="title_bn" class="form-control" required value="<?php echo escape($edit_item['title_bn']); ?>">
                </div>

                <div class="admin-form-group">
                    <label for="title_en">মেনুর নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                    <input type="text" id="title_en" name="title_en" class="form-control" required value="<?php echo escape($edit_item['title_en']); ?>">
                </div>

                <div class="admin-form-group">
                    <label for="url">ইউআরএল / পাথ (URL) <span style="color:var(--danger);">*</span></label>
                    <input type="text" id="url" name="url" class="form-control" required value="<?php echo escape($edit_item['url']); ?>" placeholder="যেমন: /profile, /contact বা # (ড্রপডাউনের জন্য)">
                    <small style="color:var(--text-muted); display:block; margin-top:4px;">রুট পেজ হলে <code>/</code> লিখুন। ড্রপডাউন ক্যাটাগরি হলে <code>#</code> লিখুন।</small>
                </div>

                <div class="admin-form-group">
                    <label for="parent_id">প্যারেন্ট মেনু (Parent Menu)</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="">কোনোটিই নয় (এটি নিজেই রুট মেনু হবে)</option>
                        <?php foreach ($root_options as $root): ?>
                            <?php if ($root['id'] != $edit_item['id']): ?>
                                <option value="<?php echo $root['id']; ?>" <?php echo $edit_item['parent_id'] == $root['id'] ? 'selected' : ''; ?>>
                                    <?php echo escape($root['title_bn']); ?> (<?php echo escape($root['title_en']); ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--text-muted); display:block; margin-top:4px;">প্যারেন্ট সিলেক্ট করলে এটি তার অধীনে সাব-মেনু হিসেবে ড্রপডাউনে দেখাবে।</small>
                </div>

                <div class="admin-form-group">
                    <label for="sort_order">ক্রম নম্বর (Sort Order)</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo escape($edit_item['sort_order']); ?>">
                    <small style="color:var(--text-muted); display:block; margin-top:4px;">ন্যাভিগেশন বারে বাঁ দিক থেকে ডানে ও ড্রপডাউনে উপর থেকে নিচে সাজানোর জন্য ব্যবহৃত হয়।</small>
                </div>
            </div>

            <div class="form-actions" style="margin-top:20px; padding-top:15px;">
                <button type="submit" name="update_menu" class="btn-admin btn-primary"><i class="fa fa-save"></i> পরিবর্তন সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- Normal Mode: Tabbed Layout for List and Add -->
    <div class="admin-card" style="padding: 0; overflow: visible;">
        <!-- Tab Navigation -->
        <div style="background-color: var(--bg-sidebar); border-radius: var(--radius) var(--radius) 0 0; display: flex; overflow-x: auto; border-bottom: 3px solid var(--accent); padding: 0 10px;">
            <button class="tab-btn active" onclick="switchTab(event, 'list-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid var(--accent); transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-list"></i> মেনু তালিকা (Menu List)
            </button>
            <button class="tab-btn" onclick="switchTab(event, 'add-tab')" style="padding: 15px 25px; background: none; border: none; color: #94a3b8; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 15px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-plus-circle"></i> নতুন মেনু আইটেম (Add New)
            </button>
        </div>

        <!-- Tab Contents -->
        <div style="padding: 25px;">
            <!-- Tab 1: Menu List -->
            <div id="list-tab" class="tab-content active-content">
                <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-compass" style="color:var(--accent);"></i> ন্যাভিগেশন মেনু আইটেমসমূহের তালিকা</h3>
                
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>মেনু শিরোনাম (বাংলা / English)</th>
                                <th>পাথ / ইউআরএল (URL)</th>
                                <th>অবস্থান (Type)</th>
                                <th>ক্রম নম্বর (Sort)</th>
                                <th class="actions-cell">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($menus_list['roots'])): ?>
                                <?php foreach ($menus_list['roots'] as $root): ?>
                                    <!-- Parent/Root Row -->
                                    <tr style="background: rgba(26, 92, 56, 0.04); font-weight: bold;">
                                        <td style="text-align: left; padding-left: 15px;">
                                            📁 <?php echo escape($root['title_bn']); ?>
                                            <span style="font-size:11px; font-weight:normal; color: var(--text-muted); display:block; padding-left:22px;"><?php echo escape($root['title_en']); ?></span>
                                        </td>
                                        <td><code><?php echo escape($root['url']); ?></code></td>
                                        <td><span class="badge badge-success">রুট মেনু (Root)</span></td>
                                        <td style="font-family: var(--font-en);"><?php echo escape($root['sort_order']); ?></td>
                                        <td class="actions-cell">
                                            <a href="?edit=<?php echo $root['id']; ?>" class="btn-action edit" title="সম্পাদনা"><i class="fa fa-edit"></i></a>
                                            <a href="?delete=<?php echo $root['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই মেনু আইটেমটি মুছে ফেলতে চান? এর অধীনে থাকা সকল সাব-মেনুও মুছে যাবে!');"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>

                                    <!-- Render Submenus if any -->
                                    <?php if (isset($menus_list['subs'][$root['id']])): ?>
                                        <?php foreach ($menus_list['subs'][$root['id']] as $sub): ?>
                                            <tr>
                                                <td style="text-align: left; padding-left: 45px;">
                                                    ↳ 📄 <?php echo escape($sub['title_bn']); ?>
                                                    <span style="font-size:11px; font-weight:normal; color: var(--text-muted); display:block; padding-left:25px;"><?php echo escape($sub['title_en']); ?></span>
                                                </td>
                                                <td><code><?php echo escape($sub['url']); ?></code></td>
                                                <td><span class="badge badge-role" style="background:#cbd5e1; color:#1e293b;">সাব-মেনু (Submenu)</span></td>
                                                <td style="font-family: var(--font-en);"><?php echo escape($sub['sort_order']); ?></td>
                                                <td class="actions-cell">
                                                    <a href="?edit=<?php echo $sub['id']; ?>" class="btn-action edit" title="সম্পাদনা"><i class="fa fa-edit"></i></a>
                                                    <a href="?delete=<?php echo $sub['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই সাব-মেনু আইটেমটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="color: var(--text-muted); font-style:italic;">ন্যাভিগেশন বারে কোনো মেনু আইটেম পাওয়া যায়নি।</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Add New Menu Item -->
            <div id="add-tab" class="tab-content" style="display: none;">
                <h3 style="font-size: 16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-plus-circle" style="color:var(--accent);"></i> নতুন ন্যাভিগেশন মেনু আইটেম যুক্ত করুন</h3>
                
                <form method="POST">
                    <div class="form-grid">
                        <div class="admin-form-group">
                            <label for="title_bn">মেনুর নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                            <input type="text" id="title_bn" name="title_bn" class="form-control" required placeholder="যেমন: পরিচিতি">
                        </div>

                        <div class="admin-form-group">
                            <label for="title_en">মেনুর নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                            <input type="text" id="title_en" name="title_en" class="form-control" required placeholder="যেমন: Profile">
                        </div>

                        <div class="admin-form-group">
                            <label for="url">ইউআরএল / পাথ (URL) <span style="color:var(--danger);">*</span></label>
                            <input type="text" id="url" name="url" class="form-control" required placeholder="যেমন: /profile, /contact বা # (ড্রপডাউনের জন্য)">
                            <small style="color:var(--text-muted); display:block; margin-top:4px;">রুট পেজ হলে <code>/</code> লিখুন। ড্রপডাউন ক্যাটাগরি হলে <code>#</code> লিখুন।</small>
                        </div>

                        <div class="admin-form-group">
                            <label for="parent_id">প্যারেন্ট মেনু (Parent Menu)</label>
                            <select id="parent_id" name="parent_id" class="form-control">
                                <option value="">কোনোটিই নয় (এটি নিজেই রুট মেনু হবে)</option>
                                <?php foreach ($root_options as $root): ?>
                                    <option value="<?php echo $root['id']; ?>">
                                        <?php echo escape($root['title_bn']); ?> (<?php echo escape($root['title_en']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:var(--text-muted); display:block; margin-top:4px;">প্যারেন্ট সিলেক্ট করলে এটি তার অধীনে সাব-মেনু হিসেবে ড্রপডাউনে দেখাবে।</small>
                        </div>

                        <div class="admin-form-group">
                            <label for="sort_order">ক্রম নম্বর (Sort Order)</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control" value="0">
                            <small style="color:var(--text-muted); display:block; margin-top:4px;">ন্যাভিগেশন বারে বাঁ দিক থেকে ডানে ও ড্রপডাউনে উপর থেকে নিচে সাজানোর জন্য ব্যবহৃত হয়।</small>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:20px; padding-top:15px; border-top:none;">
                        <button type="submit" name="add_menu" class="btn-admin btn-accent"><i class="fa fa-save"></i> মেনু সংরক্ষণ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

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
