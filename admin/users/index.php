<?php
/**
 * Admin User List Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Restrict to superadmin only
check_role('superadmin');

// Fetch all users
$users = [];
try {
    $users = $pdo->query("SELECT * FROM `users` ORDER BY `id` ASC")->fetchAll();
} catch (PDOException $e) {}

$role_names = [
    'superadmin' => 'সুপার অ্যাডমিন',
    'headteacher' => 'প্রধান শিক্ষক',
    'staff' => 'স্টাফ'
];
?>

<div class="page-title">
    <span><i class="fa-solid fa-user-group"></i> অ্যাডমিন অ্যাকাউন্টস (System Users)</span>
    <a href="<?php echo BASE_URL; ?>/admin/users/add" class="btn-admin btn-primary"><i class="fa fa-user-plus"></i> নতুন অ্যাকাউন্ট তৈরি করুন</a>
</div>

<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ক্রম</th>
                    <th>ইউজারনেম (Username)</th>
                    <th>পূর্ণ নাম (বাংলা)</th>
                    <th>পূর্ণ নাম (ইংরেজি)</th>
                    <th>প্রবেশাধিকার লেভেল (Role)</th>
                    <th>ইমেইল</th>
                    <th>তৈরির তারিখ</th>
                    <th class="actions-cell">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php $i = 1; foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo escape($user['username']); ?></strong></td>
                            <td style="font-weight: bold;"><?php echo escape($user['name_bn']); ?></td>
                            <td><?php echo escape($user['name_en']); ?></td>
                            <td>
                                <?php 
                                    $role = $user['role'];
                                    $class_label = $role === 'superadmin' ? 'badge-danger' : ($role === 'headteacher' ? 'badge-success' : 'badge-warning');
                                    $role_txt = $role_names[$role] ?? 'স্টাফ';
                                    echo "<span class='badge {$class_label}'>{$role_txt}</span>";
                                ?>
                            </td>
                            <td style="font-family: var(--font-en);"><?php echo escape($user['email'] ?: '-'); ?></td>
                            <td style="font-family: var(--font-en); font-size:12px; color: var(--text-muted);"><?php echo format_date($user['created_at']); ?></td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/users/edit?id=<?php echo $user['id']; ?>" class="btn-action edit" title="সম্পাদনা"><i class="fa fa-edit"></i></a>
                                <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/delete?id=<?php echo $user['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই ইউজার অ্যাকাউন্টটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:11px; font-style:italic;">নিজে (Self)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="color: var(--text-muted);">কোনো ইউজার পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
