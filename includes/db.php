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

    // Auto-migration: Ensure remember_token column exists in users table
    try {
        $pdo->query("SELECT `remember_token` FROM `users` LIMIT 1");
    } catch (PDOException $ex) {
        try {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(255) NULL AFTER `email`");
        } catch (PDOException $alterEx) {}
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
