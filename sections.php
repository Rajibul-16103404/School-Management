<?php
/**
 * Public Class-wise Approved Sections
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// Fetch sections and classes
$sections = [];
if ($pdo) {
    try {
        $query = "
            SELECT 
                c.name_bn AS class_name_bn,
                c.name_en AS class_name_en,
                s.name_bn AS section_name_bn,
                s.name_en AS section_name_en,
                s.approved_sections_count,
                s.existing_sections_count,
                s.remark
            FROM `sections` s
            JOIN `classes` c ON s.class_id = c.id
            ORDER BY c.numeric_name ASC, s.id ASC
        ";
        $sections = $pdo->query($query)->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}
?>

<div class="card">
    <h2 class="card-title"><i class="fa fa-sitemap" style="color: var(--accent);"></i> শ্রেণি ভিত্তিক অনুমোদিত শাখার তথ্য</h2>
    <p style="color: var(--text-muted); margin-bottom: 20px;">
        মাধ্যমিক ও উচ্চশিক্ষা অধিদপ্তর (DSHE) কর্তৃক অনুমোদিত এবং বর্তমানে সোনারগাঁও উচ্চ বিদ্যালয়ে চলমান বিভিন্ন শাখার (Sections) তথ্য নিচে দেওয়া হলো:
    </p>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ক্রমিক নং</th>
                    <th>শ্রেণি</th>
                    <th>শাখার নাম</th>
                    <th>অনুমোদিত শাখার সংখ্যা</th>
                    <th>চলমান শাখার সংখ্যা</th>
                    <th>মন্তব্য (Remarks)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sections)): ?>
                    <?php $i = 1; foreach ($sections as $sec): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td style="font-weight: 600;"><?php echo escape($sec['class_name_bn']); ?> (<?php echo escape($sec['class_name_en']); ?>)</td>
                            <td><?php echo escape($sec['section_name_bn']); ?></td>
                            <td><span class="badge badge-success"><?php echo escape($sec['approved_sections_count']); ?> টি</span></td>
                            <td><span class="badge badge-success"><?php echo escape($sec['existing_sections_count']); ?> টি</span></td>
                            <td style="text-align: left; font-size:14px; color: var(--text-muted);"><?php echo escape($sec['remark'] ?: '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="color: var(--text-muted);">কোনো শাখার তথ্য পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
