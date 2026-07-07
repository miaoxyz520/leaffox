<?php
/**
 * 邮箱验证回调
 * GET /api/verify_email.php?token=xxx&uid=xxx
 * 验证通过后自动跳转
 */
require_once __DIR__ . '/../config.php';

if (!$db) {
    die('系统错误');
}

$token = trim($_GET['token'] ?? '');
$uid   = (int)($_GET['uid'] ?? 0);

if (empty($token) || $uid <= 0) {
    die('参数无效');
}

$stmt = $db->prepare("SELECT id, email, email_verified, verify_token_expires FROM users WHERE id = ? AND email_verify_token = ? LIMIT 1");
$stmt->execute([$uid, $token]);
$user = $stmt->fetch();

if (!$user) {
    die('<html><body style="background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;font-family:sans-serif;"><div style="text-align:center;"><div style="font-size:48px;margin-bottom:20px;">❌</div><h2>验证链接无效</h2><p style="color:#999;">链接可能已过期或已被使用</p></div></body></html>');
}

if ($user['email_verified']) {
    // 已经验证过，直接跳转到用户页面
    $redirect = BASE_URL . '/user/dashboard.php?verified=1';
    header("Location: $redirect");
    exit;
}

// 检查是否过期
if (!empty($user['verify_token_expires'])) {
    $expires = strtotime($user['verify_token_expires']);
    if ($expires < time()) {
        die('<html><body style="background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;font-family:sans-serif;"><div style="text-align:center;"><div style="font-size:48px;margin-bottom:20px;">⏰</div><h2>验证链接已过期</h2><p style="color:#999;">请重新发送验证邮件</p></div></body></html>');
    }
}

// 标记已验证
$stmt = $db->prepare("UPDATE users SET email_verified = 1, email_verify_token = '', verify_token_expires = NULL WHERE id = ?");
$stmt->execute([$uid]);

$email = h($user['email']);

echo <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>邮箱验证成功</title>
<style>
body{background:linear-gradient(135deg,#0f172a,#1e1b4b);color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.card{background:rgba(255,255,255,0.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.1);border-radius:24px;padding:48px;text-align:center;max-width:420px;width:90%;}
.btn{display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:14px 40px;border-radius:30px;text-decoration:none;font-size:16px;font-weight:600;margin-top:24px;transition:all 0.3s;}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(99,102,241,0.35);}
</style>
</head>
<body>
<div class="card">
  <div style="font-size:64px;margin-bottom:20px;">✅</div>
  <h2 style="margin:0 0 8px;">邮箱验证成功</h2>
  <p style="color:rgba(255,255,255,0.6);margin:0 0 4px;">您的邮箱</p>
  <p style="color:#a5b4fc;font-size:18px;font-weight:500;margin:0 0 8px;">{$email}</p>
  <p style="color:rgba(255,255,255,0.5);font-size:14px;">已成功验证</p>
  <a href="../user/dashboard.php" class="btn">进入个人主页</a>
</div>
</body>
</html>
HTML;
