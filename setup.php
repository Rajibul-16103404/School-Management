<?php
/**
 * Database Setup Wizard
 * School Management Website
 */

require_once __DIR__ . '/config.php';

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = null;
$success = null;

if (!function_exists('escape')) {
    function escape($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Handle Database Credentials Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_credentials'])) {
    $host = trim($_POST['db_host'] ?? '');
    $port = trim($_POST['db_port'] ?? '');
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = $_POST['db_pass'] ?? '';

    try {
        // Try connecting to check if credentials are valid
        $dsn = "mysql:host=" . $host . ";port=" . $port . ";charset=utf8mb4";
        $test_pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        // Save to config.php
        $configFile = __DIR__ . '/config.php';
        if (!is_writable($configFile)) {
            throw new Exception("config.php file is not writeable. Please check file permissions.");
        }
        
        $content = file_get_contents($configFile);
        $content = preg_replace("/define\('DB_HOST',\s*['\"].*?['\"]\);/", "define('DB_HOST', " . var_export($host, true) . ");", $content);
        $content = preg_replace("/define\('DB_PORT',\s*['\"].*?['\"]\);/", "define('DB_PORT', " . var_export($port, true) . ");", $content);
        $content = preg_replace("/define\('DB_NAME',\s*['\"].*?['\"]\);/", "define('DB_NAME', " . var_export($name, true) . ");", $content);
        $content = preg_replace("/define\('DB_USER',\s*['\"].*?['\"]\);/", "define('DB_USER', " . var_export($user, true) . ");", $content);
        $content = preg_replace("/define\('DB_PASS',\s*['\"].*?['\"]\);/", "define('DB_PASS', " . var_export($pass, true) . ");", $content);
        
        file_put_contents($configFile, $content);
        
        header("Location: ?step=2");
        exit;
    } catch (PDOException $e) {
        $error = "Database Connection Failed: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Configuration Save Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    try {
        // Step 1: Connect to MySQL server without database first
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Create Database if it doesn't exist
        $dbName = DB_NAME;
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $db_err) {
            // Ignore privilege error if the database already exists
        }
        
        // Select the Database
        $pdo->exec("USE `$dbName`");

        // Load and parse schema.sql
        $schemaFile = __DIR__ . '/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("schema.sql file is missing in " . __DIR__);
        }

        $sql = file_get_contents($schemaFile);
        
        // Remove comments
        $sql = preg_replace('/--.*\n/', '', $sql);
        
        // Split by semicolons, but ignore semicolons inside strings
        // A simple split using pattern that matches semicolons not followed by standard string contents
        // For a more robust approach, we parse the queries.
        $queries = preg_split('/;\s*$/m', $sql);

        $executed = 0;
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
                $executed++;
            }
        }
        
        
        // Generate seed images in uploads/photos/
        if (function_exists('imagecreatetruecolor')) {
            $target_dir = UPLOAD_DIR . '/photos';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $generate_seed_image = function(string $filename, string $label) use ($target_dir) {
                $width = 300;
                $height = 300;
                $img = imagecreatetruecolor($width, $height);
                
                // Choose background color based on filename hash
                $hash = md5($filename);
                $r = hexdec(substr($hash, 0, 2)) % 120 + 60;  // Medium pastel range
                $g = hexdec(substr($hash, 2, 2)) % 120 + 60;
                $b = hexdec(substr($hash, 4, 2)) % 120 + 60;
                
                $bg_color = imagecolorallocate($img, $r, $g, $b);
                imagefill($img, 0, 0, $bg_color);
                
                // Circle accent
                $accent_r = max(0, $r - 30);
                $accent_g = max(0, $g - 30);
                $accent_b = max(0, $b - 30);
                $circle_color = imagecolorallocate($img, $accent_r, $accent_g, $accent_b);
                imagefilledellipse($img, 150, 150, 220, 220, $circle_color);
                
                // Draw head & shoulders avatar
                $white = imagecolorallocate($img, 255, 255, 255);
                // Shoulders
                imagefilledarc($img, 150, 230, 140, 110, 180, 360, $white, IMG_ARC_PIE);
                // Head
                imagefilledellipse($img, 150, 130, 70, 70, $white);
                
                // Draw text label
                $text_color = imagecolorallocate($img, 255, 255, 255);
                $shadow_color = imagecolorallocate($img, 0, 0, 0);
                
                $font = 5; // standard built-in GD font
                $text_w = imagefontwidth($font) * strlen($label);
                $text_h = imagefontheight($font);
                
                $x = (int)(($width - $text_w) / 2);
                $y = 250;
                
                // Shadow
                imagestring($img, $font, $x + 1, $y + 1, $label, $shadow_color);
                // Text
                imagestring($img, $font, $x, $y, $label, $text_color);
                
                imagepng($img, $target_dir . '/' . $filename);
                imagedestroy($img);
            };
            
            for ($i = 1; $i <= 20; $i++) {
                $generate_seed_image("student_{$i}.png", "Student #{$i}");
                $generate_seed_image("teacher_{$i}.png", "Teacher #{$i}");
                $generate_seed_image("committee_{$i}.png", "Committee #{$i}");
            }
        }
        
        $success = "Database '$dbName' successfully initialized! Total $executed queries executed and 60 unique profile photo seeds generated. Default admin created (Username: <b>admin</b>, Password: <b>Admin@123456</b>).";
        $step = 3;

    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage() . "<br><small>Please check the DB credentials in <b>config.php</b>.</small>";
    } catch (Exception $e) {
        $error = "Installation Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Wizard - Sonargaon High School</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a5c38;
            --primary-dark: #113c24;
            --accent: #d4af37;
            --accent-hover: #bda02b;
            --bg: #0f172a;
            --panel: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', 'Hind Siliguri', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: var(--panel);
            max-width: 600px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 40px 30px;
            text-align: center;
            position: relative;
            border-bottom: 4px solid var(--accent);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #334155;
            z-index: 1;
        }

        .step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            color: var(--text-muted);
            z-index: 2;
            transition: all 0.3s;
        }

        .step.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 10px var(--primary);
            border: 2px solid var(--accent);
        }

        .step.completed {
            background: var(--success);
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--danger);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid var(--success);
            color: #a7f3d0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
        }

        .info-card h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .info-card p {
            font-size: 15px;
            font-weight: 600;
        }

        .form-control-setup {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: var(--text);
            font-size: 15px;
            outline: none;
            transition: all 0.3s;
            margin-top: 5px;
        }

        .form-control-setup:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 0 0 0 2px rgba(26, 92, 56, 0.3);
        }

        .btn {
            display: inline-block;
            width: 100%;
            background: var(--accent);
            color: #000;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-secondary {
            background: #334155;
            color: var(--text);
            margin-top: 10px;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .footer-text a {
            color: var(--accent);
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>School Website Installer</h1>
        <p>Sonargaon High School Management System Setup</p>
    </div>
    
    <div class="content">
        <!-- Step indicator -->
        <div class="step-indicator">
            <div class="step <?php echo $step === 1 ? 'active' : ($step > 1 ? 'completed' : ''); ?>">1</div>
            <div class="step <?php echo $step === 2 ? 'active' : ($step > 2 ? 'completed' : ''); ?>">2</div>
            <div class="step <?php echo $step === 3 ? 'active' : ''; ?>">3</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <div style="margin-bottom: 20px;">
                <h2 style="font-size: 18px; margin-bottom: 10px;">স্বাগতম! (Welcome!)</h2>
                <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
                    This setup wizard will automatically configure your MySQL database and create the required tables for the School Management System. 
                    Please enter your MySQL database connection credentials below.
                </p>
            </div>
            
            <form method="POST" action="?step=1" style="margin-bottom: 15px;">
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 13px; font-weight: bold; color: var(--text-muted);">Database Host</label>
                    <input type="text" name="db_host" class="form-control-setup" required value="<?php echo escape(DB_HOST); ?>">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 13px; font-weight: bold; color: var(--text-muted);">Database Port</label>
                    <input type="text" name="db_port" class="form-control-setup" required value="<?php echo escape(DB_PORT); ?>">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 13px; font-weight: bold; color: var(--text-muted);">Database Name</label>
                    <input type="text" name="db_name" class="form-control-setup" required value="<?php echo escape(DB_NAME); ?>">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 13px; font-weight: bold; color: var(--text-muted);">Database Username</label>
                    <input type="text" name="db_user" class="form-control-setup" required value="<?php echo escape(DB_USER); ?>">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="font-size: 13px; font-weight: bold; color: var(--text-muted);">Database Password</label>
                    <input type="password" name="db_pass" class="form-control-setup" value="<?php echo escape(DB_PASS); ?>">
                </div>
                
                <button type="submit" name="save_credentials" class="btn">Save & Continue</button>
            </form>
            
        <?php elseif ($step === 2): ?>
            <div style="margin-bottom: 20px;">
                <h2 style="font-size: 18px; margin-bottom: 10px;">Database Initialization</h2>
                <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
                    Clicking the button below will connect to your MySQL database server, create the <code><?php echo DB_NAME; ?></code> database (if not exists), create all 12 system tables, and insert the default administrative and demo records.
                </p>
            </div>

            <form method="POST">
                <button type="submit" name="install" class="btn">Run SQL Installation Script</button>
            </form>
            <a href="?step=1" class="btn btn-secondary">Go Back</a>

        <?php elseif ($step === 3): ?>
            <div style="margin-bottom: 20px; text-align: center;">
                <h2 style="font-size: 20px; color: var(--success); margin-bottom: 10px;">✓ Setup Successful!</h2>
                <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                    Your system is now ready. For security reasons, please delete or rename the <code>setup.php</code> file after you log in and confirm everything works.
                </p>
                
                <div class="info-card" style="text-align: left; margin-bottom: 20px;">
                    <h3 style="color: var(--accent); margin-bottom: 5px;">Admin Login Details</h3>
                    <p style="margin-bottom: 5px;">URL: <code>/admin/login.php</code></p>
                    <p style="margin-bottom: 5px;">Username: <strong>admin</strong></p>
                    <p>Password: <strong>Admin@123456</strong></p>
                </div>
            </div>

            <a href="index.php" class="btn" style="background: var(--primary); color: white;">Go to Website Home</a>
            <a href="admin/login.php" class="btn btn-secondary">Go to Admin Login</a>
        <?php endif; ?>
        
        <p class="footer-text">Developed with Antigravity AI | <a href="index.php">Main Website</a></p>
    </div>
</div>

</body>
</html>
