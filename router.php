<?php
/**
 * Local Development Router Script
 * For PHP built-in web server: php -S localhost:8000 router.php
 */

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$queryString = parse_url($requestUri, PHP_URL_QUERY);

// 1. External Redirect: Clean up any direct requests to index.php or *.php
if (preg_match('/^(.+)\/index\.php$/i', $path, $matches)) {
    $cleanPath = $matches[1] ?: '/';
    $target = $cleanPath . ($queryString ? '?' . $queryString : '');
    header("Location: " . $target, true, 301);
    exit;
} elseif (preg_match('/^(.+)\.php$/i', $path, $matches)) {
    $cleanPath = $matches[1];
    $target = $cleanPath . ($queryString ? '?' . $queryString : '');
    header("Location: " . $target, true, 301);
    exit;
}

$file = __DIR__ . $path;

// 2. If it's a real file that isn't a PHP file, serve it directly (e.g. CSS, JS, images)
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
    return false;
}

// 3. If it's a directory, check if index.php exists inside it
if (is_dir($file)) {
    $indexPath = rtrim($file, '/') . '/index.php';
    if (is_file($indexPath)) {
        $_SERVER['SCRIPT_NAME'] = rtrim($path, '/') . '/index.php';
        $_SERVER['PHP_SELF'] = rtrim($path, '/') . '/index.php';
        include $indexPath;
        exit;
    }
}

// 4. If appending .php results in a file
$phpFile = rtrim($file, '/') . '.php';
if (is_file($phpFile)) {
    $_SERVER['SCRIPT_NAME'] = rtrim($path, '/') . '.php';
    $_SERVER['PHP_SELF'] = rtrim($path, '/') . '.php';
    include $phpFile;
    exit;
}

// 5. Fallback to normal behavior (404)
return false;
