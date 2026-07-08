<?php
/**
 * 注册页面 - 重定向到用户登录/注册页
 */
require_once __DIR__ . '/config.php';

// 数据库未连接时跳转安装向导
if (!$db) { header("Location: install.php"); exit; }

$settings = getSettings($db);

if (!$settings['reg_enabled']) {
    die('注册已关闭');
}

header("Location: " . BASE_URL . "/user/");
exit;
