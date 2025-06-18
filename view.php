<?php
$uploadDir = __DIR__ . '/uploads/';
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
        if ($meta && isset($meta['orig'])) {
            $filename = $meta['orig'];
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

// Проверка срока хранения
$metaFile = $uploadDir . $filename . '.meta';
$expiresIn = '';
if (file_exists($metaFile)) {
    $created = (int)file_get_contents($metaFile);
    $left = $created + $ttl - time();
    if ($left > 0) {
        $days = floor($left / 86400);
        $hours = floor(($left % 86400) / 3600);
        $expiresIn = ($days > 0 ? $days . 'd ' : '') . $hours . 'h left';
    } else {
        $expiresIn = 'Expired';
        @unlink($filepath);
    }
}

if (!$filename || !preg_match('/^[a-zA-Z0-9._-]+$/', $filename) || !file_exists($filepath)) {
    // 404 страница
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>link not found</title>
        <style>
            body {
                background: #181a1b;
                color: #fff;
                min-height: 100vh;
                margin: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .notfound {
                font-size: 2.2rem;
                font-weight: 700;
                margin-bottom: 32px;
                text-align: center;
            }
            .home-btn {
                background: linear-gradient(90deg, #8f5cff 0%, #5865f2 100%);
                color: #fff;
                border: none;
                border-radius: 8px;
                padding: 12px 36px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                transition: background 0.2s, box-shadow 0.2s;
                box-shadow: 0 2px 12px #0006;
            }
            .home-btn:hover {
                background: linear-gradient(90deg, #5865f2 0%, #8f5cff 100%);
            }
        </style>
    </head>
    <body>
        <div class="notfound">link not found</div>
        <a href="https://hapka.lol" class="home-btn">Home</a>
    </body>
    </html>
    <?php
    exit;
}

if (isImage($ext)) {
    // Просмотр изображения
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Image</title>
        <style>
            body {
                text-align: center;
                margin: 40px;
                font-family: Arial, sans-serif;
                background: #181a1b;
            }
            img {
                max-width: 90vw;
                max-height: 80vh;
                border: 1px solid #333;
                border-radius: 10px;
                background: #23272a;
                box-shadow: 0 2px 16px #0008;
            }
            .expires {
                color: #888;
                font-size: 1.05em;
                margin: 18px 0 0 0;
            }
        </style>
    </head>
    <body>
        <img src="uploads/<?= htmlspecialchars($filename) ?>" alt="image"><br>
        <?php if ($expiresIn): ?><div class="expires">Storage: <?= htmlspecialchars($expiresIn) ?></div><?php endif; ?>
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
        <title>Video</title>
        <style>
            body {
                text-align: center;
                margin: 40px;
                font-family: Arial, sans-serif;
                background: #181a1b;
            }
            video {
                max-width: 90vw;
                max-height: 80vh;
                border: 1px solid #333;
                border-radius: 10px;
                background: #23272a;
                box-shadow: 0 2px 16px #0008;
            }
            .expires {
                color: #888;
                font-size: 1.05em;
                margin: 18px 0 0 0;
            }
        </style>
    </head>
    <body>
        <video controls>
            <source src="uploads/<?= htmlspecialchars($filename) ?>">
            Your browser does not support the video tag.
        </video><br>
        <?php if ($expiresIn): ?><div class="expires">Storage: <?= htmlspecialchars($expiresIn) ?></div><?php endif; ?>
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
        <title>Audio</title>
        <style>
            body {
                text-align: center;
                margin: 40px;
                font-family: Arial, sans-serif;
                background: #181a1b;
            }
            audio {
                width: 90vw;
                max-width: 600px;
                margin-top: 40px;
            }
            .expires {
                color: #888;
                font-size: 1.05em;
                margin: 18px 0 0 0;
            }
        </style>
    </head>
    <body>
        <audio controls>
            <source src="uploads/<?= htmlspecialchars($filename) ?>" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio><br>
        <?php if ($expiresIn): ?><div class="expires">Storage: <?= htmlspecialchars($expiresIn) ?></div><?php endif; ?>
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