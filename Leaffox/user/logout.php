<?php
/**
 * 用户退出
 */
require_once __DIR__ . '/../config.php';
session_destroy();
redirect('./index.php');
