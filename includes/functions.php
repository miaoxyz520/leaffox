<?php
/**
 * 公共函数模块 — 数据库 · 工具 · 模板 · 邮件
 */

// ---- 数据库连接 ----

function connectDatabase() {
    try {
        if (DB_TYPE === 'sqlite') {
            $dbDir = dirname(SQLITE_PATH);
            try {
                if (!is_dir($dbDir)) @mkdir($dbDir, 0755, true);
            } catch (Throwable $e) {
                // 忽略目录创建异常
            }
            $db = new PDO("sqlite:" . SQLITE_PATH, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $db->exec("PRAGMA journal_mode=WAL");
            $db->exec("PRAGMA foreign_keys=ON");
        } else {
            if (defined('DB_USE_TCP') && DB_USE_TCP) {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            } else {
                $socketPath = defined('DB_SOCKET') ? DB_SOCKET : '/run/mysqld/mysqld.sock';
                $dsn = "mysql:unix_socket=$socketPath;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            }
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return $db;
    } catch (PDOException $e) {
        error_log("[Leaffox DB Error] " . $e->getMessage());
        return null;
    }
}

function renderDbError($dbError) {
    $errMsg = htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8');
    die('<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"><title>数据库连接失败</title>'
        . '<style>body{background:#0f0f1a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;'
        . 'min-height:100vh;font-family:system-ui,sans-serif;margin:0;padding:20px}'
        . '.card{background:#1e1b4b;border:1px solid #7c3aed;border-radius:16px;padding:40px;max-width:520px;text-align:center}'
        . 'h1{color:#fca5a5;font-size:22px;margin:0 0 12px}'
        . 'p{color:#94a3b8;line-height:1.7;margin:0 0 16px;font-size:14px}'
        . 'code{background:#0f0f1a;padding:3px 10px;border-radius:6px;font-size:13px;color:#a78bfa}'
        . '.dbg{color:#64748b;font-size:12px;word-break:break-all}</style></head><body>'
        . '<div class="card"><h1>⚠ 数据库连接失败</h1>'
        . '<p>请检查 <code>config.php</code> 中的数据库信息：<br>'
        . '主机、库名、用户名、密码是否正确。</p>'
        . '<p class="dbg">错误详情：' . $errMsg . '</p>'
        . '</div></body></html>');
}

// ---- IP / 字符串工具 ----

function getClientIP() {
    $ip = '';
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = explode(',', $_SERVER[$k])[0];
            break;
        }
    }
    return filter_var(trim($ip), FILTER_VALIDATE_IP) ?: '0.0.0.0';
}

/** XSS 安全过滤 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** 安全截取字符串 */
function safeSubstr($str, $len = 100) {
    return mb_substr($str, 0, $len, 'UTF-8');
}

/** 生成随机令牌 */
function generateToken($len = 32) {
    return bin2hex(random_bytes($len));
}

// ---- 文件上传（安全版） ----

function validImage($file) {
    return safeValidImage($file); // 使用 security.php 的安全版
}

function validVideo($file) {
    $allowed = ['video/mp4', 'video/webm', 'video/quicktime'];
    $maxSize = 100 * 1024 * 1024;
    $realMime = getRealFileType($file['tmp_name']);
    if (!in_array($realMime, $allowed)) return '仅支持 MP4/WebM/MOV 格式';
    if ($file['size'] > $maxSize) return '视频大小不能超过 100MB';
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4', 'webm', 'mov'])) return '文件扩展名不合法';
    return true;
}

function uploadFile($file, $prefix = 'file', $allowedMimes = [], $maxSize = 2 * 1024 * 1024) {
    if (empty($allowedMimes)) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    }
    $realMime = getRealFileType($file['tmp_name']);
    if (!in_array($realMime, $allowedMimes)) return ['error' => '不支持的文件格式'];
    if ($file['size'] > $maxSize) return ['error' => '文件大小超过限制'];
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $uploadPath = UPLOAD_DIR . $filename;
    
    try {
        if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
    } catch (Throwable $e) {}
    if (!is_dir(UPLOAD_DIR)) return ['error' => '上传目录无法创建，请检查权限'];
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['error' => '上传失败，请检查目录权限'];
    }
    
    return ['path' => 'uploads/' . $filename];
}

function uploadImage($file, $prefix = 'avatar') {
    $result = validImage($file);
    if ($result !== true) return ['error' => $result];
    return uploadFile($file, $prefix);
}

// ---- 站点设置（带缓存） ----

function getSiteName($db = null) {
    $settings = $db ? getSettings($db) : [];
    return $settings['site_name'] ?? 'Leaffox主页系统';
}

function getPoweredBy($db = null) {
    $settings = $db ? getSettings($db) : [];
    return $settings['powered_by_name'] ?? 'Leaffox主页系统';
}

/**
 * 获取全站设置 - 带静态缓存（避免重复查询），优先尝试文件缓存
 * 文件缓存写失败时自动降级为请求级静态缓存
 */
function getSettings($db, $forceRefresh = false) {
    static $settings = null;
    if ($settings !== null && !$forceRefresh) return $settings;
    if (!$db) return [];
    
    // 尝试文件缓存（跨请求复用，减少SQL查询）
    if (!$forceRefresh && $settings === null) {
        $cacheData = tryGetFileCache('settings');
        if ($cacheData !== null) {
            $settings = $cacheData;
            return $settings;
        }
    }
    
    try {
        $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
        $settings = $stmt->fetch() ?: [];
        trySetFileCache('settings', $settings); // 尝试写缓存，失败静默
    } catch (Exception $e) {
        $settings = [];
    }
    return $settings ?: [];
}

/**
 * 尝试从文件缓存读取（安全：无警告）
 */
function tryGetFileCache($key) {
    $cacheFile = __DIR__ . '/../cache/' . $key . '.cache.php';
    if (!@file_exists($cacheFile)) return null;
    $expires = @filemtime($cacheFile);
    if (!$expires || (time() - $expires > 300)) return null; // 5分钟过期
    $data = @include $cacheFile;
    return is_array($data) ? $data : null;
}

/**
 * 尝试写文件缓存（安全：权限不足时静默跳过，无任何警告）
 */
function trySetFileCache($key, $data) {
    try {
        $dir = __DIR__ . '/../cache';
        if (!is_dir($dir)) {
            $parentWritable = @is_writable(dirname($dir));
            if (!$parentWritable || (!@mkdir($dir, 0755, true) && !is_dir($dir))) {
                return false;
            }
        }
        if (!is_writable($dir)) return false;
        $cacheFile = $dir . '/' . $key . '.cache.php';
        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . ';', LOCK_EX);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * 清除全部文件缓存
 */
function clearAllCache() {
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) return 0;
    $count = 0;
    foreach (glob($cacheDir . '/*.cache.php') as $f) {
        if (@unlink($f)) $count++;
    }
    return $count;
}

/**
 * 获取缓存统计信息
 */
function getCacheStats() {
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) return ['count' => 0, 'size' => 0];
    $count = 0;
    $size = 0;
    foreach (glob($cacheDir . '/*.cache.php') as $f) {
        $count++;
        $size += filesize($f);
    }
    return ['count' => $count, 'size' => $size];
}

function setSetting($db, $key, $value) {
    try {
        $cols = $db->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array($key, $cols)) {
            $safeKey = str_replace('`', '``', $key);
            $db->exec("ALTER TABLE settings ADD COLUMN `$safeKey` TEXT DEFAULT ''");
        }
        $stmt = $db->prepare("UPDATE settings SET `$key` = ? WHERE id = 1");
        $stmt->execute([$value]);
        getSettings($db, true); // 刷新缓存
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ---- 管理员日志 ----

function adminLog($db, $action, $target_type = '', $target_id = 0, $detail = '') {
    if (!isset($_SESSION['admin_id'])) return;
    $detail = is_array($detail) ? json_encode($detail, JSON_UNESCAPED_UNICODE) : $detail;
    $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, detail, ip, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['admin_id'], $action, $target_type, $target_id, $detail, getClientIP(), date('Y-m-d H:i:s')]);
}

// ---- 响应工具 ----

function jsonResponse($data, $code = 200) {
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// ---- 分页工具 ----

function paginate($db, $table, $where = '', $params = [], $page = 1, $perPage = 20) {
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));
    $whereSql = $where ? "WHERE $where" : '';
    
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM $table $whereSql");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $lastPage = max(1, ceil($total / $perPage));
    $page = min($page, $lastPage);
    $offset = ($page - 1) * $perPage;
    
    return [
        'page'     => $page,
        'perPage'  => $perPage,
        'total'    => $total,
        'lastPage' => $lastPage,
        'offset'   => $offset,
    ];
}

// ---- 权限检查 ----

function requireAdmin() {
    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_login'])) {
        $loginUrl = (defined('BASE_URL') ? BASE_URL : '') . '/admin/index.php';
        header("Location: $loginUrl");
        exit;
    }
    checkSessionHijack();
}

function requireUser() {
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_login'])) {
        $loginUrl = (defined('BASE_URL') ? BASE_URL : '') . '/user/';
        header("Location: $loginUrl");
        exit;
    }
    checkSessionHijack();
}

// ---- 模板系统 ----

function getTemplates($type = 'home') {
    static $cache = [];
    if (isset($cache[$type])) return $cache[$type];
    $dir = __DIR__ . '/../templates/' . $type;
    $previewDir = __DIR__ . '/../templates/' . $type . '_preview';
    $templates = [];
    if (!is_dir($dir)) return $templates;
    $files = glob($dir . '/*.php');
    sort($files);
    foreach ($files as $file) {
        $id = basename($file, '.php');
        $content = file_get_contents($file);
        $name = $id;
        $desc = '';
        if (preg_match('/\* Template:\s*(.+?)(?:\n|\r)/', $content, $m)) $name = trim($m[1]);
        if (preg_match('/\*\s*Desc:\s*(.+?)(?:\n|\r)/', $content, $m)) $desc = trim($m[1]);
        $preview = '';
        foreach (['png','jpg','jpeg','webp'] as $ext) {
            $pf = $previewDir . '/' . $id . '.' . $ext;
            if (file_exists($pf)) { $preview = 'templates/' . $type . '_preview/' . $id . '.' . $ext; break; }
        }
        $templates[] = [
            'id'       => $id,
            'name'     => $name,
            'desc'     => $desc,
            'preview'  => $preview,
            'file'     => $file,
        ];
    }
    $cache[$type] = $templates;
    return $templates;
}

function getTemplateMeta($type, $id) {
    $file = __DIR__ . '/../templates/' . $type . '/' . $id . '.php';
    if (!file_exists($file)) return null;
    $content = file_get_contents($file);
    $name = $id;
    $desc = '';
    if (preg_match('/\* Template:\s*(.+?)(?:\n|\r)/', $content, $m)) $name = trim($m[1]);
    if (preg_match('/\*\s*Desc:\s*(.+?)(?:\n|\r)/', $content, $m)) $desc = trim($m[1]);
    return ['id' => $id, 'name' => $name, 'desc' => $desc];
}

function getHomeTemplateId($db) {
    $settings = getSettings($db);
    $tid = $settings['home_template'] ?? 'default';
    $meta = getTemplateMeta('home', $tid);
    return $meta ? $tid : 'default';
}

function getUserTemplateId($user) {
    $tid = $user['page_template'] ?? 'glass';
    return $tid;
}

// ---- 邮件模板 ----

function renderEmailTpl($tpl, $vars = []) {
    foreach ($vars as $key => $val) {
        $tpl = str_replace('{' . $key . '}', $val, $tpl);
    }
    return $tpl;
}

function getEmailTpl($type, $db) {
    $settings = getSettings($db);
    $key = 'email_tpl_' . $type;
    $tpl = $settings[$key] ?? '';
    if (empty($tpl)) {
        $tpl = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>{site_name}</title></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif;">
<div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);">
  <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:22px;">{title}</h1>
  </div>
  <div style="padding:35px 30px;">
    <p style="color:#333;font-size:15px;line-height:1.7;">{body}</p>
    <div style="text-align:center;margin:30px 0;">
      {action_html}
    </div>
    <p style="color:#999;font-size:13px;">{footer_note}</p>
  </div>
  <div style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #eee;">
    <p style="color:#aaa;font-size:12px;margin:0;">{site_name}</p>
  </div>
</div>
</body></html>
HTML;
    }
    return $tpl;
}

// ---- 数据验证 ----

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$/u', $username);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateSuffix($suffix) {
    return preg_match('/^[a-zA-Z0-9_-]+$/', $suffix);
}

function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}
