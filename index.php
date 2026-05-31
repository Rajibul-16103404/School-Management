<?php
/**
 * Public Home Page
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// Fetch quick stats
$total_students = 0;
$total_teachers = 0;
$total_sections = 0;

if ($pdo) {
    try {
        $total_students = $pdo->query("SELECT COUNT(*) FROM `students`")->fetchColumn();
        $total_teachers = $pdo->query("SELECT COUNT(*) FROM `teachers` WHERE `is_teacher` = 1")->fetchColumn();
        $total_sections = $pdo->query("SELECT COUNT(*) FROM `sections`")->fetchColumn();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fetch 3 latest notices
$latest_notices = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `notices` WHERE `is_published` = 1 ORDER BY `publish_date` DESC, `id` DESC LIMIT 3");
        $stmt->execute();
        $latest_notices = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fetch school welcome content
$school_mission = $school['mission_bn'] ?? 'আমাদের লক্ষ্য শিক্ষার্থীদের মানসম্মত শিক্ষা প্রদান করা।';
$school_vision = $school['vision_bn'] ?? 'একটি সুশৃঙ্খল ডিজিটাল প্রতিষ্ঠান গড়ে তোলা।';
?>

<!-- Announcement Ticker -->
<?php if (!empty($latest_notices)): ?>
<div class="ticker-wrap">
    <span class="ticker-title">সর্বশেষ সংবাদ</span>
    <div class="ticker-text">
        <?php 
        $ticker_items = [];
        foreach ($latest_notices as $notice) {
            $ticker_items[] = escape($notice['title_bn']) . ' (' . format_date($notice['publish_date']) . ')';
        }
        echo implode(' &nbsp; | &nbsp; ', $ticker_items);
        ?>
    </div>
</div>
<?php endif; ?>

<!-- Hero Banner Area -->
<div class="hero-slider">
    <div class="hero-content">
        <h2>স্বাগতম - <?php echo escape($sch_name_bn); ?></h2>
        <p><?php echo escape($sch_name_en); ?></p>
        <p style="font-size: 15px; margin-top: 15px; color: var(--accent);">ইআইআইএন (EIIN): <?php echo escape($sch_eiin); ?> | স্থাপন কাল: <?php echo escape($school['founding_year'] ?? '১৯৭১'); ?></p>
    </div>
</div>

<!-- Stats Counter Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa fa-graduation-cap"></i></div>
        <div class="stat-number"><?php echo escape($total_students); ?></div>
        <div class="stat-label">মোট শিক্ষার্থী</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa fa-chalkboard-user"></i></div>
        <div class="stat-number"><?php echo escape($total_teachers); ?></div>
        <div class="stat-label">মোট শিক্ষক</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa fa-sitemap"></i></div>
        <div class="stat-number"><?php echo escape($total_sections); ?></div>
        <div class="stat-label">অনুমোদিত শাখা</div>
    </div>
</div>

<div class="contact-grid">
    <!-- Notice Board Section -->
    <section class="card">
        <h2 class="card-title"><i class="fa fa-bullhorn" style="color: var(--accent);"></i> নোটিশ বোর্ড</h2>
        <?php if (!empty($latest_notices)): ?>
            <ul class="notice-list">
                <?php foreach ($latest_notices as $notice): ?>
                    <li class="notice-item">
                        <div class="notice-left">
                            <div class="notice-date-badge">
                                <span><?php echo date('d', strtotime($notice['publish_date'])); ?></span>
                                <?php echo date('M', strtotime($notice['publish_date'])); ?>
                            </div>
                            <div>
                                <a href="<?php echo BASE_URL; ?>/academics.php" class="notice-title"><?php echo escape($notice['title_bn']); ?></a>
                                <p style="font-size:12px; color: var(--text-muted); margin-top:3px;"><?php echo escape($notice['title_en']); ?></p>
                            </div>
                        </div>
                        <?php if ($notice['attachment']): ?>
                            <a href="<?php echo UPLOAD_URL . '/' . escape($notice['attachment']); ?>" target="_blank" class="badge badge-success"><i class="fa fa-download"></i> ডাউনলোড</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div style="text-align: right; margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>/academics.php" style="font-weight: bold; font-size:14px;"><i class="fa fa-list"></i> সকল নোটিশ দেখুন</a>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">কোনো নোটিশ পাওয়া যায়নি।</p>
        <?php endif; ?>
    </section>

    <!-- Welcome & Message Section -->
    <section class="card">
        <h2 class="card-title"><i class="fa fa-quote-left" style="color: var(--accent);"></i> প্রতিষ্ঠানের বাণী ও লক্ষ্য</h2>
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 16px; color: var(--primary); margin-bottom: 5px;">আমাদের লক্ষ্য (Mission)</h3>
            <p style="font-size: 14px; color: var(--text-color);"><?php echo nl2br(escape($school_mission)); ?></p>
        </div>
        <div>
            <h3 style="font-size: 16px; color: var(--primary); margin-bottom: 5px;">আমাদের স্বপ্ন (Vision)</h3>
            <p style="font-size: 14px; color: var(--text-color);"><?php echo nl2br(escape($school_vision)); ?></p>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
