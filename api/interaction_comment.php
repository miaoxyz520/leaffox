<?php
/**
 * 评论 API
 * GET  /api/interaction_comment.php?page_user_id=X&page=N  → 获取评论列表（仅公开）
 * POST /api/interaction_comment.php                          → 添加评论（有审核则待审核）
 * POST参数: page_user_id, content
 * 
 * 管理专用（需管理员session）:
 * GET  /api/interaction_comment.php?action=admin_list&status=0&page=1  → 管理员获取全部评论
 * POST /api/interaction_comment.php?action=admin_set_status            → 管理设置状态
 * POST /api/interaction_comment.php?action=admin_delete                → 管理删除评论
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// ======================================================================
// 管理员操作
// ======================================================================
$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

if ($action === 'admin_list') {
    if (empty($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => '未登录']);
        exit;
    }
    $status = $_GET['status'] ?? ''; // 空=全部, 0=待审核, 1=已通过, 2=隐藏
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    $where = "1=1";
    $params = [];
    if ($status !== '' && $status !== 'all') {
        $where .= " AND c.status = ?";
        $params[] = (int)$status;
    }
    $search = trim($_GET['search'] ?? '');
    if ($search) {
        $where .= " AND (c.content LIKE ? OR u.username LIKE ? OR u.nickname LIKE ?)";
        $s = "%$search%";
        $params[] = $s; $params[] = $s; $params[] = $s;
    }

    // 总数
    $stmt = $db->prepare("SELECT COUNT(*) FROM page_comments c WHERE $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // 列表
    $stmt = $db->prepare("
        SELECT c.id, c.page_user_id, c.content, c.status, c.created_at,
               u.id as visitor_id, u.username, u.nickname, u.avatar,
               pu.username as page_username, pu.nickname as page_nickname
        FROM page_comments c
        LEFT JOIN users u ON c.visitor_id = u.id
        LEFT JOIN users pu ON c.page_user_id = pu.id
        WHERE $where
        ORDER BY c.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $comments = $stmt->fetchAll();

    foreach ($comments as &$c) {
        $c['time_ago'] = timeAgo($c['created_at']);
        $c['visitor_name'] = $c['nickname'] ?: $c['username'];
        $c['page_name'] = $c['page_nickname'] ?: $c['page_username'];
    }

    echo json_encode([
        'success' => true,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'has_more' => ($offset + $perPage) < $total,
        'comments' => $comments,
    ]);
    exit;
}

if ($action === 'admin_set_status') {
    if (empty($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => '未登录']);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    if (!$id || !in_array($status, [0,1,2])) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        exit;
    }
    $stmt = $db->prepare("UPDATE page_comments SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    adminLog($db, '设置评论状态', 'comment', $id, "status=$status");
    echo json_encode(['success' => true, 'message' => '操作成功']);
    exit;
}

if ($action === 'admin_delete') {
    if (empty($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => '未登录']);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        exit;
    }
    $stmt = $db->prepare("DELETE FROM page_comments WHERE id = ?");
    $stmt->execute([$id]);
    adminLog($db, '删除评论', 'comment', $id, '管理员删除');
    echo json_encode(['success' => true, 'message' => '已删除']);
    exit;
}

// ======================================================================
// 公开 GET：获取评论列表（仅 status=1 已通过）
// ======================================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    $pageUserId = (int)($_GET['page_user_id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    if (!$pageUserId) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        exit;
    }

    // 总数（仅公开）
    $stmt = $db->prepare("SELECT COUNT(*) FROM page_comments WHERE page_user_id = ? AND status = 1");
    $stmt->execute([$pageUserId]);
    $total = (int)$stmt->fetchColumn();

    // 列表（仅 status=1）
    $stmt = $db->prepare("
        SELECT c.id, c.content, c.created_at,
               u.id as visitor_id, u.username, u.nickname, u.avatar
        FROM page_comments c
        LEFT JOIN users u ON c.visitor_id = u.id
        WHERE c.page_user_id = ? AND c.status = 1
        ORDER BY c.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$pageUserId, $perPage, $offset]);
    $comments = $stmt->fetchAll();

    foreach ($comments as &$c) {
        $c['time_ago'] = timeAgo($c['created_at']);
        $c['visitor_name'] = $c['nickname'] ?: $c['username'];
    }

    echo json_encode([
        'success' => true,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'has_more' => ($offset + $perPage) < $total,
        'comments' => $comments,
    ]);
    exit;
}

// ======================================================================
// POST：添加评论
// ======================================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $visitorId = (int)($_SESSION['visitor_id'] ?? 0);
    if (empty($visitorId) || empty($_SESSION['visitor_login'])) {
        echo json_encode(['success' => false, 'message' => '请先登录', 'need_login' => true]);
        exit;
    }

    $pageUserId = (int)($_POST['page_user_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if (!$pageUserId) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        exit;
    }
    if (mb_strlen($content) < 1 || mb_strlen($content) > 500) {
        echo json_encode(['success' => false, 'message' => '评论内容为1-500字']);
        exit;
    }

    // 检查用户是否开启评论
    $stmt = $db->prepare("SELECT id, enable_comments, comment_audit_enabled FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$pageUserId]);
    $targetUser = $stmt->fetch();
    if (!$targetUser) {
        echo json_encode(['success' => false, 'message' => '用户不存在']);
        exit;
    }
    if (!$targetUser['enable_comments']) {
        echo json_encode(['success' => false, 'message' => '该用户已关闭评论功能']);
        exit;
    }

    // 审核模式：如果开启了审核，初始状态为 0（待审核），否则为 1（直接公开）
    $initialStatus = $targetUser['comment_audit_enabled'] ? 0 : 1;

    // 频率限制
    $stmt = $db->prepare("SELECT COUNT(*) FROM page_comments WHERE visitor_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 SECOND)");
    $stmt->execute([$visitorId]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => '操作过于频繁，请稍后再试']);
        exit;
    }

    // 入库
    $stmt = $db->prepare("INSERT INTO page_comments (page_user_id, visitor_id, content, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pageUserId, $visitorId, $content, $initialStatus]);
    $commentId = (int)$db->lastInsertId();

    // 获取评论者信息
    $stmt = $db->prepare("SELECT id, username, nickname, avatar FROM users WHERE id = ?");
    $stmt->execute([$visitorId]);
    $visitor = $stmt->fetch();

    // 获取最新评论总数（仅公开）
    $stmt = $db->prepare("SELECT COUNT(*) FROM page_comments WHERE page_user_id = ? AND status = 1");
    $stmt->execute([$pageUserId]);
    $total = (int)$stmt->fetchColumn();

    $message = $initialStatus === 0 ? '评论已提交，等待审核通过后展示' : '评论成功';

    echo json_encode([
        'success' => true,
        'message' => $message,
        'need_audit' => ($initialStatus === 0),
        'comment' => [
            'id' => $commentId,
            'content' => $content,
            'status' => $initialStatus,
            'created_at' => date('Y-m-d H:i:s'),
            'time_ago' => '刚刚',
            'visitor_id' => $visitorId,
            'visitor_name' => $visitor['nickname'] ?: $visitor['username'],
            'avatar' => $visitor['avatar'] ?? '',
        ],
        'total' => $total,
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => '不支持的请求方法']);

// ===== 辅助：时间格式化 =====
function timeAgo($datetime) {
    $now = time();
    $time = strtotime($datetime);
    $diff = $now - $time;
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    if ($diff < 2592000) return floor($diff / 86400) . '天前';
    return date('m-d', $time);
}
