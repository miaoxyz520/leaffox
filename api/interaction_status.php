<?php
/**
 * 互动状态查询 API
 * GET /api/interaction_status.php?page_user_id=X
 * 返回点赞数、评论数、收藏数，以及当前访客的点赞/收藏状态
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$pageUserId = (int)($_GET['page_user_id'] ?? 0);
if (!$pageUserId) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit;
}

$visitorId = (int)($_SESSION['visitor_id'] ?? 0);

// 点赞数
$stmt = $db->prepare("SELECT COUNT(*) FROM page_likes WHERE page_user_id = ?");
$stmt->execute([$pageUserId]);
$likeCount = (int)$stmt->fetchColumn();

// 评论数
$stmt = $db->prepare("SELECT COUNT(*) FROM page_comments WHERE page_user_id = ?");
$stmt->execute([$pageUserId]);
$commentCount = (int)$stmt->fetchColumn();

// 收藏数
$stmt = $db->prepare("SELECT COUNT(*) FROM page_favorites WHERE page_user_id = ?");
$stmt->execute([$pageUserId]);
$favoriteCount = (int)$stmt->fetchColumn();

// 当前访客是否已点赞
$liked = false;
if ($visitorId > 0) {
    $stmt = $db->prepare("SELECT id FROM page_likes WHERE page_user_id = ? AND visitor_id = ?");
    $stmt->execute([$pageUserId, $visitorId]);
    $liked = (bool)$stmt->fetch();
}

// 当前访客是否已收藏
$favorited = false;
if ($visitorId > 0) {
    $stmt = $db->prepare("SELECT id FROM page_favorites WHERE page_user_id = ? AND visitor_id = ?");
    $stmt->execute([$pageUserId, $visitorId]);
    $favorited = (bool)$stmt->fetch();
}

// 用户开关
$stmt = $db->prepare("SELECT enable_likes, enable_comments, enable_favorites FROM users WHERE id = ?");
$stmt->execute([$pageUserId]);
$settings = $stmt->fetch();

echo json_encode([
    'success' => true,
    'logged_in' => $visitorId > 0 && !empty($_SESSION['visitor_login']),
    'like_count' => $likeCount,
    'comment_count' => $commentCount,
    'favorite_count' => $favoriteCount,
    'liked' => $liked,
    'favorited' => $favorited,
    'settings' => $settings ? [
        'enable_likes' => (bool)$settings['enable_likes'],
        'enable_comments' => (bool)$settings['enable_comments'],
        'enable_favorites' => (bool)$settings['enable_favorites'],
    ] : ['enable_likes'=>true, 'enable_comments'=>true, 'enable_favorites'=>true],
]);
