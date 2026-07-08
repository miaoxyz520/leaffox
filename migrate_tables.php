<?php
/**
 * 一键迁移工具：检测并修复 page_comments / page_likes / page_favorites 表结构
 * 访问 https://你的域名/migrate_tables.php  运行一次后请删除此文件
 */
ob_start();
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>表结构迁移</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:0 auto}
h1{color:#818cf8;font-size:24px}
pre{background:#1e293b;padding:20px;border-radius:12px;line-height:1.8;overflow-x:auto}
.success{color:#22c55e}.error{color:#ef4444}.info{color:#f59e0b}
.btn{display:inline-block;padding:10px 24px;border-radius:10px;background:rgba(99,102,241,0.2);color:#818cf8;text-decoration:none;font-weight:600;margin-top:16px}
</style></head><body>
<h1>🛠 表结构迁移工具</h1>
<pre>
<?php

require_once __DIR__ . '/config.php';

if (!$db) {
    echo '<span class="error">❌ 数据库未连接，请检查 config.php</span>';
    exit;
}

// 检测驱动类型
$driver = 'mysql';
try {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
} catch (Exception $e) {}

echo "数据库驱动: " . $driver . "\n\n";

// ---- 检测并重建 page_comments ----
$rebuildAll = false;
try {
    $cols = $db->query("SHOW COLUMNS FROM page_comments")->fetchAll(PDO::FETCH_COLUMN);
    echo "page_comments 现有列: " . implode(', ', $cols) . "\n";
    if (in_array('page_id', $cols)) {
        $rebuildAll = true;
    }
} catch (Exception $e) {
    // 表不存在，需要创建
    $rebuildAll = true;
}

if ($rebuildAll) {
    echo "\n<span class=\"info\">ℹ️ 检测到旧表结构，正在删除旧表并重建...</span>\n";

    // 先备份（以防万一）
    echo "备份: 旧表将被删除并重建\n";

    $errors = [];
    foreach (['page_comments', 'page_favorites', 'page_likes'] as $tbl) {
        try {
            $db->exec("DROP TABLE IF EXISTS `$tbl`");
            echo "  ✅ 已删除旧表: $tbl\n";
        } catch (Exception $e) {
            $errors[] = "删除 $tbl 失败: " . $e->getMessage();
        }
    }

    if ($driver === 'mysql') {
        $sqls = [
            "page_comments" => "CREATE TABLE IF NOT EXISTS `page_comments` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `page_user_id` INT UNSIGNED NOT NULL COMMENT '被评论的主页主人用户ID',
                `visitor_id`   INT UNSIGNED NOT NULL COMMENT '评论者用户ID',
                `content`      VARCHAR(500) NOT NULL COMMENT '评论内容',
                `status`       TINYINT(1) DEFAULT 1 COMMENT '0=待审核 1=已通过(公开) 2=隐藏',
                `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_comments_page` (`page_user_id`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "page_favorites" => "CREATE TABLE IF NOT EXISTS `page_favorites` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `page_user_id` INT UNSIGNED NOT NULL COMMENT '被收藏的主页主人用户ID',
                `visitor_id`   INT UNSIGNED NOT NULL COMMENT '收藏者用户ID',
                `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_fav` (`page_user_id`, `visitor_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "page_likes" => "CREATE TABLE IF NOT EXISTS `page_likes` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `page_user_id` INT UNSIGNED NOT NULL COMMENT '被点赞的主页主人用户ID',
                `visitor_id`   INT UNSIGNED NOT NULL COMMENT '点赞者用户ID',
                `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_like` (`page_user_id`, `visitor_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
    } else {
        $sqls = [
            "page_comments" => "CREATE TABLE IF NOT EXISTS page_comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                page_user_id INTEGER NOT NULL,
                visitor_id INTEGER NOT NULL,
                content TEXT NOT NULL,
                status INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "page_favorites" => "CREATE TABLE IF NOT EXISTS page_favorites (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                page_user_id INTEGER NOT NULL,
                visitor_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(page_user_id, visitor_id)
            )",
            "page_likes" => "CREATE TABLE IF NOT EXISTS page_likes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                page_user_id INTEGER NOT NULL,
                visitor_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(page_user_id, visitor_id)
            )",
        ];
    }

    foreach ($sqls as $name => $sql) {
        try {
            $db->exec($sql);
            echo "  ✅ 已创建新表: $name\n";
        } catch (Exception $e) {
            $errors[] = "创建 $name 失败: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        echo "\n<span class=\"error\">❌ 部分操作失败:</span>\n";
        foreach ($errors as $e) echo "  $e\n";
    } else {
        echo "\n<span class=\"success\">✅ 所有表重建完成！</span>\n";
    }

} else {
    echo "\n<span class=\"success\">✅ page_comments 已是最新结构，无需迁移</span>\n";
}

// ---- 补 page_comments.status 列（如果旧表刚被加过 status 但没重建） ----
try {
    $db->exec("ALTER TABLE page_comments ADD COLUMN status TINYINT(1) DEFAULT 1");
    echo "<span class=\"success\">✅ 已补 status 列</span>\n";
} catch (Exception $e) {
    // 列已存在，正常
}

// ---- 补用户表互动开关 ----
foreach (['enable_likes','enable_comments','enable_favorites','comment_audit_enabled'] as $col) {
    try {
        if ($driver === 'mysql') {
            $db->exec("ALTER TABLE users ADD COLUMN `$col` TINYINT(1) DEFAULT 1");
        } else {
            $db->exec("ALTER TABLE users ADD COLUMN $col INTEGER DEFAULT 1");
        }
        echo "<span class=\"success\">✅ 已补 users.$col</span>\n";
    } catch (Exception $e) {}
}

echo "\n<span class=\"success\">✅ 迁移完成！</span>\n";
echo "可以 <a href='admin/dashboard.php?page=comments' class='btn'>👉 返回评论管理</a>\n";
?>
</pre>
<p style="color:#64748b;font-size:13px;margin-top:20px;text-align:center">迁移完成后请删除此文件</p>
</body></html>
