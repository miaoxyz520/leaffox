<?php
/**
 * Leaffox主页系统 - 前端口
 *
 * 访问方式:
 *   /               → 展示系统介绍（功能展示 + 注册/登录入口）
 *   /用户后缀       → 显示该用户的公开主页（URL 保持不变）
 *   /page/ /user/ /admin/ /api/ → 内部路由
 */
require_once __DIR__ . '/config.php';

// 数据库未连接时跳转安装向导
if (!$db) {
    // 如果反复安装还跳转，显示详细错误信息（在 install.php 也能看到）
    if (!empty($dbError)) {
        // 记录到 session 让 install.php 显示
        $_SESSION['install_db_error'] = $dbError;
    }
    header("Location: install.php");
    exit;
}

// ---- 路由解析 ----
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$basePath   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
$path       = '';

if (strpos($requestUri, '?') !== false) {
    $requestUri = substr($requestUri, 0, strpos($requestUri, '?'));
}

if ($basePath && $basePath !== '/') {
    $prefix = $basePath . '/';
    if (strncmp($requestUri, $prefix, strlen($prefix)) === 0) {
        $path = substr($requestUri, strlen($prefix));
    } elseif (strncmp($requestUri, $basePath, strlen($basePath)) === 0) {
        $path = substr($requestUri, strlen($basePath) + 1);
    }
} else {
    $path = ltrim($requestUri, '/');
}

$path = trim($path, '/');

// 内部保留路径——404
$skip_prefixes = ['page/', 'user/', 'admin/', 'api/'];
foreach ($skip_prefixes as $pfx) {
    if (strncmp($path, $pfx, strlen($pfx)) === 0) {
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>404 Not Found</title>';
        echo '<style>body{background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;flex-direction:column}</style>';
        echo '</head><body><h1 style="font-size:48px;margin:0">404</h1><p style="color:#64748b">页面不存在</p></body></html>';
        exit;
    }
}

// 如果有后缀 → 展示用户主页
if (!empty($path)) {
    $stmt = $db->prepare("SELECT id FROM users WHERE suffix = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$path]);
    $user = $stmt->fetch();

    if ($user) {
        $settings = getSettings($db);
        $bannedRaw = trim($settings['banned_suffixes'] ?? '');
        $bannedList = $bannedRaw ? array_map('trim', explode("\n", $bannedRaw)) : [];
        if (in_array($path, $bannedList)) {
            http_response_code(404);
            die('<h1 style="color:#e2e8f0;background:#0f172a;display:flex;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;margin:0">此页面已被管理员禁用</h1>');
        }
        $_GET['id'] = (int)$user['id'] ?? 0;
        require __DIR__ . '/page/index.php';
        exit;
    }

    // 后缀匹配不到 → 404
    http_response_code(404);
    ?>
    <!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><?php $sn = getSiteName($db); ?><title>404 - <?=h($sn)?></title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{background:#0f172a;color:#e2e8f0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;flex-direction:column;gap:16px;text-align:center;padding:20px}
    h1{font-size:72px;font-weight:900;background:linear-gradient(135deg,#2563eb,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    p{color:#64748b;font-size:16px}
    a{color:#3b82f6;text-decoration:none;border:1px solid rgba(129,140,248,0.3);padding:10px 24px;border-radius:12px;font-size:14px;transition:all 0.2s;margin-top:8px;display:inline-block}
    a:hover{background:rgba(129,140,248,0.1);border-color:#3b82f6}</style></head>
    <body><h1>404</h1><p>你访问的页面不存在</p><a href="<?=h(BASE_URL)?>">返回首页</a></body></html>
    <?php
    exit;
}

// ============================================================
// 空路径 → 展示着陆页（系统介绍 + 功能展示 + 注册/登录入口）
// ============================================================
$settings = getSettings($db);
$siteName = getSiteName($db);
$siteDesc = $settings['site_desc'] ?? '每个人都有自己的专属主页';
$isLogin  = !empty($_SESSION['user_id']) && !empty($_SESSION['user_login']);

// ---- 加载首页模板 ----
$templateName = $settings['site_template'] ?? 'default';
$templatePath = __DIR__ . "/templates/landing/{$templateName}.php";
if (!file_exists($templatePath)) {
    $templatePath = __DIR__ . '/templates/landing/default.php';
}
require $templatePath;
