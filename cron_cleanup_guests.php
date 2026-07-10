<?php
/**
 * 定时清理脚本 v2.0 — 清理过期游客 + 过期的密码重置令牌 + 旧日志
 *
 * crontab 示例（每 5 分钟）：
 *  每5分钟执行一次: php /path/to/cron_cleanup_guests.php >/dev/null 2>&1
 *
 * 日志位置：data/logs/cleanup.log
 * 使用 SQL 事务 + Prepared Statements 保证安全与一致性
 */

define('CLEANUP_LOCK_FILE', __DIR__ . '/data/logs/cleanup_running.lock');
define('MAX_EXECUTION_TIME', 30); // 最大执行时间（秒）

// 防止并发执行
if (file_exists(CLEANUP_LOCK_FILE)) {
    $lockTime = filemtime(CLEANUP_LOCK_FILE);
    if ($lockTime && (time() - $lockTime) < MAX_EXECUTION_TIME) {
        exit("[CLEANUP SKIP] 上一次清理还在运行中\n");
    }
    // 锁超时，强制释放
    @unlink(CLEANUP_LOCK_FILE);
}
file_put_contents(CLEANUP_LOCK_FILE, getmypid());

try {
    require_once __DIR__ . '/config.php';

    $startTime = microtime(true);
    $startMem  = memory_get_usage(true);
    $results   = [];

    // ================================================================
    // 任务 1：清理过期游客账号
    // ================================================================
    $deletedGuests = 0;
    try {
        $stmt = $db->prepare(
            "SELECT id, username FROM users 
             WHERE is_guest = 1 
               AND guest_expires_at IS NOT NULL 
               AND guest_expires_at <= NOW()"
        );
        $stmt->execute();
        $expiredGuests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($expiredGuests as $guest) {
            $uid = (int)$guest['id'];
            $db->beginTransaction();
            try {
                // 用 prepared statements 安全删除关联数据
                $db->prepare("DELETE FROM stats WHERE user_id = ?")->execute([$uid]);
                $db->prepare("DELETE FROM links WHERE user_id = ?")->execute([$uid]);
                $db->prepare("DELETE FROM page_likes WHERE page_user_id = ? OR visitor_id = ?")->execute([$uid, $uid]);
                $db->prepare("DELETE FROM page_comments WHERE page_user_id = ? OR visitor_id = ?")->execute([$uid, $uid]);
                $db->prepare("DELETE FROM page_favorites WHERE page_user_id = ? OR visitor_id = ?")->execute([$uid, $uid]);
                $db->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
                $db->commit();
                $deletedGuests++;
            } catch (Exception $e) {
                $db->rollBack();
                error_log("[CLEANUP ERROR] 游客 #{$uid} 删除失败: " . $e->getMessage());
            }
        }
        $results[] = "游客账号: 删除 {$deletedGuests} 个";
    } catch (Exception $e) {
        $results[] = "游客账号: 查询失败 - " . $e->getMessage();
    }

    // ================================================================
    // 任务 2：清理过期的密码重置令牌
    // ================================================================
    try {
        $stmt = $db->prepare(
            "DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1"
        );
        $stmt->execute();
        $deletedTokens = $stmt->rowCount();
        $results[] = "过期令牌: 删除 {$deletedTokens} 个";
    } catch (Exception $e) {
        $results[] = "过期令牌: 清理失败 - " . $e->getMessage();
    }

    // ================================================================
    // 任务 3：清理 30 天前的管理员操作日志
    // ================================================================
    try {
        $stmt = $db->prepare(
            "DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $stmt->execute();
        $deletedLogs = $stmt->rowCount();
        $results[] = "旧日志: 删除 {$deletedLogs} 条（30天前）";
    } catch (Exception $e) {
        $results[] = "旧日志: 清理失败 - " . $e->getMessage();
    }

    // ================================================================
    // 任务 4：修复数据表索引（静默执行，仅当添加新索引时）
    // ================================================================
    if (DB_TYPE !== 'sqlite') {
        try {
            // 仅在表不存在索引时添加
            $idxStmt = $db->query("SHOW INDEX FROM users");
            $existingIndexes = $idxStmt->fetchAll(PDO::FETCH_COLUMN, 2);
            
            $neededIndexes = [
                'idx_users_email'      => "ALTER TABLE users ADD INDEX idx_users_email (email)",
                'idx_users_suffix'     => "ALTER TABLE users ADD INDEX idx_users_suffix (suffix)",
                'idx_users_guest'      => "ALTER TABLE users ADD INDEX idx_users_guest (is_guest, guest_expires_at)",
                'idx_links_user_active'=> "ALTER TABLE links ADD INDEX idx_links_user_active (user_id, is_active)",
                'idx_stats_date'       => "ALTER TABLE stats ADD INDEX idx_stats_date (created_at)",
            ];
            
            $addedIndexes = 0;
            foreach ($neededIndexes as $idxName => $sql) {
                if (!in_array($idxName, $existingIndexes)) {
                    try {
                        $db->exec($sql);
                        $addedIndexes++;
                    } catch (Exception $e) {
                        // 索引已存在或无法创建，静默跳过
                    }
                }
            }
            if ($addedIndexes > 0) {
                $results[] = "索引优化: 新增 {$addedIndexes} 个索引";
            }
        } catch (Exception $e) {
            // 静默忽略
        }
    }

    // ================================================================
    // 写入日志
    // ================================================================
    $elapsed = round(microtime(true) - $startTime, 3);
    $peakMem = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    $logMsg  = date('Y-m-d H:i:s') . " | 耗时:{$elapsed}s | 内存:{$peakMem}MB | " . implode(' | ', $results) . "\n";

    $logDir = __DIR__ . '/data/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logDir . '/cleanup.log', $logMsg, FILE_APPEND);

    // ================================================================
    // CLI 友好输出
    // ================================================================
    if (php_sapi_name() === 'cli') {
        echo "✅ 清理任务完成\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        foreach ($results as $r) {
            echo "  • {$r}\n";
        }
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  耗时: {$elapsed}s | 峰值内存: {$peakMem}MB\n";
    }

} catch (Exception $e) {
    $errMsg = date('Y-m-d H:i:s') . " | 致命错误: " . $e->getMessage() . "\n";
    $logDir = __DIR__ . '/data/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents($logDir . '/cleanup.log', $errMsg, FILE_APPEND);
    if (php_sapi_name() === 'cli') {
        echo "❌ 错误: " . $e->getMessage() . "\n";
    }
} finally {
    // 清理锁文件
    @unlink(CLEANUP_LOCK_FILE);
}
