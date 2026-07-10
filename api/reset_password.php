<?php
/**
 * 密码重置页面（用户点击邮件中的链接后到达此处）
 * 展示新密码输入表单，提交后更新密码
 */
require_once __DIR__ . '/../config.php';

if (!$db) { header("Location: ../install.php"); exit; }

$settings = getSettings($db);
$error = '';
$success = '';
$token = trim($_GET['token'] ?? '');
$showForm = false;

// 验证令牌
if (empty($token)) {
    $error = '无效的链接';
} else {
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > ? LIMIT 1");
    $stmt->execute([$token, date('Y-m-d H:i:s')]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $error = '链接已过期或已使用，请重新申请';
    } else {
        $showForm = true;
    }
}

// 处理密码重置提交
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $showForm) {
    // 速率限制
    $rateCheck = checkRateLimit('reset_pwd');
    if (!$rateCheck['allowed']) {
        $error = $rateCheck['message'];
    } else {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    
    if (strlen($password) < 6) {
        $error = '密码至少6位';
    } elseif ($password !== $confirm) {
        $error = '两次密码不一致';
    } else {
        // 查找对应用户
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$reset['email']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = '用户不存在';
        } else {
            // 更新密码
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $user['id'] ?? 0]);
            
            // 标记令牌已使用
            $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->execute([$reset['id']]);
            
            $success = '密码重置成功！请用新密码登录。';
            recordRateLimit('reset_pwd');
            $showForm = false;
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
<?php $sn = getSiteName($db ?? null); ?><title>重置密码 - <?=h($sn)?></title>
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
      <h1 class="text-2xl font-bold text-white">设置新密码</h1>
      <p class="text-gray-400 text-sm mt-1">请输入您的新密码</p>
    </div>
    
    <?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($error)?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($success)?></div>
    <?php endif; ?>
    
    <?php if ($showForm): ?>
    <form method="POST">
      <div class="mb-5">
        <label class="block text-gray-300 text-sm font-medium mb-2">新密码</label>
        <input type="password" name="password" class="input-field" placeholder="至少6位" minlength="6" required>
      </div>
      <div class="mb-6">
        <label class="block text-gray-300 text-sm font-medium mb-2">确认新密码</label>
        <input type="password" name="confirm" class="input-field" placeholder="再次输入新密码" minlength="6" required>
      </div>
      <button type="submit" class="btn-primary">重置密码</button>
    </form>
    <?php endif; ?>
    
    <div class="mt-6 text-center space-y-2">
      <a href="../user/index.php" class="text-gray-400 hover:text-indigo-400 text-sm transition block">← 返回登录</a>
    </div>
  </div>
</body>
</html>
