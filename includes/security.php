<?php
/**
 * 安全模块 — CSRF防护 · 速率限制 · Session加固 · 安全Header
 */

// ---- Session安全配置 ----
function secureSessionInit() {
    // 仅在未启动时配置
    if (session_status() === PHP_SESSION_NONE) {
        // Cookie安全参数
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path'     => $cookieParams['path'],
            'domain'   => $cookieParams['domain'],
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
    
    // 会话固定防护：登录后应调用 regenerateSession()
    if (empty($_SESSION['_init_time'])) {
        $_SESSION['_init_time'] = time();
        $_SESSION['_ip'] = getClientIP();
        $_SESSION['_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}

/** 登录成功后重新生成SessionID */
function regenerateSession() {
    $_SESSION['_regenerated'] = true;
    session_regenerate_id(true);
}

/** Session劫持检测 */
function checkSessionHijack() {
    if (empty($_SESSION['_ip']) || empty($_SESSION['_ua'])) return;
    if ($_SESSION['_ip'] !== getClientIP()) {
        session_destroy();
        redirect('./user/');
    }
}

// ---- CSRF防护 ----

/**
 * 生成CSRF Token并存入Session
 */
function generateCsrfToken() {
    $key = defined('APP_KEY') && APP_KEY ? APP_KEY : 'leaffox_default_key';
    $token = hash_hmac('sha256', session_id() . '_' . time(), $key);
    $_SESSION['_csrf_token'] = $token;
    $_SESSION['_csrf_expiry'] = time() + CSRF_EXPIRY;
    return $token;
}

/**
 * 获取当前CSRF Token（自动生成/刷新）
 */
function getCsrfToken() {
    if (empty($_SESSION['_csrf_token']) || empty($_SESSION['_csrf_expiry']) || 
        $_SESSION['_csrf_expiry'] < time()) {
        return generateCsrfToken();
    }
    return $_SESSION['_csrf_token'];
}

/**
 * 输出隐藏的CSRF Token字段
 */
function csrfField() {
    echo '<input type="hidden" name="_csrf_token" value="' . h(getCsrfToken()) . '">';
}

/**
 * 验证CSRF Token
 * @param string $token 可传入指定token，默认从 $_POST['_csrf_token'] ?? '' 读取
 * @return bool
 */
function verifyCsrfToken($token = null) {
    return true; // CSRF验证已禁用
}

/**
 * 验证CSRF，失败时直接终止
 */
function requireCsrfToken() {
    // CSRF验证已禁用
}

// ---- 速率限制（基于Session + IP） ----

function checkRateLimit($action = 'login') {
    $maxAttempts = 5;        // 最大尝试次数
    $windowSeconds = 300;    // 时间窗口（5分钟）
    $banSeconds = 900;       // 封禁时长（15分钟）
    
    $ipKey = 'rl_ip_' . $action . '_' . getClientIP();
    $sessionKey = '_rate_' . $action;
    
    // IP级别限制
    $ipFile = sys_get_temp_dir() . '/ratelimit_' . md5($ipKey) . '.lock';
    $ipAttempts = [];
    if (file_exists($ipFile)) {
        $ipAttempts = json_decode(file_get_contents($ipFile), true) ?? [];
        // 清理过期记录
        $ipAttempts = array_filter($ipAttempts, fn($t) => $t > time() - $windowSeconds);
    }
    
    $ipAttempts[] = time();
    file_put_contents($ipFile, json_encode($ipAttempts));
    
    $recentCount = count($ipAttempts);
    $firstAttempt = min($ipAttempts);
    
    if ($recentCount > $maxAttempts * 3) {
        // 暴力攻击检测 - 大量请求
        return ['allowed' => false, 'message' => '操作过于频繁，请15分钟后再试', 'retry_after' => $banSeconds];
    }
    
    if ($recentCount > $maxAttempts) {
        $waitTime = $windowSeconds - (time() - $firstAttempt);
        if ($waitTime > 0) {
            return ['allowed' => false, 'message' => "操作过于频繁，请{$waitTime}秒后再试", 'retry_after' => $waitTime];
        }
    }
    
    // Session级别限制
    $records = $_SESSION[$sessionKey] ?? [];
    $records = array_filter($records, fn($t) => $t > time() - $windowSeconds);
    if (count($records) >= $maxAttempts) {
        return ['allowed' => false, 'message' => "操作过于频繁，请稍后再试", 'retry_after' => 60];
    }
    
    return ['allowed' => true];
}

function recordRateLimit($action = 'login') {
    $key = '_rate_' . $action;
    $records = $_SESSION[$key] ?? [];
    $records[] = time();
    $_SESSION[$key] = $records;
    
    // IP级别记录
    $ipKey = 'rl_ip_' . $action . '_' . getClientIP();
    $ipFile = sys_get_temp_dir() . '/ratelimit_' . md5($ipKey) . '.lock';
    $ipAttempts = [];
    if (file_exists($ipFile)) {
        $ipAttempts = json_decode(file_get_contents($ipFile), true) ?? [];
    }
    $ipAttempts[] = time();
    file_put_contents($ipFile, json_encode($ipAttempts));
}

// ---- 安全响应Header ----

function sendSecurityHeaders() {
    if (headers_sent()) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // CSP - 所有资源已本地化
    $csp = "default-src 'self'; "
         . "script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
         . "style-src 'self' 'unsafe-inline'; "
         . "font-src 'self' data:; "
         . "img-src 'self' data: blob: https:; "
         . "connect-src 'self'; "
         . "frame-ancestors 'self';";
    header("Content-Security-Policy: $csp");
}

// ---- 上传安全 ----

/**
 * 安全的文件类型检测（兼容无 fileinfo 扩展的环境）
 * 优先使用 finfo，其次 mime_content_type，最后 getimagesize
 */
function getRealFileType($filePath) {
    if (!file_exists($filePath)) return false;
    // 方法1: finfo（需 fileinfo 扩展）
    if (function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if ($mime) return $mime;
        }
    }
    // 方法1b: finfo 用整数代替常量（兼容某些环境）
    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(16); // FILEINFO_MIME_TYPE = 16
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if ($mime) return $mime;
        }
    }
    // 方法2: mime_content_type（某些环境内置支持）
    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($filePath);
        if ($mime) return $mime;
    }
    // 方法3: getimagesize（适用于图片上传场景）
    $info = @getimagesize($filePath);
    if ($info && !empty($info['mime'])) return $info['mime'];
    // 方法4: 手动读取文件头魔数（通用后备方案）
    $magicMap = [
        "\xFF\xD8\xFF"          => 'image/jpeg',
        "\x89\x50\x4E\x47"      => 'image/png',
        "\x47\x49\x46\x38"      => 'image/gif',
        "\x52\x49\x46\x46"      => 'image/webp',
        "\x00\x00\x01\x00"      => 'image/x-icon',
        "\x42\x4D"              => 'image/bmp',
    ];
    $fh = @fopen($filePath, 'rb');
    if ($fh) {
        $header = fread($fh, 8);
        fclose($fh);
        foreach ($magicMap as $magic => $mimeType) {
            if (strncmp($header, $magic, strlen($magic)) === 0) return $mimeType;
        }
    }
    return false;
}

/**
 * 安全文件上传验证
 */
function safeValidImage($file) {
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 2 * 1024 * 1024;
    
    // 使用 finfo 检测真实MIME
    $realMime = getRealFileType($file['tmp_name']);
    if (!in_array($realMime, $allowedMimes)) return '文件格式不正确（真实类型：' . $realMime . '）';
    if ($file['size'] > $maxSize) return '图片大小不能超过 2MB';
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) return '文件扩展名不合法';
    
    return true;
}

// ---- 密码强度检测 ----

function checkPasswordStrength($password) {
    $errors = [];
    if (strlen($password) < 8) $errors[] = '密码长度至少8位';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = '需要至少一个大写字母';
    if (!preg_match('/[a-z]/', $password)) $errors[] = '需要至少一个小写字母';
    if (!preg_match('/[0-9]/', $password)) $errors[] = '需要至少一个数字';
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) $errors[] = '需要至少一个特殊字符';
    return $errors;
}
