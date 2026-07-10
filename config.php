<?php
/**
 * Leaffox 主页系统 - 优化版 v2.6
 * ===========================================
 * 【安全升级】CSRF防护 · 安全Header · 速率限制
 * 【性能优化】静态缓存 · 查询优化 · 索引增强
 * 【代码质量】模块化拆分 · 统一错误处理
 * 【UX增强】Rememer Me · 游客转化 · 模板预览
 * ===========================================
 */

// ---- 基础配置（首次安装时修改） ----
define('DB_TYPE', 'mysql');           // 'mysql' 或 'sqlite'
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'leaffox_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_USE_TCP', true);
define('SQLITE_PATH', __DIR__ . '/data/leaffox.db');

// ---- 安全配置 ----
define('APP_KEY', '');                // 留空自动生成，用于CSRF/加密
define('CSRF_EXPIRY', 7200);          // CSRF Token有效期（秒）
define('RATE_LIMIT_WINDOW', 300);     // 速率限制窗口（秒）
define('RATE_LIMIT_MAX', 10);         // 窗口内最大尝试次数
define('PASSWORD_MIN_LENGTH', 8);     // 密码最小长度

// ---- 路径常量 ----
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
define('BASE_URL', $protocol . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1'));
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('INCLUDE_DIR', __DIR__ . '/includes/');

// ---- 加载核心模块 ----
require_once INCLUDE_DIR . 'functions.php';
require_once INCLUDE_DIR . 'security.php';
require_once INCLUDE_DIR . 'init.php';
