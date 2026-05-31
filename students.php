<?php
/**
 * Public Class & Gender-wise Student Statistics
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// Fetch class-wise student statistics
$stats = [];
if ($pdo) {
    try {
        $query = "
            SELECT 
                c.name_bn AS class_name_bn,
                c.name_en AS class_name_en,
                COALESCE(SUM(CASE WHEN s.gender = 'Male' THEN 1 ELSE 0 END), 0) AS boys_count,
                COALESCE(SUM(CASE WHEN s.gender = 'Female' THEN 1 ELSE 0 END), 0) AS girls_count,
                COUNT(s.id) AS total_count
            FROM `classes` c
            LEFT JOIN `students` s ON c.id = s.class_id
            GROUP BY c.id, c.numeric_name
            ORDER BY c.numeric_name ASC
        ";
        $stats = $pdo->query($query)->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Variables for total summation
$total_boys = 0;
$total_girls = 0;
$total_students = 0;
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-bottom: 20px;">
        <h2 style="font-size:20px; color:var(--primary-dark); border-bottom:none; margin-bottom:0; display:flex; align-items:center; gap:10px;">
            <i class="fa fa-chart-simple" style="color: var(--accent);"></i> শ্রেণি ও লিঙ্গ ভিত্তিক শিক্ষার্থীর তথ্য
        </h2>
        <!-- Print Button for print CSS check -->
        <button onclick="window.print();" class="top-bar-btn" style="border:none; cursor:pointer;"><i class="fa fa-print"></i> প্রিন্ট করুন</button>
    </div>

    <p style="color: var(--text-muted); margin-bottom: 20px;">
        সোনারগাঁও উচ্চ বিদ্যালয়ের বিভিন্ন শ্রেণিতে অধ্যয়নরত ছাত্র ও ছাত্রীদের শ্রেণিভিত্তিক এবং জেন্ডারভিত্তিক রিয়েল-টাইম তথ্য ও পরিসংখ্যান নিচে উপস্থাপন করা হলো:
    </p>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ক্রমিক নং</th>
                    <th>শ্রেণির নাম</th>
                    <th>ছাত্র (Boys)</th>
                    <th>ছাত্রী (Girls)</th>
                    <th>মোট শিক্ষার্থী (Total)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stats)): ?>
                    <?php $i = 1; foreach ($stats as $row): 
                        $total_boys += $row['boys_count'];
                        $total_girls += $row['girls_count'];
                        $total_students += $row['total_count'];
                    ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td style="font-weight: 600;"><?php echo escape($row['class_name_bn']); ?> (<?php echo escape($row['class_name_en']); ?>)</td>
                            <td><?php echo escape($row['boys_count']); ?> জন</td>
                            <td><?php echo escape($row['girls_count']); ?> জন</td>
                            <td style="font-weight: bold; color: var(--primary);"><?php echo escape($row['total_count']); ?> জন</td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Summation Totals Row -->
                    <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid var(--primary);">
                        <td colspan="2" style="text-align: right; padding-right: 30px;">সর্বমোট:</td>
                        <td><?php echo escape($total_boys); ?> জন</td>
                        <td><?php echo escape($total_girls); ?> জন</td>
                        <td style="color: var(--primary-dark); font-size:18px; font-weight: 800;"><?php echo escape($total_students); ?> জন</td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="color: var(--text-muted);">কোনো শিক্ষার্থীর তথ্য পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
