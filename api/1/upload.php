<?php
header('Content-Type: application/json');

$apiKey = 'dac5f11c-728d-402c-86ea-0d7d84d3e372';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status_code' => 405, 'error' => ['message' => 'Method not allowed']]);
    exit;
}

$key = $_POST['key'] ?? '';
if ($key !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status_code' => 403, 'error' => ['message' => 'Invalid API key']]);
    exit;
}

if (!isset($_FILES['source'])) {
    http_response_code(400);
    echo json_encode(['status_code' => 400, 'error' => ['message' => 'No file uploaded']]);
    exit;
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$file = $_FILES['source'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status_code' => 400, 'error' => ['message' => 'Upload error']]);
    exit;
}

// Проверка размера файла (100 МБ)
if ($file['size'] > 100 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['status_code' => 400, 'error' => ['message' => 'File exceeds 100 MB limit']]);
    exit;
}

function randomString($length = 6) {
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

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Блокируем опасные расширения
$forbidden = ['php','php3','php4','php5','phtml','phar','exe','js','html','htm','shtml','pl','py','cgi','asp','aspx','jsp','sh','bat','cmd','dll','vbs','wsf','jar','scr','msi','com','cpl','rb','ini','htaccess'];
if (in_array($ext, $forbidden)) {
    http_response_code(400);
    echo json_encode(['status_code' => 400, 'error' => ['message' => 'File type not allowed']]);
    exit;
}

$cleanName = sanitizeFileName($file['name']);

do {
    $short = randomString(6);
    $metaPath = $uploadDir . $short . '.meta';
    $target = $uploadDir . $cleanName;
    $savedName = $cleanName;
} while (file_exists($metaPath) || file_exists($target));

if (!move_uploaded_file($file['tmp_name'], $target)) {
    http_response_code(500);
    echo json_encode(['status_code' => 500, 'error' => ['message' => 'Failed to save file']]);
    exit;
}

// Создаём .meta-файл для статистики
file_put_contents($metaPath, json_encode([
    'orig' => $file['name'],
    'saved' => $savedName,
    'created' => time()
]));

$url = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $short;

echo json_encode([
    'status_code' => 200,
    'success' => [
        'short' => $short,
        'url' => $url
    ]
]); 