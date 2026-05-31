<?php
/**
 * Admin Database Backup & Restore Utility
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Restrict to superadmin or headteacher role
check_role(['superadmin', 'headteacher']);

$error = null;
$success = null;

// 1. Handle Backup Download (Export SQL)
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    // Verify CSRF token for security on actions
    if (!isset($_GET['csrf_token']) || !validate_csrf_token($_GET['csrf_token'])) {
        $_SESSION['flash_error'] = "নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে।";
        header("Location: " . BASE_URL . "/admin/backup");
        exit;
    }

    try {
        // Fetch all tables
        $tables = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sql = "-- ======================================================\n";
        $sql .= "-- School Management System Database Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . " (Asia/Dhaka)\n";
        $sql .= "-- Host: " . DB_HOST . "\n";
        $sql .= "-- Database: " . DB_NAME . "\n";
        $sql .= "-- ======================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Drop statement
            $sql .= "DROP TABLE IF EXISTS `" . $table . "`;\n";

            // Create structure statement
            $show_create = $pdo->query("SHOW CREATE TABLE `" . $table . "`")->fetch();
            $sql .= $show_create['Create Table'] . ";\n\n";

            // Get table data
            $data_stmt = $pdo->query("SELECT * FROM `" . $table . "`");
            $rows = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "-- Data for table `" . $table . "`\n";
                foreach ($rows as $row) {
                    $keys = array_keys($row);
                    $escaped_keys = array_map(function($k) { return "`$k`"; }, $keys);
                    
                    $values = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $values[] = "NULL";
                        } elseif (is_numeric($val) && (string)(int)$val === $val) {
                            $values[] = $val;
                        } else {
                            $values[] = $pdo->quote($val);
                        }
                    }

                    $sql .= "INSERT INTO `" . $table . "` (" . implode(', ', $escaped_keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        log_activity($pdo, "Download DB Backup", "Exported database backup file.");

        // Clear output buffer to prevent corrupted file downloads
        if (ob_get_level()) {
            ob_end_clean();
        }

        // File download headers
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="school_backup_' . date('Ymd_His') . '.sql"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $sql;
        exit;

    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "ব্যাকআপ ফাইল তৈরিতে ব্যর্থতা: " . $e->getMessage();
        header("Location: " . BASE_URL . "/admin/backup");
        exit;
    }
}

// 2. Handle Restore SQL Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_db'])) {
    if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "অনুগ্রহ করে একটি .sql ব্যাকআপ ফাইল আপলোড করুন।";
    } else {
        $file = $_FILES['backup_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'sql') {
            $error = "ভুল ফাইল ফরম্যাট! শুধুমাত্র .sql ফাইল গ্রহণযোগ্য।";
        } else {
            try {
                $sql_content = file_get_contents($file['tmp_name']);
                
                // Remove SQL comments and empty lines
                $sql_content = preg_replace('/--.*\n/', '', $sql_content);
                $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);
                
                // Split queries by semicolon line end
                $queries = preg_split('/;\s*$/m', $sql_content);
                
                // Disable foreign key checks
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

                $executed = 0;
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $pdo->exec($query);
                        $executed++;
                    }
                }

                // Re-enable foreign key checks
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

                log_activity($pdo, "Restore Database", "Successfully imported database backup from uploaded file.");
                $success = "ডাটাবেজ সফলভাবে রিস্টোর করা হয়েছে! মোট {$executed}টি কুয়েরি রান হয়েছে।";

            } catch (PDOException $e) {
                $error = "ডাটাবেজ রিস্টোর ব্যর্থ হয়েছে: " . $e->getMessage();
            } catch (Exception $e) {
                $error = "ফাইল রিডিং ব্যর্থতা: " . $e->getMessage();
            }
        }
    }
}

// 3. Handle Database Reset
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    // Verify CSRF
    if (!isset($_GET['csrf_token']) || !validate_csrf_token($_GET['csrf_token'])) {
        $_SESSION['flash_error'] = "নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে।";
        header("Location: " . BASE_URL . "/admin/backup");
        exit;
    }

    try {
        $schemaFile = __DIR__ . '/../../schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("মূল schema.sql ফাইলটি সার্ভারে পাওয়া যায়নি।");
        }

        $sql_content = file_get_contents($schemaFile);
        $sql_content = preg_replace('/--.*\n/', '', $sql_content);
        $queries = preg_split('/;\s*$/m', $sql_content);

        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

        log_activity($pdo, "Reset Database", "Database reset to initial demo seeds.");
        $_SESSION['flash_success'] = "ডাটাবেজ সফলভাবে রিসেট করে ডেমো ডাটা দ্বারা পূর্ণ করা হয়েছে! ইউজারনেম: admin এবং পাসওয়ার্ড: Admin@123456।";
        header("Location: " . BASE_URL . "/admin/backup");
        exit;

    } catch (Exception $e) {
        $_SESSION['flash_error'] = "ডাটাবেজ রিসেট ব্যর্থ হয়েছে: " . $e->getMessage();
        header("Location: " . BASE_URL . "/admin/backup");
        exit;
    }
}

// Calculate Database Stats
$db_size = "Unknown";
$table_count = 0;
try {
    $size_stmt = $pdo->prepare("
        SELECT SUM(data_length + index_length) AS size 
        FROM information_schema.TABLES 
        WHERE table_schema = ?
    ");
    $size_stmt->execute([DB_NAME]);
    $res = $size_stmt->fetch();
    if ($res && isset($res['size'])) {
        $bytes = $res['size'];
        if ($bytes >= 1048576) {
            $db_size = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $db_size = number_format($bytes / 1024, 2) . ' KB';
        } else {
            $db_size = $bytes . ' Bytes';
        }
    }
    
    $tables_stmt = $pdo->query("SHOW TABLES");
    $table_count = $tables_stmt->rowCount();
} catch (PDOException $e) {}
?>

<div class="page-title">
    <span><i class="fa-solid fa-database"></i> ডাটাবেজ ব্যাকআপ ও রিস্টোর (Database Utility)</span>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ⚠️ <strong>ত্রুটি!</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #a7f3d0; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        ✅ <strong>সফল!</strong> <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <!-- Card 1: Stats & Export -->
    <div class="admin-card">
        <h3 style="font-size: 16px; color: var(--primary); margin-bottom: 15px;"><i class="fa fa-download"></i> ডাটাবেজ ব্যাকআপ (Download Backup)</h3>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;">
            বর্তমান ডাটাবেজের সকল তথ্য ও টেবিল স্ট্রাকচার একটি <code>.sql</code> ফাইল হিসেবে আপনার ডিভাইসে ডাউনলোড করে রাখুন। পরবর্তীতে ডাটা রিস্টোর করার জন্য এই ফাইলটি ব্যবহার করতে পারবেন।
        </p>

        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px;">
                <span>ডাটাবেজ নাম:</span>
                <strong><?php echo escape(DB_NAME); ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px;">
                <span>মোট টেবিল সংখ্যা:</span>
                <strong><?php echo $table_count; ?> টি</strong>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:14px;">
                <span>ডাটা সাইজ:</span>
                <strong><?php echo $db_size; ?></strong>
            </div>
        </div>

        <a href="?action=download&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-admin btn-primary" style="display: flex; justify-content: center; padding: 12px; font-size: 15px; text-decoration: none;">
            <i class="fa fa-file-arrow-down"></i> ব্যাকআপ ডাউনলোড করুন
        </a>
    </div>

    <!-- Card 2: Import & Restore -->
    <div class="admin-card">
        <h3 style="font-size: 16px; color: var(--primary); margin-bottom: 15px;"><i class="fa fa-upload"></i> ডাটাবেজ রিস্টোর (Upload & Restore)</h3>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;">
            পূর্বে ডাউনলোড করা কোনো <code>.sql</code> ব্যাকআপ ফাইল আপলোড করে ডাটাবেজ পূর্বাবস্থায় ফিরিয়ে নিয়ে আসুন।
        </p>

        <div class="alert alert-warning" style="background: rgba(212, 175, 55, 0.1); border: 1px dashed var(--accent); color: #fde047; font-size: 12.5px; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ⚠️ <strong>সাবধানতা:</strong> রিস্টোর প্রক্রিয়া সম্পন্ন করলে ডাটাবেজের বর্তমান সকল তথ্য মুছে যাবে এবং আপলোড করা ফাইলের ডাটা প্রতিস্থাপিত হবে!
        </div>

        <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই ব্যাকআপ ফাইলটি রিস্টোর করতে চান? এটি আপনার বর্তমান সকল ডাটা ওভাররাইট করবে!');">
            <?php echo csrf_input(); ?>
            <div class="admin-form-group" style="margin-bottom: 20px;">
                <label for="backup_file" style="font-weight: bold; font-size: 13.5px; display:block; margin-bottom:5px;">ব্যাকআপ ফাইল (.sql) নির্বাচন করুন:</label>
                <input type="file" id="backup_file" name="backup_file" class="form-control" accept=".sql" required>
            </div>

            <button type="submit" name="restore_db" class="btn-admin btn-accent" style="width: 100%; display: flex; justify-content: center; padding: 12px; font-size: 15px;">
                <i class="fa fa-clock-rotate-left"></i> ব্যাকআপ রিস্টোর করুন
            </button>
        </form>
    </div>
</div>

<!-- Reset Section -->
<div class="admin-card" style="margin-top: 25px; border: 1px solid rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.02);">
    <h3 style="font-size: 16px; color: var(--danger); margin-bottom: 10px;"><i class="fa fa-trash-can"></i> ডাটাবেজ রিসেট (Clean System Reset)</h3>
    <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 15px;">
        সিস্টেমের সকল কাস্টম ডাটা ও ফাইল এন্ট্রি মুছে দিয়ে সম্পূর্ণ নতুনভাবে ইনস্টল করা অবস্থায় রিসেট করুন। এটি ডাটাবেজকে একদম প্রাথমিক ডেমো ডাটা এন্ট্রিতে ফিরিয়ে নিয়ে যাবে।
    </p>
    <a href="?action=reset&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-admin btn-danger" style="display: inline-flex; padding: 10px 20px; font-size: 14px; text-decoration: none;" onclick="return confirm('আপনি কি নিশ্চিতভাবে ডাটাবেজ রিসেট করতে চান? আপনার তৈরি করা সমস্ত শিক্ষার্থী, শিক্ষক ও নোটিশ মুছে যাবে!');">
        <i class="fa fa-triangle-exclamation"></i> ডাটাবেজ রিসেট করুন
    </a>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
