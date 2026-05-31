<?php
/**
 * Public School Profile Page
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// School settings are loaded in includes/header.php under $school
$school_mission_bn = $school['mission_bn'] ?? '';
$school_mission_en = $school['mission_en'] ?? '';
$school_vision_bn = $school['vision_bn'] ?? '';
$school_vision_en = $school['vision_en'] ?? '';
$school_objectives_bn = $school['objectives_bn'] ?? '';
$school_objectives_en = $school['objectives_en'] ?? '';
$school_map = $school['map_embed'] ?? '';

// Decode gallery photos JSON
$gallery_photos = [];
if (!empty($school['gallery'])) {
    $gallery_photos = json_decode($school['gallery'], true) ?: [];
}
?>

<div class="card">
    <h2 class="card-title"><i class="fa fa-school" style="color: var(--accent);"></i> প্রতিষ্ঠান পরিচিতি (School Profile)</h2>
    
    <div style="margin-bottom: 30px;">
        <p style="font-size: 16px; line-height: 1.8; color: var(--text-color); margin-bottom: 20px;">
            আমাদের প্রতিষ্ঠানটি <strong><?php echo escape($school['founding_year'] ?? '১৯৭১'); ?></strong> সালে প্রতিষ্ঠিত হয়। এটি নারায়ণগঞ্জ জেলার সোনারগাঁও উপজেলার একটি ঐতিহ্যবাহী শিক্ষাপ্রতিষ্ঠান। জাতীয় শিক্ষা ধারা ও নীতিমালার আলোকে শিক্ষার্থীদের মাঝে নৈতিক গুণাবলী বিকশিত করাই আমাদের লক্ষ্য।
        </p>
        
        <table class="table-responsive" style="margin-top: 20px; width: 100%;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: var(--primary-dark);">এক নজরে প্রতিষ্ঠান</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: bold; width: 35%;">প্রতিষ্ঠানের নাম (বাংলা)</td>
                    <td><?php echo escape($sch_name_bn); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">প্রতিষ্ঠানের নাম (ইংরেজি)</td>
                    <td><?php echo escape($sch_name_en); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">ইআইআইএন (EIIN)</td>
                    <td><?php echo escape($sch_eiin); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">স্থাপিত</td>
                    <td><?php echo escape($school['founding_year'] ?? '১৯৭১'); ?> খ্রিষ্টাব্দ</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">ফোন / মোবাইল</td>
                    <td><?php echo escape($sch_phone); ?> / <?php echo escape($sch_mobile); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">ইমেইল</td>
                    <td><?php echo escape($sch_email); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="contact-grid" style="margin-bottom: 30px;">
    <!-- Mission & Vision Details -->
    <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title"><i class="fa fa-bullseye" style="color: var(--accent);"></i> লক্ষ্য ও উদ্দেশ্য (Mission & Objectives)</h2>
        
        <div style="margin-bottom: 15px;">
            <h3 style="font-size:15px; color: var(--primary);">লক্ষ্য (Mission)</h3>
            <p style="font-size: 14px; margin-bottom: 10px; color: #475569;"><?php echo nl2br(escape($school_mission_bn)); ?></p>
            <p style="font-size: 13px; font-style: italic; color: var(--text-muted);"><?php echo nl2br(escape($school_mission_en)); ?></p>
        </div>
        
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">
        
        <div>
            <h3 style="font-size:15px; color: var(--primary);">উদ্দেশ্য (Objectives)</h3>
            <p style="font-size: 14px; margin-bottom: 10px; color: #475569;"><?php echo nl2br(escape($school_objectives_bn)); ?></p>
            <p style="font-size: 13px; font-style: italic; color: var(--text-muted);"><?php echo nl2br(escape($school_objectives_en)); ?></p>
        </div>
    </div>

    <!-- Location Map Embed -->
    <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title"><i class="fa fa-map-location-dot" style="color: var(--accent);"></i> গুগল ম্যাপে আমাদের অবস্থান</h2>
        <?php if (!empty($school_map)): ?>
            <div style="border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm);">
                <?php echo $school_map; // Safe output as this is raw HTML map iframe configured by admin ?>
            </div>
        <?php else: ?>
            <div style="background-color: #f1f5f9; height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); border-radius: 8px;">
                ম্যাপ কনফিগার করা হয়নি।
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Photo Gallery -->
<div class="card">
    <h2 class="card-title"><i class="fa fa-images" style="color: var(--accent);"></i> ছবি গ্যালারি (Photo Gallery)</h2>
    <?php if (!empty($gallery_photos)): ?>
        <div class="gallery-grid">
            <?php foreach ($gallery_photos as $photo): ?>
                <div class="gallery-item">
                    <img src="<?php echo UPLOAD_URL . '/' . escape($photo); ?>" alt="School Photo" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 25px;">গ্যালারিতে কোনো ছবি পাওয়া যায়নি।</p>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
