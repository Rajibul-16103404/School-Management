<?php
/**
 * Admin Teachers & Staff List Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Filter parameter
$type_filter = isset($_GET['type']) ? sanitize_input($_GET['type']) : 'all';

// Build SQL query
$query = "SELECT * FROM `teachers`";
$params = [];

if ($type_filter === 'teachers') {
    $query .= " WHERE `is_teacher` = 1";
} elseif ($type_filter === 'staff') {
    $query .= " WHERE `is_teacher` = 0";
}

$query .= " ORDER BY `is_teacher` DESC, `joining_date` ASC";

$members = [];
try {
    $members = $pdo->query($query)->fetchAll();
} catch (PDOException $e) {}
?>

<div class="page-title">
    <span><i class="fa-solid fa-chalkboard-user"></i> শিক্ষক ও কর্মচারী তালিকা</span>
    <a href="add" class="btn-admin btn-primary"><i class="fa fa-user-plus"></i> নতুন শিক্ষক/স্টাফ যুক্ত করুন</a>
</div>

<!-- Type Filter Toggle -->
<div class="admin-card" style="margin-bottom: 25px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div style="display:flex; gap:10px;">
            <a href="?type=all" class="btn-admin <?php echo $type_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>" style="background-color: <?php echo $type_filter === 'all' ? 'var(--primary)' : '#cbd5e1'; ?>; color: <?php echo $type_filter === 'all' ? 'white' : '#0f172a'; ?>;"><i class="fa fa-users"></i> সকলে</a>
            <a href="?type=teachers" class="btn-admin <?php echo $type_filter === 'teachers' ? 'btn-primary' : 'btn-secondary'; ?>" style="background-color: <?php echo $type_filter === 'teachers' ? 'var(--primary)' : '#cbd5e1'; ?>; color: <?php echo $type_filter === 'teachers' ? 'white' : '#0f172a'; ?>;"><i class="fa fa-chalkboard-user"></i> শুধুমাত্র শিক্ষক</a>
            <a href="?type=staff" class="btn-admin <?php echo $type_filter === 'staff' ? 'btn-primary' : 'btn-secondary'; ?>" style="background-color: <?php echo $type_filter === 'staff' ? 'var(--primary)' : '#cbd5e1'; ?>; color: <?php echo $type_filter === 'staff' ? 'white' : '#0f172a'; ?>;"><i class="fa fa-user-tie"></i> শুধুমাত্র কর্মচারী</a>
        </div>
        <span style="font-size:14px; color:var(--text-muted);">মোট রেকর্ড সংখ্যা: <strong><?php echo count($members); ?></strong></span>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ছবি</th>
                    <th>নাম</th>
                    <th>পদবী</th>
                    <th>টাইপ (Type)</th>
                    <th>বিভাগ / প্রকার</th>
                    <th>এমপিও ইনডেক্স</th>
                    <th>যোগদানের তারিখ</th>
                    <th>মোবাইল নম্বর</th>
                    <th class="actions-cell">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td>
                                <?php if (!empty($m['photo']) && file_exists(UPLOAD_DIR . '/' . $m['photo'])): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . escape($m['photo']); ?>" alt="Teacher Avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width:40px; height:40px; border-radius:50%; background-color:#e2e8f0; display:inline-flex; align-items:center; justify-content:center; border: 1px solid var(--border-color); margin: 0 auto;"><i class="fa fa-user" style="color:#94a3b8;"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: bold; text-align: left;">
                                <?php echo escape($m['name_bn']); ?>
                                <p style="font-size:11px; font-weight:normal; color: var(--text-muted); margin-top:2px;"><?php echo escape($m['name_en']); ?></p>
                            </td>
                            <td><?php echo escape($m['designation_bn']); ?></td>
                            <td>
                                <?php echo $m['is_teacher'] ? '<span class="badge badge-success">শিক্ষক</span>' : '<span class="badge badge-warning">কর্মচারী</span>'; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($m['is_teacher']) {
                                        echo escape($m['department'] ?: 'General');
                                    } else {
                                        echo escape($m['staff_type'] ?: 'অন্যান্য');
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($m['mpo_index'])): ?>
                                    <strong style="color:var(--primary);"><?php echo escape($m['mpo_index']); ?></strong>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size:12px;">প্রযোজ্য নয়</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: var(--font-en);"><?php echo format_date($m['joining_date']); ?></td>
                            <td style="font-family: var(--font-en);"><?php echo escape($m['phone'] ?: '-'); ?></td>
                            <td class="actions-cell">
                                <a href="edit?id=<?php echo $m['id']; ?>" class="btn-action edit" title="সম্পাদনা"><i class="fa fa-edit"></i></a>
                                <a href="delete?id=<?php echo $m['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই শিক্ষক/স্টাফ প্রোফাইলটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="color: var(--text-muted);">কোনো রেকর্ড পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
