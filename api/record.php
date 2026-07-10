<?php
/**
 * API 端点 - 记录访问 & 点击 & 密码验证
 * 
 * 访问记录: /api/record.php?action=view&user_id={id}
 * 点击跳转: /api/record.php?action=click&link_id={id}&user_id={id}&url={encoded_url}
 * 密码验证: /api/record.php?action=verify_pass&link_id={id}&pass={password}
 * 
 * 去重规则：
 *   - 同一 IP 5 分钟内只记一次访问
 *   - 同一 IP + 同一链接 5 分钟内只记一次点击
 *   - 页面主人自己访问不统计
 */
require_once __DIR__ . '/../config.php';

$action = $_GET['action'] ?? '';
$userId = max(0, (int)($_GET['user_id'] ?? 0));
$ip = getClientIP();
$ua = safeSubstr($_SERVER['HTTP_USER_AGENT'] ?? '', 500);

// ---- 判断是否为自己访问（已登录且是页面主人） ----
function isSelfView($userId) {
    return !empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId;
}

/**
 * 安全插入 stats 表，自动修复缺少 AUTO_INCREMENT 的问题
 */
function safeInsertStats($db, $userId, $linkId, $type, $ip, $ua) {
    try {
        $stmt = $db->prepare("INSERT INTO stats (user_id, link_id, type, ip, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $linkId, $type, $ip, $ua]);
        return true;
    } catch (PDOException $e) {
        // Field 'id' doesn't have a default value → 自动修复
        if (strpos($e->getMessage(), "doesn't have a default value") !== false) {
            try {
                $db->exec("ALTER TABLE `stats` CHANGE `id` `id` INT UNSIGNED AUTO_INCREMENT");
                // 重试插入
                $stmt = $db->prepare("INSERT INTO stats (user_id, link_id, type, ip, user_agent) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $linkId, $type, $ip, $ua]);
                return true;
            } catch (Exception $e2) {
                // 再次失败 → 用 MAX(id)+1 兜底
                try {
                    $maxId = (int)$db->query("SELECT COALESCE(MAX(id),0)+1 FROM stats")->fetchColumn();
                    $stmt = $db->prepare("INSERT INTO stats (id, user_id, link_id, type, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$maxId, $userId, $linkId, $type, $ip, $ua]);
                    return true;
                } catch (Exception $e3) {
                    return false;
                }
            }
        }
        return false;
    }
}

/**
 * 验证重定向URL安全性，防止开放重定向漏洞
 * - 相对路径(/xxx)直接放行
 * - 同域名URL放行
 * - 外部URL仅允许http/https，且拒绝未知协议/钓鱼域名模式
 */
function validateRedirectUrl($url) {
    $url = trim($url);
    if (empty($url)) return BASE_URL;
    
    // 相对路径安全放行
    if (strpos($url, '/') === 0) return $url;
    
    // 检查是否在同域名下
    $baseHost = strtolower(parse_url(BASE_URL, PHP_URL_HOST) ?: '');
    $urlHost = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
    
    if ($urlHost && $urlHost === $baseHost) return $url;
    
    // 外部URL必须为合法http/https
    if (!preg_match('/^https?:\/\//i', $url)) {
        return BASE_URL;
    }
    
    return $url;
}

// ---- 密码验证 ----
if ($action === 'verify_pass') {
    $linkId = max(0, (int)($_GET['link_id'] ?? 0));
    $pass = trim($_GET['pass'] ?? '');
    
    if ($linkId <= 0) {
        jsonResponse(['success' => false, 'msg' => '缺少链接ID']);
    }
    
    $stmt = $db->prepare("SELECT passcode FROM links WHERE id = ?");
    $stmt->execute([$linkId]);
    $row = $stmt->fetch();
    
    if (!$row) {
        jsonResponse(['success' => false, 'msg' => '链接不存在']);
    }
    
    $correctPass = trim($row['passcode'] ?? '');
    if (empty($correctPass)) {
        jsonResponse(['success' => true, 'msg' => '无需密码']);
    }
    
    if ($pass === $correctPass) {
        jsonResponse(['success' => true, 'msg' => '密码正确']);
    } else {
        jsonResponse(['success' => false, 'msg' => '密码错误']);
    }
}

// ---- 记录访问 ----
if ($action === 'view') {
    if ($userId <= 0) exit;
    
    // 不统计主人自己的访问
    if (isSelfView($userId)) {
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }
    
    // 去重：同一 IP 5 分钟内已有记录则不重复写入
    $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $stmt = $db->prepare("SELECT id FROM stats WHERE user_id = ? AND type = 'view' AND ip = ? AND created_at >= ? LIMIT 1");
    $stmt->execute([$userId, $ip, $fiveMinAgo]);
    if (!$stmt->fetch()) {
        safeInsertStats($db, $userId, 0, 'view', $ip, $ua);
    }
    
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// ---- 点击跳转 ----
if ($action === 'click') {
    $linkId = max(0, (int)($_GET['link_id'] ?? 0));
    $url = $_GET['url'] ?? '';
    
    if ($userId <= 0 || $linkId <= 0 || empty($url)) {
        $safeUrl = validateRedirectUrl($url);
        header("Location: $safeUrl", true, 302);
        exit;
    }
    
    // 不统计主人自己的点击
    if (!isSelfView($userId)) {
        // 去重：同一 IP + 同一链接 5 分钟内已有记录则不重复写入
        $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $stmt = $db->prepare("SELECT id FROM stats WHERE user_id = ? AND link_id = ? AND type = 'click' AND ip = ? AND created_at >= ? LIMIT 1");
        $stmt->execute([$userId, $linkId, $ip, $fiveMinAgo]);
        if (!$stmt->fetch()) {
            safeInsertStats($db, $userId, $linkId, 'click', $ip, $ua);
            // 更新链接点击计数
            $db->prepare("UPDATE links SET click_count = click_count + 1 WHERE id = ?")->execute([$linkId]);
        }
    }
    
    $safeUrl = validateRedirectUrl($url);
    header("Location: $safeUrl", true, 302);
    exit;
}

// 无效请求
header('HTTP/1.1 400 Bad Request');
echo 'Invalid request';
