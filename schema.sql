-- DB Schema for School Management Website
-- Conforming to DSHE regulations in Bangladesh

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `recognition_docs`;
DROP TABLE IF EXISTS `syllabi`;
DROP TABLE IF EXISTS `routines`;
DROP TABLE IF EXISTS `notices`;
DROP TABLE IF EXISTS `committee_members`;
DROP TABLE IF EXISTS `schools`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `teachers`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Teachers & Staff Table
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_bn` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `designation_bn` VARCHAR(100) NOT NULL,
  `designation_en` VARCHAR(100) NOT NULL,
  `subject_bn` VARCHAR(100) NULL,
  `subject_en` VARCHAR(100) NULL,
  `mpo_index` VARCHAR(50) NULL,
  `mpo_scale` VARCHAR(50) NULL,
  `mpo_date` DATE NULL,
  `nid` VARCHAR(50) NOT NULL,
  `qualification_bn` TEXT NULL,
  `qualification_en` TEXT NULL,
  `photo` VARCHAR(255) NULL,
  `joining_date` DATE NOT NULL,
  `is_teacher` TINYINT(1) DEFAULT 1, -- 1 for teacher, 0 for staff
  `staff_type` VARCHAR(50) NULL, -- '3rd class', '4th class' (for staff)
  `department` VARCHAR(100) NULL, -- 'Science', 'Humanities', 'Commerce', 'General'
  `phone` VARCHAR(20) NULL,
  `email` VARCHAR(100) NULL,
  `status` VARCHAR(20) DEFAULT 'Active', -- 'Active', 'Retired', 'Suspended'
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Classes Table
CREATE TABLE IF NOT EXISTS `classes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_bn` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100) NOT NULL,
  `numeric_name` INT NOT NULL,
  `class_teacher_id` INT NULL,
  FOREIGN KEY (`class_teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sections Table
CREATE TABLE IF NOT EXISTS `sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NOT NULL,
  `name_bn` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100) NOT NULL,
  `approved_sections_count` INT DEFAULT 1,
  `existing_sections_count` INT DEFAULT 1,
  `remark` VARCHAR(255) NULL,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Students Table
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_bn` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `class_id` INT NOT NULL,
  `section_id` INT NOT NULL,
  `roll` INT NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `dob` DATE NOT NULL,
  `guardian_name_bn` VARCHAR(255) NOT NULL,
  `guardian_name_en` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `photo` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. School Profile/Settings Table
CREATE TABLE IF NOT EXISTS `schools` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_bn` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `eiin` VARCHAR(50) NOT NULL,
  `founding_year` INT NOT NULL,
  `logo` VARCHAR(255) NULL,
  `mission_bn` TEXT NULL,
  `mission_en` TEXT NULL,
  `vision_bn` TEXT NULL,
  `vision_en` TEXT NULL,
  `objectives_bn` TEXT NULL,
  `objectives_en` TEXT NULL,
  `map_embed` TEXT NULL,
  `gallery` TEXT NULL, -- JSON array of photo file names/paths
  `phone` VARCHAR(50) NULL,
  `mobile` VARCHAR(50) NULL,
  `email` VARCHAR(100) NULL,
  `fax` VARCHAR(50) NULL,
  `address_bn` TEXT NULL,
  `address_en` TEXT NULL,
  `mpo_status` VARCHAR(50) DEFAULT 'Non-MPO', -- 'MPO', 'Non-MPO', 'Nationalized'
  `mpo_number` VARCHAR(50) NULL,
  `mpo_date` DATE NULL,
  `nationalization_status` VARCHAR(100) NULL,
  `nationalization_date` DATE NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Committee Members Table
CREATE TABLE IF NOT EXISTS `committee_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_bn` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `designation_bn` VARCHAR(100) NOT NULL, -- e.g., সভাপতি, সদস্য সচিব
  `designation_en` VARCHAR(100) NOT NULL, -- e.g., President, Member Secretary
  `profession_bn` VARCHAR(255) NULL,
  `profession_en` VARCHAR(255) NULL,
  `contact` VARCHAR(50) NULL,
  `photo` VARCHAR(255) NULL,
  `session_start` VARCHAR(10) NOT NULL,
  `session_end` VARCHAR(10) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Notice Board Table
CREATE TABLE IF NOT EXISTS `notices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title_bn` VARCHAR(255) NOT NULL,
  `title_en` VARCHAR(255) NOT NULL,
  `content_bn` TEXT NULL,
  `content_en` TEXT NULL,
  `attachment` VARCHAR(255) NULL,
  `publish_date` DATE NOT NULL,
  `is_published` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Routines Table
CREATE TABLE IF NOT EXISTS `routines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Syllabi Table
CREATE TABLE IF NOT EXISTS `syllabi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NOT NULL,
  `subject_bn` VARCHAR(100) NOT NULL,
  `subject_en` VARCHAR(100) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Recognition & Permission Documents Table
CREATE TABLE IF NOT EXISTS `recognition_docs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `permission_date` DATE NOT NULL,
  `recognition_date` DATE NOT NULL,
  `recognition_number` VARCHAR(100) NOT NULL,
  `issuing_authority_bn` VARCHAR(255) NOT NULL,
  `issuing_authority_en` VARCHAR(255) NOT NULL,
  `document_path` VARCHAR(255) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Admin Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('superadmin', 'headteacher', 'staff') NOT NULL,
  `name_bn` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(255) NOT NULL,
  `details` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================================================================
-- SEED INITIAL DATA
-- =========================================================================

-- Seed default school settings
INSERT INTO `schools` (`id`, `name_bn`, `name_en`, `eiin`, `founding_year`, `logo`, `mission_bn`, `mission_en`, `vision_bn`, `vision_en`, `objectives_bn`, `objectives_en`, `phone`, `mobile`, `email`, `address_bn`, `address_en`, `mpo_status`, `mpo_number`, `mpo_date`, `map_embed`) VALUES
(1, 'সোনারগাঁও উচ্চ বিদ্যালয়', 'Sonargaon High School', '123456', 1971, NULL, 
'আমাদের লক্ষ্য শিক্ষার্থীদের মাঝে নৈতিক মূল্যবোধ, বিজ্ঞানমনস্ক দৃষ্টিভঙ্গি এবং দেশপ্রেম জাগ্রত করার মাধ্যমে সুনাগরিক হিসেবে গড়ে তোলা।', 
'Our mission is to foster moral values, scientific mindset, and patriotism in students to build them as responsible citizens.', 
'একটি আদর্শ ও আধুনিক ডিজিটাল শিক্ষাপ্রতিষ্ঠান হিসেবে আত্মপ্রকাশ করা এবং শতভাগ মানসম্মত শিক্ষা নিশ্চিত করা।', 
'To emerge as an ideal, modern digital educational institution ensuring 100% quality education.', 
'১. মানসম্মত শিক্ষাদান নিশ্চিত করা।\n২. শৃঙ্খলা ও নিয়মানুবর্তিতা চর্চা।\n৩. সহশিক্ষা কার্যক্রমের প্রসার ঘটানো।', 
'1. Ensuring quality education.\n2. Practicing discipline and order.\n3. Promoting co-curricular activities.', 
'+৮৮০২১২৩৪৫৬', '+৮৮০১৭১২৩৪৫৬৭৮', 'info@sonargaonhighschool.edu.bd', 'সোনারগাঁও, নারায়ণগঞ্জ, ঢাকা', 'Sonargaon, Narayanganj, Dhaka', 'MPO', 'MPO-987654321', '1995-03-01', 
'<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14619.539828359239!2d90.59604314999999!3d23.644265699999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b11eb0aefd9b%3A0xc39f993d3957ebbb!2sSonargaon!5e0!3m2!1sen!2sbd!4v1716942000000!5m2!1sen!2sbd" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>');

-- Seed default admin user (username: admin, password: Admin@123456)
INSERT INTO `users` (`id`, `username`, `password`, `role`, `name_bn`, `name_en`, `email`) VALUES
(1, 'admin', '$2y$12$cELO4H7gZM3BbLLPK14BK.PF6sGrVyWZos9L0KltkN6BfZ5L36gNy', 'superadmin', 'প্রধান অ্যাডমিন', 'System Administrator', 'admin@school.gov.bd');

-- Seed default teachers for referencing
INSERT INTO `teachers` (`id`, `name_bn`, `name_en`, `designation_bn`, `designation_en`, `subject_bn`, `subject_en`, `mpo_index`, `mpo_scale`, `mpo_date`, `nid`, `qualification_bn`, `qualification_en`, `photo`, `joining_date`, `is_teacher`, `department`, `phone`, `email`) VALUES
(1, 'মোঃ রফিকুল ইসলাম', 'Md. Rafiqul Islam', 'প্রধান শিক্ষক', 'Head Teacher', 'গণিত', 'Mathematics', 'T-1002030', 'Grade 7 (Scale: 29000-63000)', '1998-05-10', '1980671234567', 'এম.এসসি (গণিত), বি.এড (১ম শ্রেণি)', 'M.Sc (Mathematics), B.Ed (1st Class)', 'photos/teacher_1.png', '2010-01-15', 1, 'General', '01711223344', 'rafiq@school.edu.bd'),
(2, 'মোসাম্মাৎ ফাতেমা আক্তার', 'Mst. Fatema Akter', 'সহকারী প্রধান শিক্ষক', 'Assistant Head Teacher', 'ইংরেজি', 'English', 'T-2003040', 'Grade 8 (Scale: 23000-55470)', '2002-09-20', '1985671234568', 'এম.এ (ইংরেজি), এম.এড', 'M.A (English), M.Ed', 'photos/teacher_2.png', '2012-03-01', 1, 'General', '01722334455', 'fatema@school.edu.bd'),
(3, 'আব্দুল করিম', 'Abdul Karim', 'সহকারী শিক্ষক', 'Assistant Teacher', 'পদার্থবিজ্ঞান', 'Physics', 'T-3004050', 'Grade 9 (Scale: 22000-53060)', '2008-01-12', '1990671234569', 'বি.এসসি (অনার্স), এম.এসসি (পদার্থবিজ্ঞান)', 'B.Sc (Hons), M.Sc (Physics)', 'photos/teacher_3.png', '2015-08-01', 1, 'Science', '01733445566', 'karim@school.edu.bd'),
(4, 'মোসাম্মাৎ সালমা বেগম', 'Mst. Salma Begum', 'সহকারী শিক্ষক', 'Assistant Teacher', 'বাংলা', 'Bangla', 'T-4005060', 'Grade 9 (Scale: 22000-53060)', '2010-02-15', '1992671234570', 'বি.এ (অনার্স), এম.এ (বাংলা)', 'B.A (Hons), M.A (Bangla)', 'photos/teacher_4.png', '2016-01-10', 1, 'General', '01744556677', 'salma@school.edu.bd'),
(5, 'মোঃ আবদুর রহমান', 'Md. Abdur Rahman', 'সহকারী শিক্ষক', 'Assistant Teacher', 'ইংরেজি', 'English', 'T-5006070', 'Grade 9 (Scale: 22000-53060)', '2012-04-18', '1991671234571', 'বি.এ (অনার্স), এম.এ (ইংরেজি)', 'B.A (Hons), M.A (English)', 'photos/teacher_5.png', '2017-03-01', 1, 'General', '01755667788', 'rahman@school.edu.bd'),
(6, 'মোঃ শফিকুল ইসলাম', 'Md. Shafiqul Islam', 'সহকারী শিক্ষক', 'Assistant Teacher', 'গণিত', 'Mathematics', 'T-6007080', 'Grade 9 (Scale: 22000-53060)', '2013-05-22', '1993671234572', 'বি.এসসি (অনার্স), এম.এসসি (গণিত)', 'B.Sc (Hons), M.Sc (Math)', 'photos/teacher_6.png', '2018-02-15', 1, 'Science', '01766778899', 'shafiq@school.edu.bd'),
(7, 'মোসাম্মাৎ রেবেকা সুলতানা', 'Mst. Rebeka Sultana', 'সহকারী শিক্ষক', 'Assistant Teacher', 'রসায়ন', 'Chemistry', 'T-7008090', 'Grade 9 (Scale: 22000-53060)', '2014-06-20', '1994671234573', 'বি.এসসি (অনার্স), এম.এসসি (রসায়ন)', 'B.Sc (Hons), M.Sc (Chemistry)', 'photos/teacher_7.png', '2019-04-01', 1, 'Science', '01777889900', 'rebeka@school.edu.bd'),
(8, 'মোঃ জাহাঙ্গীর আলম', 'Md. Jahangir Alam', 'সহকারী শিক্ষক', 'Assistant Teacher', 'জীববিজ্ঞান', 'Biology', 'T-8009100', 'Grade 9 (Scale: 22000-53060)', '2015-08-25', '1995671234574', 'বি.এসসি (অনার্স), এম.এসসি (উদ্ভিদবিজ্ঞান)', 'B.Sc (Hons), M.Sc (Botany)', 'photos/teacher_8.png', '2020-05-01', 1, 'Science', '01788990011', 'jahangir@school.edu.bd'),
(9, 'মোঃ কামরুজ্জামান', 'Md. Kamruzzaman', 'সহকারী শিক্ষক', 'Assistant Teacher', '정보 ও যোগাযোগ প্রযুক্তি', 'ICT', 'T-9010110', 'Grade 10 (Scale: 16000-38640)', '2016-10-10', '1996671234575', 'বি.এসসি (সিএসই)', 'B.Sc (CSE)', 'photos/teacher_9.png', '2021-01-10', 1, 'General', '01799001122', 'kamruzzaman@school.edu.bd'),
(10, 'মোসাম্মাৎ রোকেয়া খানম', 'Mst. Rokeya Khanam', 'সহকারী শিক্ষক', 'Assistant Teacher', 'সমাজ বিজ্ঞান', 'Social Science', 'T-1011120', 'Grade 9 (Scale: 22000-53060)', '2005-03-12', '1987671234576', 'বি.এ (অনার্স), এম.এ (ইতিহাস)', 'B.A (Hons), M.A (History)', 'photos/teacher_10.png', '2011-09-01', 1, 'Humanities', '01710112233', 'rokeya@school.edu.bd'),
(11, 'মোঃ আবু বকর', 'Md. Abu Bakar', 'সহকারী শিক্ষক', 'Assistant Teacher', 'ইতিহাস', 'History', 'T-1112130', 'Grade 9 (Scale: 22000-53060)', '2008-07-15', '1988671234577', 'বি.এ (অনার্স), এম.এ (ইতিহাস)', 'B.A (Hons), M.A (History)', 'photos/teacher_11.png', '2013-06-01', 1, 'Humanities', '01720223344', 'bakar@school.edu.bd'),
(12, 'মোঃ মোস্তফা কামাল', 'Md. Mostafa Kamal', 'সহকারী শিক্ষক', 'Assistant Teacher', 'ভূগোল', 'Geography', 'T-1213140', 'Grade 9 (Scale: 22000-53060)', '2009-11-20', '1989671234578', 'বি.এসসি (অনার্স), এম.এসসি (ভূগোল)', 'B.Sc (Hons), M.Sc (Geography)', 'photos/teacher_12.png', '2014-08-15', 1, 'Humanities', '01730334455', 'mostafa@school.edu.bd'),
(13, 'মোসাম্মাৎ নাহিদা পারভীন', 'Mst. Nahida Parvin', 'সহকারী শিক্ষক', 'Assistant Teacher', 'ধর্ম শিক্ষা', 'Religion', 'T-1314150', 'Grade 10 (Scale: 16000-38640)', '2011-01-25', '1990671234579', 'কামিল (হাদিস)', 'Kamil (Hadith)', 'photos/teacher_13.png', '2015-02-01', 1, 'General', '01740445566', 'nahida@school.edu.bd'),
(14, 'মোঃ সাইদুল ইসলাম', 'Md. Saidul Islam', 'সহকারী শিক্ষক', 'Assistant Teacher', 'শারীরিক শিক্ষা', 'Physical Education', 'T-1415160', 'Grade 10 (Scale: 16000-38640)', '2012-05-18', '1991671234580', 'বি.পি.এড', 'B.P.Ed', 'photos/teacher_14.png', '2016-03-01', 1, 'General', '01750556677', 'saidul@school.edu.bd'),
(15, 'মোঃ দেলোয়ার হোসেন', 'Md. Delwar Hossain', 'সহকারী শিক্ষক', 'Assistant Teacher', 'চারু ও কারুকলা', 'Arts & Crafts', 'T-1516170', 'Grade 10 (Scale: 16000-38640)', '2014-09-22', '1992671234581', 'বি.এফ.এ (অনার্স)', 'B.F.A (Hons)', 'photos/teacher_15.png', '2018-05-15', 1, 'General', '01760667788', 'delwar@school.edu.bd'),
(16, 'মোঃ মিজানুর রহমান', 'Md. Mizanur Rahman', 'উচ্চমান সহকারী', 'Senior Clerk', NULL, NULL, 'S-1617180', 'Grade 14 (Scale: 10200-24680)', '2005-02-10', '1982671234582', 'এইচ.এস.সি', 'H.S.C', 'photos/teacher_16.png', '2008-01-15', 0, 'General', '01770778899', 'mizan@school.edu.bd'),
(17, 'মোঃ আলমগীর হোসেন', 'Md. Alamgir Hossain', 'অফিস সহকারী', 'Office Assistant', NULL, NULL, 'S-1718190', 'Grade 16 (Scale: 9300-22490)', '2008-06-15', '1985671234583', 'এইচ.এস.সি', 'H.S.C', 'photos/teacher_17.png', '2010-03-01', 0, 'General', '01780889900', 'alamgir@school.edu.bd'),
(18, 'মোসাম্মাৎ কোহিনূর বেগম', 'Mst. Kohinur Begum', 'গ্রন্থাগারিক', 'Librarian', NULL, NULL, 'T-1819200', 'Grade 10 (Scale: 16000-38640)', '2010-09-12', '1987671234584', 'গ্রন্থাগার বিজ্ঞানে ডিপ্লোমা', 'Diploma in Library Science', 'photos/teacher_18.png', '2012-05-15', 1, 'General', '01790990011', 'kohinur@school.edu.bd'),
(19, 'মোঃ জিল্লুর রহমান', 'Md. Zillur Rahman', 'ল্যাব সহকারী', 'Lab Assistant', NULL, NULL, 'S-1920210', 'Grade 18 (Scale: 8800-21310)', '2012-11-20', '1990671234585', 'এস.এস.সি (বিজ্ঞান)', 'S.S.C (Science)', 'photos/teacher_19.png', '2015-08-01', 0, 'General', '01710224466', 'zillur@school.edu.bd'),
(20, 'মোঃ নুরুল ইসলাম', 'Md. Nurul Islam', 'দপ্তরি', 'Peon', NULL, NULL, NULL, 'Grade 20 (Scale: 8250-20010)', NULL, '1992671234586', 'অষ্টম শ্রেণী পাস', 'Class Eight Pass', 'photos/teacher_20.png', '2016-09-01', 0, 'General', '01720335577', 'nurul@school.edu.bd');

-- Seed default class structures (6th to 10th grade)
INSERT INTO `classes` (`id`, `name_bn`, `name_en`, `numeric_name`, `class_teacher_id`) VALUES
(1, 'ষষ্ঠ শ্রেণি', 'Class Six', 6, 3),
(2, 'সপ্তম শ্রেণি', 'Class Seven', 7, NULL),
(3, 'অষ্টম শ্রেণি', 'Class Eight', 8, NULL),
(4, 'নবম শ্রেণি', 'Class Nine', 9, 2),
(5, 'দশম শ্রেণি', 'Class Ten', 10, 1);

-- Seed default sections/branches per class
INSERT INTO `sections` (`id`, `class_id`, `name_bn`, `name_en`, `approved_sections_count`, `existing_sections_count`, `remark`) VALUES
(1, 1, 'ক শাখা (পদ্মা)', 'Section A (Padma)', 2, 2, 'Approved by DSHE'),
(2, 1, 'খ শাখা (মেঘনা)', 'Section B (Meghna)', 2, 1, 'Shortage of students'),
(3, 2, 'ক শাখা', 'Section A', 1, 1, 'Single section approved'),
(4, 3, 'ক শাখা', 'Section A', 1, 1, 'Single section approved'),
(5, 4, 'বিজ্ঞান শাখা', 'Science Group', 1, 1, 'Approved'),
(6, 4, 'মানবিক শাখা', 'Humanities Group', 1, 1, 'Approved'),
(7, 5, 'বিজ্ঞান শাখা', 'Science Group', 1, 1, 'Approved'),
(8, 5, 'মানবিক শাখা', 'Humanities Group', 1, 1, 'Approved');

-- Seed initial students data (used for dynamic statistical charts/tables)
INSERT INTO `students` (`name_bn`, `name_en`, `class_id`, `section_id`, `roll`, `gender`, `dob`, `guardian_name_bn`, `guardian_name_en`, `mobile`, `photo`) VALUES
('মোঃ তানভীর হোসেন', 'Md. Tanvir Hossain', 4, 5, 1, 'Male', '2011-09-18', 'মোঃ আবুল হোসেন', 'Md. Abul Hossain', '01911111117', 'photos/student_boy.png'),
('আফরিন সুলতানা', 'Afrin Sultana', 5, 7, 1, 'Female', '2010-01-30', 'মোঃ সুলতান আহমেদ', 'Md. Sultan Ahmed', '01911111118', 'photos/student_girl.png'),
('মোঃ রাশেদুল ইসলাম', 'Md. Rashedul Islam', 1, 2, 1, 'Male', '2014-04-10', 'মোঃ জহিরুল ইসলাম', 'Md. Johirul Islam', '01911111119', 'photos/student_boy.png'),
('সুমি আক্তার', 'Sumi Akter', 1, 2, 2, 'Female', '2014-06-15', 'মোঃ রফিকুল ইসলাম', 'Md. Rafiqul Islam', '01911111120', 'photos/student_girl.png'),
('মোঃ আরিয়ান খান', 'Md. Ariyan Khan', 2, 3, 3, 'Male', '2013-09-22', 'মোঃ শামীম খান', 'Md. Shamim Khan', '01911111121', 'photos/student_boy.png'),
('নাবিলা রহমান', 'Nabila Rahman', 2, 3, 4, 'Female', '2013-05-18', 'মোঃ লুৎফর রহমান', 'Md. Lutfar Rahman', '01911111122', 'photos/student_girl.png'),
('মোঃ মাহিন ইসলাম', 'Md. Mahin Islam', 3, 4, 2, 'Male', '2012-03-30', 'মোঃ নূরুল ইসলাম', 'Md. Nurul Islam', '01911111123', 'photos/student_boy.png'),
('মোসাম্মাৎ জেস্মিন আক্তার', 'Mst. Jesmin Akter', 3, 4, 3, 'Female', '2012-07-12', 'মোঃ আলমগীর হোসেন', 'Md. Alamgir Hossain', '01911111124', 'photos/student_girl.png'),
('মোঃ আসিফ রহমান', 'Md. Asif Rahman', 4, 5, 2, 'Male', '2011-12-05', 'মোঃ মিজানুর রহমান', 'Md. Mijanur Rahman', '01911111125', 'photos/student_boy.png'),
('তানজিলা আক্তার', 'Tanjila Akter', 4, 6, 1, 'Female', '2011-08-25', 'মোঃ আনোয়ার হোসেন', 'Md. Anwar Hossain', '01911111126', 'photos/student_girl.png'),
('মোঃ জিসান আহমেদ', 'Md. Jisan Ahmed', 4, 6, 2, 'Male', '2011-05-14', 'মোঃ শাহজাহান আলী', 'Md. Shahjahan Ali', '01911111127', 'photos/student_boy.png'),
('মোঃ ফাহিম মুনতাসির', 'Md. Fahim Muntasir', 5, 7, 2, 'Male', '2010-10-10', 'মোঃ জাহাঙ্গীর আলম', 'Md. Jahangir Alam', '01911111128', 'photos/student_boy.png'),
('জান্নাতুল ফেরদৌস', 'Jannatul Ferdous', 5, 8, 1, 'Female', '2010-04-16', 'মোঃ ফরিদুল ইসলাম', 'Md. Faridul Islam', '01911111129', 'photos/student_girl.png'),
('মোঃ নাঈম হোসেন', 'Md. Naim Hossain', 5, 8, 2, 'Male', '2010-09-05', 'মোঃ দেলোয়ার হোসেন', 'Md. Delwar Hossain', '01911111130', 'photos/student_boy.png');

-- Seed initial Notices
INSERT INTO `notices` (`title_bn`, `title_en`, `content_bn`, `content_en`, `publish_date`, `is_published`) VALUES
('শিক্ষা কার্যক্রম সংক্রান্ত জরুরি নোটিশ', 'Urgent Notice regarding Academic Activities', 'এতদ্বারা সোনারগাঁও উচ্চ বিদ্যালয়ের সকল ছাত্র-ছাত্রী ও শিক্ষকবৃন্দের অবগতির জন্য জানানো যাচ্ছে যে, আগামীকাল থেকে সকল শ্রেণির ক্লাস নতুন সময়সূচী অনুযায়ী অনুষ্ঠিত হবে। বিস্তারিত রুটিন একাডেমিক পাতায় দেখুন।', 'This is to inform all students and teachers of Sonargaon High School that all classes will be held according to the new schedule starting tomorrow. Please see the academic section for details.', '2026-05-30', 1),
('২০২৬ শিক্ষাবর্ষের অর্ধবার্ষিক পরীক্ষার সময়সূচী', 'Half Yearly Examination Schedule 2026', 'আসন্ন অর্ধবার্ষিক পরীক্ষা আগামী ১৫ই জুন থেকে শুরু হতে যাচ্ছে। পরীক্ষার সিলেবাস ও সময়সূচী শ্রেণি শিক্ষকের নিকট থেকে অথবা ওয়েবসাইট থেকে সংগ্রহ করতে বলা হলো।', 'The upcoming half-yearly examination will commence on June 15. Students are advised to collect the syllabus and routine from their class teacher or download from the website.', '2026-05-28', 1);

-- Seed initial Committee members
INSERT INTO `committee_members` (`id`, `name_bn`, `name_en`, `designation_bn`, `designation_en`, `profession_bn`, `profession_en`, `contact`, `photo`, `session_start`, `session_end`, `sort_order`) VALUES
(1, 'হাজী মোফাজ্জল হোসেন', 'Haji Mofazzal Hossain', 'সভাপতি', 'President', 'ব্যবসায়ী', 'Businessman', '01712000001', 'photos/committee_1.png', '2025', '2027', 1),
(2, 'মোঃ রফিকুল ইসলাম', 'Md. Rafiqul Islam', 'সদস্য সচিব (প্রধান শিক্ষক)', 'Member Secretary (Head Teacher)', 'শিক্ষকতা', 'Teaching', '01711223344', 'photos/committee_2.png', '2025', '2027', 2),
(3, 'ড. একেএম মজিবুর রহমান', 'Dr. AKM Mojibur Rahman', 'দাতা সদস্য', 'Donor Member', 'অবসরপ্রাপ্ত সরকারি কর্মকর্তা', 'Retired Government Officer', '01712000002', 'photos/committee_3.png', '2025', '2027', 3),
(4, 'মোঃ আনিসুর রহমান', 'Md. Anisur Rahman', 'সহ-সভাপতি', 'Vice President', 'সমাজসেবক', 'Social Worker', '01712000004', 'photos/committee_4.png', '2025', '2027', 4),
(5, 'মোঃ শাহজাহান আলী', 'Md. Shahjahan Ali', 'শিক্ষক প্রতিনিধি', 'Teacher Representative', 'শিক্ষকতা', 'Teaching', '01712000005', 'photos/committee_5.png', '2025', '2027', 5),
(6, 'মোসাম্মাৎ ফাতেমা আক্তার', 'Mst. Fatema Akter', 'শিক্ষক প্রতিনিধি', 'Teacher Representative', 'শিক্ষকতা', 'Teaching', '01722334455', 'photos/committee_6.png', '2025', '2027', 6),
(7, 'মোঃ আবুল হাশেম', 'Md. Abul Hashem', 'অভিভাবক প্রতিনিধি', 'Parent Representative', 'ব্যবসায়ী', 'Businessman', '01712000007', 'photos/committee_7.png', '2025', '2027', 7),
(8, 'মোঃ আব্দুল জলিল', 'Md. Abdul Jalil', 'অভিভাবক প্রতিনিধি', 'Parent Representative', 'কৃষিজীবী', 'Farmer', '01712000008', 'photos/committee_8.png', '2025', '2027', 8),
(9, 'মোঃ ইউসুফ আলী', 'Md. Yusuf Ali', 'অভিভাবক প্রতিনিধি', 'Parent Representative', 'চাকরিজীবী', 'Service Holder', '01712000009', 'photos/committee_9.png', '2025', '2027', 9),
(10, 'মোসাম্মাৎ রহিমা বেগম', 'Mst. Rahima Begum', 'মহিলা অভিভাবক প্রতিনিধি', 'Female Parent Representative', 'গৃহিণী', 'Housewife', '01712000010', 'photos/committee_10.png', '2025', '2027', 10),
(11, 'মোঃ নুরুল হুদা', 'Md. Nurul Huda', 'সাধারণ সদস্য', 'General Member', 'সমাজসেবক', 'Social Worker', '01712000011', 'photos/committee_11.png', '2025', '2027', 11),
(12, 'মোঃ ফরিদ উদ্দিন', 'Md. Farid Uddin', 'সাধারণ সদস্য', 'General Member', 'ব্যবসায়ী', 'Businessman', '01712000012', 'photos/committee_12.png', '2025', '2027', 12),
(13, 'মোঃ সিরাজুল ইসলাম', 'Md. Sirajul Islam', 'সাধারণ সদস্য', 'General Member', 'কৃষিজীবী', 'Farmer', '01712000013', 'photos/committee_13.png', '2025', '2027', 13),
(14, 'মোঃ আমিনুল ইসলাম', 'Md. Aminul Islam', 'সাধারণ সদস্য', 'General Member', 'সমাজসেবক', 'Social Worker', '01712000014', 'photos/committee_14.png', '2025', '2027', 14),
(15, 'মোঃ মাহবুবুর রহমান', 'Md. Mahbubur Rahman', 'সাধারণ সদস্য', 'General Member', 'ব্যবসায়ী', 'Businessman', '01712000015', 'photos/committee_15.png', '2025', '2027', 15),
(16, 'মোঃ আক্তার হোসেন', 'Md. Akhtar Hossain', 'কো-অপ্ট সদস্য', 'Co-opted Member', 'আইনজীবী', 'Lawyer', '01712000016', 'photos/committee_16.png', '2025', '2027', 16),
(17, 'মোঃ জিয়াউল হক', 'Md. Ziaul Haque', 'কো-অপ্ট সদস্য', 'Co-opted Member', 'চিকিৎসক', 'Doctor', '01712000017', 'photos/committee_17.png', '2025', '2027', 17),
(18, 'মোসাম্মাৎ শাহিদা আক্তার', 'Mst. Shahida Akter', 'সমাজসেবক সদস্য', 'Social Worker', 'সমাজসেবা', 'Social Work', '01712000018', 'photos/committee_18.png', '2025', '2027', 18),
(19, 'মোঃ রফিক উদ্দিন', 'Md. Rafique Uddin', 'শিক্ষানুরাগী সদস্য', 'Educationist Member', 'অবসরপ্রাপ্ত শিক্ষক', 'Retired Teacher', '01712000019', 'photos/committee_19.png', '2025', '2027', 19),
(20, 'মোঃ আসাদুজ্জামান', 'Md. Asaduzzaman', 'প্রতিষ্ঠাতা সদস্য', 'Founder Member', 'ব্যবসায়ী', 'Businessman', '01712000020', 'photos/committee_20.png', '2025', '2027', 20);

-- Seed Navigation Menus
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title_bn` VARCHAR(100) NOT NULL,
  `title_en` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `parent_id` INT NULL,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`parent_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(13, 'যোগাযোগ', 'Contact', '/contact', NULL, 6);

