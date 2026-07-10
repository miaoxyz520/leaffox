<?php
/**
 * 收藏/取消收藏 API
 * POST /api/interaction_favorite.php
 * 参数: page_user_id (被收藏的主页主人ID)
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持POST']);
    exit;
}

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

// 不能收藏自己
if ($pageUserId === $visitorId) {
    echo json_encode(['success' => false, 'message' => '不能收藏自己的主页']);
    exit;
}

// 检查用户是否开启收藏
$stmt = $db->prepare("SELECT id, enable_favorites FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$pageUserId]);
$targetUser = $stmt->fetch();
if (!$targetUser) {
    echo json_encode(['success' => false, 'message' => '用户不存在']);
    exit;
}
if (!$targetUser['enable_favorites']) {
    echo json_encode(['success' => false, 'message' => '该用户已关闭收藏功能']);
    exit;
}

// 切换收藏状态
$stmt = $db->prepare("SELECT id FROM page_favorites WHERE page_user_id = ? AND visitor_id = ?");
$stmt->execute([$pageUserId, $visitorId]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $db->prepare("DELETE FROM page_favorites WHERE id = ?");
    $stmt->execute([$existing['id']]);
    $favorited = false;
} else {
    $stmt = $db->prepare("INSERT IGNORE INTO page_favorites (page_user_id, visitor_id) VALUES (?, ?)");
    $stmt->execute([$pageUserId, $visitorId]);
    $favorited = true;
}

$stmt = $db->prepare("SELECT COUNT(*) FROM page_favorites WHERE page_user_id = ?");
$stmt->execute([$pageUserId]);
$count = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'favorited' => $favorited,
    'count' => $count,
    'message' => $favorited ? '收藏成功' : '已取消收藏'
]);
