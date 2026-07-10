<?php
/**
 * Leaffox主页系统 安装向导
 * 支持 MySQL / SQLite 两种数据库
 */
require_once __DIR__ . '/config.php';

// 初始化变量（防止数据库未连接时出现 Undefined variable 警告）
$db = $db ?? null;

$step = max(1, (int)($_GET['step'] ?? 1));
$error = '';
$success = '';
$dbType = '';

// 显示上一次数据库连接错误（来自 config.php）
if (!empty($_SESSION['install_db_error'])) {
    $error = '<i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> 数据库连接失败: ' . $_SESSION['install_db_error'] . '<br><span style="color:#94a3b8;font-size:12px;"><i class="fas fa-lightbulb"></i> 常见原因：MySQL 服务未启动 / 密码错误 / PHP缺少pdo_mysql或pdo_sqlite扩展</span>';
    unset($_SESSION['install_db_error']);
}

// 检查 config.php 是否可写
$configFile = __DIR__ . '/config.php';
$configWritable = is_writable($configFile);
if (!$configWritable && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    // 只在首次加载时警告，提交后没必要
    $error = '<i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> config.php 文件不可写！安装程序无法保存配置。<br>请执行：<code>chmod 666 ' . $configFile . '</code>';
}

// 检测是否已安装（连接失败或未安装都视为未安装）
$alreadyInstalled = false;
if (isset($db) && $db) {
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM admin");
        if ($stmt && $stmt->fetchColumn() > 0) $alreadyInstalled = true;
    } catch (Exception $e) {}
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $dbType = $_POST['db_type'] ?? 'mysql';
    
    if ($dbType === 'sqlite') {
        // ---- SQLite 安装 ----
        $dbPath = __DIR__ . '/data/leaffox.db';
        // 如果已存在且已有数据，询问是否覆盖
        if (file_exists($dbPath) && filesize($dbPath) > 0) {
            if (empty($_POST['overwrite'] ?? '')) {
                $error = '数据库文件已存在，勾选「覆盖已有数据」可重新安装';
            }
        }
        if (!$error) {
            try {
                $dbDir = dirname($dbPath);
                if (!is_dir($dbDir)) {
                    if (!@mkdir($dbDir, 0755, true)) {
                        throw new Exception("无法自动创建目录 <b>$dbDir</b>，请手动执行：<br><code>mkdir -p $dbDir && chmod 777 $dbDir</code>");
                    }
                }
                // 测试目录是否可写
                if (!is_writable($dbDir)) {
                    throw new Exception("目录 <b>$dbDir</b> 不可写，请手动执行：<br><code>chmod 777 $dbDir</code>");
                }
                
                // 删除旧库
                if (file_exists($dbPath)) unlink($dbPath);
                
                $sqliteDb = new PDO("sqlite:$dbPath", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $sqliteDb->exec("PRAGMA journal_mode=WAL");
                $sqliteDb->exec("PRAGMA foreign_keys=ON");
                
                // 读入 SQLite 建表 SQL
                $sqlFile = __DIR__ . '/install_sqlite.sql';
                if (!file_exists($sqlFile)) throw new Exception('install_sqlite.sql 文件缺失');
                
                $sql = file_get_contents($sqlFile);
                $statements = explode(';', $sql);
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (!empty($stmt) && strncmp($stmt, '--', 2) !== 0) {
                        try { $sqliteDb->exec($stmt); } catch (Exception $e) { /* 忽略已存在错误 */ }
                    }
                }
                
                // 写入 config.php 中的 DB 配置（标记为 sqlite）
                $configContent = file_get_contents(__DIR__ . '/config.php');
                if ($configContent === false) $configContent = '';
                if (!empty($configContent)) {
                    $configContent = preg_replace(
                        "/define('DB_TYPE',\s*'mysql'\);/",
                        "define('DB_TYPE', 'sqlite');",
                        $configContent
                    );
                    // 同时也更新 BASE_URL
                    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
                    $configContent = preg_replace(
                        "/define('BASE_URL',\s*'[^']+'\);/",
                        "define('BASE_URL', 'http://$host');",
                        $configContent
                    );
                    file_put_contents(__DIR__ . '/config.php', $configContent);
                }
                
                $success = '<i class="fas fa-check-circle" style="color:#10b981"></i> SQLite 安装成功！数据库文件: data/leaffox.db<br>默认管理员账号: <b>admin</b> / <b>admin123</b>';
            } catch (Exception $e) {
                $error = 'SQLite 安装失败: ' . $e->getMessage();
            }
        }
    } else {
        // ---- MySQL 安装 ----
        $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbName = trim($_POST['db_name'] ?? 'leaffox_system');
        $dbUser = trim($_POST['db_user'] ?? 'root');
        $dbPass = $_POST['db_pass'] ?? '';
        
        try {
            $testDsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
            $testDb = new PDO($testDsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            $safeDbName = str_replace('`', '``', $dbName);
            $testDb->exec("CREATE DATABASE IF NOT EXISTS `$safeDbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $testDb->exec("USE `$safeDbName`");
            
            $sqlFile = __DIR__ . '/install.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
                $sql = preg_replace('/USE.*?;/i', '', $sql);
                $statements = explode(';', $sql);
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (!empty($stmt)) {
                        try { $testDb->exec($stmt); } catch (Exception $e) {}
                    }
                }
            }
            
            // 写入配置
            $configContent = file_get_contents(__DIR__ . '/config.php');
            if ($configContent === false) $configContent = '';
            
            if (!empty($configContent)) {
                // 注意：使用单引号字符串避免 PHP 双引号内 \)、\; 等产生弃用警告
                $configContent = preg_replace(
                    '/define\(\'DB_TYPE\',\s*\'[^\']*\'\);/',
                    "define('DB_TYPE', 'mysql');",
                    $configContent
                );
                $configContent = preg_replace(
                    '/define\(\'DB_HOST\',\s*\'[^\']*\'\);/',
                    "define('DB_HOST', '$dbHost');",
                    $configContent
                );
                $configContent = preg_replace(
                    '/define\(\'DB_PORT\',\s*\'[^\']*\'\);/',
                    "define('DB_PORT', '$dbPort');",
                    $configContent
                );
                $configContent = preg_replace(
                    '/define\(\'DB_NAME\',\s*\'[^\']*\'\);/',
                    "define('DB_NAME', '$dbName');",
                    $configContent
                );
                $configContent = preg_replace(
                    '/define\(\'DB_USER\',\s*\'[^\']*\'\);/',
                    "define('DB_USER', '$dbUser');",
                    $configContent
                );
                $configContent = preg_replace(
                    '/define\(\'DB_PASS\',\s*\'[^\']*\'\);/',
                    "define('DB_PASS', '$dbPass');",
                    $configContent
                );
                // 确保 MySQL 使用 TCP 连接方式（兼容性更好）
                if (!preg_match('/define\(\'DB_USE_TCP\'/', $configContent)) {
                    $configContent = preg_replace(
                        '/(define\(\'DB_CHARSET.*?\'\)\;)/',
                        "$1
// 使用 TCP 方式连接 MySQL (install.php 自动设置)
define('DB_USE_TCP', true);",
                        $configContent
                    );
                } else {
                    $configContent = preg_replace(
                        '/define\(\'DB_USE_TCP\',\s*(true|false)\)\;/',
                        "define('DB_USE_TCP', true);",
                        $configContent
                    );
                }
                $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
                $configContent = preg_replace(
                    '/define\(\'BASE_URL\',\s*\'[^\']+\'\);/',
                    "define('BASE_URL', 'http://$host');",
                    $configContent
                );
                file_put_contents(__DIR__ . '/config.php', $configContent);
            }
            
            $success = '<i class="fas fa-check-circle" style="color:#10b981"></i> MySQL 安装成功！默认管理员账号: <b>admin</b> / <b>admin123</b>';
        } catch (Exception $e) {
            $error = '数据库连接失败: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leaffox主页系统 安装向导</title>
<link rel="stylesheet" href="assets/css/tailwind.css">
<link rel="stylesheet" href="assets/css/fontawesome.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;color:#e2e8f0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.install-box{background:rgba(255,255,255,0.04);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:40px;max-width:600px;width:100%}
input{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff;border-radius:10px;padding:12px 16px;width:100%;outline:none;transition:all 0.2s;font-size:14px}
input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(129,140,248,0.15)}
input::placeholder{color:rgba(255,255,255,0.3)}
label{display:block;color:rgba(255,255,255,0.7);font-size:13px;font-weight:500;margin-bottom:6px}
.btn-primary{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:12px;padding:14px 32px;font-size:15px;font-weight:600;cursor:pointer;transition:all 0.3s;display:inline-block;text-align:center;text-decoration:none}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(79,70,229,0.35)}
.tab-bar{display:flex;background:rgba(255,255,255,0.05);border-radius:12px;padding:4px;margin-bottom:24px}
.tab-btn{flex:1;padding:10px;text-align:center;border-radius:10px;cursor:pointer;font-size:14px;font-weight:500;transition:all 0.2s;border:none;background:transparent;color:rgba(255,255,255,0.5)}
.tab-btn.active{background:rgba(79,70,229,0.25);color:#a5b4fc}
.tab-btn:hover{color:rgba(255,255,255,0.8)}
.tab-content{display:none}
.tab-content.active{display:block}
@media(max-width:640px){
.install-box{padding:24px 20px}
.grid.grid-cols-2{grid-template-columns:1fr!important}
body{padding:12px}
.tab-bar{gap:4px}
.tab-btn{font-size:13px;padding:8px 6px}
.btn-primary{padding:12px 24px;font-size:14px}
}
code{background:rgba(255,255,255,0.08);padding:2px 8px;border-radius:4px;font-size:12px}
</style>
</head>
<body>
<div class="install-box">
  <div class="text-center mb-8">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">L</div>
    <h1 class="text-2xl font-bold">Leaffox主页系统 安装向导</h1>
    <p class="text-gray-500 text-sm mt-1">多用户短链主页系统</p>
  </div>

  <?php if ($error): ?>
  <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($error)?></div>
  <?php endif; ?>
  
  <?php if ($success): ?>
  <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=$success?></div>
  <div class="space-y-3">
    <a href="./admin/index.php" class="btn-primary w-full block no-underline">进入管理后台</a>
    <a href="./user/index.php" class="block text-center text-blue-500 hover:text-blue-400 text-sm mt-3">进入用户登录</a>
  </div>
  <?php elseif ($alreadyInstalled): ?>
  <div class="bg-amber-500/10 border border-amber-500/30 text-amber-300 px-4 py-3 rounded-xl mb-6 text-sm">
    <i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> 系统似乎已安装，如需重新安装请先清空数据库或删除 data/leaffox.db 文件
  </div>
  <a href="./admin/index.php" class="btn-primary w-full block no-underline">进入管理后台</a>
  <a href="./user/index.php" class="block text-center text-blue-500 hover:text-blue-400 text-sm mt-3">进入用户登录</a>
  <?php else: ?>
  
  <!-- Tab 切换 -->
  <div class="tab-bar" id="tabBar">
    <button class="tab-btn active" data-tab="mysql" onclick="switchTab('mysql')">🐬 MySQL</button>
    <button class="tab-btn" data-tab="sqlite" onclick="switchTab('sqlite')"><i class="fas fa-folder"></i> SQLite（轻量）</button>
  </div>

  <form method="POST" id="installForm">
    <input type="hidden" name="db_type" id="dbType" value="mysql">

    <!-- MySQL 配置 -->
    <div class="tab-content active" id="tabMysql">
      <h2 class="text-lg font-semibold mb-4">MySQL 数据库配置</h2>
      <p class="text-gray-500 text-sm mb-5">请确保 MySQL 服务已启动，填写连接信息后自动建库</p>
      
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div><label>数据库主机</label><input type="text" name="db_host" value="127.0.0.1" required></div>
        <div><label>端口</label><input type="text" name="db_port" value="3306" required></div>
      </div>
      <div class="mb-4"><label>数据库名</label><input type="text" name="db_name" value="leaffox_system" required></div>
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div><label>用户名</label><input type="text" name="db_user" value="root" required></div>
        <div><label>密码</label><input type="password" name="db_pass" value="root"></div>
      </div>
    </div>

    <!-- SQLite 配置 -->
    <div class="tab-content" id="tabSqlite">
      <h2 class="text-lg font-semibold mb-4">SQLite 轻量安装</h2>
      <div class="bg-blue-600/10 border border-blue-500/20 rounded-xl p-4 mb-5 text-sm text-blue-400">
        <p class="mb-2"><i class="fas fa-check-circle" style="color:#10b981"></i> 无需安装数据库服务，随系统自动创建</p>
        <p><i class="fas fa-folder"></i> 数据库文件位置: <code class="text-indigo-200 bg-white/10 px-2 py-0.5 rounded">data/leaffox.db</code></p>
      </div>
      <label class="flex items-center gap-3 cursor-pointer bg-white/5 rounded-xl p-3 mb-6">
        <input type="checkbox" name="overwrite" value="1" class="accent-blue-500 w-4 h-4">
        <span class="text-sm text-gray-300">覆盖已有数据（如果之前安装过）</span>
      </label>
      <p class="text-gray-500 text-xs">SQLite 适合个人使用或低并发场景，无需额外配置</p>
    </div>

    <button type="submit" class="btn-primary w-full mt-6">开始安装</button>
    <p class="text-gray-500 text-xs text-center mt-3">安装过程中将自动创建数据库和表结构</p>
  </form>
  <?php endif; ?>
</div>

<script>
function switchTab(type) {
  document.getElementById('dbType').value = type;
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  document.querySelectorAll('.tab-content').forEach(function(c){ c.classList.remove('active'); });
  document.querySelector('.tab-btn[data-tab="'+type+'"]').classList.add('active');
  document.getElementById('tab'+type.charAt(0).toUpperCase()+type.slice(1)).classList.add('active');
}
</script>
</body>
</html>
