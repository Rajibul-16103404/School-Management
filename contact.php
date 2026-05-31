<?php
/**
 * Public Contact Information Page
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

$success_msg = null;
$error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // Server-side validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_msg = "সবগুলো ঘর পূরণ করা আবশ্যক।";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "সঠিক ইমেইল ঠিকানা প্রদান করুন।";
    } else {
        // Save to Simulated Contact Log (Since SMTP is local)
        $log_file = UPLOAD_DIR . '/contact_submissions.log';
        $log_data = sprintf(
            "Time: %s\nName: %s\nEmail: %s\nSubject: %s\nMessage: %s\n---------------------------------------\n",
            date('Y-m-d H:i:s'),
            $name,
            $email,
            $subject,
            $message
        );
        
        file_put_contents($log_file, $log_data, FILE_APPEND);
        
        // Log in database if DB is up (via functions log helper)
        log_activity($pdo, "Contact Submission", "From: $name ($email), Subject: $subject");
        
        $success_msg = "আপনার বার্তাটি সফলভাবে পাঠানো হয়েছে! শীঘ্রই আপনার সাথে যোগাযোগ করা হবে।";
    }
}
?>

<div class="card">
    <h2 class="card-title"><i class="fa fa-envelope" style="color: var(--accent);"></i> যোগাযোগের ঠিকানা ও ফরম</h2>
    
    <?php if ($success_msg): ?>
        <div class="alert alert-success" style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #a7f3d0; font-size:14px;">
            <strong>সফল!</strong> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger" style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; font-size:14px;">
            <strong>ত্রুটি!</strong> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div class="contact-grid">
        <!-- Address & Map details -->
        <div>
            <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 25px; border-radius: 8px; margin-bottom: 25px;">
                <h3 style="font-size:16px; color: var(--primary-dark); margin-bottom: 15px; border-bottom:1px solid var(--border-color); padding-bottom:5px;"><i class="fa fa-address-book"></i> অফিসিয়াল ঠিকানা</h3>
                
                <p style="margin-bottom: 10px; font-size:14px;">
                    <strong>ঠিকানা:</strong> <?php echo escape($school['address_bn'] ?? 'সোনারগাঁও, নারায়ণগঞ্জ, ঢাকা'); ?><br>
                    <strong>ইআইআইএন (EIIN):</strong> <?php echo escape($sch_eiin); ?>
                </p>
                <p style="margin-bottom: 10px; font-size:14px;">
                    <strong>টেলিফোন:</strong> <?php echo escape($school['phone'] ?? '+৮৮০২১২৩৪৫৬'); ?><br>
                    <strong>মোবাইল:</strong> <?php echo escape($school['mobile'] ?? '+৮৮০১৭১২৩৪৫৬৭৮'); ?>
                </p>
                <p style="margin-bottom: 10px; font-size:14px;">
                    <strong>ইমেইল:</strong> <?php echo escape($school['email'] ?? 'info@school.gov.bd'); ?><br>
                    <strong>ফ্যাক্স:</strong> <?php echo escape($school['fax'] ?? '-'); ?>
                </p>
            </div>
            
            <?php if (!empty($school['map_embed'])): ?>
                <div style="border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                    <?php echo $school['map_embed']; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Submission Form -->
        <div>
            <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 25px; border-radius: 8px;">
                <h3 style="font-size:16px; color: var(--primary-dark); margin-bottom: 15px; border-bottom:1px solid var(--border-color); padding-bottom:5px;"><i class="fa fa-paper-plane"></i> বার্তা পাঠান (Send Message)</h3>
                
                <form class="contact-form" method="POST">
                    <div class="form-group">
                        <label for="name">আপনার নাম <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="আপনার পূর্ণ নাম লিখুন">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">ইমেইল ঠিকানা <span style="color:var(--danger);">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="name@domain.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">বিষয় <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="subject" name="subject" required placeholder="বার্তার মূল বিষয়">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">বার্তার বিবরণ <span style="color:var(--danger);">*</span></label>
                        <textarea id="message" name="message" rows="5" required placeholder="এখানে আপনার বার্তাটি বিস্তারিত লিখুন..."></textarea>
                    </div>
                    
                    <button type="submit" name="send_message" class="btn-submit" style="width: 100%;"><i class="fa fa-paper-plane"></i> বার্তা পাঠান</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
