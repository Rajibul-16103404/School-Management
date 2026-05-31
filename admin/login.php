<?php
/**
 * Admin Login Page
 * School Management Website
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/admin/index.php");
    exit;
}

$error_msg = null;
$timeout = isset($_GET['timeout']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_msg = "ইউজারনেম এবং পাসওয়ার্ড প্রদান করুন।";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `username` = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name_bn'];
                $_SESSION['last_activity'] = time();

                // Audit log entry
                log_activity($pdo, "Admin Login", "User '$username' successfully logged in.");

                header("Location: " . BASE_URL . "/admin/index.php");
                exit;
            } else {
                $error_msg = "ভুল ইউজারনেম অথবা পাসওয়ার্ড!";
                // Log failed attempt
                log_activity($pdo, "Failed Login Attempt", "Username tried: '$username'");
            }
        } catch (PDOException $e) {
            $error_msg = "সার্ভার ত্রুটি: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sonargaon High School</title>
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

        .login-card {
            background: var(--panel);
            max-width: 420px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid var(--accent);
        }

        .card-header h1 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .card-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: bold;
        }

        .form-control {
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text);
            padding: 12px;
            border-radius: 8px;
            outline: none;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.1);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.4;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--danger);
            color: #fca5a5;
        }

        .alert-warning {
            background: rgba(212, 175, 55, 0.15);
            border: 1px solid var(--accent);
            color: #fde047;
        }

        .btn-login {
            width: 100%;
            background: var(--accent);
            color: #000;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--accent-hover);
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .footer-link a {
            color: var(--accent);
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-header">
        <h1>কন্ট্রোল প্যানেল লগইন</h1>
        <p>Sonargaon High School Management System</p>
    </div>
    
    <div class="card-body">
        <?php if ($error_msg): ?>
            <div class="alert alert-danger">⚠️ <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if ($timeout): ?>
            <div class="alert alert-warning">🕒 নিষ্ক্রিয়তার কারণে সেশন শেষ হয়েছে। দয়া করে পুনরায় লগইন করুন।</div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">ইউজারনেম (Username)</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="admin" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">পাসওয়ার্ড (Password)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>
            
            <button type="submit" name="login" class="btn-login">লগইন করুন</button>
        </form>
        
        <div class="footer-link">
            <a href="../index.php">← মূল ওয়েবসাইটে ফিরুন</a>
        </div>
    </div>
</div>

</body>
</html>
