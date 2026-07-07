<?php
/**
 * 普通用户 - 登录/注册页（支持邮箱注册与登录）
 */
require_once __DIR__ . '/../config.php';

// 数据库未连接时跳转安装向导
if (!$db) { header("Location: ../install.php"); exit; }

if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_login'])) {
    redirect('./dashboard.php');
}

$settings = getSettings($db);
$error = '';
$regSuccess = '';
$showEmailField = ($settings['reg_email_verify'] ?? 0) || ($settings['user_email_login'] ?? 1);

// 登录处理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'login') {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($login) || empty($password)) {
            $error = '请输入账号/邮箱和密码';
        } else {
            // 判断是邮箱还是用户名
            if (filter_var($login, FILTER_VALIDATE_EMAIL) && ($settings['user_email_login'] ?? 1)) {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$login]);
            } else {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$login]);
            }
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = '账号不存在';
            } elseif (!($user['is_active'] ?? 1)) {
                $error = '账号已被封禁，请联系管理员';
            } elseif (password_verify($password, $user['password'])) {
                // 检查是否需要邮箱验证
                if (($settings['reg_email_verify'] ?? 0) && !empty($user['email']) && !($user['email_verified'] ?? 0)) {
                    $error = '请先验证邮箱后再登录，请前往个人设置绑定并验证邮箱';
                } else {
                    $_SESSION['user_id']    = (int)$user['id'];
                    $_SESSION['user_name']  = $user['nickname'] ?: $user['username'];
                    $_SESSION['user_login'] = true;
                    
                    $stmt = $db->prepare("UPDATE users SET last_ip = ?, last_login = ? WHERE id = ?");
                    $stmt->execute([getClientIP(), date('Y-m-d H:i:s'), $user['id']]);
                    
                    redirect('./dashboard.php');
                }
            } else {
                $error = '密码错误';
            }
        }
    } elseif ($action === 'register') {
        if (!$settings['reg_enabled']) {
            $error = '系统暂未开放注册';
        } else {
            $username = trim($_POST['reg_username'] ?? '');
            $password = $_POST['reg_password'] ?? '';
            $confirm  = $_POST['reg_confirm'] ?? '';
            $email    = trim($_POST['reg_email'] ?? '');
            
            if (empty($username) || empty($password)) {
                $error = '请填写所有字段';
            } elseif (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$/u', $username)) {
                $error = '用户名2-20位，支持中文、字母、数字、下划线';
            } elseif (strlen($password) < 6) {
                $error = '密码至少6位';
            } elseif ($password !== $confirm) {
                $error = '两次密码不一致';
            } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = '邮箱格式不正确';
            } else {
                // 检查用户名重复
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = '用户名已被注册';
                } elseif (!empty($email)) {
                    // 检查邮箱重复
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = '邮箱已被注册';
                    }
                }
                
                if (empty($error)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $suffix = 'u' . bin2hex(random_bytes(6));
                    
                    if (!empty($email) && ($settings['reg_email_verify'] ?? 0)) {
                        // 需要邮箱验证 → 先创建未验证账号
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', time() + 1800);
                        $stmt = $db->prepare("INSERT INTO users (username, password, nickname, suffix, email, email_verified, email_verify_token, verify_token_expires) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
                        $stmt->execute([$username, $hash, $username, $suffix, $email, $token, $expires]);
                        $userId = $db->lastInsertId();
                        
                        // 发送验证邮件
                        require_once __DIR__ . '/../api/mail.php';
                        $verifyUrl = BASE_URL . '/api/verify_email.php?token=' . urlencode($token) . '&uid=' . $userId;
                        $siteName = h($settings['site_name'] ?? 'Leaffox主页系统');
                        $subject = "验证您的邮箱 - {$siteName}";
                        $body = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>邮箱验证</title></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:sans-serif;">
<div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);">
  <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:22px;">📧 欢迎注册 {$siteName}</h1>
  </div>
  <div style="padding:35px 30px;">
    <p style="color:#333;font-size:15px;line-height:1.7;">您好，<strong>{$username}</strong>！</p>
    <p style="color:#333;font-size:15px;line-height:1.7;">请点击下方按钮验证您的邮箱地址：</p>
    <div style="text-align:center;margin:30px 0;">
      <a href="{$verifyUrl}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:14px 40px;border-radius:30px;text-decoration:none;font-size:16px;font-weight:600;">✅ 验证邮箱并激活账号</a>
    </div>
    <p style="color:#999;font-size:13px;">链接有效期30分钟，请尽快验证。验证后账号即可正常使用。</p>
    <p style="color:#999;font-size:13px;">如果您没有注册，请忽略此邮件。</p>
  </div>
  <div style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #eee;">
    <p style="color:#aaa;font-size:12px;margin:0;">{$siteName}</p>
  </div>
</div>
</body></html>
HTML;
                        $mailResult = sendMail($email, $subject, $body);
                        if ($mailResult['success']) {
                            $regSuccess = '注册成功！验证邮件已发送到您的邮箱，请查收并验证。';
                        } else {
                            $regSuccess = '注册成功，但邮件发送失败(' . h($mailResult['message']) . ')。请联系管理员。';
                        }
                    } else {
                        // 不需要邮箱验证
                        $emailField = !empty($email) ? $email : '';
                        $stmt = $db->prepare("INSERT INTO users (username, password, nickname, suffix, email) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $hash, $username, $suffix, $emailField]);
                        $regSuccess = '注册成功，请登录';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $sn = getSiteName($db ?? null); ?><title>用户登录 - <?=h($sn)?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);min-height:100vh}
.glass-card{background:rgba(255,255,255,0.05);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.1);border-radius:24px}
.input-field{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff;border-radius:12px;padding:14px 18px;width:100%;outline:none;transition:all 0.3s}
.input-field:focus{border-color:#818cf8;box-shadow:0 0 0 3px rgba(129,140,248,0.2)}
.input-field::placeholder{color:rgba(255,255,255,0.35)}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:14px;width:100%;font-weight:600;cursor:pointer;transition:all 0.3s}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(99,102,241,0.35)}
.tab-btn{background:none;border:none;color:rgba(255,255,255,0.5);padding:12px 24px;cursor:pointer;font-size:15px;font-weight:500;position:relative;transition:color 0.3s}
.tab-btn.active{color:#fff}
.tab-btn.active::after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:40px;height:3px;background:linear-gradient(90deg,#6366f1,#a78bfa);border-radius:3px}
</style>
</head>
<body class="flex items-center justify-center p-4">
  <div class="glass-card w-full max-w-md p-8">
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">L</div>
      <h1 class="text-2xl font-bold text-white" id="formTitle">用户登录</h1>
      <p class="text-gray-400 text-sm mt-1"><?=h(getSiteName($db))?> 个人主页系统</p>
    </div>
    
    <?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($error)?></div>
    <?php endif; ?>
    <?php if ($regSuccess): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($regSuccess)?></div>
    <?php endif; ?>
    
    <!-- 选项卡 -->
    <div class="flex justify-center mb-6 border-b border-white/10">
      <button class="tab-btn active" onclick="switchTab('login')">登录</button>
      <button class="tab-btn" onclick="switchTab('register')">注册</button>
    </div>
    
    <!-- 登录表单 -->
    <form method="POST" id="loginForm">
      <input type="hidden" name="action" value="login">
      <div class="mb-5">
        <label class="block text-gray-300 text-sm font-medium mb-2">账号 / 邮箱</label>
        <input type="text" name="login" class="input-field" placeholder="请输入用户名或邮箱" required>
      </div>
      <div class="mb-6">
        <label class="block text-gray-300 text-sm font-medium mb-2">密码</label>
        <input type="password" name="password" class="input-field" placeholder="请输入密码" required>
      </div>
      <button type="submit" class="btn-primary">登 录</button>
      <div class="mt-3 text-right">
        <a href="./forgot_password.php" class="text-gray-500 hover:text-indigo-400 text-xs transition">忘记密码？</a>
      </div>
    </form>
    
    <!-- 注册表单 -->
    <form method="POST" id="registerForm" style="display:none">
      <input type="hidden" name="action" value="register">
      <div class="mb-5">
        <label class="block text-gray-300 text-sm font-medium mb-2">用户名</label>
        <input type="text" name="reg_username" class="input-field" placeholder="2-20位，支持中文/字母/数字" pattern="^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$">
      </div>
      <div class="mb-5">
        <label class="block text-gray-300 text-sm font-medium mb-2">邮箱 <span class="text-gray-500 text-xs">(选填，用于登录和找回密码)</span></label>
        <input type="email" name="reg_email" class="input-field" placeholder="example@email.com">
      </div>
      <div class="mb-5">
        <label class="block text-gray-300 text-sm font-medium mb-2">密码</label>
        <input type="password" name="reg_password" class="input-field" placeholder="至少6位">
      </div>
      <div class="mb-6">
        <label class="block text-gray-300 text-sm font-medium mb-2">确认密码</label>
        <input type="password" name="reg_confirm" class="input-field" placeholder="再次输入密码">
      </div>
      <button type="submit" class="btn-primary">注 册</button>
      <?php if ($settings['reg_email_verify'] ?? 0): ?>
      <p class="text-gray-500 text-xs mt-3 text-center">注册后需验证邮箱方可登录</p>
      <?php endif; ?>
    </form>
    
    <div class="mt-6 text-center">
      <a href="../admin/index.php" class="text-gray-400 hover:text-indigo-400 text-sm transition">← 管理员登录</a>
    </div>
  </div>

<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
  document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
  document.getElementById('formTitle').textContent = tab === 'login' ? '用户登录' : '用户注册';
  if (event && event.currentTarget) event.currentTarget.classList.add('active');
}
</script>
</body>
</html>
