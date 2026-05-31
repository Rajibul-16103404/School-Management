<?php
/**
 * Public MPO & Nationalization Page
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// School-wide MPO details are loaded in $school
$mpo_status = $school['mpo_status'] ?? 'Non-MPO';
$mpo_number = $school['mpo_number'] ?? '';
$mpo_date = $school['mpo_date'] ?? '';
$nationalization_status = $school['nationalization_status'] ?? '';
$nationalization_date = $school['nationalization_date'] ?? '';

// Fetch teacher-wise MPO details
$teachers_mpo = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT * FROM `teachers` 
            WHERE `is_teacher` = 1 AND `status` = 'Active' 
            ORDER BY `joining_date` ASC
        ");
        $teachers_mpo = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}
?>

<div class="card">
    <h2 class="card-title"><i class="fa fa-file-invoice-dollar" style="color: var(--accent);"></i> এমপিও ও জাতীয়করণ তথ্য</h2>
    <p style="color: var(--text-muted); margin-bottom: 20px;">
        সোনারগাঁও উচ্চ বিদ্যালয়ের বিদ্যালয়-স্তরের সরকারি এমপিওভুক্তি (Monthly Pay Order) এবং জাতীয়করণ সংক্রান্ত তথ্যাদি নিচে প্রদান করা হলো:
    </p>

    <!-- School MPO Card Grid -->
    <div class="contact-grid" style="margin-bottom: 30px;">
        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 20px; border-radius: 8px; border-top: 4px solid var(--primary);">
            <h3 style="font-size:16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-building-circle-check"></i> বিদ্যালয় এমপিও তথ্য</h3>
            <ul style="list-style:none; line-height:2.0; font-size:14px;">
                <li>এমপিও স্ট্যাটাস: <strong><?php echo $mpo_status === 'MPO' ? '<span class="badge badge-success">এমপিওভুক্ত (MPO)</span>' : '<span class="badge badge-danger">নন-এমপিও (Non-MPO)</span>'; ?></strong></li>
                <?php if ($mpo_status === 'MPO'): ?>
                    <li>এমপিও কোড / নম্বর: <strong><?php echo escape($mpo_number ?: '-'); ?></strong></li>
                    <li>এমপিওভুক্তির তারিখ: <strong><?php echo !empty($mpo_date) ? format_date($mpo_date) : '-'; ?></strong></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 20px; border-radius: 8px; border-top: 4px solid var(--accent);">
            <h3 style="font-size:16px; color: var(--primary-dark); margin-bottom: 15px;"><i class="fa fa-landmark"></i> জাতীয়করণ তথ্য (Nationalization)</h3>
            <ul style="list-style:none; line-height:2.0; font-size:14px;">
                <li>জাতীয়করণ স্ট্যাটাস: <strong><?php echo !empty($nationalization_status) ? escape($nationalization_status) : 'প্রযোজ্য নয় / বেসরকারি'; ?></strong></li>
                <?php if (!empty($nationalization_status)): ?>
                    <li>জাতীয়করণের তারিখ: <strong><?php echo !empty($nationalization_date) ? format_date($nationalization_date) : '-'; ?></strong></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Teacher-wise table -->
    <h3 style="font-size:18px; color: var(--primary); margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
        <i class="fa fa-user-check"></i> শিক্ষক-কর্মচারী ভিত্তিক সরকারি এমপিও বিবরণী
    </h3>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ক্রমিক নং</th>
                    <th>শিক্ষকের নাম</th>
                    <th>পদবী</th>
                    <th>এমপিও ইনডেক্স</th>
                    <th>সরকারি বেতন গ্রেড / স্কেল</th>
                    <th>ইনডেক্স প্রাপ্তির তারিখ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($teachers_mpo)): ?>
                    <?php $i = 1; $has_mpo = false; foreach ($teachers_mpo as $teacher): 
                        if (empty($teacher['mpo_index'])) continue;
                        $has_mpo = true;
                    ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td style="font-weight: bold;"><?php echo escape($teacher['name_bn']); ?></td>
                            <td><?php echo escape($teacher['designation_bn']); ?></td>
                            <td><strong style="color: var(--primary);"><?php echo escape($teacher['mpo_index']); ?></strong></td>
                            <td><?php echo escape($teacher['mpo_scale'] ?: '-'); ?></td>
                            <td><?php echo !empty($teacher['mpo_date']) ? format_date($teacher['mpo_date']) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (!$has_mpo): ?>
                        <tr>
                            <td colspan="6" style="color: var(--text-muted);">কোনো শিক্ষকের এমপিও বিবরণী নিবন্ধিত পাওয়া যায়নি।</td>
                        </tr>
                    <?php endif; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="color: var(--text-muted);">কোনো শিক্ষকের তথ্য পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
