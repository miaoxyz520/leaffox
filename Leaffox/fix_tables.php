<?php
/**
 * 修复工具：重建 admin 表（不丢失用户数据）
 * 访问一次后请删除此文件
 */
require_once __DIR__ . '/config.php';

if (!$db) {
    die("❌ 数据库未连接，请先运行 install.php 完成安装");
}

echo "🔄 开始修复...<br>";

// 检查哪些表缺失
$tables = ['admin', 'users', 'links', 'stats', 'settings', 'admin_logs', 'reports'];
$ok = 0;
foreach ($tables as $table) {
    try {
        $db->query("SELECT 1 FROM \"$table\" LIMIT 1");
        echo "✅ $table 表正常<br>";
        $ok++;
    } catch (Exception $e) {
        echo "⚠️ $table 表缺失，正在创建...<br>";
        // 由 config.php 的自动修复逻辑处理
    }
}

if ($ok == count($tables)) {
    echo "<br>🎉 所有表都已存在，无需修复！";
    exit;
}

// 从 install_sqlite.sql 读取并执行建表语句
$sql = file_get_contents(__DIR__ . '/install_sqlite.sql');
if (!$sql) {
    die("❌ 找不到 install_sqlite.sql");
}

// 逐条执行（忽略已存在错误）
$statements = explode(';', $sql);
$count = 0;
foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (!empty($stmt) && strncmp($stmt, '--', 2) !== 0 && stripos($stmt, 'INSERT') === false) {
        try {
            $db->exec($stmt);
            $count++;
        } catch (Exception $e) {
            // 静默跳过（表已存在等）
        }
    }
}

// 插入默认管理员（如果不存在）
try {
    $check = $db->query("SELECT COUNT(*) FROM admin")->fetchColumn();
    if ($check == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO admin (username, password, nickname, role) VALUES ('admin', '$hash', '超级管理员', 'super')");
        echo "✅ 已插入默认管理员 admin / admin123<br>";
    }
} catch (Exception $e) {}

// 插入默认设置（如果不存在）
try {
    $check = $db->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($check == 0) {
        $db->exec("INSERT INTO settings (id, site_name, site_desc) VALUES (1, 'Link3主页系统', '每个人都有自己的专属主页')");
        echo "✅ 已插入默认设置<br>";
    }
} catch (Exception $e) {}

echo "<br>🎉 修复完成！<a href='admin/index.php'>前往管理员登录</a>";
