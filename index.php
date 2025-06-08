<?php
session_start();
$maxFileSize = 100 * 1024 * 1024; // 100 MB
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$storageDays = 30;
$ttl = $storageDays * 24 * 60 * 60;

// Удаление старых файлов
foreach (glob($uploadDir . '*') as $file) {
    if (preg_match('/\.meta$/', $file)) {
        $base = substr($file, 0, -5);
        if (!file_exists($base)) {
            unlink($file);
            continue;
        }
        $created = (int)@file_get_contents($file);
        if ($created && $created + $ttl < time()) {
            @unlink($base);
            @unlink($file);
        }
    }
}

function getExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}
function isImage($ext) {
    return in_array($ext, ['jpg','jpeg','png','gif','webp']);
}
function isVideo($ext) {
    return in_array($ext, ['mp4','webm','mov','avi','mkv']);
}
function isAudio($ext) {
    return in_array($ext, ['mp3']);
}
function randomString($length = 5) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $str;
}

// Обработка загрузки
$error = '';
$link = '';
if (isset($_GET['link'])) {
    $link = htmlspecialchars($_GET['link']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error.';
    } elseif ($file['size'] > $maxFileSize) {
        $error = 'File is too large (max 100 MB).';
    } else {
        $ext = getExtension($file['name']);
        do {
            $uniq = randomString(5) . '.' . $ext;
            $target = $uploadDir . $uniq;
        } while (file_exists($target));
        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Сохраняем дату загрузки
            file_put_contents($uploadDir . $uniq . '.meta', time());
            // История загрузок в сессии
            if (!isset($_SESSION['history'])) $_SESSION['history'] = [];
            array_unshift($_SESSION['history'], [
                'link' => $uniq,
                'filename' => htmlspecialchars($file['name'])
            ]);
            $_SESSION['history'] = array_slice($_SESSION['history'], 0, 10);
            header('Location: index.php?link=' . urlencode($uniq));
            exit;
        } else {
            $error = 'Failed to save file.';
        }
    }
}

// Обработка очистки истории
if (isset($_GET['clear_history'])) {
    unset($_SESSION['history']);
    header('Location: index.php');
    exit;
}

// PHP: обработка удаления из истории
if (isset($_GET['del_history'])) {
    $i = (int)$_GET['del_history'];
    if (isset($_SESSION['history'][$i])) {
        // Удаляем файл и .meta, если передан del_file
        if (isset($_GET['del_file'])) {
            $file = basename($_GET['del_file']);
            $filePath = $uploadDir . $file;
            $metaPath = $filePath . '.meta';
            if (file_exists($filePath)) unlink($filePath);
            if (file_exists($metaPath)) unlink($metaPath);
        }
        array_splice($_SESSION['history'], $i, 1);
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File upload — hapka.lol</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #181a1b;
            color: #f1f1f1;
        }
        .header-wrap {
            max-width: 600px;
            margin: 0 auto 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0 8px 0;
            margin-bottom: 0;
            background: transparent;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .logo {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }
        .logo-gradient {
            background: linear-gradient(90deg, #8f5cff 0%, #5865f2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .nav {
            margin-left: 18px;
            font-size: 1rem;
            color: #8ab4f8;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-link {
            color: #8ab4f8;
            text-decoration: none;
            transition: color 0.2s;
        }
        .nav-link:hover {
            color: #fff;
            text-decoration: underline;
        }
        .nav-dot {
            color: #5865f2;
            margin: 0 4px;
            font-size: 1.1em;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .header-link {
            color: #8ab4f8;
            text-decoration: none;
            font-size: 1rem;
            padding: 4px 10px;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
        }
        .header-link:hover {
            background: #23272a;
            color: #fff;
        }
        .support-btn {
            background: #23272a;
            color: #fff;
            border-radius: 6px;
            padding: 4px 14px 4px 10px;
            font-size: 1rem;
            text-decoration: none;
            font-weight: 500;
            border: 1px solid #36393f;
            transition: background 0.2s, border 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .support-btn:hover {
            background: #5865f2;
            border: 1px solid #5865f2;
            color: #fff;
        }
        .header-underline {
            height: 3px;
            background: linear-gradient(90deg, #5865f2 0%, #8ab4f8 100%);
            border-radius: 2px;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #202225;
            border-radius: 12px;
            border: 1px solid #23272a;
            box-shadow: none;
            padding: 18px 28px 24px 28px;
        }
        input[type=file] {
            margin-bottom: 10px;
            color: #f1f1f1;
            background: #23272a;
            border: 1px solid #444;
            border-radius: 6px;
            padding: 8px;
        }
        input[type=file]::file-selector-button {
            background: #36393f;
            color: #f1f1f1;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        input[type=file]::file-selector-button:hover {
            background: #5865f2;
            color: #fff;
        }
        .link {
            margin-top: 20px;
            color: #b9bbbe;
        }
        .link a {
            color: #8ab4f8;
            word-break: break-all;
        }
        .link a:hover {
            color: #fff;
            text-decoration: underline;
        }
        .error {
            color: #ff6b6b;
        }
        ul {
            margin: 0;
            margin-top: 8px;
            padding-left: 18px;
        }
        li {
            margin-bottom: 4px;
        }
        #preview img {
            border-radius: 8px;
            box-shadow: 0 2px 8px #0006;
        }
        .upload-btn {
            display: none;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #23272a;
            color: #fff;
            border-radius: 10px;
            max-width: 420px;
            width: 90vw;
            margin: auto;
            padding: 32px 28px 24px 28px;
            box-shadow: 0 2px 16px #000a;
            position: relative;
            font-size: 1.05rem;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-close {
            position: absolute;
            top: 12px; right: 18px;
            font-size: 1.7rem;
            color: #8ab4f8;
            cursor: pointer;
            font-weight: bold;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: #ff6b6b;
        }
        .custom-file-upload {
            display: inline-block;
            padding: 10px 22px;
            background: #23272a;
            color: #f1f1f1;
            border: 1px solid #444;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.2s, border 0.2s, color 0.2s;
            margin-bottom: 10px;
            user-select: none;
        }
        .custom-file-upload:hover {
            background: #36393f;
            color: #fff;
            border: 1px solid #5865f2;
        }
        .custom-file-upload input[type="file"] {
            display: none;
        }
        #fileLabelText {
            margin-left: 8px;
            color: #b9bbbe;
            font-weight: 400;
        }
        .body-dragover {
            outline: 3px dashed #5865f2 !important;
            outline-offset: -8px;
            background: #202225 !important;
        }
        @media (max-width: 700px) {
            .header-wrap, .header, .header-underline, .container {
                max-width: 98vw !important;
                min-width: 0;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            .container {
                padding: 10vw 2vw 6vw 2vw !important;
                font-size: 1.05em;
            }
            .header {
                padding: 12px 0 6px 0;
            }
            .header-left, .header-right {
                gap: 10px;
            }
            .logo {
                font-size: 1.3rem;
            }
            .modal-content {
                max-width: 98vw;
                padding: 18px 8vw 18px 8vw;
                font-size: 1em;
            }
            .custom-file-upload {
                font-size: 1em;
                padding: 12px 10px;
            }
            #fileLabelText {
                font-size: 1em;
            }
            .link, .error {
                font-size: 1em;
            }
            ul {
                padding-left: 10px !important;
            }
            .modal-close {
                top: 8px;
                right: 12px;
                font-size: 1.3rem;
            }
            audio, video, img {
                max-width: 98vw !important;
            }
        }
        .history-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-top: 30px;
        }
        .history-card {
            display: flex;
            background: #23272a;
            border: 1.5px solid #36393f;
            border-radius: 12px;
            box-shadow: 0 2px 12px #0004;
            padding: 16px 18px;
            gap: 18px;
            align-items: center;
            max-width: 100%;
        }
        .history-preview {
            flex: 0 0 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 64px;
            width: 64px;
            background: #181a1b;
            border-radius: 8px;
            overflow: hidden;
        }
        .history-thumb {
            max-width: 64px;
            max-height: 64px;
            border-radius: 6px;
            display: block;
        }
        .history-icon {
            font-size: 2.2rem;
            opacity: 0.7;
        }
        .history-info {
            flex: 1 1 auto;
            min-width: 0;
        }
        .history-filename {
            font-weight: 600;
            font-size: 1.08em;
            margin-bottom: 4px;
            color: #fff;
            word-break: break-all;
        }
        .history-link-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
        }
        .history-link {
            flex: 1 1 auto;
            background: #181a1b;
            color: #8ab4f8;
            border: none;
            border-radius: 5px;
            padding: 4px 8px;
            font-size: 1em;
            outline: none;
            word-break: break-all;
        }
        .copy-btn, .del-btn {
            background: #23272a;
            color: #8ab4f8;
            border: 1px solid #36393f;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .copy-btn:hover, .del-btn:hover {
            background: #5865f2;
            color: #fff;
        }
        .history-meta {
            color: #b9bbbe;
            font-size: 0.97em;
            display: flex;
            gap: 12px;
            margin-top: 2px;
            flex-wrap: wrap;
        }
        @media (max-width: 700px) {
            .history-card {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 6vw;
                gap: 10px;
            }
            .history-preview {
                margin: 0 auto 8px auto;
                width: 56px;
                height: 56px;
            }
            .history-thumb {
                max-width: 56px;
                max-height: 56px;
            }
            .history-link-row {
                flex-direction: row;
                gap: 4px;
            }
        }
    </style>
</head>
<body>
<div class="header-wrap">
    <div class="header">
        <div class="header-left">
            <span class="logo"><a href="https://hapka.lol" target="_blank" rel="noopener" class="logo-gradient" style="text-decoration:none;">hapka.lol</a></span>
        </div>
        <div class="header-right">
            <a href="https://github.com/Palitraq/hapka.lol" class="header-link">GitHub</a>
            <span class="nav-dot">&bull;</span>
            <a href="#" class="support-btn">&#10084; Support</a>
        </div>
    </div>
    <div class="header-underline"></div>
</div>
<div class="container">
    <div style="text-align:left; margin-bottom:18px;">
        <a href="#" id="terms-link" style="color:#8ab4f8;text-decoration:underline;font-size:1.05em;">Terms and Privacy Policy</a>
    </div>
    <h2>Upload file (up to 100 MB)</h2>
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form id="uploadForm" method="post" enctype="multipart/form-data" autocomplete="off" style="margin-top:0;margin-bottom:0;">
        <label class="custom-file-upload" style="margin-top:0;margin-bottom:10px;">
            <input type="file" id="fileInput" name="file" required>
            <span id="fileLabelText">Choose file</span>
        </label>
    </form>
    <div id="preview"></div>
    <?php if (!empty($_SESSION['history'])): ?>
        <div class="history-list">
        <?php foreach ($_SESSION['history'] as $idx => $item): ?>
            <?php
            $metaFile = $uploadDir . $item['link'] . '.meta';
            $expiresIn = '';
            $createdStr = '';
            $type = '';
            $isImg = false;
            $isVid = false;
            $isAudio = false;
            $filePath = $uploadDir . $item['link'];
            $ext = strtolower(pathinfo($item['link'], PATHINFO_EXTENSION));
            if (file_exists($metaFile)) {
                $created = (int)file_get_contents($metaFile);
                $createdStr = date('Y-m-d H:i:s', $created) . ' GMT+3';
                $left = $created + $ttl - time();
                if ($left > 0) {
                    $days = floor($left / 86400);
                    $hours = floor(($left % 86400) / 3600);
                    $expiresIn = ($days > 0 ? $days . 'd ' : '') . $hours . 'h left';
                } else {
                    $expiresIn = 'Expired';
                }
            }
            if (isImage($ext)) {
                $type = 'image/' . $ext;
                $isImg = true;
            } elseif (isVideo($ext)) {
                $type = 'video/' . $ext;
                $isVid = true;
            } elseif (isAudio($ext)) {
                $type = 'audio/' . $ext;
                $isAudio = true;
            } else {
                $type = $ext;
            }
            $url = htmlspecialchars($item['link']);
            $filename = htmlspecialchars($item['filename']);
            $sizeMB = '';
            if (file_exists($filePath)) {
                $sizeMB = number_format(filesize($filePath) / 1048576, 2) . ' MB';
            }
            ?>
            <div class="history-card">
                <div class="history-preview">
                    <?php if ($isImg && file_exists($filePath)): ?>
                        <img src="uploads/<?= $url ?>" alt="preview" class="history-thumb">
                    <?php elseif ($isVid): ?>
                        <div class="history-icon history-video">🎬</div>
                    <?php elseif ($isAudio): ?>
                        <div class="history-icon history-audio">🎵</div>
                    <?php else: ?>
                        <div class="history-icon history-file">📄</div>
                    <?php endif; ?>
                </div>
                <div class="history-info">
                    <div class="history-filename"><a class="history-filename" href="<?= $url ?>" target="_blank"><?= $filename ?></a></div>
                    <div class="history-link-row">
                        <input class="history-link" type="text" value="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/' . $url ?>" readonly>
                        <button class="copy-btn" data-link="<?= $url ?>" title="Copy">📋</button>
                        <button class="del-btn" data-idx="<?= $idx ?>" data-file="<?= $url ?>" title="Remove">🗑️</button>
                    </div>
                    <div class="history-size" style="color:#b9bbbe;font-size:0.97em;margin-bottom:2px;">
                        <?= $sizeMB ?>
                    </div>
                    <div class="history-meta">
                        <span><?= $createdStr ?></span>
                        <span><?= $type ?></span>
                        <span><?= $expiresIn ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <script>
        // Copy to clipboard
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.onclick = function() {
                const link = btn.parentElement.querySelector('.history-link').value;
                navigator.clipboard.writeText(link);
                btn.textContent = '✔';
                setTimeout(()=>{btn.textContent='📋';}, 1000);
            };
        });
        // Remove from history
        document.querySelectorAll('.del-btn').forEach(btn => {
            btn.onclick = function() {
                const idx = btn.getAttribute('data-idx');
                const file = btn.getAttribute('data-file');
                window.location = '?del_history=' + idx + '&del_file=' + encodeURIComponent(file);
            };
        });
        </script>
    <?php endif; ?>
</div>
<div id="support-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="modal-close" id="support-close">&times;</span>
    <h2>&hearts; Support my life</h2>
    <div style="margin-bottom: 12px;">
      <b>Bitcoin:</b><br>
      <span style="word-break:break-all;">bc1qh6r4ptmh5txv43c50s4wfv8ts5z06453ss3tmc</span>
    </div>
    <div style="margin-bottom: 12px;">
      <b>USDT TRC20:</b><br>
      <span style="word-break:break-all;">TNDvHcXWUcbjoJGQpEd6J3VygKT7RCHZ4g</span>
    </div>
    <div>
      <b>TON:</b><br>
      <span style="word-break:break-all;">UQCR0jBsHh8jSKw-hrs2cBehRg0rDdIOeZPOIiMYKoCBtQq9</span>
    </div>
  </div>
</div>
<div id="terms-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="modal-close" id="terms-close">&times;</span>
    <h2>Terms and Privacy Policy</h2>
    <h3>Terms of Service</h3>
    <ul style="margin-bottom:18px;">
      <li>This service allows you to upload and share files up to 100 MB.</li>
      <li>All files are stored for 30 days and then automatically deleted.</li>
      <li>You are solely responsible for the content you upload.</li>
      <li>Do not upload illegal, harmful, or copyrighted content without permission.</li>
      <li>The service is provided "as is" without any warranty.</li>
    </ul>
    <h3>Privacy Policy</h3>
    <ul>
      <li>No registration or personal data is required to use this service.</li>
      <li>Uploaded files and access links are not indexed or shared publicly.</li>
      <li>Your upload history is stored only in your browser session and is not accessible to others.</li>
      <li>We do not track users or use cookies for advertising.</li>
      <li>Files may be removed at any time for abuse or legal reasons.</li>
    </ul>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html> 