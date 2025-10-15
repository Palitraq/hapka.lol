<?php
session_start();
$maxFileSize = 100 * 1024 * 1024; // 100 MB
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$storageDays = 30;
$ttl = $storageDays * 24 * 60 * 60;

// Удаление старых файлов
// (удалено)

function getExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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
function randomString($length = 5) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $str;
}
function sanitizeFileName($name) {
    $name = str_replace(' ', '_', $name);
    return $name;
}

// Обработка очистки истории
if (isset($_GET['clear_history'])) {
    unset($_SESSION['history']);
    header('Location: index.php');
    exit;
}
// PHP: обработка удаления из истории и файла
if (isset($_GET['del_history'])) {
    $i = (int)$_GET['del_history'];
    if (isset($_SESSION['history'][$i])) {
        // Удаляем только сам файл, .meta оставляем
        if (isset($_GET['del_file'])) {
            $code = basename($_GET['del_file']);
            $metaPath = $uploadDir . $code . '.meta';
            if (file_exists($metaPath)) {
                $meta = @json_decode(@file_get_contents($metaPath), true);
                if ($meta && (isset($meta['saved']) || isset($meta['orig']))) {
                    $fileToDelete = isset($meta['saved']) ? $meta['saved'] : $meta['orig'];
                    $filePath = $uploadDir . $fileToDelete;
                    if (file_exists($filePath)) unlink($filePath);
                    // Удаляем файл просмотров, если есть
                    $viewsPath = $filePath . '.views';
                    if (file_exists($viewsPath)) unlink($viewsPath);
                }
                // Удаляем .meta файл
                unlink($metaPath);
            }
        }
        array_splice($_SESSION['history'], $i, 1);
    }
    header('Location: /');
    exit;
}

$link = '';
if (isset($_GET['link'])) {
    $code = basename($_GET['link']);
    $metaPath = $uploadDir . $code . '.meta';
    if (!file_exists($metaPath)) {
        http_response_code(404);
        include __DIR__ . '/templates/404.php';
        exit;
    }
}
$error = '';
$uploadedFiles = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $files = $_FILES['files'];
    $uploadCount = count($files['name']);
    
    for ($i = 0; $i < $uploadCount; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $error = 'Upload error for file ' . ($i + 1) . '.';
            break;
        } elseif ($files['size'][$i] > $maxFileSize) {
            $error = 'File ' . ($i + 1) . ' is too large (max 100 MB).';
            break;
        } else {
            $origName = $files['name'][$i];
            $cleanName = sanitizeFileName($origName);
            $ext = getExtension($origName);
            // Блокируем опасные расширения
            $forbidden = ['php','php3','php4','php5','phtml','phar','exe','js','html','htm','shtml','pl','py','cgi','asp','aspx','jsp','sh','bat','cmd','dll','vbs','wsf','jar','scr','msi','com','cpl','rb','ini','htaccess'];
            if (in_array($ext, $forbidden)) {
                $error = 'File type not allowed.';
                break;
            }
            do {
                $short = randomString(5);
                $metaPath = $uploadDir . $short . '.meta';
            } while (file_exists($metaPath));
            
            // Определяем, содержит ли имя не-ASCII символы (например, кириллицу)
            $hasNonAscii = !preg_match('/^[\x20-\x7E]+$/u', $cleanName);

            // Генерация имени
            if ($ext === 'png') {
                // Для PNG по умолчанию генерируем случайное имя; если не-ASCII — длина 10
                $randLen = $hasNonAscii ? 10 : 8;
                do {
                    $randomName = randomString($randLen) . '.' . $ext;
                    $target = $uploadDir . $randomName;
                } while (file_exists($target));
            } else {
                if ($hasNonAscii) {
                    // Если есть не-ASCII символы — заменить basename на 10 латинских символов
                    do {
                        $randomName = randomString(10) . '.' . $ext;
                    $target = $uploadDir . $randomName;
                } while (file_exists($target));
            } else {
                // Новый блок: уникализация имени для дубликатов
                    $baseName = pathinfo($cleanName, PATHINFO_FILENAME);
                    $extension = pathinfo($cleanName, PATHINFO_EXTENSION);
                    $randomName = $cleanName;
                $target = $uploadDir . $randomName;
                $counter = 1;
                while (file_exists($target)) {
                    $randomName = $baseName . '_' . $counter . '.' . $extension;
                    $target = $uploadDir . $randomName;
                    $counter++;
                }
            }
            }
            // Гарантируем отсутствие пробелов
            $randomName = str_replace(' ', '_', $randomName);
            $target = str_replace(' ', '_', $target);
            
            if (move_uploaded_file($files['tmp_name'][$i], $target)) {
                // Сохраняем метаинформацию
                file_put_contents($metaPath, json_encode([
                    'orig' => $origName,
                    'saved' => $randomName,
                    'created' => time()
                ]));
                
                // История загрузок в сессии
                if (!isset($_SESSION['history'])) $_SESSION['history'] = [];
                array_unshift($_SESSION['history'], [
                    'code' => $short,
                    'filename' => htmlspecialchars($origName)
                ]);
                
                $uploadedFiles[] = $short;
            } else {
                $error = 'Failed to save file ' . ($i + 1) . '.';
                break;
            }
        }
    }
    
    // Ограничиваем историю до 10 последних файлов
    if (isset($_SESSION['history'])) {
        $_SESSION['history'] = array_slice($_SESSION['history'], 0, 10);
    }
    
    if (empty($error) && !empty($uploadedFiles)) {
        // Если загружен только один файл, перенаправляем на него
        if (count($uploadedFiles) === 1) {
            header('Location: index.php?link=' . urlencode($uploadedFiles[0]));
        } else {
            // Если несколько файлов, показываем все ссылки
            $link = implode(',', $uploadedFiles);
        }
        exit;
    }
}
// Проверка на несуществующий путь (чтобы показывать 404 для любых левых адресов)
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
if ($path !== '/' && !isset($_GET['link'])) {
    http_response_code(404);
    include __DIR__ . '/templates/404.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="hapka.lol — fast and anonymous file sharing. Upload and share files up to 100 MB, no registration. Free!">
    <meta name="keywords" content="hapka.lol, file sharing, anonymous file upload, free file hosting, temporary file sharing, share files online, upload files up to 100MB, image hosting, video hosting, mp3 hosting, direct download link, short links, drag and drop upload, ShareX upload, ShareX config, fast uploads, no registration, privacy focused, secure file transfer, send large files, simple file uploader, cloud file sharing, one‑click upload, file transfer online, public download link, file viewer online, png upload, jpg upload, gif upload, webp upload, avif upload, mp4 upload, webm upload, mov upload, avi upload, mkv upload, mp3 upload, screenshot uploader, paste file online, 30 days storage, auto delete files, ephemeral uploads, minimal file host, lightweight file host, бесплатный файлообменник, анонимная загрузка файлов, обмен файлами онлайн, без регистрации, прямая ссылка на файл, временное хранение файлов">
    <link rel="icon" type="image/png" href="/static/logo.png">
    <title>hapka.lol</title>
    <link rel="stylesheet" href="/static/css/main.css">
</head>
<body>
<div class="header-wrap">
    <div class="header">
        <div class="header-left">
            <span class="logo"><a href="https://hapka.lol" target="_blank" rel="noopener" class="logo-gradient" style="text-decoration:none;"><img src="/static/logo.png" alt="logo" style="height:32px;vertical-align:middle;margin-right:10px;">hapka.lol</a></span>
        </div>
        <div class="header-right">
            <a href="/ShareX.php" class="header-link">ShareX</a>
            <a href="https://github.com/Palitraq/hapka.lol" class="header-link" title="GitHub" aria-label="GitHub">GitHub</a>
            <a href="#" class="header-link support-btn">Support</a>
        </div>
    </div>
    <div class="header-underline"></div>
</div>
<div class="container">
    <h2>Upload files (up to 100 MB each)</h2>
    <div style="text-align:left; margin-bottom:18px;">
        <a href="#" id="terms-link" style="color:#8ab4f8;text-decoration:underline;font-size:1.05em;">Terms and Privacy Policy</a>
    </div>
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form id="uploadForm" method="post" enctype="multipart/form-data" autocomplete="off" style="margin-top:0;margin-bottom:0;">
        <label class="custom-file-upload" style="margin-top:0;margin-bottom:10px;">
            <input type="file" id="fileInput" name="files" required multiple>
            <span id="fileLabelText">Choose files</span>
        </label>
    </form>
    <div id="preview"></div>
    
    <?php if ($link): ?>
        <div class="link">
            <?php 
            $links = explode(',', $link);
            if (count($links) === 1): ?>
                <strong>Your file:</strong><br>
                <a href="<?= htmlspecialchars($link) ?>" target="_blank">https://<?= $_SERVER['HTTP_HOST'] ?>/<?= htmlspecialchars($link) ?></a>
            <?php else: ?>
                <strong>Your files (<?= count($links) ?>):</strong>
                <div class="multiple-links">
                    <?php foreach ($links as $index => $fileLink): ?>
                        <div class="link-item">
                            <a href="<?= htmlspecialchars($fileLink) ?>" target="_blank">https://<?= $_SERVER['HTTP_HOST'] ?>/<?= htmlspecialchars($fileLink) ?></a>
                            <button class="copy-btn" data-link="https://<?= $_SERVER['HTTP_HOST'] ?>/<?= htmlspecialchars($fileLink) ?>" title="Copy">📋</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 12px; text-align: center;">
                    <button id="copyAllBtn" class="copy-all-btn">
                        📋 Copy all links
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['history'])): ?>
        <div class="history-list">
        <?php foreach ($_SESSION['history'] as $idx => $item): ?>
            <?php
            $code = $item['code'];
            $metaFile = $uploadDir . $code . '.meta';
            $createdStr = '';
            $type = '';
            $isImg = false;
            $isVid = false;
            $isAudio = false;
            $origName = '';
            $sizeMB = '';
            if (file_exists($metaFile)) {
                $meta = @json_decode(@file_get_contents($metaFile), true);
                if ($meta && isset($meta['orig'])) {
                    $origName = $meta['orig'];
                    $filePath = $uploadDir . $origName;
                    if (file_exists($filePath)) {
                        $sizeMB = number_format(filesize($filePath) / 1048576, 2) . ' MB';
                    }
                    $ext = getExtension($origName);
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
                    $created = (int)$meta['created'];
                    $createdStr = date('Y-m-d H:i:s', $created) . ' GMT+3';
                }
            }
            $url = htmlspecialchars($code);
            $filename = htmlspecialchars($origName);
            ?>
            <div class="history-card">
                <div class="history-preview">
                    <?php if ($isImg && !empty($origName) && file_exists($uploadDir . $origName)): ?>
                        <img src="uploads/<?= rawurlencode($origName) ?>" alt="preview" class="history-thumb">
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
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/templates/modals/support-modal.php'; ?>
<?php include __DIR__ . '/templates/modals/terms-modal.php'; ?>
<script src="/static/js/main.js"></script>
<script src="/static/js/history.js"></script>
<script src="/static/js/modals.js"></script>

<!-- Drag & Drop индикатор -->
<div id="dragIndicator" class="drag-indicator">📁 Drop files here</div>

</body>
</html> 