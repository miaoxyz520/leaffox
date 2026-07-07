<?php
/**
 * 管理员退出
 */
require_once __DIR__ . '/../config.php';
adminLog($db, '管理员退出', 'admin', $_SESSION['admin_id'] ?? 0);
session_destroy();
redirect('./index.php');
