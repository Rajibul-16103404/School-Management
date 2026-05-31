<?php
/**
 * Admin Edit User Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Restrict to superadmin only
check_role('superadmin');

$error = null;
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['flash_error'] = "ভুল রিকুয়েস্ট আইডি।";
    header("Location: " . BASE_URL . "/admin/users/index.php");
    exit;
}

// Fetch user profile
$user = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {}

if (!$user) {
    $_SESSION['flash_error'] = "অ্যাকাউন্ট পাওয়া যায়নি।";
    header("Location: " . BASE_URL . "/admin/users/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'staff');
    $name_bn = sanitize_input($_POST['name_bn'] ?? '');
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');

    // Validation
    if (empty($username) || empty($role) || empty($name_bn) || empty($name_en)) {
        $error = "প্রয়োজনীয় ক্ষেত্রসমূহ (ইউজারনেম, রোল, নাম) পূরণ করা আবশ্যক।";
    } else {
        try {
            // Check username duplication excluding this user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = ? AND `id` != ?");
            $stmt->execute([$username, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "দুঃখিত! এই ইউজারনেমটি (Username) ইতিমধ্যেই অন্য অ্যাকাউন্টে ব্যবহৃত হয়েছে।";
            } else {
                // Prepare query
                if (!empty($password)) {
                    // Update details WITH new password
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $update_sql = "
                        UPDATE `users` 
                        SET `username` = ?, `password` = ?, `role` = ?, `name_bn` = ?, `name_en` = ?, `email` = ? 
                        WHERE `id` = ?
                    ";
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute([$username, $hashed_password, $role, $name_bn, $name_en, $email, $user_id]);
                } else {
                    // Update details WITHOUT password change
                    $update_sql = "
                        UPDATE `users` 
                        SET `username` = ?, `role` = ?, `name_bn` = ?, `name_en` = ?, `email` = ? 
                        WHERE `id` = ?
                    ";
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute([$username, $role, $name_bn, $name_en, $email, $user_id]);
                }

                // If editing self, update active session name and role
                if ($user_id === (int)$_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_name'] = $name_bn;
                }

                log_activity($pdo, "Edit User Account", "Updated user details: '$username' (ID: $user_id)");
                $_SESSION['flash_success'] = "অ্যাকাউন্ট বিবরণ সফলভাবে আপডেট করা হয়েছে।";
                
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
    <span><i class="fa-solid fa-user-gear"></i> অ্যাডমিন অ্যাকাউন্ট সম্পাদন করুন</span>
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
                <input type="text" id="username" name="username" class="form-control" required value="<?php echo escape($user['username']); ?>" autocomplete="username">
            </div>
            
            <div class="admin-form-group">
                <label for="password">পাসওয়ার্ড (Password - পরিবর্তন করতে চাইলে লিখুন, অন্যথায় ফাঁকা রাখুন)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" autocomplete="new-password">
            </div>

            <div class="admin-form-group">
                <label for="role">প্রবেশাধিকার লেভেল (Access Role) <span style="color:var(--danger);">*</span></label>
                <select id="role" name="role" class="form-control" required>
                    <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>স্টাফ (Staff)</option>
                    <option value="headteacher" <?php echo $user['role'] === 'headteacher' ? 'selected' : ''; ?>>প্রধান শিক্ষক (Headteacher)</option>
                    <option value="superadmin" <?php echo $user['role'] === 'superadmin' ? 'selected' : ''; ?>>সুপার অ্যাডমিন (Superadmin)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="email">ইমেইল ঠিকানা</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo escape($user['email']); ?>">
            </div>

            <div class="admin-form-group">
                <label for="name_bn">পূর্ণ নাম (বাংলা) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_bn" name="name_bn" class="form-control" required value="<?php echo escape($user['name_bn']); ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="name_en">পূর্ণ নাম (ইংরেজি) <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name_en" name="name_en" class="form-control" required value="<?php echo escape($user['name_en']); ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-admin btn-secondary">বাতিল করুন</a>
            <button type="submit" name="edit_user" class="btn-admin btn-primary"><i class="fa fa-save"></i> আপডেট সংরক্ষণ করুন</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
