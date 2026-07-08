<?php
/**
 * 管理员一键登录用户后台
 * 
 * 用法: GET ?id=用户ID
 * 安全限制：仅 admin_role 为 super 的管理员可使用
 * 操作记录：记录 admin_log
 */
require_once __DIR__ . '/../config.php';
requireAdmin();

// 仅超级管理员可用
if (empty($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
    header('HTTP/1.1 403 Forbidden');
    die('权限不足，仅超级管理员可使用此功能');
}

$targetUid = max(0, (int)($_GET['id'] ?? 0));
if ($targetUid <= 0) {
    die('无效的用户ID');
}

// 查询目标用户是否存在
$stmt = $db->prepare("SELECT id, username, nickname FROM users WHERE id = ?");
$stmt->execute([$targetUid]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    die('用户不存在');
}

// 记录操作日志
adminLog($db, '一键登录用户后台', 'user', $targetUid, 
    "管理员 {$_SESSION['admin_name']} 登录到用户 #{$targetUid} ({$targetUser['username']}) 后台");

// 保存当前管理员身份（方便返回）
$_SESSION['impersonate_admin_id']   = (int)$_SESSION['admin_id'];
$_SESSION['impersonate_admin_name'] = $_SESSION['admin_name'];
$_SESSION['impersonate_admin_role'] = $_SESSION['admin_role'];
$_SESSION['impersonated_by_admin']  = true;

// 设置目标用户的会话
$_SESSION['user_id']    = (int)$targetUser['id'];
$_SESSION['user_name']  = $targetUser['nickname'] ?: $targetUser['username'];
$_SESSION['user_login'] = true;

// 重定向到用户后台
redirect('../user/dashboard.php');
