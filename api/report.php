<?php
/**
 * 举报 API
 * POST /api/report.php
 * 参数: user_id, type, reason
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持POST']);
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$type   = trim($_POST['type'] ?? '');
$reason = trim($_POST['reason'] ?? '');

// 验证用户是否存在
if (!$userId) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit;
}
$user = $db->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1")->execute([$userId])
    ? $db->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1")->fetch()
    : null;
// 修正写法
$stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$userId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => '用户不存在']);
    exit;
}

// 验证举报类型
$allowedTypes = ['violation', 'spam', 'copyright', 'pornographic', 'fraud', 'other'];
if (!in_array($type, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => '无效的举报类型']);
    exit;
}

// 获取举报者IP
$reporterIp = $_SERVER['REMOTE_ADDR'] ?? '';
// 如果经过代理，尝试获取真实IP
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $reporterIp = trim($ips[0]);
} elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
    $reporterIp = $_SERVER['HTTP_X_REAL_IP'];
}

// 限制同一IP对同一用户的重复举报（短时间内）
$oneHourAgo = date('Y-m-d H:i:s', time() - 3600);
$stmt = $db->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND reporter_ip = ? AND created_at >= ?");
$stmt->execute([$userId, $reporterIp, $oneHourAgo]);
if ($stmt->fetchColumn() >= 5) {
    echo json_encode(['success' => false, 'message' => '举报过于频繁，请稍后再试']);
    exit;
}

// 入库
$stmt = $db->prepare("INSERT INTO reports (user_id, reporter_ip, type, reason) VALUES (?, ?, ?, ?)");
$stmt->execute([$userId, $reporterIp, $type, $reason]);

echo json_encode(['success' => true, 'message' => '举报已提交']);
