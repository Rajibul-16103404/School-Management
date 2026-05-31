<?php
/**
 * Global Helper Functions
 * School Management Website
 */

require_once __DIR__ . '/db.php';

/**
 * XSS escaping helper
 */
function escape(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize user input strings
 */
function sanitize_input(string $data): string {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Add an audit log entry
 */
function log_activity(?PDO $pdo, string $action, ?string $details = null): bool {
    if (!$pdo) {
        return false;
    }
    
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO `activity_logs` (`user_id`, `action`, `details`) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $action, $details]);
    } catch (PDOException $e) {
        // Fail silently in production
        return false;
    }
}

/**
 * Get flash message and clear it from session
 */
function get_flash_messages(): array {
    $messages = [];
    if (isset($_SESSION['flash_success'])) {
        $messages['success'] = $_SESSION['flash_success'];
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        $messages['error'] = $_SESSION['flash_error'];
        unset($_SESSION['flash_error']);
    }
    return $messages;
}

/**
 * Display flash alerts as HTML
 */
function display_flash_alerts(): string {
    $messages = get_flash_messages();
    $html = '';
    
    if (isset($messages['success'])) {
        $html .= '<div class="alert alert-success" role="alert" style="display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 10px; margin-bottom: 25px; background: #ecfdf5; border-left: 6px solid #10b981; border-top: 1px solid #a7f3d0; border-right: 1px solid #a7f3d0; border-bottom: 1px solid #a7f3d0; color: #065f46; font-size: 15px; font-weight: 500; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.08), 0 4px 6px -4px rgba(16, 185, 129, 0.08);">';
        $html .= '<span style="font-size: 20px; color: #10b981; display: flex; align-items: center;"><i class="fa-solid fa-circle-check"></i></span>';
        $html .= '<div><strong>সফল!</strong> ' . $messages['success'] . '</div>';
        $html .= '</div>';
    }
    
    if (isset($messages['error'])) {
        $html .= '<div class="alert alert-danger" role="alert" style="display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 10px; margin-bottom: 25px; background: #fef2f2; border-left: 6px solid #ef4444; border-top: 1px solid #fca5a5; border-right: 1px solid #fca5a5; border-bottom: 1px solid #fca5a5; color: #991b1b; font-size: 15px; font-weight: 500; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.08), 0 4px 6px -4px rgba(239, 68, 68, 0.08);">';
        $html .= '<span style="font-size: 20px; color: #ef4444; display: flex; align-items: center;"><i class="fa-solid fa-circle-exclamation"></i></span>';
        $html .= '<div><strong>ত্রুটি!</strong> ' . $messages['error'] . '</div>';
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Handles secure file uploads
 * @param array $file $_FILES['input_name'] array
 * @param string $subfolder Directory path under /uploads (e.g. 'photos', 'notices')
 * @param array $allowed_extensions Allowed extensions (e.g. ['jpg', 'png', 'pdf'])
 * @param int $max_size Max file size in bytes (default 5MB)
 * @return string Uploaded file name on success
 * @throws Exception on upload failure
 */
function upload_file(array $file, string $subfolder, array $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'], int $max_size = 10485760): string {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid upload parameter schema.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new Exception('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('Exceeded file size limits.');
        default:
            throw new Exception('Unknown upload error.');
    }

    if ($file['size'] > $max_size) {
        throw new Exception('Exceeded file size limit (' . round($max_size / 1024 / 1024) . 'MB).');
    }

    // Double check extensions and MIME type
    $filename = basename($file['name']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_extensions)) {
        throw new Exception('Invalid file extension. Allowed: ' . implode(', ', $allowed_extensions));
    }

    // Verify MIME type for security
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf'
    ];

    $matched = false;
    foreach ($allowed_extensions as $allowed_ext) {
        if (isset($allowed_mimes[$allowed_ext]) && $mime === $allowed_mimes[$allowed_ext]) {
            $matched = true;
            break;
        }
    }

    if (!$matched) {
        throw new Exception('File mime-type mismatch. Security check failed.');
    }

    // Generate safe, unique file name
    $safeName = sprintf('%s_%s.%s', $subfolder, uniqid('', true), $ext);
    
    $targetDir = UPLOAD_DIR . '/' . $subfolder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $targetPath = $targetDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to save uploaded file.');
    }

    return $subfolder . '/' . $safeName;
}

/**
 * Format DATE from Database for display (Bangla support optional)
 */
function format_date(string $date): string {
    return date('d/m/Y', strtotime($date));
}

/**
 * Generate CSRF token if not exists
 */
function generate_csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $session_token = $_SESSION['csrf_token'] ?? '';
    if (empty($session_token) || empty($token)) {
        return false;
    }
    return hash_equals($session_token, $token);
}

/**
 * Render CSRF hidden input field
 */
function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . escape(generate_csrf_token()) . '">';
}

/**
 * Automatically check CSRF token for active POST requests or state-changing actions
 */
function verify_csrf() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Ignore verification on login page itself, or check it if we inject token
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    if ($current_script === 'login.php' || $current_script === 'setup.php') {
        return;
    }

    $is_delete_script = (strpos($current_script, 'delete') !== false);
    $has_state_changing_param = false;
    $state_changing_get_params = ['delete', 'delete_doc_id', 'delete_notice', 'delete_routine', 'delete_syllabus', 'delete_photo'];
    foreach ($state_changing_get_params as $param) {
        if (isset($_GET[$param])) {
            $has_state_changing_param = true;
            break;
        }
    }

    // 1. Enforce token validation on standalone delete scripts or state-changing params
    if ($is_delete_script || $has_state_changing_param) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!validate_csrf_token($token)) {
            $_SESSION['flash_error'] = 'নিরাপত্তা ত্রুটি: অবৈধ নিরাপত্তা টোকেন।';
            
            // Redirect back safely or fallback to dashboard
            $redirect_url = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL . '/admin/index.php' : '/admin/index.php');
            
            // Prevent redirecting to the same delete URL or params to avoid infinite loop
            if (strpos($redirect_url, 'delete') !== false) {
                $redirect_url = defined('BASE_URL') ? BASE_URL . '/admin/index.php' : '/admin/index.php';
            }
            
            header("Location: " . $redirect_url);
            exit;
        }
    }

    // 2. Enforce token validation on any other POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf_token($token)) {
            $_SESSION['flash_error'] = 'নিরাপত্তা ত্রুটি: CSRF টোকেন ভ্যালিডেশন ব্যর্থ হয়েছে।';
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}
