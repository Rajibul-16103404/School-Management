<?php
/**
 * Admin Add User Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Restrict to superadmin only
check_role('superadmin');

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'staff');
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');

    // Validation
    if (empty($username) || empty($password) || empty($role) || empty($name_bn) || empty($name_en)) {
        $error = "প্রয়োজনীয় ক্ষেত্রসমূহ (ইউজারনেম, পাসওয়ার্ড, রোল, নাম) পূরণ করা আবশ্যক।";
    } elseif (!in_array($role, ['superadmin', 'headteacher', 'staff'])) {
        $error = "ভুল রোল নির্বাচন করা হয়েছে।";
    } else {
        try {
            // Check username duplication
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = "দুঃখিত! এই ইউজারনেমটি (Username) ইতিমধ্যেই ব্যবহৃত হয়েছে। অনুগ্রহ করে অন্য নাম ব্যবহার করুন।";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $insert_sql = "
                    INSERT INTO `users` (`username`, `password`, `role`, `name_bn`, `name_en`, `email`) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ";
                $stmt = $pdo->prepare($insert_sql);
                $stmt->execute([
                    $username,
                    $hashed_password,
                    $role,
                    $name_bn,
                    $name_en,
                    $email
                ]);

                log_activity($pdo, "Add User Account", "Created admin user: '$username' with role: $role");
                $_SESSION['flash_success'] = "ইউজার অ্যাকাউন্ট সফলভাবে তৈরি করা হয়েছে।";
                
                header("Location: " . BASE_URL . "/admin/users/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-user-plus"></i> নতুন অ্যাডমিন ইউজার তৈরি করুন</span>
    <a href="index.php" class="btn-admin btn-secondary"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST">
    <?php echo csrf_input(); ?>
        <div class="form-grid">
            <div class="admin-form-group">
                <label for="username">ইউজারনেম (Username) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="যেমন: headteacher_fatema" autocomplete="username">
            </div>
            
            <div class="admin-form-group">
                <label for="password">পাসওয়ার্ড (Password) <span style="color:var(--danger);">*</span></label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="পাসওয়ার্ড লিখুন" autocomplete="new-password">
            </div>

            <div class="admin-form-group">
                <label for="role">প্রবেশাধিকার লেভেল (Access Role) <span style="color:var(--danger);">*</span></label>
                <select id="role" name="role" class="form-control" required>
                    <option value="staff">স্টাফ (Staff)</option>
                    <option value="headteacher">প্রধান শিক্ষক (Headteacher)</option>
                    <option value="superadmin">সুপার অ্যাডমিন (Superadmin)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="email">ইমেইল ঠিকানা</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="user@domain.com">
            </div>

            <div class="admin-form-group">
                <label for="name_bn">পূর্ণ নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required placeholder="যেমন: ফাতেমা বেগম">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">পূর্ণ নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required placeholder="যেমন: Fatema Begum">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="add_user" class="btn-admin btn-primary"><i class="fa fa-save"></i> অ্যাকাউন্ট তৈরি করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
