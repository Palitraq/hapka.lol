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
        <link rel="stylesheet" href="/static/css/admin.css">
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
$storageLimitGB = (float)(getEnvVar('STORAGE_LIMIT_GB') ?: 26);
$storageLimitBytes = $storageLimitGB * 1024 * 1024 * 1024;
$storageUsage = $totalSize / $storageLimitBytes * 100; $storageUsage = min($storageUsage, 100);
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
    <link rel="stylesheet" href="/static/css/admin.css">
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
$weeks = [];
$days = array_keys($activity);
$firstDay = strtotime($days[0]);
$firstWeekDay = date('N', $firstDay);
$week = [];
$dayIdx = 0;
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
function activityColor($count) {
    if ($count == 0) return '#2d3140';
    if ($count == 1) return '#8f5cff22';
    if ($count <= 3) return '#8f5cff55';
    if ($count <= 7) return '#8f5cffaa';
    return '#8f5cff';
}
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
                        $tooltip = '';
                        if ($d) {
                            $tooltip = htmlspecialchars($d, ENT_QUOTES, 'UTF-8') . ': ' . $c . ' upload' . ($c==1?'':'s');
                        }
                        ?>
                        <div class="activity-cell" style="width:13px;height:13px;border-radius:3px;background:<?= activityColor($c) ?>;cursor:default;"<?php if ($tooltip): ?> data-tooltip="<?= $tooltip ?>"<?php endif; ?>></div>
                    <?php endfor; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
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

<div style="margin: 32px auto 0 auto; max-width: 900px; width:100%;">
    <div style="background:rgba(40,40,60,0.8); border-radius:15px; border:1px solid rgba(255,255,255,0.1); padding:25px;">
        <form id="deleteForm" style="display:flex; gap:12px; align-items:flex-end;">
            <div style="flex:1;">
                <input 
                    type="text" 
                    id="deleteCode" 
                    name="delete_code" 
                    placeholder="Enter file code" 
                    required
                    style="width:100%; padding:12px 15px; border:2px solid #404060; border-radius:10px; font-size:1rem; transition:all 0.3s ease; background:#2a2a3a; color:#e0e0e0;"
                    autocomplete="off"
                    onfocus="this.style.borderColor='#667eea'; this.style.background='#303040'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.2)';"
                    onblur="this.style.borderColor='#404060'; this.style.background='#2a2a3a'; this.style.boxShadow='none';"
                >
            </div>
            <button 
                type="submit" 
                id="deleteBtn"
                style="padding:12px 32px; background:linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color:white; border:none; border-radius:10px; font-size:1rem; font-weight:400; cursor:pointer; transition:all 0.3s ease; white-space:nowrap; height:fit-content;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(255, 107, 107, 0.3)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';"
            >
                Delete
            </button>
        </form>
        <div id="deleteResult" style="margin-top:15px; display:none; padding:12px; border-radius:8px; font-size:0.9rem;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('deleteForm');
    const codeInput = document.getElementById('deleteCode');
    const deleteBtn = document.getElementById('deleteBtn');
    const resultDiv = document.getElementById('deleteResult');
    
    // Get CSRF token
    const csrfInput = document.querySelector('input[name="csrf_token"]');
    const csrfToken = csrfInput ? csrfInput.value : '';
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const code = codeInput.value.trim();
        if (!code) return;
        
        // Disable button
        deleteBtn.disabled = true;
        deleteBtn.style.opacity = '0.6';
        deleteBtn.style.cursor = 'not-allowed';
        
        // Hide previous result
        resultDiv.style.display = 'none';
        
        try {
            const formData = new FormData();
            formData.append('delete_code', code);
            formData.append('csrf_token', csrfToken);
            
            const res = await fetch('./admin.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!res.ok) throw new Error('Request failed');
            
            const data = await res.json();
            
            resultDiv.style.display = 'block';
            
            if (data.status === 'ok') {
                resultDiv.style.background = 'rgba(76, 175, 80, 0.2)';
                resultDiv.style.color = '#81c784';
                resultDiv.style.border = '1px solid rgba(76, 175, 80, 0.3)';
                resultDiv.textContent = 'File deleted successfully';
                codeInput.value = '';
            } else {
                resultDiv.style.background = 'rgba(244, 67, 54, 0.2)';
                resultDiv.style.color = '#e57373';
                resultDiv.style.border = '1px solid rgba(244, 67, 54, 0.3)';
                resultDiv.textContent = (data.message || 'Failed to delete file');
            }
        } catch (error) {
            resultDiv.style.display = 'block';
            resultDiv.style.background = 'rgba(244, 67, 54, 0.2)';
            resultDiv.style.color = '#e57373';
            resultDiv.style.border = '1px solid rgba(244, 67, 54, 0.3)';
            resultDiv.textContent = 'Error: Failed to delete file';
        } finally {
            deleteBtn.disabled = false;
            deleteBtn.style.opacity = '1';
            deleteBtn.style.cursor = 'pointer';
        }
    });
});
</script>

        <div class="progress-section" style="margin-top:32px;">
            <div class="progress-title">Storage Usage</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $storageUsage ?>%"></div>
            </div>
            <div class="progress-text">
                <?= number_format($storageUsage, 1) ?>% used (<?= number_format($totalSize / 1024 / 1024, 1) ?> MB / <?= number_format($storageLimitGB, 0) ?> GB)
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
