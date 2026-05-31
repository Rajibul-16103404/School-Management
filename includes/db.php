<?php
/**
 * Database Connection Wrapper
 * School Management Website
 */

require_once __DIR__ . '/../config.php';

$pdo = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Auto-migration: Ensure gallery column exists in schools table
    try {
        $pdo->query("SELECT `gallery` FROM `schools` LIMIT 1");
    } catch (PDOException $ex) {
        try {
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `gallery` TEXT NULL AFTER `map_embed`");
        } catch (PDOException $alterEx) {}
    }

    // Auto-migration: Ensure CMS columns exist in schools table
    try {
        $pdo->query("SELECT `headmaster_name_bn` FROM `schools` LIMIT 1");
    } catch (PDOException $ex) {
        try {
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `headmaster_name_bn` VARCHAR(255) NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `headmaster_name_en` VARCHAR(255) NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `headmaster_photo` VARCHAR(255) NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `headmaster_quote_bn` TEXT NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `headmaster_quote_en` TEXT NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `slider_data` LONGTEXT NULL");
            
            // Seed initial CMS values to the existing record
            $initial_sliders = [
                [
                    "image" => "slide_1.png",
                    "title_bn" => "সোনারগাঁও উচ্চ বিদ্যালয়",
                    "title_en" => "Sonargaon High School",
                    "subtitle_bn" => "ঐতিহ্যবাহী বিদ্যাপীঠ, নারায়ণগঞ্জের একটি অন্যতম আধুনিক শিক্ষাপ্রতিষ্ঠান।",
                    "subtitle_en" => "Traditional educational institution, a modern school in Narayanganj."
                ],
                [
                    "image" => "slide_2.png",
                    "title_bn" => "মানসম্মত শিক্ষা ও আধুনিক পরিবেশ",
                    "title_en" => "Quality Education & Modern Environment",
                    "subtitle_bn" => "শিক্ষার্থীদের সৃজনশীলতা, বুদ্ধিবৃত্তিক ও নৈতিক গুণাবলীর সুষম বিকাশ নিশ্চিত করা আমাদের অঙ্গীকার।",
                    "subtitle_en" => "We are committed to nurturing creativity, intelligence, and moral values."
                ],
                [
                    "image" => "slide_3.png",
                    "title_bn" => "সহশিক্ষা কার্যক্রম ও বিজ্ঞানমনস্ক শিক্ষা",
                    "title_en" => "Co-curricular Activities & Science-oriented Education",
                    "subtitle_bn" => "স্মার্ট বাংলাদেশ গঠনে যুগোপযোগী আইসিটি সমৃদ্ধ ও বাস্তবমুখী শিক্ষা প্রদান করা আমাদের অন্যতম লক্ষ্য।",
                    "subtitle_en" => "Our key goal is providing ICT-rich and practical education for a Smart Bangladesh."
                ]
            ];
            $slider_json = json_encode($initial_sliders, JSON_UNESCAPED_UNICODE);
            
            $pdo->exec("
                UPDATE `schools` SET 
                `headmaster_name_bn` = 'মোঃ রফিকুল ইসলাম',
                `headmaster_name_en` = 'Md. Rafiqul Islam',
                `headmaster_photo` = 'teacher_1.png',
                `headmaster_quote_bn` = 'শিক্ষা কেবল বইয়ের জ্ঞানার্জনে সীমাবদ্ধ নয়, বরং শিক্ষার্থীর আত্মিক, নৈতিক ও মানবিক গুণাবলীর সামগ্রিক উন্নয়ন সাধন করাই শিক্ষার আসল লক্ষ্য। আমরা শিক্ষার্থীদের বিজ্ঞানমনস্ক ও সুনাগরিক হিসেবে গড়ে তুলতে প্রতিশ্রুতিবদ্ধ।',
                `headmaster_quote_en` = 'Education is not limited to textbook knowledge, but rather to develop spiritual, moral and human qualities. We are committed to building science-oriented good citizens.',
                `slider_data` = " . $pdo->quote($slider_json) . "
                WHERE `id` = 1
            ");
        } catch (PDOException $alterEx) {}
    }

    // Auto-migration: Ensure footer and about content columns exist in schools table
    try {
        $pdo->query("SELECT `about_text_bn` FROM `schools` LIMIT 1");
    } catch (PDOException $ex) {
        try {
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `about_text_bn` TEXT NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `about_text_en` TEXT NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `footer_text_bn` TEXT NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `footer_text_en` TEXT NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `footer_copyright_bn` VARCHAR(255) NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `footer_copyright_en` VARCHAR(255) NULL");
            $pdo->exec("ALTER TABLE `schools` ADD COLUMN `footer_links` LONGTEXT NULL");
            
            // Seed initial values
            $initial_links = [
                ["title_bn" => "মাধ্যমিক ও উচ্চশিক্ষা অধিদপ্তর", "title_en" => "Directorate of Secondary and Higher Education", "url" => "https://dshe.gov.bd"],
                ["title_bn" => "শিক্ষা মন্ত্রণালয়", "title_en" => "Ministry of Education", "url" => "https://moedu.gov.bd"],
                ["title_bn" => "ঢাকা শিক্ষা বোর্ড", "title_en" => "Board of Intermediate and Secondary Education, Dhaka", "url" => "https://dhakaeducationboard.gov.bd"],
                ["title_bn" => "জাতীয় তথ্য বাতায়ন", "title_en" => "National Web Portal", "url" => "https://www.bangladesh.gov.bd"]
            ];
            $links_json = json_encode($initial_links, JSON_UNESCAPED_UNICODE);
            
            $pdo->exec("
                UPDATE `schools` SET 
                `about_text_bn` = 'আমাদের প্রতিষ্ঠানটি ১৯৭১ সালে প্রতিষ্ঠিত হয়। এটি নারায়ণগঞ্জ জেলার সোনারগাঁও উপজেলার একটি ঐতিহ্যবাহী শিক্ষাপ্রতিষ্ঠান। জাতীয় শিক্ষা ধারা ও নীতিমালার আলোকে শিক্ষার্থীদের মাঝে নৈতিক গুণাবলী বিকশিত করাই আমাদের লক্ষ্য।',
                `about_text_en` = 'Our institution was established in 1971. It is a traditional educational institution in Sonargaon Upazila of Narayanganj district. Our goal is to develop moral qualities among students in the light of national educational trends and policies.',
                `footer_text_bn` = 'আমাদের মূল লক্ষ্য শিক্ষার্থীদের মানবিক মূল্যবোধ সম্পন্ন সুনাগরিক হিসেবে গড়ে তোলা। মানসম্মত শিক্ষা নিশ্চিতকরণে আমরা সর্বদা প্রতিজ্ঞাবদ্ধ।',
                `footer_text_en` = 'Our main goal is to build students as good citizens with human values. We are always committed to ensuring quality education.',
                `footer_copyright_bn` = 'সোনারগাঁও উচ্চ বিদ্যালয়. সর্বস্বত্ব সংরক্ষিত।',
                `footer_copyright_en` = 'Sonargaon High School. All rights reserved.',
                `footer_links` = " . $pdo->quote($links_json) . "
                WHERE `id` = 1
            ");
        } catch (PDOException $alterEx) {}
    }

    // Auto-migration: Ensure remember_token column exists in users table
    try {
        $pdo->query("SELECT `remember_token` FROM `users` LIMIT 1");
    } catch (PDOException $ex) {
        try {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(255) NULL AFTER `email`");
        } catch (PDOException $alterEx) {}
    }

    // Auto-migration: Ensure menus table exists and is seeded
    try {
        $pdo->query("SELECT 1 FROM `menus` LIMIT 1");
    } catch (PDOException $ex) {
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `menus` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `title_bn` VARCHAR(100) NOT NULL,
                  `title_en` VARCHAR(100) NOT NULL,
                  `url` VARCHAR(255) NOT NULL,
                  `parent_id` INT NULL,
                  `sort_order` INT DEFAULT 0,
                  FOREIGN KEY (`parent_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Seed initial menus
            $pdo->exec("
                INSERT INTO `menus` (`id`, `title_bn`, `title_en`, `url`, `parent_id`, `sort_order`) VALUES
                (1, 'হোম', 'Home', '/', NULL, 1),
                (2, 'আমাদের সম্পর্কে', 'About Us', '#', NULL, 2),
                (3, 'পরিচিতি', 'Profile', '/profile', 2, 1),
                (4, 'অনুমতি ও স্বীকৃতি', 'Recognition', '/recognition', 2, 2),
                (5, 'একাডেমিক', 'Academics', '#', NULL, 3),
                (6, 'শিক্ষার্থীর তথ্য', 'Students Info', '/students', 5, 1),
                (7, 'অনুমোদিত শাখা', 'Approved Sections', '/sections', 5, 2),
                (8, 'পাঠদান তথ্য', 'Academics Info', '/academics', 5, 3),
                (9, 'জনবল', 'Personnel', '#', NULL, 4),
                (10, 'শিক্ষক-কর্মচারী', 'Teachers & Staff', '/teachers', 9, 1),
                (11, 'ব্যবস্থাপনা কমিটি', 'Management Committee', '/committee', 9, 2),
                (12, 'এমপিও ও জাতীয়করণ', 'MPO & Nationalization', '/mpo', NULL, 5),
                (13, 'যোগাযোগ', 'Contact', '/contact', NULL, 6)
            ");
        } catch (PDOException $createEx) {}
    }
} catch (PDOException $e) {
    // If database connection fails and we are not in setup.php, output warning
    $currentScript = basename($_SERVER['SCRIPT_NAME']);
    if ($currentScript !== 'setup.php') {
        header("Content-Type: text/html; charset=utf-8");
        ?>
        <!DOCTYPE html>
        <html lang="bn">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Error</title>
            <style>
                body {
                    background: #0f172a;
                    color: #f8fafc;
                    font-family: system-ui, sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                }
                .card {
                    background: #1e293b;
                    border: 1px solid #ef4444;
                    padding: 30px;
                    border-radius: 12px;
                    max-width: 500px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                }
                h1 { color: #ef4444; font-size: 20px; margin-top: 0; }
                p { line-height: 1.6; color: #94a3b8; font-size: 14px; }
                a {
                    display: inline-block;
                    background: #d4af37;
                    color: #000;
                    padding: 10px 20px;
                    text-decoration: none;
                    font-weight: bold;
                    border-radius: 6px;
                    margin-top: 15px;
                }
                a:hover { background: #bda02b; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>ডাটাবেজ সংযোগে ব্যর্থতা!</h1>
                <p>ডাটাবেজের সাথে সংযোগ স্থাপন করা সম্ভব হয়নি। অনুগ্রহ করে <code>config.php</code> ফাইলের কনফিগারেশন চেক করুন অথবা নিচের বাটনে ক্লিক করে ডেটাবেজ সেটআপ সম্পন্ন করুন।</p>
                <p style="background: rgba(0,0,0,0.2); padding: 10px; border-radius: 6px; font-family: monospace; font-size: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    Error: <?php echo htmlspecialchars($e->getMessage()); ?>
                </p>
                <a href="<?php echo BASE_URL; ?>/setup.php">সেটআপ উইজার্ড চালু করুন</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
