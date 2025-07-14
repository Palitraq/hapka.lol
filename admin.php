<?php
session_start();
// Load password from .env
function getEnvVar($key) {
    $lines = @file(__DIR__ . '/.env');
    if (!$lines) return null;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            if (trim($k) === $key) return trim($v);
        }
    }
    return null;
}
$adminPassword = getEnvVar('ADMIN_PASSWORD');
if (!$adminPassword) {
    die('Admin password not set in .env');
}
// Authorization
if (isset($_POST['password'])) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Incorrect password';
    }
}
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header('Location: admin.php');
    exit;
}
if (empty($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="/logo.png">
        <title>Admin Panel — hapka.lol</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #e0e0e0;
            }
            
            .login-container {
                background: rgba(30, 30, 50, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                width: 100%;
                max-width: 400px;
                text-align: center;
                animation: slideInUp 0.6s ease-out;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .logo {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 10px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            
            .subtitle {
                color: #b0b0b0;
                margin-bottom: 30px;
                font-size: 1.1rem;
            }
            
            .form-group {
                margin-bottom: 20px;
                text-align: left;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #d0d0d0;
                font-weight: 500;
            }
            
            .form-group input {
                width: 100%;
                padding: 15px;
                border: 2px solid #404060;
                border-radius: 10px;
                font-size: 1rem;
                transition: all 0.3s ease;
                background: #2a2a3a;
                color: #e0e0e0;
            }
            
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
                background: #303040;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            }
            
            .form-group input::placeholder {
                color: #808080;
            }
            
            .login-btn {
                width: 100%;
                padding: 15px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 10px;
            }
            
            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            }
            
            .login-btn:active {
                transform: translateY(0);
            }
            
            .error {
                background: #ff6b6b;
                color: white;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                font-size: 0.9rem;
            }
            
            .security-note {
                margin-top: 20px;
                padding: 15px;
                background: rgba(25, 118, 210, 0.2);
                border-radius: 8px;
                font-size: 0.85rem;
                color: #90caf9;
                border-left: 4px solid #2196f3;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo"><img src="logo.png" alt="logo" style="height:38px;vertical-align:middle;margin-right:12px;">hapka.lol</div>
            <div class="subtitle">Admin Panel</div>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="post">
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
// File statistics
$uploadDir = __DIR__ . '/uploads/';
$allFiles = glob($uploadDir . '*');
$currentFiles = 0;
$totalSize = 0;
foreach ($allFiles as $file) {
    // Считаем только если файл не .meta и не .views (или похожее)
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
        if ($metaData['created'] >= $monthAgo) {
            $uploadedLastMonth++;
        }
        if ($metaData['created'] >= $weekAgo) {
            $uploadedLastWeek++;
        }
    }
}

// Get real disk space information
function getDiskSpace() {
    $uploadDir = __DIR__ . '/uploads/';
    $totalSpace = disk_total_space($uploadDir);
    $freeSpace = disk_free_space($uploadDir);
    $usedSpace = $totalSpace - $freeSpace;
    
    return [
        'total' => $totalSpace,
        'free' => $freeSpace,
        'used' => $usedSpace,
        'usage_percent' => $totalSpace > 0 ? ($usedSpace / $totalSpace) * 100 : 0
    ];
}

function formatFileSize($bytes) {
    if ($bytes >= 1024 * 1024 * 1024) {
        return number_format($bytes / 1024 / 1024 / 1024, 1) . ' GB';
    } elseif ($bytes >= 1024 * 1024) {
        return number_format($bytes / 1024 / 1024, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

$diskInfo = getDiskSpace();

// Calculate storage usage
$storageUsage = $totalSize / (13 * 1024 * 1024 * 1024) * 100; // Assuming 13GB limit
$storageUsage = min($storageUsage, 100);

// Get server info
$serverLoad = sys_getloadavg();
$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/logo.png">
    <title>Admin Panel — hapka.lol</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: #e0e0e0;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(30, 30, 50, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: slideInUp 0.6s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            color: #b0b0b0;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .nav-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(40, 40, 60, 0.8);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            background: rgba(50, 50, 70, 0.9);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #b0b0b0;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .progress-section {
            background: rgba(40, 40, 60, 0.8);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .progress-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #e0e0e0;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 0.9rem;
            color: #b0b0b0;
        }
        
        .server-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .info-card {
            background: rgba(40, 40, 60, 0.8);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .info-title {
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 10px;
        }
        
        .info-value {
            color: #667eea;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 20px;
                margin: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
            
            .logo {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <div class="logo"><img src="logo.png" alt="logo" style="height:38px;vertical-align:middle;margin-right:12px;">hapka.lol</div>
            <div class="subtitle">Administration Dashboard</div>
            <div class="nav-buttons">
                <a href="https://hapka.lol" target="_blank" class="nav-btn">
                    🌐 Visit Site
                </a>
                <a href="?logout=1" class="nav-btn logout-btn">
                    🚪 Logout
                </a>
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
                        <div class="activity-cell" data-tooltip="<?= htmlspecialchars($d) ?>: <?= $c ?> upload<?= $c==1?'':'s' ?>" style="width:13px;height:13px;border-radius:3px;background:<?= activityColor($c) ?>;"></div>
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
<style>
.activity-cell {
    position: relative;
    cursor: pointer;
}
.activity-cell:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    top: -32px;
    transform: translateX(-50%);
    background: #23272a;
    color: #fff;
    padding: 6px 14px;
    border-radius: 7px;
    font-size: 0.98em;
    white-space: nowrap;
    box-shadow: 0 2px 12px #0008;
    z-index: 10;
    pointer-events: none;
    opacity: 1;
}
.activity-cell::after {
    opacity: 0;
    transition: opacity 0.15s;
}
.activity-cell:hover::after {
    opacity: 1;
}
</style>
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
