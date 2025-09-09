<?php
session_start();
$uploadDir = dirname(__DIR__) . '/uploads/';
$storageDays = 30;
$ttl = $storageDays * 24 * 60 * 60;

$filename = isset($_GET['f']) ? basename($_GET['f']) : '';
$filepath = $uploadDir . $filename;
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Если передан code, ищем .meta и подставляем имя файла
if (isset($_GET['code'])) {
    $code = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['code']);
    $metaPath = $uploadDir . $code . '.meta';
    if (file_exists($metaPath)) {
        $meta = @json_decode(@file_get_contents($metaPath), true);
        if ($meta && (isset($meta['saved']) || isset($meta['orig']))) {
            $filename = isset($meta['saved']) ? $meta['saved'] : $meta['orig'];
            $filepath = $uploadDir . $filename;
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        }
    }
}

function isImage($ext) {
    return in_array($ext, ['jpg','jpeg','png','gif','webp','avif']);
}
function isVideo($ext) {
    return in_array($ext, ['mp4','webm','mov','avi','mkv']);
}
function isAudio($ext) {
    return in_array($ext, ['mp3']);
}

if (!$filename || !preg_match('/^[a-zA-Z0-9._+\-]+$/', $filename) || !file_exists($filepath)) {
    // 404 страница
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// --- Счётчик уникальных просмотров (по сессии) ---
$viewsFile = $uploadDir . $filename . '.views';
$sessionKey = 'viewed_' . $filename;
$views = 0;
if (file_exists($viewsFile)) {
    $views = (int)file_get_contents($viewsFile);
}
if (empty($_SESSION[$sessionKey])) {
    $views++;
    file_put_contents($viewsFile, $views);
    $_SESSION[$sessionKey] = true;
}

if (isImage($ext)) {
    // Просмотр изображения
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" type="image/png" href="/static/logo.png">
        <title><?= isset($code) ? htmlspecialchars($code) : 'Image' ?> - hapka.lol</title>
        <link rel="stylesheet" href="../css/view.css">
    </head>
    <body>
        <img class="zoomable" src="/uploads/<?= htmlspecialchars($filename) ?>" alt="image"><br>
        <div class="views">
            <span style="font-size: 20px;">&#128064;</span>
            <span style="font-size: 20px; font-weight: 500; margin-left: 4px; vertical-align: middle; position: relative; top: 6px;"><?= $views ?></span>
        </div>
        <script src="../js/view.js"></script>
    </body>
    </html>
    <?php
    exit;
} elseif (isVideo($ext)) {
    // Просмотр видео
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" type="image/png" href="/static/logo.png">
        <title><?= isset($code) ? htmlspecialchars($code) : 'Video' ?> - hapka.lol</title>
        <link rel="stylesheet" href="../css/view.css">
    </head>
    <body>
        <video controls>
            <source src="/uploads/<?= htmlspecialchars($filename) ?>">
            Your browser does not support the video tag.
        </video><br>
        <div class="views">
            <span style="font-size: 20px;">&#128064;</span>
            <span style="font-size: 20px; font-weight: 500; margin-left: 4px; vertical-align: middle; position: relative; top: 6px;"><?= $views ?></span>
        </div>
    </body>
    </html>
    <?php
    exit;
} elseif (isAudio($ext)) {
    // Просмотр аудио
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" type="image/png" href="/static/logo.png">
        <title><?= isset($code) ? htmlspecialchars($code) : 'Audio' ?> - hapka.lol</title>
        <link rel="stylesheet" href="../css/view.css">
    </head>
    <body>
        <audio controls>
            <source src="/uploads/<?= htmlspecialchars($filename) ?>" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio><br>
        <div class="views">
            <span style="font-size: 20px;">&#128064;</span>
            <span style="font-size: 20px; font-weight: 500; margin-left: 4px; vertical-align: middle; position: relative; top: 6px;"><?= $views ?></span>
        </div>
    </body>
    </html>
    <?php
    exit;
} else {
    // Скачивание других файлов
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
} 