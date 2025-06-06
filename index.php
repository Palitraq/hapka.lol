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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
    <?php if ($link): ?>
        <div class="link">
            <b>File link:</b><br>
            <a href="<?= $link ?>" target="_blank"><?= $link ?></a>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['history'])): ?>
        <div class="link" style="margin-top:30px;">
            <b>Upload history:</b>
            <form method="get" style="display:inline; float:right; margin:0;">
                <button type="submit" name="clear_history" value="1" style="background:#23272a;color:#fff;border:1px solid #36393f;border-radius:6px;padding:4px 14px 4px 10px;font-size:1rem;cursor:pointer;transition:background 0.2s;">Clear history</button>
            </form>
            <ul style="padding-left:18px; clear:both;">
            <?php foreach ($_SESSION['history'] as $item): ?>
                <?php
                $metaFile = $uploadDir . $item['link'] . '.meta';
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
                    }
                }
                ?>
                <li><a href="<?= $item['link'] ?>" target="_blank"><?= $item['filename'] ?></a> <span style="color:#888;font-size:0.95em;">(<?= $expiresIn ?>)</span></li>
            <?php endforeach; ?>
            </ul>
        </div>
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
<script>
// Paste screenshot support
const fileInput = document.getElementById('fileInput');
const uploadForm = document.getElementById('uploadForm');
const preview = document.getElementById('preview');

document.addEventListener('paste', function (event) {
    const items = (event.clipboardData || event.originalEvent.clipboardData).items;
    for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') !== -1) {
            const blob = items[i].getAsFile();
            const dt = new DataTransfer();
            dt.items.add(blob);
            fileInput.files = dt.files;
            // Show preview
            const img = document.createElement('img');
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.src = URL.createObjectURL(blob);
            preview.innerHTML = '';
            preview.appendChild(img);
            // Auto submit
            setTimeout(() => uploadForm.submit(), 100);
        }
    }
});
fileInput.addEventListener('change', function() {
    let label = document.getElementById('fileLabelText');
    if (fileInput.files.length) {
        label.textContent = fileInput.files[0].name;
    } else {
        label.textContent = 'Choose file';
    }
    preview.innerHTML = '';
    if (fileInput.files.length && fileInput.files[0].type.startsWith('image/')) {
        const img = document.createElement('img');
        img.style.maxWidth = '100%';
        img.style.maxHeight = '200px';
        img.src = URL.createObjectURL(fileInput.files[0]);
        preview.appendChild(img);
    }
    if (fileInput.files.length) {
        setTimeout(() => uploadForm.submit(), 100);
    }
});
document.querySelector('.support-btn').onclick = function(e) {
    e.preventDefault();
    document.getElementById('support-modal').style.display = 'flex';
};
document.getElementById('support-close').onclick = function() {
    document.getElementById('support-modal').style.display = 'none';
};
window.onclick = function(event) {
    let modal = document.getElementById('support-modal');
    if (event.target === modal) modal.style.display = 'none';
};
// Drag & Drop upload
window.addEventListener('dragover', function(e) {
    e.preventDefault();
    document.body.classList.add('body-dragover');
});
window.addEventListener('dragleave', function(e) {
    if (e.target === document.body) {
        document.body.classList.remove('body-dragover');
    }
});
window.addEventListener('drop', function(e) {
    e.preventDefault();
    document.body.classList.remove('body-dragover');
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        let label = document.getElementById('fileLabelText');
        label.textContent = fileInput.files[0].name;
        preview.innerHTML = '';
        if (fileInput.files[0].type.startsWith('image/')) {
            const img = document.createElement('img');
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.src = URL.createObjectURL(fileInput.files[0]);
            preview.appendChild(img);
        }
        setTimeout(() => uploadForm.submit(), 100);
    }
});
</script>
</body>
</html> 