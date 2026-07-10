<?php
/**
 * 初始化模块 — 启动顺序：Session → DB → 安全Header → 自动修复
 */

// ---- 1. Session 安全初始化 ----
secureSessionInit();

// ---- 2. 安全响应Header ----
sendSecurityHeaders();

// ---- 3. 数据库连接 ----
$db = connectDatabase();
$dbError = '';
if (!$db) {
    $dbError = '数据库连接失败';
}

// ---- 4. 自动修复表结构（仅每 5 分钟检查一次，避免每次请求都执行） ----
if ($db) {
    $fixCheckFile = __DIR__ . '/../cache/.tables_checked';
    $needsCheck = true;
    if (file_exists($fixCheckFile)) {
        $checkTime = (int)file_get_contents($fixCheckFile);
        $needsCheck = (time() - $checkTime) > 300; // 5分钟间隔
    }
    if ($needsCheck) {
        autoFixTables($db);
        $cacheDir = dirname($fixCheckFile);
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
        @file_put_contents($fixCheckFile, time());
    }
}

// ---- 5. 请求根路径 ----
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
$basePath = str_replace('\\', '/', $scriptDir);

// ---- 6. 是否在admin路径 ----
$isAdminPath = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false;

// ---- 7. Admin页面数据库错误处理 ----
if (!$db && $isAdminPath) {
    renderDbError($dbError);
}

// ===== 自动修复表结构（从原config.php迁移） =====

function autoFixTables($db) {
    try {
        $tablesExist = true;
        try {
            $db->query("SELECT 1 FROM links LIMIT 1");
        } catch (Exception $e) {
            $tablesExist = false;
        }
        
        if (!$tablesExist) {
            createMissingTables($db);
        } else {
            addMissingColumns($db);
        }
        
        // 补列：邮箱相关
        addEmailColumns($db);
        
        // 补列：互动相关
        addInteractionColumns($db);
        
        // settings 补列
        try { $db->exec("ALTER TABLE `settings` ADD COLUMN `style_data` TEXT"); } catch (Exception $e) {}
        
        // 密码升级
        upgradeAdminPassword($db);
        
    } catch (Exception $e) {
        error_log("[Leaffox AutoFix] " . $e->getMessage());
    }
}

function createMissingTables($db) {
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
    
    $db->exec("CREATE TABLE IF NOT EXISTS `links` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id`     INT UNSIGNED NOT NULL,
        `title`       VARCHAR(100) NOT NULL,
        `url`         VARCHAR(500) NOT NULL,
        `icon`        VARCHAR(10)  DEFAULT '<i class=\'fas fa-link\'></i>',
        `sort_order`  INT UNSIGNED DEFAULT 0,
        `click_count` INT UNSIGNED DEFAULT 0,
        `is_active`   TINYINT(1)   DEFAULT 1,
        `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        INDEX `idx_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
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
    
    // 互动表
    $db->exec("CREATE TABLE IF NOT EXISTS `page_likes` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `page_user_id` INT UNSIGNED NOT NULL COMMENT '被点赞的主页主人用户ID',
        `visitor_id`   INT UNSIGNED NOT NULL COMMENT '点赞者用户ID',
        `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_like` (`page_user_id`, `visitor_id`),
        FOREIGN KEY (`page_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`visitor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
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
    
    $db->exec("CREATE TABLE IF NOT EXISTS `page_favorites` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `page_user_id` INT UNSIGNED NOT NULL COMMENT '被收藏的主页主人用户ID',
        `visitor_id`   INT UNSIGNED NOT NULL COMMENT '收藏者用户ID',
        `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_fav` (`page_user_id`, `visitor_id`),
        FOREIGN KEY (`page_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`visitor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function addMissingColumns($db) {
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
            "ALTER TABLE `users` ADD COLUMN `page_template` VARCHAR(30) DEFAULT 'glass'",
            "ALTER TABLE `users` ADD COLUMN `last_ip` VARCHAR(45) DEFAULT ''",
            "ALTER TABLE `users` ADD COLUMN `last_login` DATETIME DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN `is_hidden` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `users` ADD COLUMN `is_violation` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `users` ADD COLUMN `violation_reason` VARCHAR(200) DEFAULT ''",
            "ALTER TABLE `users` ADD COLUMN `gmt_offset` VARCHAR(10) DEFAULT '+08:00'",
        ],
        'links' => [
            "ALTER TABLE `links` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1",
            "ALTER TABLE `links` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE `links` ADD COLUMN `is_hidden` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `links` ADD COLUMN `is_violation` TINYINT(1) DEFAULT 0",
        ],
    ];
    
    foreach ($fixColumns as $table => $sqls) {
        foreach ($sqls as $sql) {
            try { $db->exec($sql); } catch (Exception $e) {}
        }
    }
}

function addEmailColumns($db) {
    $emailColumns = [
        'users' => [
            "ALTER TABLE `users` ADD COLUMN `email` VARCHAR(128) DEFAULT ''",
            "ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `users` ADD COLUMN `is_guest` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `users` ADD COLUMN `suffix` VARCHAR(32) DEFAULT ''",
            "ALTER TABLE `users` ADD COLUMN `email_verify_token` VARCHAR(64) DEFAULT ''",
            "ALTER TABLE `users` ADD COLUMN `verify_token_expires` DATETIME DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN `password_updated_at` DATETIME DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN `login_attempts` INT UNSIGNED DEFAULT 0",
            "ALTER TABLE `users` ADD COLUMN `locked_until` DATETIME DEFAULT NULL",
        ],
        'settings' => [
            "ALTER TABLE `settings` ADD COLUMN `reg_enabled` TINYINT(1) DEFAULT 1",
            "ALTER TABLE `settings` ADD COLUMN `reg_invite` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `settings` ADD COLUMN `guest_mode` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `settings` ADD COLUMN `reg_email_verify` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `settings` ADD COLUMN `user_email_login` TINYINT(1) DEFAULT 1",
            "ALTER TABLE `settings` ADD COLUMN `admin_email_login` TINYINT(1) DEFAULT 0",
            "ALTER TABLE `settings` ADD COLUMN `smtp_host` VARCHAR(100) DEFAULT ''",
            "ALTER TABLE `settings` ADD COLUMN `smtp_port` INT DEFAULT 465",
            "ALTER TABLE `settings` ADD COLUMN `smtp_user` VARCHAR(100) DEFAULT ''",
            "ALTER TABLE `settings` ADD COLUMN `smtp_pass` TEXT DEFAULT NULL",
            "ALTER TABLE `settings` ADD COLUMN `smtp_encrypt` VARCHAR(10) DEFAULT 'ssl'",
            "ALTER TABLE `settings` ADD COLUMN `smtp_from_name` VARCHAR(60) DEFAULT ''",
            "ALTER TABLE `settings` ADD COLUMN `admin_email` VARCHAR(128) DEFAULT ''",
            "ALTER TABLE `settings` ADD COLUMN `email_tpl_register` TEXT DEFAULT NULL",
            "ALTER TABLE `settings` ADD COLUMN `email_tpl_resetpwd` TEXT DEFAULT NULL",
            "ALTER TABLE `settings` ADD COLUMN `email_tpl_verify` TEXT DEFAULT NULL",
            "ALTER TABLE `settings` ADD COLUMN `site_template` VARCHAR(30) DEFAULT 'default'",
            "ALTER TABLE `settings` ADD COLUMN `login_template` VARCHAR(30) DEFAULT 'default'",
            "ALTER TABLE `settings` ADD COLUMN `register_template` VARCHAR(30) DEFAULT 'default'",
            "ALTER TABLE `settings` ADD COLUMN `home_template` VARCHAR(30) DEFAULT 'default'",
            "ALTER TABLE `settings` ADD COLUMN `announcement` TEXT DEFAULT NULL",
            "ALTER TABLE `settings` ADD COLUMN `powered_by_enabled` TINYINT(1) DEFAULT 1",
            "ALTER TABLE `settings` ADD COLUMN `powered_by_name` VARCHAR(60) DEFAULT ''",
            "ALTER TABLE `settings` ADD COLUMN `banned_suffixes` TEXT DEFAULT NULL",
            "ALTER TABLE `settings` ADD COLUMN `footer_text` VARCHAR(200) DEFAULT ''",
            "ALTER TABLE `settings` ADD COLUMN `show_free_make_btn` TINYINT(1) DEFAULT 1",
        ],
    ];
    
    foreach ($emailColumns as $table => $sqls) {
        foreach ($sqls as $sql) {
            try { $db->exec($sql); } catch (Exception $e) {}
        }
    }
}

function addInteractionColumns($db) {
    $interactionColumns = [
        "ALTER TABLE `users` ADD COLUMN `enable_likes` TINYINT(1) DEFAULT 1",
        "ALTER TABLE `users` ADD COLUMN `enable_comments` TINYINT(1) DEFAULT 1",
        "ALTER TABLE `users` ADD COLUMN `enable_favorites` TINYINT(1) DEFAULT 1",
        "ALTER TABLE `users` ADD COLUMN `comment_audit_enabled` TINYINT(1) DEFAULT 0",
    ];
    foreach ($interactionColumns as $sql) {
        try { $db->exec($sql); } catch (Exception $e) {}
    }
    try { $db->exec("ALTER TABLE `page_comments` ADD COLUMN `status` TINYINT(1) DEFAULT 1"); } catch (Exception $e) {}
    // 游客过期自动删除字段
    try { $db->exec("ALTER TABLE `users` ADD COLUMN `guest_expires_at` DATETIME DEFAULT NULL"); } catch (Exception $e) {}
}

function upgradeAdminPassword($db) {
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
}
