<?php
/**
 * 点赞/取消点赞 API
 * POST /api/interaction_like.php
 * 参数: page_user_id (被点赞的主页主人ID)
 * 需要已登录 (session visitor_id)
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持POST']);
    exit;
}

// 检查登录
$visitorId = (int)($_SESSION['visitor_id'] ?? 0);
if (empty($visitorId) || empty($_SESSION['visitor_login'])) {
    echo json_encode(['success' => false, 'message' => '请先登录', 'need_login' => true]);
    exit;
}

$pageUserId = (int)($_POST['page_user_id'] ?? 0);
if (!$pageUserId) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit;
}

// 不能给自己的主页点赞
if ($pageUserId === $visitorId) {
    echo json_encode(['success' => false, 'message' => '不能给自己点赞']);
    exit;
}

// 检查被点赞用户是否开启点赞功能
$stmt = $db->prepare("SELECT id, enable_likes FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$pageUserId]);
$targetUser = $stmt->fetch();
if (!$targetUser) {
    echo json_encode(['success' => false, 'message' => '用户不存在']);
    exit;
}
if (!$targetUser['enable_likes']) {
    echo json_encode(['success' => false, 'message' => '该用户已关闭点赞功能']);
    exit;
}

// 检查是否已点赞 → 切换
$stmt = $db->prepare("SELECT id FROM page_likes WHERE page_user_id = ? AND visitor_id = ?");
$stmt->execute([$pageUserId, $visitorId]);
$existing = $stmt->fetch();

if ($existing) {
    // 取消点赞
    $stmt = $db->prepare("DELETE FROM page_likes WHERE id = ?");
    $stmt->execute([$existing['id']]);
    $liked = false;
} else {
    // 点赞
    $stmt = $db->prepare("INSERT IGNORE INTO page_likes (page_user_id, visitor_id) VALUES (?, ?)");
    $stmt->execute([$pageUserId, $visitorId]);
    $liked = true;
}

// 返回最新点赞数
$stmt = $db->prepare("SELECT COUNT(*) FROM page_likes WHERE page_user_id = ?");
$stmt->execute([$pageUserId]);
$count = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'count' => $count,
    'message' => $liked ? '点赞成功' : '已取消点赞'
]);
