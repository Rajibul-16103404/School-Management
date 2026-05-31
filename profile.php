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

<div class="card" style="padding: 0; overflow: visible;">
    <!-- Tab Navigation -->
    <div style="background-color: var(--primary-dark); border-radius: var(--radius-md) var(--radius-md) 0 0; display: flex; overflow-x: auto; border-bottom: 3px solid var(--accent);">
        <button class="tab-btn active" onclick="switchTab(event, 'info-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 16px; border-bottom: 3px solid var(--accent); transition: var(--transition); white-space: nowrap;">
            <i class="fa fa-school"></i> সাধারণ পরিচিতি (Overview)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'mission-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 16px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap;">
            <i class="fa fa-bullseye"></i> লক্ষ্য ও স্বপ্ন (Mission)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'map-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 16px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap;">
            <i class="fa fa-map-location-dot"></i> অবস্থান মানচিত্র (Map)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'gallery-tab')" style="padding: 15px 25px; background: none; border: none; color: white; font-weight: bold; cursor: pointer; font-family: var(--font-bn); font-size: 16px; border-bottom: 3px solid transparent; transition: var(--transition); white-space: nowrap;">
            <i class="fa fa-images"></i> ছবি গ্যালারি (Gallery)
        </button>
    </div>

    <!-- Tab Contents -->
    <div style="padding: 30px;">
        <!-- Tab 1: Info -->
        <div id="info-tab" class="tab-content active-content">
            <h3 style="font-size: 18px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa fa-school"></i> প্রতিষ্ঠান পরিচিতি (School Profile)
            </h3>
            
            <p style="font-size: 16px; line-height: 1.8; color: var(--text-color); margin-bottom: 20px;">
                <?php echo nl2br(escape($school['about_text_bn'] ?? 'আমাদের প্রতিষ্ঠানটি ১৯৭১ সালে প্রতিষ্ঠিত হয়। এটি নারায়ণগঞ্জ জেলার সোনারগাঁও উপজেলার একটি ঐতিহ্যবাহী শিক্ষাপ্রতিষ্ঠান। জাতীয় শিক্ষা ধারা ও নীতিমালার আলোকে শিক্ষার্থীদের মাঝে নৈতিক গুণাবলী বিকশিত করাই আমাদের লক্ষ্য।')); ?>
            </p>
            
            <div class="table-responsive" style="margin-top: 20px;">
                <table style="width: 100%;">
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

        <!-- Tab 2: Mission & Objectives -->
        <div id="mission-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 18px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa fa-bullseye"></i> লক্ষ্য ও উদ্দেশ্য (Mission & Objectives)
            </h3>
            
            <div style="margin-bottom: 20px; background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid var(--border-color);">
                <h4 style="font-size:16px; color: var(--primary); margin-bottom: 10px;"><i class="fa fa-crosshairs"></i> লক্ষ্য (Mission)</h4>
                <p style="font-size: 14.5px; margin-bottom: 10px; color: #1e293b; font-weight: 500;"><?php echo nl2br(escape($school_mission_bn)); ?></p>
                <p style="font-size: 13.5px; font-style: italic; color: var(--text-muted);"><?php echo nl2br(escape($school_mission_en)); ?></p>
            </div>
            
            <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid var(--border-color);">
                <h4 style="font-size:16px; color: var(--primary); margin-bottom: 10px;"><i class="fa fa-arrow-down-up-lock"></i> স্বপ্ন ও উদ্দেশ্য (Vision & Objectives)</h4>
                <p style="font-size: 14.5px; margin-bottom: 10px; color: #1e293b; font-weight: 500;"><?php echo nl2br(escape($school_objectives_bn)); ?></p>
                <p style="font-size: 13.5px; font-style: italic; color: var(--text-muted);"><?php echo nl2br(escape($school_objectives_en)); ?></p>
            </div>
        </div>

        <!-- Tab 3: Google Map Location -->
        <div id="map-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 18px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa fa-map-location-dot"></i> গুগল ম্যাপে আমাদের অবস্থান
            </h3>
            
            <?php if (!empty($school_map)): ?>
                <div style="border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                    <?php echo $school_map; // Safe output as this is raw HTML map iframe configured by admin ?>
                </div>
            <?php else: ?>
                <div style="background-color: #f1f5f9; height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); border-radius: 8px;">
                    ম্যাপ কনফিগার করা হয়নি।
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 4: Photo Gallery -->
        <div id="gallery-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 18px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa fa-images"></i> ছবি গ্যালারি (Photo Gallery)
            </h3>
            
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


<?php
require_once __DIR__ . '/includes/footer.php';
?>
