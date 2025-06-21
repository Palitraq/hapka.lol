<?php
$uploadDir = __DIR__ . '/uploads/';
$code = isset($_GET['code']) ? preg_replace('/[^a-zA-Z]/', '', $_GET['code']) : '';
if (!$code || strlen($code) !== 5) {
    http_response_code(404);
    exit('Not found');
}
$metaPath = $uploadDir . $code . '.meta';
if (!file_exists($metaPath)) {
    http_response_code(404);
    exit('Not found');
}
$meta = @json_decode(@file_get_contents($metaPath), true);
if (!$meta || !isset($meta['orig'])) {
    http_response_code(404);
    exit('Not found');
}
$origName = $meta['orig'];
$savedName = isset($meta['saved']) ? $meta['saved'] : $origName;
$filePath = $uploadDir . $savedName;
if (!file_exists($filePath)) {
    http_response_code(404);
    exit('Not found');
}
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';
if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
    $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
} elseif (in_array($ext, ['mp4','webm','mov','avi','mkv'])) {
    $mime = $ext === 'avif' ? 'video/avif' : 'video/' . $ext;
} elseif ($ext === 'mp3') {
    $mime = 'audio/mp3';
}
if ($ext === 'avif') {
    http_response_code(404);
    exit('Not found');
}
// Для изображений, видео, mp3 — inline, для остальных — attachment
$disposition = (strpos($mime, 'image/') === 0 || strpos($mime, 'video/') === 0 || strpos($mime, 'audio/') === 0)
    ? 'inline' : 'attachment';
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($origName) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath); 