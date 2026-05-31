<?php
/**
 * Admin Panel Footer
 * School Management Website
 */
?>
        </div> <!-- Close admin-content -->
        
        <footer class="admin-footer">
            <p>&copy; <?php echo date('Y'); ?> সোনারগাঁও উচ্চ বিদ্যালয় | অ্যাডমিন প্যানেল v1.0.0</p>
            <p>Developed with <i class="fa fa-heart" style="color: #ef4444;"></i> by Antigravity AI</p>
        </footer>
    </div> <!-- Close admin-main -->
</div> <!-- Close admin-wrapper -->

<!-- Custom System Confirmation Modal -->
<div id="customConfirmModal" class="custom-confirm-overlay" style="display: none;">
    <div class="custom-confirm-card">
        <div class="custom-confirm-icon">
            <i class="fa fa-exclamation-triangle"></i>
        </div>
        <h3 id="customConfirmTitle" class="custom-confirm-title">নিশ্চিতকরণ</h3>
        <p id="customConfirmMessage" class="custom-confirm-message">আপনি কি নিশ্চিতভাবে এই কাজটি করতে চান?</p>
        <div class="custom-confirm-actions">
            <button id="customConfirmCancelBtn" class="btn-confirm btn-confirm-secondary">বাতিল করুন</button>
            <button id="customConfirmOkBtn" class="btn-confirm btn-confirm-danger">হ্যাঁ, নিশ্চিত করুন</button>
        </div>
    </div>
</div>

<!-- Admin Javascript -->
<script src="<?php echo BASE_URL; ?>/assets/js/admin.js"></script>
</body>
</html>
