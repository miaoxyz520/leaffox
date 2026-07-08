<?php
/**
 * API 端点 - 文件上传（图片/视频）
 * 
 * POST /api/upload.php
 *   - file: 上传的文件
 *   - type: image 或 video
 * 
 * 返回 JSON: { success: true, url: "uploads/xxx.jpg" }
 */
require_once __DIR__ . '/../config.php';

// 仅允许已登录用户上传
if (empty($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'msg' => '请先登录']);
}

$type = $_POST['type'] ?? 'image';
$file = $_FILES['file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $errMsg = '上传失败';
    if ($file && $file['error'] === UPLOAD_ERR_INI_SIZE) $errMsg = '文件超过服务器限制';
    elseif ($file && $file['error'] === UPLOAD_ERR_FORM_SIZE) $errMsg = '文件超过表单限制';
    jsonResponse(['success' => false, 'msg' => $errMsg]);
}

$result = false;

if ($type === 'video') {
    $result = uploadFile($file, 'video', ['video/mp4', 'video/webm', 'video/quicktime'], 100 * 1024 * 1024);
} else {
    $result = uploadImage($file, 'img');
}

if (isset($result['error'])) {
    jsonResponse(['success' => false, 'msg' => $result['error']]);
}

// 返回可访问的完整 URL
$url = BASE_URL . '/' . $result['path'];
jsonResponse(['success' => true, 'url' => $url, 'path' => $result['path']]);
