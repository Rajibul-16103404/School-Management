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

<!-- Hero Showcase Container (Slider & Headmaster Side-by-Side) -->
<div class="showcase-container">
    <!-- Main Image Slider -->
    <div class="main-slider">
        <div class="slider-wrapper">
            <!-- Slide 1 -->
            <div class="slide active" style="background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.55)), url('<?php echo BASE_URL; ?>/assets/images/slide_1.png');">
                <div class="slide-content">
                    <h2>সোনারগাঁও উচ্চ বিদ্যালয়</h2>
                    <p>ঐতিহ্যবাহী বিদ্যাপীঠ, নারায়ণগঞ্জের একটি অন্যতম আধুনিক শিক্ষাপ্রতিষ্ঠান।</p>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.55)), url('<?php echo BASE_URL; ?>/assets/images/slide_2.png');">
                <div class="slide-content">
                    <h2>মানসম্মত শিক্ষা ও আধুনিক পরিবেশ</h2>
                    <p>শিক্ষার্থীদের সৃজনশীলতা, বুদ্ধিবৃত্তিক ও নৈতিক গুণাবলীর সুষম বিকাশ নিশ্চিত করা আমাদের অঙ্গীকার।</p>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.55)), url('<?php echo BASE_URL; ?>/assets/images/slide_3.png');">
                <div class="slide-content">
                    <h2>সহশিক্ষা কার্যক্রম ও বিজ্ঞানমনস্ক শিক্ষা</h2>
                    <p>স্মার্ট বাংলাদেশ গঠনে যুগোপযোগী আইসিটি সমৃদ্ধ ও বাস্তবমুখী শিক্ষা প্রদান করা আমাদের অন্যতম লক্ষ্য।</p>
                </div>
            </div>
        </div>
        
        <!-- Slider Navigation Controls -->
        <button class="slider-nav-btn prev-btn" aria-label="Previous slide">&#10094;</button>
        <button class="slider-nav-btn next-btn" aria-label="Next slide">&#10095;</button>
        
        <!-- Slider Dots -->
        <div class="slider-dots">
            <span class="dot active" data-index="0"></span>
            <span class="dot" data-index="1"></span>
            <span class="dot" data-index="2"></span>
        </div>
    </div>
    
    <!-- Headmaster Info Beside Slider -->
    <div class="headmaster-card">
        <div class="headmaster-card-header">
            <h3><i class="fa fa-user-tie"></i> প্রধান শিক্ষকের বাণী</h3>
        </div>
        <div class="headmaster-card-body">
            <div class="hm-avatar-wrapper">
                <img src="<?php echo BASE_URL; ?>/uploads/photos/teacher_1.png" alt="Md. Rafiqul Islam, Head Teacher" class="hm-avatar">
            </div>
            <h4>মোঃ রফিকুল ইসলাম</h4>
            <p class="hm-title">প্রধান শিক্ষক, সোনারগাঁও উচ্চ বিদ্যালয়</p>
            <div class="hm-quote">
                "শিক্ষা কেবল বইয়ের জ্ঞানার্জনে সীমাবদ্ধ নয়, বরং শিক্ষার্থীর আত্মিক, নৈতিক ও মানবিক গুণাবলীর সামগ্রিক উন্নয়ন সাধন করাই শিক্ষার আসল লক্ষ্য। আমরা শিক্ষার্থীদের বিজ্ঞানমনস্ক ও সুনাগরিক হিসেবে গড়ে তুলতে প্রতিশ্রুতিবদ্ধ।"
            </div>
            <a href="<?php echo BASE_URL; ?>/teachers" class="hm-readmore-btn"><i class="fa fa-id-card"></i> পূর্ণ প্রোফাইল দেখুন</a>
        </div>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    let currentSlide = 0;
    const slideInterval = 5000; // 5 seconds auto-cycle
    let autoSlideTimer;
    
    function showSlide(index) {
        // Handle wrapping
        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }
        
        // Update slides visibility
        slides.forEach((slide, i) => {
            if (i === currentSlide) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });
        
        // Update dots state
        dots.forEach((dot, i) => {
            if (i === currentSlide) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    function startAutoSlide() {
        stopAutoSlide();
        autoSlideTimer = setInterval(nextSlide, slideInterval);
    }
    
    function stopAutoSlide() {
        if (autoSlideTimer) {
            clearInterval(autoSlideTimer);
        }
    }
    
    // Add Click Events for controls
    nextBtn.addEventListener('click', () => {
        nextSlide();
        startAutoSlide(); // Reset timer on click
    });
    
    prevBtn.addEventListener('click', () => {
        prevSlide();
        startAutoSlide(); // Reset timer on click
    });
    
    // Add Click Events for dots
    dots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            const index = parseInt(e.target.getAttribute('data-index'));
            showSlide(index);
            startAutoSlide(); // Reset timer on click
        });
    });
    
    // Start the slider
    startAutoSlide();
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
