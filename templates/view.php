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

// Прямой вывод файла без HTML и без редиректа
$mime = 'application/octet-stream';
if (isImage($ext)) {
	$mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
} elseif (isVideo($ext)) {
	$mime = 'video/' . $ext;
} elseif (isAudio($ext)) {
	$mime = 'audio/mpeg';
}

// Для изображений/видео/аудио — inline; для остальных — attachment
$isInline = (strpos($mime, 'image/') === 0 || strpos($mime, 'video/') === 0 || strpos($mime, 'audio/') === 0);
$disposition = $isInline ? 'inline' : 'attachment';

header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode(basename($filename)) . '"');
header('Content-Length: ' . filesize($filepath));
header('Accept-Ranges: bytes');
readfile($filepath);
exit;