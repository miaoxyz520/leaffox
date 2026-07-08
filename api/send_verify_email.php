<?php
/**
 * API: 发送邮箱验证码（用于注册/绑定邮箱）
 * POST /api/send_verify_email.php
 *   user_id  - 用户ID
 *   email    - 目标邮箱
 *   type     - 'register' 或 'bind'
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mail.php';

if (!$db) {
    jsonResponse(['success' => false, 'message' => '系统错误'], 500);
}

$settings = getSettings($db);
if (empty($settings['smtp_host']) || empty($settings['smtp_user']) || empty($settings['smtp_pass'])) {
    jsonResponse(['success' => false, 'message' => '管理员未配置邮件发送服务'], 400);
}

$user_id = (int)($_POST['user_id'] ?? 0);
$email   = trim($_POST['email'] ?? '');
$type    = $_POST['type'] ?? 'bind';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => '请输入有效的邮箱地址'], 400);
}

// 检查邮箱是否已被其他用户使用
$stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
$stmt->execute([$email, $user_id]);
if ($stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => '该邮箱已被其他账号绑定'], 400);
}

// 60秒防刷
$stmt = $db->prepare("SELECT verify_token_expires FROM users WHERE id = ? AND email_verify_token != ''");
$stmt->execute([$user_id]);
$row = $stmt->fetch();
if ($row && !empty($row['verify_token_expires'])) {
    $expires = strtotime($row['verify_token_expires']);
    if ($expires > time() - 55) {
        jsonResponse(['success' => false, 'message' => '发送过于频繁，请稍后再试'], 429);
    }
}

// 生成验证令牌
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', time() + 1800); // 30分钟有效

$stmt = $db->prepare("UPDATE users SET email = ?, email_verify_token = ?, verify_token_expires = ? WHERE id = ?");
$stmt->execute([$email, $token, $expires, $user_id]);

// 构建验证链接
$verifyUrl = BASE_URL . '/api/verify_email.php?token=' . urlencode($token) . '&uid=' . $user_id;

$siteName = h($settings['site_name'] ?? 'Leaffox主页系统');
$subject  = "验证您的邮箱 - {$siteName}";

$body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>邮箱验证</title></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);">
  <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:22px;"><i class="fas fa-envelope"></i> 邮箱验证</h1>
  </div>
  <div style="padding:35px 30px;">
    <p style="color:#333;font-size:15px;line-height:1.7;">您好！</p>
    <p style="color:#333;font-size:15px;line-height:1.7;">
      您正在 <strong>{$siteName}</strong> 进行邮箱验证，请点击下方按钮完成验证：
    </p>
    <div style="text-align:center;margin:30px 0;">
      <a href="{$verifyUrl}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:14px 40px;border-radius:30px;text-decoration:none;font-size:16px;font-weight:600;"><i class="fas fa-check-circle" style="color:#10b981"></i> 验证邮箱</a>
    </div>
    <p style="color:#999;font-size:13px;">如果按钮无法点击，请复制以下链接到浏览器中打开：</p>
    <p style="color:#666;font-size:12px;word-break:break-all;background:#f8f9fa;padding:10px;border-radius:8px;">{$verifyUrl}</p>
    <p style="color:#999;font-size:13px;">链接有效期30分钟，请尽快验证。</p>
    <p style="color:#999;font-size:13px;">如果您没有进行此操作，请忽略此邮件。</p>
  </div>
  <div style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #eee;">
    <p style="color:#aaa;font-size:12px;margin:0;">{$siteName}</p>
  </div>
</div>
</body>
</html>
HTML;

$result = sendMail($email, $subject, $body);

if ($result['success']) {
    jsonResponse(['success' => true, 'message' => '验证邮件已发送']);
} else {
    jsonResponse(['success' => false, 'message' => '邮件发送失败: ' . $result['message']]);
}
