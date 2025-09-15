<?php
session_start();
// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function generateCSRFToken() {
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Load password from root .env (robust search)
function getEnvVar($key) {
    $paths = [
        dirname(__DIR__) . '/.env',
        (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : '') . '/.env',
        __DIR__ . '/.env',
    ];
    foreach ($paths as $path) {
        if ($path && file_exists($path)) {
            $lines = @file($path);
            if ($lines) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') continue;
                    if (strpos($line, '=') !== false) {
                        list($k, $v) = explode('=', $line, 2);
                        if (trim($k) === $key) {
                            $v = trim($v);
                            // remove optional wrapping quotes
                            if ((substr($v, 0, 1) === '"' && substr($v, -1) === '"') || (substr($v, 0, 1) === "'" && substr($v, -1) === "'")) {
                                $v = substr($v, 1, -1);
                            }
                            return $v;
                        }
                    }
                }
            }
        }
    }
    // Fallback to server env
    $env = getenv($key);
    return $env !== false ? $env : null;
}
$adminPassword = getEnvVar('ADMIN_PASSWORD');
if (!$adminPassword) {
    die('Admin password not set in .env');
}
// Authorization
if (isset($_POST['password'])) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['is_admin'] = true;
        session_regenerate_id(true); // Prevent session fixation
        header('Location: /admin.php');
        exit;
    } else {
        $error = 'Incorrect password';
    }
}
// POST-only logout with CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF attack detected');
    }
    unset($_SESSION['is_admin']);
    session_regenerate_id(true);
    header('Location: /admin.php');
    exit;
}
if (empty($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="/static/logo.png">
        <title>Admin Panel — hapka.lol</title>
        <link rel="stylesheet" href="../css/admin.css">
    </head>
    <body>
        <div class="login-container">
            <div class="logo"><img src="/static/logo.png" alt="logo" style="height:38px;vertical-align:middle;margin-right:12px;">hapka.lol</div>
            <div class="subtitle">Admin Panel</div>
            <?php if (!empty($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter admin password" required>
                </div>
                <button type="submit" class="login-btn">🔐 Login</button>
            </form>
            <div class="security-note">
                <strong>🔒 Security Note:</strong><br>
                This panel is protected. Only authorized administrators can access.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
// AJAX: list files by date (YYYY-MM-DD)
if (isset($_GET['list_by_date'])) {
    $dateStr = $_GET['list_by_date'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid date']);
        exit;
    }
    $uploadDir = dirname(__DIR__) . '/uploads/';
    $metaFiles = glob($uploadDir . '*.meta');
    $startTs = strtotime($dateStr . ' 00:00:00');
    $endTs = strtotime($dateStr . ' 23:59:59');
    $items = [];
    foreach ($metaFiles as $metaPath) {
        $code = basename($metaPath, '.meta');
        $meta = @json_decode(@file_get_contents($metaPath), true);
        if (!$meta || !isset($meta['created'])) continue;
        $created = (int)$meta['created'];
        if ($created < $startTs || $created > $endTs) continue;
        $saved = isset($meta['saved']) ? $meta['saved'] : (isset($meta['orig']) ? $meta['orig'] : '');
        $filePath = $uploadDir . $saved;
        $size = file_exists($filePath) ? filesize($filePath) : 0;
        $items[] = [
            'code' => $code,
            'orig' => isset($meta['orig']) ? $meta['orig'] : '',
            'saved' => $saved,
            'size' => $size,
            'created' => $created,
        ];
    }
    usort($items, function($a, $b) { return $b['created'] <=> $a['created']; });
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'date' => $dateStr, 'count' => count($items), 'items' => $items]);
    exit;
}
// AJAX: delete by code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_code'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'CSRF attack detected']);
        exit;
    }
    $code = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['delete_code']);
    $uploadDir = dirname(__DIR__) . '/uploads/';
    $metaPath = $uploadDir . $code . '.meta';
    $result = ['status' => 'error'];
    if (file_exists($metaPath)) {
        $meta = @json_decode(@file_get_contents($metaPath), true);
        if ($meta && (isset($meta['saved']) || isset($meta['orig']))) {
            $fileName = isset($meta['saved']) ? $meta['saved'] : $meta['orig'];
            $filePath = $uploadDir . $fileName;
            if (file_exists($filePath)) @unlink($filePath);
            $viewsPath = $filePath . '.views';
            if (file_exists($viewsPath)) @unlink($viewsPath);
        }
        @unlink($metaPath);
        $result = ['status' => 'ok'];
    } else {
        $result = ['status' => 'error', 'message' => 'Not found'];
    }
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
// File statistics
$uploadDir = dirname(__DIR__) . '/uploads/';
$allFiles = glob($uploadDir . '*');
$currentFiles = 0;
$totalSize = 0;
foreach ($allFiles as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (is_file($file) && $ext !== 'meta' && $ext !== 'views') {
        $currentFiles++;
        $totalSize += filesize($file);
    }
}
$metaFiles = glob($uploadDir . '*.meta');
$uploadedLastMonth = 0;
$uploadedLastWeek = 0;
$monthAgo = time() - 30 * 24 * 60 * 60;
$weekAgo = time() - 7 * 24 * 60 * 60;
foreach ($metaFiles as $meta) {
    $metaData = @json_decode(@file_get_contents($meta), true);
    if ($metaData && isset($metaData['created'])) {
        if ($metaData['created'] >= $monthAgo) { $uploadedLastMonth++; }
        if ($metaData['created'] >= $weekAgo) { $uploadedLastWeek++; }
    }
}
function getDiskSpace() {
    $uploadDir = dirname(__DIR__) . '/uploads/';
    $totalSpace = disk_total_space($uploadDir);
    $freeSpace = disk_free_space($uploadDir);
    $usedSpace = $totalSpace - $freeSpace;
    return [ 'total' => $totalSpace, 'free' => $freeSpace, 'used' => $usedSpace, 'usage_percent' => $totalSpace > 0 ? ($usedSpace / $totalSpace) * 100 : 0 ];
}
function formatFileSize($bytes) {
    if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / 1024 / 1024 / 1024, 1) . ' GB';
    if ($bytes >= 1024 * 1024) return number_format($bytes / 1024 / 1024, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
$diskInfo = getDiskSpace();
$storageUsage = $totalSize / (13 * 1024 * 1024 * 1024) * 100; $storageUsage = min($storageUsage, 100);
$serverLoad = sys_getloadavg();
$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/static/logo.png">
    <title>Admin Panel — hapka.lol</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-dashboard">
    <div class="admin-container">
        <div class="header">
            <div class="logo"><img src="/static/logo.png" alt="logo" style="height:38px;vertical-align:middle;margin-right:12px;">hapka.lol</div>
            <div class="subtitle">Administration Dashboard</div>
            <div class="nav-buttons">
                <a href="https://hapka.lol" target="_blank" class="nav-btn">
                    🌐 Visit Site
                </a>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <button type="submit" name="logout" class="nav-btn logout-btn" style="border: none; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; padding: 12px 24px; cursor: pointer;">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-number"><?= $currentFiles ?></div>
                <div class="stat-label">Current Files</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-number"><?= $uploadedLastMonth ?></div>
                <div class="stat-label">Uploaded This Month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?= $uploadedLastWeek ?></div>
                <div class="stat-label">Uploaded This Week</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💾</div>
                <div class="stat-number"><?= number_format($totalSize / 1024 / 1024, 1) ?></div>
                <div class="stat-label">Total Size (MB)</div>
            </div>
        </div>
        
<?php
// === График активности загрузки файлов (GitHub-style) ===
$metaFiles = glob($uploadDir . '*.meta');
$activity = [];
$now = time();
$start = strtotime('-1 year', strtotime(date('Y-m-d', $now)));
for ($d = $start; $d <= $now; $d += 86400) {
    $activity[date('Y-m-d', $d)] = 0;
}
foreach ($metaFiles as $meta) {
    $metaData = @json_decode(@file_get_contents($meta), true);
    if ($metaData && isset($metaData['created'])) {
        $day = date('Y-m-d', $metaData['created']);
        if (isset($activity[$day])) $activity[$day]++;
    }
}
// Преобразуем в недели для сетки
$weeks = [];
$days = array_keys($activity);
$firstDay = strtotime($days[0]);
$firstWeekDay = date('N', $firstDay); // 1=Mon, 7=Sun
$week = [];
$dayIdx = 0;
// Добавить пустые ячейки, если год назад был не понедельник
if ($firstWeekDay > 1) {
    for ($i = 1; $i < $firstWeekDay; $i++) {
        $week[] = ['count' => 0, 'date' => ''];
        $dayIdx++;
    }
}
foreach ($activity as $day => $count) {
    $week[] = ['count' => $count, 'date' => $day];
    $dayIdx++;
    if ($dayIdx % 7 === 0) {
        $weeks[] = $week;
        $week = [];
    }
}
if (count($week)) $weeks[] = $week;
// Цвета (можно настроить)
function activityColor($count) {
    if ($count == 0) return '#2d3140'; // Было #23272a, теперь светлее
    if ($count == 1) return '#8f5cff22';
    if ($count <= 3) return '#8f5cff55';
    if ($count <= 7) return '#8f5cffaa';
    return '#8f5cff';
}
// Считаем, в какой неделе начинается каждый месяц
$monthLabels = [];
$prevMonth = null;
foreach ($weeks as $wIdx => $week) {
    foreach ($week as $cell) {
        if (!empty($cell['date'])) {
            $month = date('M', strtotime($cell['date']));
            if ($month !== $prevMonth) {
                $monthLabels[$wIdx] = $month;
                $prevMonth = $month;
            }
            break;
        }
    }
}
?>
<div style="margin: 48px auto 0 auto; max-width: 900px; width:100%; text-align:center;">
    <div style="font-weight:600; color:#b9bbbe; margin-bottom:10px; font-size:1.1em;">File upload activity (last 12 months)</div>
    <div class="activity-graph" style="background:rgba(40,40,60,0.8); border-radius:15px; border:1px solid rgba(255,255,255,0.1); padding:25px; margin-bottom:30px; width:100%;">
        <div style="display:flex; gap:2px; justify-content:center; align-items:flex-start;">
            <?php foreach ($weeks as $w): ?>
                <div style="display:flex; flex-direction:column; gap:2px;">
                    <?php for ($i=0; $i<7; $i++): ?>
                        <?php
                        $cell = isset($w[$i]) ? $w[$i] : ['count'=>0,'date'=>''];
                        $c = $cell['count'];
                        $d = $cell['date'];
                        ?>
                        <div class="activity-cell" data-date="<?= htmlspecialchars($d) ?>" data-count="<?= (int)$c ?>" data-tooltip="<?= htmlspecialchars($d) ?>: <?= $c ?> upload<?= $c==1?'':'s' ?>" style="width:13px;height:13px;border-radius:3px;background:<?= activityColor($c) ?>;"></div>
                    <?php endfor; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
// Найти день с максимальной активностью
$peakDate = '';
$peakCount = 0;
foreach ($activity as $date => $count) {
    if ($count > $peakCount) {
        $peakCount = $count;
        $peakDate = $date;
    }
}
?>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; margin-right: 8px; font-size: 15px; color: #888;">
        <span style="color:#bbb; font-size:14px;">
            <?php if ($peakCount > 0): ?>
                Peak: <?= htmlspecialchars($peakDate) ?> (<?= $peakCount ?> upload<?= $peakCount==1?'':'s' ?>)
            <?php else: ?>
                Peak: —
            <?php endif; ?>
        </span>
        <span style="display: flex; align-items: center; gap: 8px;">
            <span>Less</span>
            <span style="display: flex; gap: 4px; align-items: center; margin: 0 2px;">
                <span style="width: 16px; height: 16px; border-radius: 3px; background: #23272a; display: inline-block;"></span>
                <span style="width: 16px; height: 16px; border-radius: 3px; background: #8f5cff22; display: inline-block;"></span>
                <span style="width: 16px; height: 16px; border-radius: 3px; background: #8f5cff55; display: inline-block;"></span>
                <span style="width: 16px; height: 16px; border-radius: 3px; background: #8f5cffaa; display: inline-block;"></span>
                <span style="width: 16px; height: 16px; border-radius: 3px; background: #8f5cff; display: inline-block;"></span>
            </span>
            <span>More</span>
        </span>
    </div>
</div>
    <div id="daily-listing" style="max-width: 900px; margin: 20px auto 0 auto; display:none;">
        <div style="background: rgba(40,40,60,0.8); border:1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 18px;">
            <div id="daily-title" style="font-weight:600; color:#e0e0e0; margin-bottom:12px;">Files for <span id="daily-date"></span> (<span id="daily-count">0</span>)</div>
            <div id="daily-content" style="display:flex; flex-direction:column; gap:10px;"></div>
        </div>
    </div>
        <div class="progress-section" style="margin-top:32px;">
            <div class="progress-title">Storage Usage</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $storageUsage ?>%"></div>
            </div>
            <div class="progress-text">
                <?= number_format($storageUsage, 1) ?>% used (<?= number_format($totalSize / 1024 / 1024, 1) ?> MB / 13 GB)
            </div>
        </div>
        
        <div class="server-info">
            <div class="info-card">
                <div class="info-title">Server Load</div>
                <div class="info-value"><?= number_format($serverLoad[0], 2) ?></div>
            </div>
            
            <div class="info-card">
                <div class="info-title">Memory Usage</div>
                <div class="info-value"><?= number_format($memoryUsage / 1024 / 1024, 1) ?> MB</div>
            </div>
            
            <div class="info-card">
                <div class="info-title">Peak Memory</div>
                <div class="info-value"><?= number_format($memoryPeak / 1024 / 1024, 1) ?> MB</div>
            </div>
        </div>
    </div>
</body>
</html>
<script src="../js/admin.js"></script>
