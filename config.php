<?php
/**
 * Leaffox主页系统 - 核心配置
 * 数据库连接 · 会话管理 · 公共函数
 * 支持 MySQL / SQLite 两种数据库
 */
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// ---- 数据库配置 ----
// DB_TYPE: 'mysql' 或 'sqlite'
define('DB_TYPE', 'mysql');

// MySQL 连接参数（DB_TYPE='mysql' 时生效）
// <i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> 如果使用 MySQL，请用 TCP 方式 (localhost→127.0.0.1)
// 如果用 Unix Socket，把 DB_HOST 改为 socket 路径并注释掉 DB_USE_TCP
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'leaffox_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_USE_TCP', true);          // true=TCP连接, false=Unix Socket连接

// SQLite 参数（DB_TYPE='sqlite' 时生效）
define('SQLITE_PATH', __DIR__ . '/data/leaffox.db');

// ---- 站点路径 ----
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
define('BASE_URL', $protocol . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1'));
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// ---- PDO 连接 ----
$db = null;
$dbError = '';
try {
    if (DB_TYPE === 'sqlite') {
        $dbDir = dirname(SQLITE_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        $dsn = "sqlite:" . SQLITE_PATH;
        $dbOptions = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $db = new PDO($dsn, null, null, $dbOptions);
        // SQLite 性能优化
        $db->exec("PRAGMA journal_mode=WAL");
        $db->exec("PRAGMA foreign_keys=ON");
    } else {
        if (defined('DB_USE_TCP') && DB_USE_TCP) {
            // TCP 方式连接（兼容性好，推荐）
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        } else {
            // Unix Socket 方式连接（需确认 socket 路径正确）
            $socketPath = defined('DB_SOCKET') ? DB_SOCKET : '/run/mysqld/mysqld.sock';
            $dsn = "mysql:unix_socket=$socketPath;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        }
        $db = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
} catch (PDOException $e) {
    $dbError = $e->getMessage();
    // 不直接 die()，让安装向导等页面自己处理
}

// ---- 自动修复表结构（兼容旧版升级） ----
if ($db) {
    try {
        // ---- 自动创建缺失的表 ----
        // 1. 检测是否存在「缺失表标记」；利用 links 表的存否判断是否要重建全套表
        $tablesExist = true;
        try {
            $db->query("SELECT 1 FROM links LIMIT 1");
        } catch (Exception $e) {
            $tablesExist = false;
        }
        if (!$tablesExist) {
            // stats 表
            $db->exec("CREATE TABLE IF NOT EXISTS `stats` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`     INT UNSIGNED NOT NULL,
                `link_id`     INT UNSIGNED DEFAULT 0,
                `type`        ENUM('view','click') NOT NULL,
                `ip`          VARCHAR(45)  DEFAULT '',
                `user_agent`  VARCHAR(500) DEFAULT '',
                `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_user_type` (`user_id`, `type`),
                INDEX `idx_date` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // links 表
            $db->exec("CREATE TABLE IF NOT EXISTS `links` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`     INT UNSIGNED NOT NULL,
                `title`       VARCHAR(100) NOT NULL,
                `url`         VARCHAR(500) NOT NULL,
                `icon`        VARCHAR(10)  DEFAULT '<i class=\"fas fa-link\"></i>',
                `sort_order`  INT UNSIGNED DEFAULT 0,
                `click_count` INT UNSIGNED DEFAULT 0,
                `is_active`   TINYINT(1)   DEFAULT 1,
                `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // settings 表
            $db->exec("CREATE TABLE IF NOT EXISTS `settings` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `site_name`   VARCHAR(100) DEFAULT 'Leaffox主页系统',
                `site_logo`   VARCHAR(255) DEFAULT '',
                `site_desc`   VARCHAR(200) DEFAULT '每个人都有自己的专属主页',
                `icp_record`  VARCHAR(100) DEFAULT '',
                `footer_text` VARCHAR(200) DEFAULT '',
                `show_free_make_btn` TINYINT(1) DEFAULT 1,
                `reg_enabled` TINYINT(1)   DEFAULT 1,
                `style_data`  TEXT,
                `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // admin_logs 表
            $db->exec("CREATE TABLE IF NOT EXISTS `admin_logs` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `admin_id`    INT UNSIGNED NOT NULL,
                `action`      VARCHAR(100) NOT NULL,
                `target_type` VARCHAR(32)  DEFAULT '',
                `target_id`   INT UNSIGNED DEFAULT 0,
                `detail`      TEXT         DEFAULT NULL,
                `ip`          VARCHAR(45)  DEFAULT '',
                `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`admin_id`) REFERENCES `admin`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // password_resets 表
            $db->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `email`       VARCHAR(128) NOT NULL,
                `token`       VARCHAR(64)  NOT NULL UNIQUE,
                `used`        TINYINT(1)   DEFAULT 0,
                `expires_at`  DATETIME     NOT NULL,
                `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_token` (`token`),
                INDEX `idx_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            // 仅补列（links/stats 等表已存在但可能缺列）
            try { $db->exec("ALTER TABLE `links` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `click_count`"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE `links` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `is_active`"); } catch (Exception $e) {}
        }

        // settings 补列
        try { $db->exec("ALTER TABLE `settings` ADD COLUMN `style_data` TEXT"); } catch (Exception $e) {}

        // ---- 补列：全部可能的缺失列（直接尝试添加，重复自动跳过） ----
        $fixColumns = [
            'users' => [
                "ALTER TABLE `users` ADD COLUMN `nickname` VARCHAR(32) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1",
                "ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `bio` VARCHAR(200) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `bg_preset` VARCHAR(20) DEFAULT 'default'",
                "ALTER TABLE `users` ADD COLUMN `bg_color` VARCHAR(20) DEFAULT '#0f172a'",
                "ALTER TABLE `users` ADD COLUMN `bg_image` VARCHAR(255) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `custom_bg_type` VARCHAR(10) DEFAULT 'color'",
                "ALTER TABLE `users` ADD COLUMN `custom_gradient_from` VARCHAR(10) DEFAULT '#667eea'",
                "ALTER TABLE `users` ADD COLUMN `custom_gradient_to` VARCHAR(10) DEFAULT '#764ba2'",
                "ALTER TABLE `users` ADD COLUMN `custom_gradient_dir` VARCHAR(10) DEFAULT '135deg'",
                "ALTER TABLE `users` ADD COLUMN `theme_mode` VARCHAR(10) DEFAULT 'auto'",
                "ALTER TABLE `users` ADD COLUMN `card_style` VARCHAR(20) DEFAULT 'glass'",
                "ALTER TABLE `users` ADD COLUMN `social_data` TEXT DEFAULT NULL",
                "ALTER TABLE `users` ADD COLUMN `btn_bg` VARCHAR(20) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `btn_color` VARCHAR(20) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `btn_outline` VARCHAR(20) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `btn_arrow` TINYINT(1) DEFAULT 1",
                "ALTER TABLE `users` ADD COLUMN `arrow_color` VARCHAR(20) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `title_color` VARCHAR(20) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `desc_color` VARCHAR(20) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `announcement` TEXT DEFAULT NULL",
                "ALTER TABLE `users` ADD COLUMN `announcement_enabled` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `custom_music` VARCHAR(500) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `custom_music_autoplay` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `custom_music_icon` VARCHAR(10) DEFAULT 'b'",
                "ALTER TABLE `users` ADD COLUMN `show_stats` TINYINT(1) DEFAULT 1",
                "ALTER TABLE `users` ADD COLUMN `footer_text` VARCHAR(200) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `footer_align` VARCHAR(10) DEFAULT 'center'",
                "ALTER TABLE `users` ADD COLUMN `last_ip` VARCHAR(45) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `last_login` DATETIME DEFAULT NULL",
                "ALTER TABLE `users` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP",
                "ALTER TABLE `users` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
                "ALTER TABLE `users` ADD COLUMN `open_tip_wechat` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `open_tip_qq` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `open_tip_douyin` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `open_tip_weibo` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `tipping_enabled` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `tipping_qrcode` VARCHAR(500) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `tipping_title` VARCHAR(100) DEFAULT '感谢支持 <i class=\"fas fa-heart\" style=\"color:#ef4444\"></i>'",
                "ALTER TABLE `users` ADD COLUMN `video_auto_expand` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` MODIFY `suffix` VARCHAR(32) DEFAULT ''",
            ],
            'links' => [
                "ALTER TABLE `links` ADD COLUMN `favicon_type` VARCHAR(10) DEFAULT 'emoji' COMMENT '图标来源: emoji/favicon/upload'",
                "ALTER TABLE `links` ADD COLUMN `card_color` VARCHAR(30) DEFAULT ''",  # 改为空，前端统一走白色

                "ALTER TABLE `links` ADD COLUMN `type` VARCHAR(20) DEFAULT 'link'",
                "ALTER TABLE `links` ADD COLUMN `card_color` VARCHAR(20) DEFAULT 'rgba(255,255,255,0.08)'",
                "ALTER TABLE `links` ADD COLUMN `text_color` VARCHAR(20) DEFAULT '#ffffff'",
                "ALTER TABLE `links` ADD COLUMN `sort_order` INT UNSIGNED DEFAULT 0",
                "ALTER TABLE `links` ADD COLUMN `outline` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `links` ADD COLUMN `passcode` VARCHAR(10) DEFAULT ''",
                "ALTER TABLE `links` ADD COLUMN `popup_img` VARCHAR(500) DEFAULT ''",
                "ALTER TABLE `links` ADD COLUMN `text_center` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `links` ADD COLUMN `btn_radius_on` TINYINT(1) DEFAULT 1",
                "ALTER TABLE `links` ADD COLUMN `btn_radius` INT UNSIGNED DEFAULT 30",
                "ALTER TABLE `links` ADD COLUMN `video_file` VARCHAR(500) DEFAULT ''",
                "ALTER TABLE `links` ADD COLUMN `video_source` VARCHAR(20) DEFAULT 'file'",
                "ALTER TABLE `links` ADD COLUMN `video_loop` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `links` ADD COLUMN `video_poster` VARCHAR(500) DEFAULT ''",
                "ALTER TABLE `links` ADD COLUMN `is_hidden` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `links` ADD COLUMN `is_violation` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `links` ADD COLUMN `click_count` INT UNSIGNED DEFAULT 0",
                "ALTER TABLE `links` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            ],
            'admin' => [
                "ALTER TABLE `admin` ADD COLUMN `nickname` VARCHAR(32) DEFAULT '管理员'",
                "ALTER TABLE `admin` ADD COLUMN `avatar` VARCHAR(255) DEFAULT ''",
                "ALTER TABLE `admin` ADD COLUMN `email` VARCHAR(128) DEFAULT ''",
                "ALTER TABLE `admin` ADD COLUMN `last_login` DATETIME DEFAULT NULL",
            ],
            'stats' => [
                "ALTER TABLE `stats` MODIFY `id` INT UNSIGNED AUTO_INCREMENT",
                "ALTER TABLE `stats` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP",
            ],
        ];
        // ---- 补列：邮箱相关（用户可邮箱登录/注册/验证） ----
        $emailColumns = [
            'users' => [
                "ALTER TABLE `users` ADD COLUMN `email` VARCHAR(128) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `users` ADD COLUMN `email_verify_token` VARCHAR(64) DEFAULT ''",
                "ALTER TABLE `users` ADD COLUMN `verify_token_expires` DATETIME DEFAULT NULL",
            ],
            'settings' => [
                "ALTER TABLE `settings` ADD COLUMN `reg_email_verify` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `settings` ADD COLUMN `smtp_host` VARCHAR(100) DEFAULT ''",
                "ALTER TABLE `settings` ADD COLUMN `smtp_port` INT UNSIGNED DEFAULT 465",
                "ALTER TABLE `settings` ADD COLUMN `smtp_user` VARCHAR(100) DEFAULT ''",
                "ALTER TABLE `settings` ADD COLUMN `smtp_pass` VARCHAR(128) DEFAULT ''",
                "ALTER TABLE `settings` ADD COLUMN `smtp_encrypt` VARCHAR(10) DEFAULT 'ssl'",
                "ALTER TABLE `settings` ADD COLUMN `smtp_from_name` VARCHAR(50) DEFAULT ''",
                "ALTER TABLE `settings` ADD COLUMN `user_email_login` TINYINT(1) DEFAULT 1",
                "ALTER TABLE `settings` ADD COLUMN `admin_email_login` TINYINT(1) DEFAULT 0",
                "ALTER TABLE `settings` ADD COLUMN `admin_email` VARCHAR(128) DEFAULT ''",
                "ALTER TABLE `settings` ADD COLUMN `show_free_make_btn` TINYINT(1) DEFAULT 1",
            ],
        ];
        foreach ($emailColumns as $table => $sqls) {
            foreach ($sqls as $sql) {
                try { $db->exec($sql); } catch (Exception $e) {}
            }
        }
        foreach ($fixColumns as $table => $sqls) {
            foreach ($sqls as $sql) {
                try { $db->exec($sql); } catch (Exception $e) {
                    // 列已存在或表不存在 → 静默跳过
                }
            }
        }
        
        // ---- 补表：reports（举报表） ----
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `reports` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`     INT UNSIGNED NOT NULL COMMENT '被举报的用户ID',
                `reporter_ip` VARCHAR(45)  DEFAULT '' COMMENT '举报者IP',
                `type`        VARCHAR(32)  NOT NULL COMMENT '举报类型',
                `reason`      TEXT         DEFAULT NULL COMMENT '详细说明',
                `status`      TINYINT(1)   DEFAULT 0 COMMENT '0=未处理 1=已处理',
                `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {}
        
        // ---- 补表：互动功能表（点赞 / 评论 / 收藏） ----
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `page_likes` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `page_user_id` INT UNSIGNED NOT NULL COMMENT '被点赞的主页主人用户ID',
                `visitor_id`   INT UNSIGNED NOT NULL COMMENT '点赞者用户ID',
                `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_like` (`page_user_id`, `visitor_id`),
                FOREIGN KEY (`page_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`visitor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {}
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `page_comments` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `page_user_id` INT UNSIGNED NOT NULL COMMENT '被评论的主页主人用户ID',
                `visitor_id`   INT UNSIGNED NOT NULL COMMENT '评论者用户ID',
                `content`      VARCHAR(500) NOT NULL COMMENT '评论内容',
                `status`       TINYINT(1)   DEFAULT 1 COMMENT '0=待审核 1=已通过(公开) 2=隐藏',
                `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_comments_page` (`page_user_id`, `created_at`),
                FOREIGN KEY (`page_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`visitor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {}
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `page_favorites` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `page_user_id` INT UNSIGNED NOT NULL COMMENT '被收藏的主页主人用户ID',
                `visitor_id`   INT UNSIGNED NOT NULL COMMENT '收藏者用户ID',
                `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_fav` (`page_user_id`, `visitor_id`),
                FOREIGN KEY (`page_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`visitor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {}
        
        // ---- 补列：用户表互动开关 + 评论审核 ----
        $interactionColumns = [
            "ALTER TABLE `users` ADD COLUMN `enable_likes` TINYINT(1) DEFAULT 1 COMMENT '允许点赞 1开启'",
            "ALTER TABLE `users` ADD COLUMN `enable_comments` TINYINT(1) DEFAULT 1 COMMENT '允许评论 1开启'",
            "ALTER TABLE `users` ADD COLUMN `enable_favorites` TINYINT(1) DEFAULT 1 COMMENT '允许收藏 1开启'",
            "ALTER TABLE `users` ADD COLUMN `comment_audit_enabled` TINYINT(1) DEFAULT 0 COMMENT '评论审核开关 1开启'",
        ];
        foreach ($interactionColumns as $sql) {
            try { $db->exec($sql); } catch (Exception $e) {}
        }
        // 补列：已存在的 page_comments 表增加 status 字段
        try { $db->exec("ALTER TABLE `page_comments` ADD COLUMN `status` TINYINT(1) DEFAULT 1 COMMENT '0=待审核 1=已通过 2=隐藏'"); } catch (Exception $e) {}
        $oldHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $newHash = '$2y$10$owes/Y/brxFK3RxLmsceY.CoaPM53Jao5Ovz7fGRZdVbFMQ2mL/Ju';
        try {
            $stmt = $db->prepare("UPDATE `admin` SET `password` = ? WHERE `username` = 'admin' AND `password` = ?");
            $stmt->execute([$newHash, $oldHash]);
            if ($stmt->rowCount() > 0) {
                $_SESSION = [];
                session_destroy();
            }
        } catch (Exception $e) {}
    } catch (Exception $e) {
        // 兼容 SQLite（SHOW COLUMNS 不适用），静默忽略
    }
}

// ---- 请求根路径 ----
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$basePath = str_replace('\\', '/', $scriptDir);

// ---- ====== 公共函数 ====== ----

/** 获取客户端真实IP */
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

/** 检查上传图片类型和大小 */
function validImage($file) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    if (!in_array($file['type'], $allowed)) return '仅支持 JPG/PNG/GIF/WebP 格式';
    if ($file['size'] > $maxSize) return '图片大小不能超过 2MB';
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return '文件扩展名不合法';
    return true;
}

/** 检查上传视频类型和大小 */
function validVideo($file) {
    $allowed = ['video/mp4', 'video/webm', 'video/quicktime'];
    $maxSize = 100 * 1024 * 1024; // 100MB
    if (!in_array($file['type'], $allowed)) return '仅支持 MP4/WebM/MOV 格式';
    if ($file['size'] > $maxSize) return '视频大小不能超过 100MB';
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4','webm','mov'])) return '文件扩展名不合法';
    return true;
}

/** 通用文件上传（图片/视频/任意） */
function uploadFile($file, $prefix = 'file', $allowedMimes = [], $maxSize = 2 * 1024 * 1024) {
    if (empty($allowedMimes)) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    }
    if (!in_array($file['type'], $allowedMimes)) return ['error' => '不支持的文件格式'];
    if ($file['size'] > $maxSize) return ['error' => '文件大小超过限制'];
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $uploadPath = UPLOAD_DIR . $filename;
    
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['error' => '上传失败，请检查目录权限'];
    }
    
    return ['path' => 'uploads/' . $filename];
}

/** 上传图片并返回存储路径 */
function uploadImage($file, $prefix = 'avatar') {
    $result = validImage($file);
    if ($result !== true) return ['error' => $result];
    return uploadFile($file, $prefix);
}

/** 获取站点名称 - 优先读数据库设置，否则返回默认 */
function getSiteName($db = null) {
    $settings = $db ? getSettings($db) : [];
    return $settings['site_name'] ?? 'Leaffox主页系统';
}

/** 获取系统署名名称 */
function getPoweredBy($db = null) {
    $settings = $db ? getSettings($db) : [];
    return $settings['powered_by_name'] ?? 'Leaffox主页系统';
}

/** 获取全站设置 */
/**
 * 渲染邮件模版 - 替换全部 {变量名}
 * @param string $tpl    模版HTML（含 {变量名}）
 * @param array  $vars   变量键值对
 * @return string 渲染后的HTML
 */
function renderEmailTpl($tpl, $vars = []) {
    foreach ($vars as $key => $val) {
        $tpl = str_replace('{' . $key . '}', $val, $tpl);
    }
    return $tpl;
}

/**
 * 获取邮件模版（带默认值兜底，默认模版支持 {title} {body} {action_html} {footer_note} {site_name}）
 * @param string $type  register / resetpwd / verify
 * @param object $db
 * @return string HTML模版
 */
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

/** 获取全站设置 */
function getSettings($db, $forceRefresh = false) {
    static $settings = null;
    if ($settings === null || $forceRefresh) {
        if (!$db) return [];
        try {
            $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
            $settings = $stmt->fetch();
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings ?: [];
}

/**
 * 更新全站设置（单字段）
 * 如果列不存在则自动添加
 */
function setSetting($db, $key, $value) {
    try {
        // 检查列是否存在
        $cols = $db->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array($key, $cols)) {
            $db->exec("ALTER TABLE settings ADD COLUMN `$key` TEXT DEFAULT ''");
        }
        $stmt = $db->prepare("UPDATE settings SET `$key` = ? WHERE id = 1");
        $stmt->execute([$value]);
        // 刷新静态缓存
        getSettings($db, true);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/** 记录管理员操作日志 */
function adminLog($db, $action, $target_type = '', $target_id = 0, $detail = '') {
    if (!isset($_SESSION['admin_id'])) return;
    $detail = is_array($detail) ? json_encode($detail, JSON_UNESCAPED_UNICODE) : $detail;
    $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, detail, ip, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['admin_id'], $action, $target_type, $target_id, $detail, getClientIP(), date('Y-m-d H:i:s')]);
}

/** 响应JSON */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** 重定向 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/** 分页工具（兼容 MySQL / SQLite） */
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

/**
 * 检查并获取管理员登录态
 */
function requireAdmin() {
    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_login'])) {
        $loginUrl = (defined('BASE_URL') ? BASE_URL : '') . '/admin/index.php';
        header("Location: $loginUrl");
        exit;
    }
}

/**
 * 检查并获取用户登录态
 */
function requireUser() {
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_login'])) {
        $loginUrl = '../user/index.php';
        header("Location: $loginUrl");
        exit;
    }
}

// ============================================================
// 模板系统函数
// ============================================================

/**
 * 扫描模板目录，返回模板列表（含元信息）
 * @param string $type 'home' | 'user'
 * @return array
 */
function getTemplates($type = 'home') {
    static $cache = [];
    if (isset($cache[$type])) return $cache[$type];
    $dir = __DIR__ . '/templates/' . $type;
    $previewDir = __DIR__ . '/templates/' . $type . '_preview';
    $templates = [];
    if (!is_dir($dir)) return $templates;
    $files = glob($dir . '/*.php');
    sort($files);
    foreach ($files as $file) {
        $id = basename($file, '.php');
        $content = file_get_contents($file);
        $name = $id;
        $desc = '';
        $variables = [];
        $author = '';
        // 从注释头解析元信息
        if (preg_match('/\* Template:\s*(.+?)(?:
|\*\/)/s', $content, $m)) $name = trim($m[1]);
        if (preg_match('/\*\s*Desc:\s*(.+?)(?:
|\*\/)/s', $content, $m)) $desc = trim($m[1]);
        if (preg_match('/\*\s*Author:\s*(.+?)(?:
|\*\/)/s', $content, $m)) $author = trim($m[1]);
        // 提取变量注释 @var $xxx - 描述
        if (preg_match_all('/\@var\s+\$([a-zA-Z_]\w*)\s*-\s*(.+?)(?:
|\*\/)/', $content, $matches)) {
            foreach ($matches[1] as $i => $var) {
                $variables[$var] = trim($matches[2][$i]);
            }
        }
        // 兼容旧格式：没有@var前缀
        if (empty($variables)) {
            if (preg_match_all('/\$([a-zA-Z_]\w*)\s*-\s*(.+?)(?:
|\*\/)/', $content, $matches)) {
                foreach ($matches[1] as $i => $var) {
                    $variables[$var] = trim($matches[2][$i]);
                }
            }
        }
        $preview = '';
        foreach (['png','jpg','jpeg','webp'] as $ext) {
            $pf = $previewDir . '/' . $id . '.' . $ext;
            if (file_exists($pf)) { $preview = 'templates/' . $type . '_preview/' . $id . '.' . $ext; break; }
        }
        $templates[] = [
            'id'       => $id,
            'name'     => $name,
            'desc'     => $desc,
            'author'   => $author,
            'variables'=> $variables,
            'preview'  => $preview,
            'file'     => $file,
        ];
    }
    $cache[$type] = $templates;
    return $templates;
}

/**
 * 获取单个模板的元信息
 */
function getTemplateMeta($type, $id) {
    foreach (getTemplates($type) as $t) {
        if ($t['id'] === $id) return $t;
    }
    return null;
}

/**
 * 获取当前启用的首页模板ID
 */
function getHomeTemplateId($db) {
    $settings = getSettings($db);
    $tid = $settings['home_template'] ?? 'default';
    $meta = getTemplateMeta('home', $tid);
    return $meta ? $tid : 'default';
}

/**
 * 获取用户选用的个人页模板ID
 */
function getUserTemplateId($user) {
    $tid = $user['page_template'] ?? 'glass';
    return $tid;
}
