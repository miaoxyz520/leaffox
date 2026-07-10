<?php
/**
 * 用户密码找回 - 输入邮箱发送重置链接
 */
require_once __DIR__ . '/../config.php';

if (!$db) { header("Location: ../install.php"); exit; }

if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_login'])) {
    redirect('./dashboard.php');
}

$settings = getSettings($db);
$error = '';
$success = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // 速率限制
    $rateCheck = checkRateLimit('forgot_pwd');
    if (!$rateCheck['allowed']) {
        $error = $rateCheck['message'];
    } else {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '请输入有效的邮箱地址';
    } else {
        // 检查邮箱是否存在
        $stmt = $db->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = '该邮箱未注册，请检查输入';
        } elseif (!($settings['smtp_host'] ?? '') || !($settings['smtp_user'] ?? '') || !($settings['smtp_pass'] ?? '')) {
            $error = '系统未配置邮件服务，请联系管理员重置密码';
        } else {
            // 60秒防刷
            $stmt = $db->prepare("SELECT id FROM password_resets WHERE email = ? AND used = 0 AND expires_at > ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$email, date('Y-m-d H:i:s')]);
            $existing = $stmt->fetch();
            if ($existing) {
                $error = '已发送重置邮件，请检查收件箱（30分钟内有效）';
            } else {
                // 生成令牌
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 1800); // 30分钟有效
                
                $stmt = $db->prepare("INSERT INTO password_resets (email, token, used, expires_at) VALUES (?, ?, 0, ?)");
                $stmt->execute([$email, $token, $expires]);
                
                // 发送重置邮件
                require_once __DIR__ . '/../api/mail.php';
                $resetUrl = BASE_URL . '/api/reset_password.php?token=' . urlencode($token);
                $siteName = h($settings['site_name'] ?? 'Leaffox主页系统');
                $username = h($user['username'] ?? '');
                $subject = "重置密码 - {$siteName}";
                
                $body = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>密码重置</title></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);">
  <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:22px;"><i class="fas fa-key"></i> 重置密码</h1>
  </div>
  <div style="padding:35px 30px;">
    <p style="color:#333;font-size:15px;line-height:1.7;">您好，<strong>{$username}</strong>！</p>
    <p style="color:#333;font-size:15px;line-height:1.7;">您正在 <strong>{$siteName}</strong> 申请重置密码，请点击下方按钮完成重置：</p>
    <div style="text-align:center;margin:30px 0;">
      <a href="{$resetUrl}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:14px 40px;border-radius:30px;text-decoration:none;font-size:16px;font-weight:600;"><i class="fas fa-key"></i> 重置密码</a>
    </div>
    <p style="color:#999;font-size:13px;">如果按钮无法点击，请复制以下链接到浏览器中打开：</p>
    <p style="color:#666;font-size:12px;word-break:break-all;background:#f8f9fa;padding:10px;border-radius:8px;">{$resetUrl}</p>
    <p style="color:#999;font-size:13px;">链接有效期30分钟，请尽快操作。</p>
    <p style="color:#999;font-size:13px;">如果您没有申请重置密码，请忽略此邮件。</p>
  </div>
  <div style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #eee;">
    <p style="color:#aaa;font-size:12px;margin:0;">{$siteName}</p>
  </div>
</div>
</body></html>
HTML;
                
                $mailResult = sendMail($email, $subject, $body);
                if ($mailResult['success']) {
                    recordRateLimit('forgot_pwd');
                    $success = '重置链接已发送至您的邮箱，请查收（30分钟内有效）。';
                } else {
                    $error = '邮件发送失败: ' . h($mailResult['message']);
                }
            }
        }
    }
    } // 速率限制结束
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $sn = getSiteName($db ?? null); ?><title>忘记密码 - <?=h($sn)?></title>
<link rel="stylesheet" href="../assets/css/tailwind.css">
<style>
body{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);min-height:100vh}
.glass-card{background:rgba(255,255,255,0.05);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.1);border-radius:12px}
.input-field{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff;border-radius:12px;padding:14px 18px;width:100%;outline:none;transition:all 0.3s}
.input-field:focus{border-color:#818cf8;box-shadow:0 0 0 3px rgba(129,140,248,0.2)}
.input-field::placeholder{color:rgba(255,255,255,0.35)}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:14px;width:100%;font-weight:600;cursor:pointer;transition:all 0.3s}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(99,102,241,0.35)}
</style>
</head>
<body class="flex items-center justify-center p-4 min-h-screen">
  <div class="glass-card w-full max-w-md p-8">
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">L</div>
      <h1 class="text-2xl font-bold text-white">忘记密码</h1>
      <p class="text-gray-400 text-sm mt-1">输入注册邮箱，我们将发送重置链接</p>
    </div>
    
    <?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($error)?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($success)?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="mb-6">
        <label class="block text-gray-300 text-sm font-medium mb-2">注册邮箱</label>
        <input type="email" name="email" class="input-field" placeholder="请输入注册时使用的邮箱" required>
      </div>
      <button type="submit" class="btn-primary">发送重置链接</button>
    </form>
    
    <div class="mt-6 text-center space-y-2">
      <a href="./index.php" class="text-gray-400 hover:text-indigo-400 text-sm transition block">← 返回登录</a>
      <a href="../admin/index.php" class="text-gray-500 hover:text-indigo-400 text-xs transition block">管理员登录</a>
    </div>
  </div>
</body>
</html>
