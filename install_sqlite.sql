-- ============================================================
-- Leaffox主页系统 · SQLite 建表 SQL
-- ============================================================

PRAGMA foreign_keys = ON;

-- -----------------------------------------------------------
-- 1. 管理员表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS "admin" (
  "id"          INTEGER PRIMARY KEY AUTOINCREMENT,
  "username"    TEXT NOT NULL UNIQUE,
  "password"    TEXT NOT NULL,
  "nickname"    TEXT DEFAULT '管理员',
  "avatar"      TEXT DEFAULT '',
  "role"        TEXT DEFAULT 'admin' CHECK(role IN ('super','admin')),
  "status"      INTEGER DEFAULT 1,
  "last_ip"     TEXT DEFAULT '',
  "last_login"  TEXT,
  "created_at"  TEXT DEFAULT (datetime('now','localtime'))
);

INSERT INTO "admin" ("username","password","nickname","role") VALUES
('admin','$2y$10$owes/Y/brxFK3RxLmsceY.CoaPM53Jao5Ovz7fGRZdVbFMQ2mL/Ju','超级管理员','super');

-- -----------------------------------------------------------
-- 2. 普通用户表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS "users" (
  "id"            INTEGER PRIMARY KEY AUTOINCREMENT,
  "username"      TEXT NOT NULL UNIQUE,
  "password"      TEXT NOT NULL,
  "email"         TEXT DEFAULT '',
  "email_verified" INTEGER DEFAULT 0,
  "email_verify_token" TEXT DEFAULT '',
  "verify_token_expires" TEXT,
  "nickname"      TEXT DEFAULT '',
  "bio"           TEXT DEFAULT '这个人很懒，什么都没写',
  "title_color"   TEXT DEFAULT '',
  "desc_color"    TEXT DEFAULT '',
  "avatar"        TEXT DEFAULT '',
  "suffix"        TEXT DEFAULT '' UNIQUE,
  "bg_preset"     TEXT DEFAULT 'default',
  "bg_color"      TEXT DEFAULT '#0f172a',
  "bg_image"      TEXT DEFAULT '',
  "custom_bg_type" TEXT DEFAULT 'color',
  "custom_gradient_from" TEXT DEFAULT '#667eea',
  "custom_gradient_to"   TEXT DEFAULT '#764ba2',
  "custom_gradient_dir"  TEXT DEFAULT '135deg',
  "theme_mode"    TEXT DEFAULT 'auto' CHECK(theme_mode IN ('auto','light','dark')),
  "card_style"    TEXT DEFAULT 'glass',
  "social_data"   TEXT,
  "btn_bg"        TEXT DEFAULT '',
  "btn_color"     TEXT DEFAULT '',
  "btn_outline"   TEXT DEFAULT '',
  "btn_arrow"     INTEGER DEFAULT 1,
  "arrow_color"   TEXT DEFAULT '',
  "announcement"       TEXT,
  "announcement_enabled" INTEGER DEFAULT 0,
  "custom_music"         TEXT DEFAULT '',
  "custom_music_loop"    INTEGER DEFAULT 0,
  "custom_music_autoplay" INTEGER DEFAULT 0,
  "custom_music_icon"    TEXT DEFAULT 'b',
  "open_tip_wechat" INTEGER DEFAULT 0,
  "open_tip_qq"     INTEGER DEFAULT 0,
  "open_tip_douyin" INTEGER DEFAULT 0,
  "open_tip_weibo"  INTEGER DEFAULT 0,
  "tipping_enabled" INTEGER DEFAULT 0,
  "tipping_qrcode"  TEXT DEFAULT '',
  "tipping_title"   TEXT DEFAULT '',
  "video_auto_expand" INTEGER DEFAULT 0,
  "show_stats"    INTEGER DEFAULT 1,
  "enable_likes"       INTEGER DEFAULT 1,
  "enable_comments"    INTEGER DEFAULT 1,
  "enable_favorites"   INTEGER DEFAULT 1,
  "comment_audit_enabled" INTEGER DEFAULT 0,
  "footer_text"   TEXT DEFAULT 'Powered by Leaffox主页系统',
  "footer_align"  TEXT DEFAULT 'center',
  "page_template" TEXT DEFAULT 'default',
  "is_active"     INTEGER DEFAULT 1,
  "is_guest"      INTEGER DEFAULT 0,
  "last_ip"       TEXT DEFAULT '',
  "last_login"    TEXT,
  "created_at"    TEXT DEFAULT (datetime('now','localtime')),
  "updated_at"    TEXT DEFAULT (datetime('now','localtime'))
);

-- SQLite 触发器: 自动更新 updated_at
CREATE TRIGGER IF NOT EXISTS trg_users_updated_at
  AFTER UPDATE ON "users"
  FOR EACH ROW
BEGIN
  UPDATE "users" SET "updated_at" = datetime('now','localtime') WHERE "id" = OLD."id";
END;

-- -----------------------------------------------------------
-- 3. 链接/模块表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS "links" (
  "id"          INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id"     INTEGER NOT NULL,
  "type"        TEXT DEFAULT 'link',
  "title"       TEXT NOT NULL,
  "url"         TEXT DEFAULT '',
  "icon"        TEXT DEFAULT '🔗',
  "favicon_type" TEXT DEFAULT 'emoji',
  "card_color"  TEXT DEFAULT '',
  "text_color"  TEXT DEFAULT '#ffffff',
  "sort_order"  INTEGER DEFAULT 50,
  "outline"     INTEGER DEFAULT 0,
  "passcode"    TEXT DEFAULT '',
  "popup_img"   TEXT DEFAULT '',
  "text_center" INTEGER DEFAULT 0,
  "btn_radius_on" INTEGER DEFAULT 1,
  "btn_radius"  INTEGER DEFAULT 30,
  "video_file"  TEXT DEFAULT '',
  "video_source" TEXT DEFAULT 'file',
  "video_loop"  INTEGER DEFAULT 0,
  "video_poster" TEXT DEFAULT '',
  "is_hidden"   INTEGER DEFAULT 0,
  "is_violation" INTEGER DEFAULT 0,
  "click_count" INTEGER DEFAULT 0,
  "created_at"  TEXT DEFAULT (datetime('now','localtime')),
  "updated_at"  TEXT DEFAULT (datetime('now','localtime')),
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_links_user" ON "links"("user_id");

CREATE TRIGGER IF NOT EXISTS trg_links_updated_at
  AFTER UPDATE ON "links"
  FOR EACH ROW
BEGIN
  UPDATE "links" SET "updated_at" = datetime('now','localtime') WHERE "id" = OLD."id";
END;

-- -----------------------------------------------------------
-- 4. 统计数据表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS "stats" (
  "id"          INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id"     INTEGER NOT NULL,
  "link_id"     INTEGER DEFAULT 0,
  "type"        TEXT NOT NULL CHECK(type IN ('view','click')),
  "ip"          TEXT DEFAULT '',
  "user_agent"  TEXT DEFAULT '',
  "created_at"  TEXT DEFAULT (datetime('now','localtime')),
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_stats_user_type" ON "stats"("user_id", "type");
CREATE INDEX IF NOT EXISTS "idx_stats_date" ON "stats"("created_at");

-- -----------------------------------------------------------
-- 5. 管理员操作日志表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS "admin_logs" (
  "id"          INTEGER PRIMARY KEY AUTOINCREMENT,
  "admin_id"    INTEGER NOT NULL,
  "action"      TEXT NOT NULL,
  "target_type" TEXT DEFAULT '',
  "target_id"   INTEGER DEFAULT 0,
  "detail"      TEXT,
  "ip"          TEXT DEFAULT '',
  "created_at"  TEXT DEFAULT (datetime('now','localtime')),
  FOREIGN KEY ("admin_id") REFERENCES "admin"("id") ON DELETE CASCADE
);

-- -----------------------------------------------------------
-- 6. 全站设置表
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS "settings" (
  "id"          INTEGER PRIMARY KEY AUTOINCREMENT,
  "site_name"   TEXT DEFAULT 'Leaffox主页系统',
  "site_logo"   TEXT DEFAULT '',
  "site_desc"   TEXT DEFAULT '每个人都有自己的专属主页',
  "icp_record"  TEXT DEFAULT '',
  "reg_enabled" INTEGER DEFAULT 1,
  "reg_invite"  INTEGER DEFAULT 0,
  "announcement" TEXT,
  "powered_by_enabled" INTEGER DEFAULT 1,
  "powered_by_name"    TEXT DEFAULT 'Leaffox主页系统',
  "banned_suffixes"    TEXT,
  "show_free_make_btn" INTEGER DEFAULT 1,
  "style_data"         TEXT,
  "site_template" TEXT DEFAULT 'default',
  "guest_mode"         INTEGER DEFAULT 0,
  "login_template"     TEXT DEFAULT 'default',
  "register_template"  TEXT DEFAULT 'default',
  "email_tpl_register" TEXT,
  "email_tpl_resetpwd"  TEXT,
  "email_tpl_verify"    TEXT,
  "updated_at"  TEXT DEFAULT (datetime('now','localtime'))
);

INSERT INTO "settings" ("id", "site_name", "site_desc", "powered_by_name", "site_template")
VALUES (1, 'Leaffox主页系统', '每个人都有自己的专属主页', 'Leaffox主页系统', 'default');

CREATE TABLE IF NOT EXISTS "reports" (
  "id"          INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id"     INTEGER NOT NULL,
  "reporter_ip" TEXT DEFAULT '',
  "type"        TEXT NOT NULL,
  "reason"      TEXT,
  "status"      INTEGER DEFAULT 0,
  "created_at"  TEXT DEFAULT (datetime('now','localtime'))
);
CREATE INDEX IF NOT EXISTS "idx_reports_user_id" ON "reports"("user_id");
CREATE INDEX IF NOT EXISTS "idx_reports_status" ON "reports"("status");

PRAGMA user_version = 1;

-- 点赞表
CREATE TABLE IF NOT EXISTS page_likes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_id TEXT NOT NULL,
    user_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(page_id, user_id)
);

-- 评论表
CREATE TABLE IF NOT EXISTS page_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_user_id INTEGER NOT NULL,
    visitor_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    status INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_comments_page ON page_comments(page_user_id, created_at);

-- 收藏表
CREATE TABLE IF NOT EXISTS page_favorites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_user_id INTEGER NOT NULL,
    visitor_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(page_user_id, visitor_id)
);

-- 用户表加开关字段
ALTER TABLE users ADD COLUMN enable_likes INTEGER NOT NULL DEFAULT 1;
ALTER TABLE users ADD COLUMN enable_comments INTEGER NOT NULL DEFAULT 1;
ALTER TABLE users ADD COLUMN enable_favorites INTEGER NOT NULL DEFAULT 1;
