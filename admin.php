<?php
session_start();
// Load password from .env
function getEnvVar($key) {
    $lines = @file(__DIR__ . '/.env');
    if (!$lines) return null;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            if (trim($k) === $key) return trim($v);
        }
    }
    return null;
}
$adminPassword = getEnvVar('ADMIN_PASSWORD');
if (!$adminPassword) {
    die('Admin password not set in .env');
}
// Authorization
if (isset($_POST['password'])) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Incorrect password';
    }
}
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header('Location: admin.php');
    exit;
}
if (empty($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><title>Admin Panel — hapka.lol</title></head>
    <body style="background:#181a1b;color:#fff;font-family:sans-serif;text-align:center;padding:40px;">
        <h2>Admin Login</h2>
        <?php if (!empty($error)) echo '<div style="color:#f55">'.$error.'</div>'; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Password" style="padding:8px;font-size:1.1em;">
            <button type="submit" style="padding:8px 18px;">Login</button>
        </form>
    </body></html>
    <?php
    exit;
}
// File statistics
$uploadDir = __DIR__ . '/uploads/';
$allFiles = glob($uploadDir . '*');
$currentFiles = 0;
foreach ($allFiles as $file) {
    if (is_file($file) && substr($file, -5) !== '.meta') {
        $currentFiles++;
    }
}
$metaFiles = glob($uploadDir . '*.meta');
$uploadedLastMonth = 0;
$monthAgo = time() - 30 * 24 * 60 * 60;
foreach ($metaFiles as $meta) {
    $metaData = @json_decode(@file_get_contents($meta), true);
    if ($metaData && isset($metaData['created'])) {
        if ($metaData['created'] >= $monthAgo) {
            $uploadedLastMonth++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel — hapka.lol</title>
    <style>
        body { background:#181a1b; color:#fff; font-family:sans-serif; padding:40px; }
        .stat { font-size:1.3em; margin:18px 0; }
        .logout { color:#8ab4f8; text-decoration:none; margin-left:18px; }
        .logout:hover { text-decoration:underline; }
        .site-link {
            display: inline-block;
            margin-bottom: 28px;
            padding: 10px 28px;
            background: linear-gradient(90deg, #8f5cff 0%, #5865f2 100%);
            color: #fff;
            border-radius: 10px;
            font-size: 1.15em;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 12px #0006;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .site-link:hover {
            background: linear-gradient(90deg, #5865f2 0%, #8f5cff 100%);
            box-shadow: 0 4px 24px #0008;
            color: #fff;
        }
    </style>
</head>
<body>
    <h2>hapka.lol Admin Panel</h2>
    <a href="https://hapka.lol" target="_blank" class="site-link">🌐 Go to hapka.lol</a>
    <div class="stat">Current files: <b><?= $currentFiles ?></b></div>
    <div class="stat">Uploaded in last 30 days: <b><?= $uploadedLastMonth ?></b></div>
    <a href="?logout=1" class="logout">Logout</a>
</body>
</html> 