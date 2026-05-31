<?php
/**
 * Public Academic Information Page
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// Fetch all classes for referencing in routines and syllabi
$classes = [];
if ($pdo) {
    try {
        $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fetch notices (all published notices)
$all_notices = [];
if ($pdo) {
    try {
        $all_notices = $pdo->query("SELECT * FROM `notices` WHERE `is_published` = 1 ORDER BY `publish_date` DESC, `id` DESC")->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fetch routines mapped by class_id
$routines = [];
if ($pdo) {
    try {
        $raw_routines = $pdo->query("SELECT * FROM `routines`")->fetchAll();
        foreach ($raw_routines as $r) {
            $routines[$r['class_id']] = $r['file_path'];
        }
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fetch syllabi grouped by class_id
$syllabi = [];
if ($pdo) {
    try {
        $raw_syllabi = $pdo->query("SELECT * FROM `syllabi` ORDER BY `id` DESC")->fetchAll();
        foreach ($raw_syllabi as $s) {
            $syllabi[$s['class_id']][] = $s;
        }
    } catch (PDOException $e) {
        // Silent catch
    }
}
?>

<div class="card" style="padding: 0; overflow: visible;">
    <!-- Tab Navigation -->
    <div style="background-color: var(--primary-dark); border-radius: var(--radius-md) var(--radius-md) 0 0; display: flex; overflow-x: auto; border-bottom: 3px solid var(--accent);">
        <button class="tab-btn active" onclick="switchTab(event, 'notices-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 16px; border-bottom: 3px solid transparent; transition: var(--transition);">
            <i class="fa fa-bullhorn"></i> নোটিশ বোর্ড (Notice Board)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'routines-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 16px; border-bottom: 3px solid transparent; transition: var(--transition);">
            <i class="fa fa-calendar-alt"></i> শ্রেণি রুটিন (Class Routines)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'syllabus-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 16px; border-bottom: 3px solid transparent; transition: var(--transition);">
            <i class="fa fa-book-bookmark"></i> সিলেবাস ও পাঠ্যক্রম (Syllabi)
        </button>
    </div>

    <!-- Tab Contents -->
    <div style="padding: 25px;">
        <!-- Tab 1: Notice Board -->
        <div id="notices-tab" class="tab-content active-content">
            <h3 style="font-size: 18px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa fa-bullhorn"></i> প্রতিষ্ঠানের নোটিশ ও বিজ্ঞপ্তি
            </h3>
            
            <?php if (!empty($all_notices)): ?>
                <div style="display:flex; flex-direction:column; gap:20px;">
                    <?php foreach ($all_notices as $notice): ?>
                        <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; background-color: white; box-shadow: var(--shadow-sm); position: relative; border-left: 5px solid var(--primary);">
                            <span style="font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 8px;">
                                <i class="fa fa-calendar-day"></i> প্রকাশিত: <strong><?php echo format_date($notice['publish_date']); ?></strong>
                            </span>
                            <h4 style="font-size:17px; margin-bottom: 10px; color: var(--primary-dark);"><?php echo escape($notice['title_bn']); ?></h4>
                            <?php if ($notice['title_en']): ?>
                                <h5 style="font-size:13px; font-weight: normal; color: var(--text-muted); margin-bottom: 12px; font-style: italic;"><?php echo escape($notice['title_en']); ?></h5>
                            <?php endif; ?>
                            
                            <?php if ($notice['content_bn']): ?>
                                <p style="font-size: 14px; line-height: 1.6; color: var(--text-color); margin-bottom: 15px;">
                                    <?php echo nl2br(escape($notice['content_bn'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($notice['attachment']): ?>
                                <div>
                                    <a href="<?php echo UPLOAD_URL . '/' . escape($notice['attachment']); ?>" target="_blank" class="top-bar-btn" style="text-decoration:none;">
                                        <i class="fa fa-file-pdf"></i> ফাইল ডাউনলোড করুন (Download)
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 30px;">কোনো নোটিশ পাওয়া যায়নি।</p>
            <?php endif; ?>
        </div>

        <!-- Tab 2: Class Routines -->
        <div id="routines-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 18px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa fa-calendar-alt"></i> শ্রেণি ভিত্তিক রুটিনসমূহ
            </h3>
            <p style="color: var(--text-muted); font-size:14px; margin-bottom: 20px;">
                আপনার শ্রেণির সাপ্তাহিক ক্লাস রুটিন দেখতে এবং ডাউনলোড করতে নিচে সংশ্লিষ্ট শ্রেণির লিঙ্কে ক্লিক করুন:
            </p>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ক্রমিক নং</th>
                            <th>শ্রেণির নাম</th>
                            <th>ফাইল</th>
                            <th>আপডেট সময়</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($classes)): ?>
                            <?php $i = 1; foreach ($classes as $cls): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td style="font-weight: bold;"><?php echo escape($cls['name_bn']); ?> (<?php echo escape($cls['name_en']); ?>)</td>
                                    <td>
                                        <?php if (isset($routines[$cls['id']])): ?>
                                            <a href="<?php echo UPLOAD_URL . '/' . escape($routines[$cls['id']]); ?>" target="_blank" class="badge badge-success">
                                                <i class="fa fa-download"></i> রুটিন ডাউনলোড করুন
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-danger">আপলোড করা হয়নি</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>-</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="color: var(--text-muted);">কোনো শ্রেণি পাওয়া যায়নি।</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Syllabus -->
        <div id="syllabus-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 18px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa fa-book-bookmark"></i> শিক্ষাবর্ষের সিলেবাস ও পাঠ্যক্রম
            </h3>
            
            <?php if (!empty($classes)): ?>
                <div style="display:grid; grid-template-columns: 1fr; gap: 20px;">
                    <?php foreach ($classes as $cls): ?>
                        <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 25px; background: white;">
                            <h4 style="font-size:16px; color: var(--primary-dark); margin-bottom: 15px; border-bottom: 2px solid var(--accent); display:inline-block; padding-bottom:3px;">
                                <?php echo escape($cls['name_bn']); ?> (<?php echo escape($cls['name_en']); ?>)
                            </h4>
                            
                            <?php if (isset($syllabi[$cls['id']]) && count($syllabi[$cls['id']]) > 0): ?>
                                <ul style="list-style:none; display:flex; flex-direction:column; gap:10px;">
                                    <?php foreach ($syllabi[$cls['id']] as $sy): ?>
                                        <li style="display:flex; justify-content:space-between; align-items:center; background-color: #f8fafc; padding: 10px 15px; border-radius:6px; border: 1px solid var(--border-color);">
                                            <span style="font-weight: 600; font-size:14px;">
                                                📖 <?php echo escape($sy['subject_bn']); ?> (<?php echo escape($sy['subject_en']); ?>)
                                            </span>
                                            <a href="<?php echo UPLOAD_URL . '/' . escape($sy['file_path']); ?>" target="_blank" class="badge badge-success" style="font-size: 11px;">
                                                <i class="fa fa-download"></i> সিলেবাস ডাউনলোড
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p style="color: var(--text-muted); font-size: 13px; font-style:italic;">এই শ্রেণির সিলেবাস এখনো আপলোড করা হয়নি।</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 30px;">কোনো শ্রেণি পাওয়া যায়নি।</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Switch tabs utility
function switchTab(evt, tabId) {
    // Hide all tab contents
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
    }

    // Deactivate all tab buttons
    const tabBtns = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < tabBtns.length; i++) {
        tabBtns[i].classList.remove("active");
        tabBtns[i].style.borderBottom = "3px solid transparent";
    }

    // Show selected tab content and active state
    document.getElementById(tabId).style.display = "block";
    evt.currentTarget.classList.add("active");
    evt.currentTarget.style.borderBottom = "3px solid var(--accent)";
}
</script>
