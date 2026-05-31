<?php
/**
 * Public Management Committee Page
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// Fetch committee members
$members = [];
if ($pdo) {
    try {
        $members = $pdo->query("SELECT * FROM `committee_members` ORDER BY `sort_order` ASC, `id` ASC")->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-bottom: 20px;">
        <h2 style="font-size:20px; color:var(--primary-dark); border-bottom:none; margin-bottom:0; display:flex; align-items:center; gap:10px;">
            <i class="fa fa-users-line" style="color: var(--accent);"></i> ব্যবস্থাপনা কমিটি (Management Committee)
        </h2>
        <button onclick="window.print();" class="top-bar-btn" style="border:none; cursor:pointer;"><i class="fa fa-print"></i> প্রিন্ট করুন</button>
    </div>
    
    <p style="color: var(--text-muted); margin-bottom: 25px;">
        সোনারগাঁও উচ্চ বিদ্যালয়ের পরিচালনা পর্ষদ / ব্যবস্থাপনা কমিটির সদস্যবৃন্দের বিবরণ নিচে দেওয়া হলো। সভাপতি এবং সদস্য সচিব পদবীসমূহ হাইলাইট করা হয়েছে।
    </p>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ক্রমিক নং</th>
                    <th>ছবি</th>
                    <th>সদস্যের নাম</th>
                    <th>কমিটিতে পদবী</th>
                    <th>পেশা</th>
                    <th>যোগাযোগ</th>
                    <th>মেয়াদকাল (Session)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php $i = 1; foreach ($members as $m): 
                        $is_president = (str_contains($m['designation_bn'], 'সভাপতি') || strtolower($m['designation_en']) === 'president');
                        $is_secretary = (str_contains($m['designation_bn'], 'সদস্য সচিব') || str_contains(strtolower($m['designation_en']), 'member secretary'));
                        
                        $row_style = '';
                        if ($is_president) {
                            $row_style = 'background-color: rgba(212, 175, 55, 0.1); border-left: 5px solid var(--accent); font-weight: bold;';
                        } elseif ($is_secretary) {
                            $row_style = 'background-color: rgba(26, 92, 56, 0.05); border-left: 5px solid var(--primary); font-weight: bold;';
                        }
                    ?>
                        <tr style="<?php echo $row_style; ?>">
                            <td><?php echo $i++; ?></td>
                            <td>
                                <?php if (!empty($m['photo']) && file_exists(UPLOAD_DIR . '/' . $m['photo'])): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . escape($m['photo']); ?>" alt="Committee Member" style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width:50px; height:50px; border-radius:50%; background-color:#e2e8f0; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--border-color);"><i class="fa fa-user" style="color:#94a3b8; font-size: 20px;"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo escape($m['name_bn']); ?>
                                <p style="font-size:12px; font-weight:normal; color: var(--text-muted); margin-top:2px;"><?php echo escape($m['name_en']); ?></p>
                            </td>
                            <td>
                                <?php if ($is_president): ?>
                                    <span class="badge" style="background-color: var(--accent); color: black;"><i class="fa fa-crown"></i> <?php echo escape($m['designation_bn']); ?></span>
                                <?php elseif ($is_secretary): ?>
                                    <span class="badge" style="background-color: var(--primary); color: white;"><i class="fa fa-pen-nib"></i> <?php echo escape($m['designation_bn']); ?></span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #cbd5e1; color: #0f172a;"><?php echo escape($m['designation_bn']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo escape($m['profession_bn'] ?: '-'); ?></td>
                            <td><?php echo escape($m['contact'] ?: '-'); ?></td>
                            <td><span style="font-family: var(--font-en); font-weight: 600;"><?php echo escape($m['session_start']); ?> - <?php echo escape($m['session_end']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="color: var(--text-muted);">কোনো কমিটির সদস্য পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
