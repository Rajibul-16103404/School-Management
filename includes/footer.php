<?php
/**
 * Public Page Footer
 * School Management Website
 */

require_once __DIR__ . '/db.php';

// Fetch school data for contact details
$school = null;
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `schools` WHERE `id` = 1");
        $school = $stmt->fetch();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Fallbacks
$sch_name_bn = $school['name_bn'] ?? 'সোনারগাঁও উচ্চ বিদ্যালয়';
$sch_address_bn = $school['address_bn'] ?? 'সোনারগাঁও, নারায়ণগঞ্জ, ঢাকা';
$sch_phone = $school['phone'] ?? '+৮৮০২১২৩৪৫৬';
$sch_mobile = $school['mobile'] ?? '+৮৮০১৭১২৩৪৫৬৭৮';
$sch_email = $school['email'] ?? 'info@school.gov.bd';

$footer_desc = $school['footer_text_bn'] ?? 'আমাদের মূল লক্ষ্য শিক্ষার্থীদের মানবিক মূল্যবোধ সম্পন্ন সুনাগরিক হিসেবে গড়ে তোলা। মানসম্মত শিক্ষা নিশ্চিতকরণে আমরা সর্বদা প্রতিজ্ঞাবদ্ধ।';
$footer_copyright = $school['footer_copyright_bn'] ?? ($sch_name_bn . '. সর্বস্বত্ব সংরক্ষিত।');

// Parse footer links
$footer_links = [];
if (!empty($school['footer_links'])) {
    $footer_links = json_decode($school['footer_links'], true) ?: [];
}
if (empty($footer_links)) {
    $footer_links = [
        ["title_bn" => "মাধ্যমিক ও উচ্চশিক্ষা অধিদপ্তর", "url" => "https://dshe.gov.bd"],
        ["title_bn" => "শিক্ষা মন্ত্রণালয়", "url" => "https://moedu.gov.bd"],
        ["title_bn" => "ঢাকা শিক্ষা বোর্ড", "url" => "https://dhakaeducationboard.gov.bd"],
        ["title_bn" => "জাতীয় তথ্য বাতায়ন", "url" => "https://www.bangladesh.gov.bd"]
    ];
}
?>
</main> <!-- Close Main Page Content Container -->

<footer class="main-footer">
    <div class="container footer-grid">
        <!-- School Description Column -->
        <div class="footer-col">
            <h3><?php echo escape($sch_name_bn); ?></h3>
            <p style="line-height: 1.6; font-size: 14px; margin-bottom: 15px; color: #a3b8cc;">
                <?php echo escape($footer_desc); ?>
            </p>
            <p><i class="fa fa-envelope" style="color: var(--accent);"></i> <?php echo escape($sch_email); ?></p>
        </div>

        <!-- Useful Links Column -->
        <div class="footer-col">
            <h3>গুরুত্বপূর্ণ লিঙ্কসমূহ</h3>
            <ul class="footer-links">
                <?php foreach ($footer_links as $link): ?>
                    <li><a href="<?php echo escape($link['url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-angle-right"></i> <?php echo escape($link['title_bn'] ?? $link['title_en'] ?? ''); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Contact Column -->
        <div class="footer-col">
            <h3>যোগাযোগের ঠিকানা</h3>
            <p style="margin-bottom: 10px; font-size: 14px; color: #a3b8cc;"><i class="fa fa-map-marker" style="color: var(--accent); margin-right: 8px;"></i> <?php echo escape($sch_address_bn); ?></p>
            <p style="margin-bottom: 5px; font-size: 14px; color: #a3b8cc;"><i class="fa fa-phone" style="color: var(--accent); margin-right: 8px;"></i> ফোন: <?php echo escape($sch_phone); ?></p>
            <p style="margin-bottom: 5px; font-size: 14px; color: #a3b8cc;"><i class="fa fa-mobile-phone" style="color: var(--accent); margin-right: 8px;"></i> মোবাইল: <?php echo escape($sch_mobile); ?></p>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="container footer-bottom-content">
            <p>&copy; <?php echo date('Y'); ?> <?php echo escape($footer_copyright); ?></p>
            <p>কারিগরি সহায়তায়: <a href="https://softinglobal.com" target="_blank" rel="noopener noreferrer" style="color: var(--accent); text-decoration: none;">Softin Global</a></p>
        </div>
    </div>
</footer>

<!-- Main Responsive Javascript -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
