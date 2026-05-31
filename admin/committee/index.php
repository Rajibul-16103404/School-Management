<?php
/**
 * Admin Committee List Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Fetch committee members
$members = [];
try {
    $members = $pdo->query("SELECT * FROM `committee_members` ORDER BY `sort_order` ASC, `id` ASC")->fetchAll();
} catch (PDOException $e) {}
?>

<div class="page-title">
    <span><i class="fa-solid fa-users-gear"></i> ব্যবস্থাপনা কমিটি সদস্যবৃন্দ (Management Committee)</span>
    <a href="<?php echo BASE_URL; ?>/admin/committee/add" class="btn-admin btn-primary"><i class="fa fa-user-plus"></i> নতুন সদস্য যুক্ত করুন</a>
</div>

<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ক্রম</th>
                    <th>ছবি</th>
                    <th>সদস্যের নাম</th>
                    <th>পদবী</th>
                    <th>পেশা</th>
                    <th>যোগাযোগ</th>
                    <th>মেয়াদকাল (Session)</th>
                    <th>ক্রমিক নম্বর (Sort)</th>
                    <th class="actions-cell">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php $i = 1; foreach ($members as $m): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td>
                                <?php if (!empty($m['photo']) && file_exists(UPLOAD_DIR . '/' . $m['photo'])): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . escape($m['photo']); ?>" alt="Committee Photo" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width:40px; height:40px; border-radius:50%; background-color:#e2e8f0; display:inline-flex; align-items:center; justify-content:center; border: 1px solid var(--border-color); margin:0 auto;"><i class="fa fa-user" style="color:#94a3b8;"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: bold; text-align: left;">
                                <?php echo escape($m['name_bn']); ?>
                                <p style="font-size:11px; font-weight:normal; color: var(--text-muted); margin-top:2px;"><?php echo escape($m['name_en']); ?></p>
                            </td>
                            <td>
                                <span class="badge" style="background-color: #cbd5e1; color:#0f172a;"><?php echo escape($m['designation_bn']); ?></span>
                            </td>
                            <td><?php echo escape($m['profession_bn'] ?: '-'); ?></td>
                            <td style="font-family: var(--font-en);"><?php echo escape($m['contact'] ?: '-'); ?></td>
                            <td style="font-family: var(--font-en);"><?php echo escape($m['session_start']); ?> - <?php echo escape($m['session_end']); ?></td>
                            <td style="font-family: var(--font-en); font-weight:bold;"><?php echo escape($m['sort_order']); ?></td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/committee/edit?id=<?php echo $m['id']; ?>" class="btn-action edit" title="সম্পাদনা"><i class="fa fa-edit"></i></a>
                                <a href="<?php echo BASE_URL; ?>/admin/committee/delete?id=<?php echo $m['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই কমিটির সদস্য মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="color: var(--text-muted);">কোনো কমিটির সদস্য পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
