-- ============================================================
-- Leaffox主页系统 · 数据库建表 SQL
-- 完整版：涵盖所有模块类型、音乐播放器、密码链接、公告、平台提示等功能
-- 适用：MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS leaffox_system DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE leaffox_system;

-- -----------------------------------------------------------
-- 1. 管理员表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '管理员ID',
  `username`    VARCHAR(32)  NOT NULL UNIQUE             COMMENT '登录账号',
  `password`    VARCHAR(128) NOT NULL                    COMMENT '登录密码(password_hash)',
  `nickname`    VARCHAR(32)  DEFAULT '管理员'             COMMENT '显示昵称',
  `avatar`      VARCHAR(255) DEFAULT ''                  COMMENT '头像URL',
  `email`       VARCHAR(128) DEFAULT ''                  COMMENT '管理员邮箱',
  `role`        ENUM('super','admin') DEFAULT 'admin'    COMMENT '角色：super超管/admin普通管理',
  `status`      TINYINT(1)   DEFAULT 1                   COMMENT '状态 1正常 0禁用',
  `last_ip`     VARCHAR(45)  DEFAULT ''                  COMMENT '最后登录IP',
  `last_login`  DATETIME     DEFAULT NULL                COMMENT '最后登录时间',
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员账号表';

INSERT INTO `admin` (`username`,`password`,`nickname`,`role`) VALUES
('admin','$2y$10$owes/Y/brxFK3RxLmsceY.CoaPM53Jao5Ovz7fGRZdVbFMQ2mL/Ju','超级管理员','super');

-- -----------------------------------------------------------
-- 2. 普通用户表（含完整主页配置 + 旧版系统全部字段）
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '用户ID',
  `username`      VARCHAR(32)  NOT NULL UNIQUE             COMMENT '登录账号',
  `password`      VARCHAR(128) NOT NULL                    COMMENT '登录密码(password_hash)',
  `email`         VARCHAR(128) DEFAULT ''                  COMMENT '电子邮箱',
  `email_verified` TINYINT(1)  DEFAULT 0                   COMMENT '邮箱已验证',
  `email_verify_token` VARCHAR(64) DEFAULT ''              COMMENT '邮箱验证令牌',
  `verify_token_expires` DATETIME DEFAULT NULL             COMMENT '令牌过期时间',
  `nickname`      VARCHAR(32)  DEFAULT ''                  COMMENT '显示昵称',
  `bio`           VARCHAR(200) DEFAULT '这个人很懒，什么都没写' COMMENT '个人简介',
  `avatar`        VARCHAR(255) DEFAULT ''                  COMMENT '头像URL',
  `suffix`        VARCHAR(32)  DEFAULT '' UNIQUE           COMMENT '个性后缀(短链路由)',

  -- 主页外观
  `bg_preset`     VARCHAR(20)  DEFAULT 'default'           COMMENT '背景预设: default/black/purple/pink/deep/cyber/gold/custom',
  `bg_color`      VARCHAR(20)  DEFAULT '#0f172a'           COMMENT '主页纯色背景',
  `bg_image`      VARCHAR(255) DEFAULT ''                  COMMENT '主页背景图URL',
  `custom_bg_type` VARCHAR(10) DEFAULT 'color'             COMMENT '自定义背景类型: color/gradient/image',
  `custom_gradient_from` VARCHAR(10) DEFAULT '#667eea'     COMMENT '渐变起始色',
  `custom_gradient_to`   VARCHAR(10) DEFAULT '#764ba2'     COMMENT '渐变结束色',
  `custom_gradient_dir`  VARCHAR(10) DEFAULT '135deg'      COMMENT '渐变方向',

  `theme_mode`    ENUM('auto','light','dark') DEFAULT 'auto' COMMENT '主题模式',
  `card_style`    VARCHAR(20)  DEFAULT 'glass'             COMMENT '卡片风格 glass/neumorphism/minimal',

  -- 社交渠道（JSON）
  `social_data`   TEXT         DEFAULT NULL                COMMENT '社交渠道JSON',

  -- 按钮样式
  `btn_bg`        VARCHAR(20)  DEFAULT ''                  COMMENT '按钮背景色(空=默认)',
  `btn_color`     VARCHAR(20)  DEFAULT ''                  COMMENT '按钮文字色',
  `btn_outline`   VARCHAR(20)  DEFAULT ''                  COMMENT '空心边框色',
  `btn_arrow`     TINYINT(1)   DEFAULT 1                   COMMENT '显示右侧箭头 1显示',

  -- 公告
  `announcement`       TEXT     DEFAULT NULL               COMMENT '个人公告(HTML)',
  `announcement_enabled` TINYINT(1) DEFAULT 0              COMMENT '是否显示公告',

  -- 音乐播放器
  `custom_music`         VARCHAR(500) DEFAULT ''           COMMENT '背景音乐URL',
  `custom_music_loop`    TINYINT(1)   DEFAULT 0            COMMENT '单曲循环',
  `custom_music_autoplay` TINYINT(1)  DEFAULT 0            COMMENT '自动播放',
  `custom_music_icon`    VARCHAR(10)  DEFAULT 'b'          COMMENT '音乐图标样式 b/h',

  -- 内置浏览器打开提示
  `open_tip_wechat` TINYINT(1) DEFAULT 0                   COMMENT '微信内提示跳转浏览器',
  `open_tip_qq`     TINYINT(1) DEFAULT 0                   COMMENT 'QQ内提示',
  `open_tip_douyin` TINYINT(1) DEFAULT 0                   COMMENT '抖音内提示',
  `open_tip_weibo`  TINYINT(1) DEFAULT 0                   COMMENT '微博内提示',

  -- 页脚设置
  `show_stats`    TINYINT(1)   DEFAULT 1                   COMMENT '前台显示统计 1显示',
  `footer_text`   VARCHAR(200) DEFAULT 'Powered by Leaffox主页系统'  COMMENT '自定义底部文字',
  `footer_align`  VARCHAR(10)  DEFAULT 'center'            COMMENT '页脚对齐: left/center/right',

  -- 账号状态
  `is_active`     TINYINT(1)   DEFAULT 1                   COMMENT '状态 1正常 0封禁',
  `last_ip`       VARCHAR(45)  DEFAULT ''                  COMMENT '最后登录IP',
  `last_login`    DATETIME     DEFAULT NULL                COMMENT '最后登录时间',
  `created_at`    DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT '注册时间',
  `updated_at`    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='普通用户表';

-- -----------------------------------------------------------
-- 3. 链接/模块表（支持全部5种类型 + 密码保护 + 视频弹窗）
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `links` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '链接ID',
  `user_id`     INT UNSIGNED NOT NULL                    COMMENT '所属用户ID',
  `type`        VARCHAR(20)  DEFAULT 'link'              COMMENT '模块类型: link/text/image/picture/video',
  `title`       VARCHAR(100) NOT NULL                    COMMENT '卡片标题/文本内容',
  `url`         VARCHAR(500) NOT NULL DEFAULT ''          COMMENT '跳转地址(链接类型)',
  `icon`        VARCHAR(100) DEFAULT '🔗'                 COMMENT '卡片Emoji图标/图片URL',
  `card_color`  VARCHAR(30)  DEFAULT '' COMMENT '卡片背景色',
  `text_color`  VARCHAR(20)  DEFAULT '#ffffff'           COMMENT '文字颜色',
  `sort_order`  INT UNSIGNED DEFAULT 50                  COMMENT '排序(越小越靠前)',

  -- 链接模块专用
  `outline`     TINYINT(1)   DEFAULT 0                   COMMENT '空心按钮样式',
  `passcode`    VARCHAR(10)  DEFAULT ''                  COMMENT '访问密码(空=无需密码)',

  -- 图片弹窗模块(image)专用
  `popup_img`   VARCHAR(500) DEFAULT ''                  COMMENT '弹窗图片URL',

  -- 文字模块(text)专用
  `text_center` TINYINT(1)   DEFAULT 0                   COMMENT '文字居中',

  -- 按钮圆角
  `btn_radius_on` TINYINT(1) DEFAULT 1                   COMMENT '自定义圆角开关',
  `btn_radius`  INT UNSIGNED DEFAULT 30                  COMMENT '按钮圆角值',

  -- 视频模块专用
  `video_file`  VARCHAR(500) DEFAULT ''                  COMMENT '视频文件URL',
  `video_loop`  TINYINT(1)   DEFAULT 0                   COMMENT '视频循环播放',
  `video_poster` VARCHAR(500) DEFAULT ''                 COMMENT '视频封面图',

  `is_hidden`   TINYINT(1)   DEFAULT 0                   COMMENT '是否隐藏 0显示 1隐藏',
  `is_violation` TINYINT(1)  DEFAULT 0                   COMMENT '违规标记 0正常 1违规下架',
  `click_count` INT UNSIGNED DEFAULT 0                   COMMENT '点击次数(冗余加速)',
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT '创建时间',
  `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='链接/模块表';

-- -----------------------------------------------------------
-- 4. 统计数据表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stats` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '记录ID',
  `user_id`     INT UNSIGNED NOT NULL                    COMMENT '所属用户ID',
  `link_id`     INT UNSIGNED DEFAULT 0                   COMMENT '链接ID(0表示主页访问)',
  `type`        ENUM('view','click') NOT NULL            COMMENT '类型 view访问 click点击',
  `ip`          VARCHAR(45)  DEFAULT ''                  COMMENT '访客IP',
  `user_agent`  VARCHAR(500) DEFAULT ''                  COMMENT '访客UA',
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT '记录时间',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_type` (`user_id`, `type`),
  INDEX `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访问点击统计表';

-- -----------------------------------------------------------
-- 5. 管理员操作日志表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '日志ID',
  `admin_id`    INT UNSIGNED NOT NULL                    COMMENT '操作管理员ID',
  `action`      VARCHAR(100) NOT NULL                    COMMENT '操作动作',
  `target_type` VARCHAR(32)  DEFAULT ''                  COMMENT '操作对象类型 user/link/setting',
  `target_id`   INT UNSIGNED DEFAULT 0                   COMMENT '操作对象ID',
  `detail`      TEXT         DEFAULT NULL                COMMENT '操作详情JSON',
  `ip`          VARCHAR(45)  DEFAULT ''                  COMMENT '操作IP',
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT '操作时间',
  FOREIGN KEY (`admin_id`) REFERENCES `admin`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员操作日志表';

-- -----------------------------------------------------------
-- 6. 全站设置表（含系统署名配置）
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '设置ID',
  `site_name`   VARCHAR(100) DEFAULT 'Leaffox主页系统'    COMMENT '网站名称',
  `site_logo`   VARCHAR(255) DEFAULT ''                  COMMENT '网站LOGO',
  `site_desc`   VARCHAR(200) DEFAULT '每个人都有自己的专属主页' COMMENT '网站描述',
  `icp_record`  VARCHAR(100) DEFAULT ''                  COMMENT '备案号',
  `reg_enabled` TINYINT(1)   DEFAULT 1                   COMMENT '是否开放注册 1开启',
  `reg_invite`  TINYINT(1)   DEFAULT 0                   COMMENT '是否需要邀请码',
  `announcement` TEXT         DEFAULT NULL                COMMENT '全站公告(HTML)',
  `powered_by_enabled` TINYINT(1) DEFAULT 1              COMMENT '显示PoweredBy 1显示',
  `powered_by_name`    VARCHAR(50) DEFAULT 'Leaffox主页系统'       COMMENT '系统署名名称',
  `banned_suffixes`    TEXT         DEFAULT NULL           COMMENT '禁止使用的后缀(一行一个)',
  `reg_email_verify`   TINYINT(1)   DEFAULT 0              COMMENT '注册需邮箱验证',
  `smtp_host`          VARCHAR(100) DEFAULT ''              COMMENT 'SMTP服务器',
  `smtp_port`          INT UNSIGNED DEFAULT 465             COMMENT 'SMTP端口',
  `smtp_user`          VARCHAR(100) DEFAULT ''              COMMENT 'SMTP账号',
  `smtp_pass`          VARCHAR(128) DEFAULT ''              COMMENT 'SMTP密码/授权码',
  `smtp_encrypt`       VARCHAR(10)  DEFAULT 'ssl'           COMMENT 'SMTP加密方式 ssl/tls',
  `smtp_from_name`     VARCHAR(50)  DEFAULT ''              COMMENT '发件人名称',
  `user_email_login`   TINYINT(1)   DEFAULT 1              COMMENT '用户可用邮箱登录',
  `admin_email_login`  TINYINT(1)   DEFAULT 0              COMMENT '管理员可用邮箱登录',
  `admin_email`        VARCHAR(128) DEFAULT ''              COMMENT '管理员邮箱',
  `show_free_make_btn` TINYINT(1)   DEFAULT 1              COMMENT '用户主页底部显示“免费制作”悬浮按钮',
  `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='全站设置表';

INSERT INTO `settings` (`id`, `site_name`, `site_desc`, `powered_by_name`)
VALUES (1, 'Leaffox主页系统', '每个人都有自己的专属主页', 'Leaffox主页系统');

-- -----------------------------------------------------------
-- 9. 举报表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reports` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '举报ID',
  `user_id`     INT UNSIGNED NOT NULL                    COMMENT '被举报的用户ID',
  `reporter_ip` VARCHAR(45)  DEFAULT ''                  COMMENT '举报者IP',
  `type`        VARCHAR(32)  NOT NULL                    COMMENT '举报类型',
  `reason`      TEXT         DEFAULT NULL                COMMENT '详细说明',
  `status`      TINYINT(1)   DEFAULT 0                   COMMENT '0=未处理 1=已处理',
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT '举报时间',
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户举报记录表';

-- -----------------------------------------------------------
-- 10. 密码重置表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT 'ID',
  `email`       VARCHAR(128) NOT NULL                    COMMENT '申请重置的邮箱',
  `token`       VARCHAR(64)  NOT NULL UNIQUE             COMMENT '重置令牌',
  `used`        TINYINT(1)   DEFAULT 0                   COMMENT '是否已使用 1已使用',
  `expires_at`  DATETIME     NOT NULL                    COMMENT '过期时间',
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT '创建时间',
  INDEX `idx_token` (`token`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='密码重置表';
