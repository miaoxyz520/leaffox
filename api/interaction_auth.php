<?php
/**
 * 互动功能 · 访客登录/注册/状态检测
 * POST /api/interaction_auth.php
 * action=login   参数: username, password
 * action=logout
 * action=check   返回当前登录态
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$action = trim($_POST['action'] ?? $_GET['action'] ?? '');

// ===== 检测登录态 =====
if ($action === 'check') {
    $loggedIn = !empty($_SESSION['visitor_id']) && !empty($_SESSION['visitor_login']);
    echo json_encode([
        'success' => true,
        'logged_in' => $loggedIn,
        'visitor' => $loggedIn ? [
            'id' => (int)$_SESSION['visitor_id'],
            'username' => $_SESSION['visitor_name'] ?? '',
        ] : null
    ]);
    exit;
}

// ===== 退出登录 =====
if ($action === 'logout') {
    unset($_SESSION['visitor_id'], $_SESSION['visitor_login'], $_SESSION['visitor_name']);
    echo json_encode(['success' => true, 'message' => '已退出']);
    exit;
}

// ===== 登录 =====
if ($action === 'login') {
    // 速率限制
    $rateCheck = checkRateLimit('visit_login');
    if (!$rateCheck['allowed']) {
        echo json_encode(['success' => false, 'message' => $rateCheck['message']]);
        exit;
    }
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '请输入账号和密码']);
        exit;
    }

    // 支持用户名或邮箱登录
    $stmt = $db->prepare("SELECT id, username, nickname, password FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'] ?? '')) {
        recordRateLimit('visit_login');
        echo json_encode(['success' => false, 'message' => '账号或密码错误']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['visitor_id'] = (int)$user['id'];
    $_SESSION['visitor_login'] = true;
    $_SESSION['visitor_name'] = $user['nickname'] ?? $user['username'] ?? '' ?? '' ?: $user['username'] ?? '';

    echo json_encode([
        'success' => true,
        'message' => '登录成功',
        'visitor' => [
            'id' => (int)$user['id'],
            'username' => $user['username'] ?? '',
            'nickname' => $user['nickname'] ?? $user['username'] ?? '' ?? '' ?: $user['username'] ?? '',
        ]
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => '未知操作']);
