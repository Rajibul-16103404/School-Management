<?php
/**
 * Admin Classes & Sections Dashboard
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Fetch classes with assigned teachers
$classes = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, t.name_bn AS teacher_name
        FROM `classes` c
        LEFT JOIN `teachers` t ON c.class_teacher_id = t.id
        ORDER BY c.numeric_name ASC
    ");
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch sections grouped by class_id
$sections = [];
try {
    $raw_sections = $pdo->query("SELECT * FROM `sections` ORDER BY `id` ASC")->fetchAll();
    foreach ($raw_sections as $sec) {
        $sections[$sec['class_id']][] = $sec;
    }
} catch (PDOException $e) {}
?>

<div class="page-title">
    <span><i class="fa-solid fa-school"></i> শ্রেণি ও শাখা ব্যবস্থাপনা (Classes & Sections)</span>
    <div style="display:flex; gap:10px;">
        <a href="add_section.php" class="btn-admin btn-accent"><i class="fa fa-plus-circle"></i> নতুন শাখা যুক্ত করুন</a>
        <a href="add_class.php" class="btn-admin btn-primary"><i class="fa fa-circle-plus"></i> নতুন শ্রেণি যুক্ত করুন</a>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr; gap: 25px;">
    <?php if (!empty($classes)): ?>
        <?php foreach ($classes as $cls): ?>
            <div class="admin-card" style="border-top: 4px solid var(--primary); margin-bottom: 0;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid var(--border-color); padding-bottom:10px; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--primary-dark);">
                            🎓 <?php echo escape($cls['name_bn']); ?> (<?php echo escape($cls['name_en']); ?>) 
                            <span style="font-size: 13px; font-weight:normal; color: var(--text-muted); margin-left: 10px;">শ্রেণি নম্বর: <?php echo escape($cls['numeric_name']); ?></span>
                        </h3>
                        <p style="font-size: 13px; margin-top:4px; color: var(--text-color);">
                            <strong>শ্রেণি শিক্ষক:</strong> <?php echo escape($cls['teacher_name'] ?: 'নির্ধারণ করা হয়নি'); ?>
                        </p>
                    </div>
                    
                    <div style="display:flex; gap:8px;">
                        <a href="edit_class.php?id=<?php echo $cls['id']; ?>" class="btn-admin btn-secondary" style="font-size:12px; padding:6px 12px;"><i class="fa fa-edit"></i> সম্পাদনা</a>
                        <a href="delete_class.php?id=<?php echo $cls['id']; ?>" class="btn-admin btn-danger" style="font-size:12px; padding:6px 12px;" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই শ্রেণিটি মুছে ফেলতে চান? এর অধীনে থাকা সকল শাখা এবং শিক্ষার্থীর বিবরণ মুছে যাবে!');"><i class="fa fa-trash"></i> মুছে ফেলুন</a>
                    </div>
                </div>
                
                <!-- Section List for this Class -->
                <h4 style="font-size: 14px; color: var(--text-muted); margin-bottom: 10px;"><i class="fa fa-sitemap"></i> শাখার তালিকা:</h4>
                
                <div class="admin-table-responsive">
                    <table class="admin-table" style="font-size: 14px;">
                        <thead>
                            <tr>
                                <th>শাখার নাম (বাংলা)</th>
                                <th>শাখার নাম (ইংরেজি)</th>
                                <th>অনুমোদিত শাখার সংখ্যা</th>
                                <th>চলমান শাখার সংখ্যা</th>
                                <th>মন্তব্য (Remark)</th>
                                <th class="actions-cell">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($sections[$cls['id']]) && count($sections[$cls['id']]) > 0): ?>
                                <?php foreach ($sections[$cls['id']] as $sec): ?>
                                    <tr>
                                        <td style="font-weight: bold;"><?php echo escape($sec['name_bn']); ?></td>
                                        <td><?php echo escape($sec['name_en']); ?></td>
                                        <td><span class="badge badge-success"><?php echo escape($sec['approved_sections_count']); ?> টি</span></td>
                                        <td><span class="badge badge-success"><?php echo escape($sec['existing_sections_count']); ?> টি</span></td>
                                        <td style="text-align: left; font-size:12px; color: var(--text-muted);"><?php echo escape($sec['remark'] ?: '-'); ?></td>
                                        <td class="actions-cell">
                                            <a href="edit_section.php?id=<?php echo $sec['id']; ?>" class="btn-action edit" style="width:28px; height:28px;" title="শাখা সম্পাদনা"><i class="fa fa-edit"></i></a>
                                            <a href="delete_section.php?id=<?php echo $sec['id']; ?>" class="btn-action delete" style="width:28px; height:28px;" title="শাখা মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই শাখাটি মুছে ফেলতে চান? এর অধীনে থাকা সকল শিক্ষার্থীর শাখা খালি হয়ে যাবে!');"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="color: var(--text-muted); font-style:italic;">কোনো শাখা যুক্ত করা হয়নি।</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="admin-card" style="text-align: center; color: var(--text-muted); padding: 40px;">
            কোনো শ্রেণি পাওয়া যায়নি।
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
