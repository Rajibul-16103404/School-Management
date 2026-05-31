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
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert" style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #a7f3d0; font-size:14px;">';
        $html .= '<strong>সফল!</strong> ' . $messages['success'];
        $html .= '</div>';
    }
    
    if (isset($messages['error'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert" style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; font-size:14px;">';
        $html .= '<strong>ত্রুটি!</strong> ' . $messages['error'];
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
