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
            $suffix   = trim($_POST['reg_suffix'] ?? '');
            $regCode  = trim($_POST['reg_code'] ?? '');
            $codeSession = trim($_POST['reg_code_session'] ?? '');
            
            if (empty($username) || empty($password) || empty($email) || empty($suffix)) {
                $error = '请填写所有字段';
            } elseif (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$/u', $username)) {
                $error = '用户名2-20位，支持中文、字母、数字、下划线';
            } elseif (strlen($password) < 6) {
                $error = '密码至少6位';
            } elseif ($password !== $confirm) {
                $error = '两次密码不一致';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = '邮箱格式不正确';
            } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $suffix)) {
                $error = '后缀只允许字母、数字、下划线和连字符';
            } else {
                // 验证邮箱验证码
                $savedCode = $_SESSION['reg_email_code'] ?? '';
                $savedEmail = $_SESSION['reg_email_addr'] ?? '';
                $codeExpiry = $_SESSION['reg_email_expiry'] ?? 0;
                
                if (empty($regCode)) {
                    $error = '请输入邮箱验证码';
                } elseif ($email !== $savedEmail) {
                    $error = '邮箱地址与发送验证码时不一致，请重新发送';
                } elseif ($savedCode === '' || time() > $codeExpiry) {
                    $error = '验证码已过期，请重新发送';
                } elseif ((string)$regCode !== (string)$savedCode) {
                    $error = '验证码不正确';
                }
                
                if (empty($error)) {
                    // 检查用户名重复
                    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = '用户名已被注册';
                    } else {
                        // 检查邮箱重复
                        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $error = '邮箱已被注册';
                        }
                    }
                }
                
                if (empty($error)) {
                    // 检查后缀重复
                    $stmt = $db->prepare("SELECT id FROM users WHERE suffix = ?");
                    $stmt->execute([$suffix]);
                    if ($stmt->fetch()) {
                        $error = '该后缀已被其他用户使用';
                    } else {
                        // 检查是否在禁止后缀列表中
                        $bannedRaw = trim($settings['banned_suffixes'] ?? '');
                        $bannedList = $bannedRaw ? array_map('trim', explode("\n", $bannedRaw)) : [];
                        $autoBanned = ['page', 'user', 'admin', 'api', 'register', 'login', 'logout'];
                        $bannedList = array_merge($bannedList, $autoBanned);
                        $bannedList = array_unique(array_filter($bannedList));
                        if (in_array($suffix, $bannedList)) {
                            $error = '该后缀已被系统保留，请换一个';
                        }
                    }
                }
                
                if (empty($error)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (username, password, nickname, suffix, email, email_verified) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$username, $hash, $username, $suffix, $email]);
                    
                    // 清除验证码session
                    unset($_SESSION['reg_email_code'], $_SESSION['reg_email_addr'], $_SESSION['reg_email_expiry']);
                    
                    $regSuccess = '注册成功，请登录！';
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
function showRegStep2(){
  var username = document.querySelector('input[name="reg_username"]').value;
  var email = document.querySelector('input[name="reg_email"]').value;
  var password = document.querySelector('input[name="reg_password"]').value;
  var confirm = document.querySelector('input[name="reg_confirm"]').value;
  var suffix = document.querySelector('input[name="reg_suffix"]').value;
  
  if(!username || !email || !password || !confirm || !suffix){
    alert('请填写所有注册信息');
    return;
  }
  if(password.length < 6){
    alert('密码至少6位');
    return;
  }
  if(password !== confirm){
    alert('两次密码不一致');
    return;
  }
  if(!/^[a-zA-Z0-9_-]+$/.test(suffix)){
    alert('后缀只允许字母、数字、下划线和连字符');
    return;
  }
  
  // 发送验证码
  var btn = document.querySelector('#regStep1 button');
  btn.disabled = true;
  btn.textContent = '发送中...';
  
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(){
    btn.disabled = false;
    btn.textContent = '发送验证码';
    try{
      var res = JSON.parse(xhr.responseText);
      if(res.success){
        document.getElementById('regEmailDisplay').textContent = email;
        document.getElementById('regCodeSession').value = Math.random().toString(36);
        document.getElementById('regStep1').style.display = 'none';
        document.getElementById('regStep2').style.display = 'block';
      } else {
        alert(res.message || '发送失败，请重试');
      }
    }catch(e){
      alert('验证码发送失败，请重试');
    }
  };
  xhr.send('action=send_register_code&email='+encodeURIComponent(email)+'&username='+encodeURIComponent(username));
}

function resendCode(){
  var email = document.querySelector('input[name="reg_email"]').value;
  var username = document.querySelector('input[name="reg_username"]').value;
  var btn = event.target;
  btn.textContent = '发送中...';
  btn.style.pointerEvents = 'none';
  
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(){
    btn.textContent = '重新发送';
    btn.style.pointerEvents = 'auto';
    try{
      var res = JSON.parse(xhr.responseText);
      if(res.success){
        alert('验证码已重新发送');
      } else {
        alert(res.message || '发送失败');
      }
    }catch(e){
      alert('发送失败');
    }
  };
  xhr.send('action=send_register_code&email='+encodeURIComponent(email)+'&username='+encodeURIComponent(username));
}

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
    
    <!-- 注册表单（两步：1.填写信息，2.验证邮箱） -->
    <form method="POST" id="registerForm" style="display:none">
      <input type="hidden" name="action" value="register">
      
      <div id="regStep1">
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium mb-2">用户名</label>
          <input type="text" name="reg_username" class="input-field" placeholder="2-20位，支持中文/字母/数字" pattern="^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$" required>
        </div>
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium mb-2">邮箱 <span class="text-red-400 text-xs">(必填，用于登录和找回密码)</span></label>
          <input type="email" name="reg_email" id="regEmail" class="input-field" placeholder="example@email.com" required>
        </div>
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium mb-2">密码</label>
          <input type="password" name="reg_password" class="input-field" placeholder="至少6位" required>
        </div>
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium mb-2">确认密码</label>
          <input type="password" name="reg_confirm" class="input-field" placeholder="再次输入密码" required>
        </div>
        <div class="mb-5">
          <label class="block text-gray-300 text-sm font-medium mb-2">个性后缀 <span class="text-gray-500 text-xs font-normal">(访问你主页的短链)</span></label>
          <div class="flex items-center gap-2">
            <span class="text-gray-500 text-sm whitespace-nowrap"><?=BASE_URL?>/</span>
            <input type="text" name="reg_suffix" class="input-field flex-1" placeholder="mypage" pattern="[a-zA-Z0-9_-]+" title="只允许字母、数字、下划线和连字符" required>
          </div>
          <p class="text-gray-500 text-xs mt-1">设置后可通过短链直接访问你的主页</p>
        </div>
        <button type="button" class="btn-primary w-full" onclick="showRegStep2()">发送验证码</button>
      </div>
      
      <div id="regStep2" style="display:none">
        <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4 mb-5 text-center">
          <p class="text-sm text-gray-300 mb-1">验证码已发送至</p>
          <p class="text-base font-semibold text-white" id="regEmailDisplay"></p>
        </div>
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium mb-2">邮箱验证码</label>
          <input type="text" name="reg_code" class="input-field text-center text-lg tracking-widest" placeholder="输入6位验证码" maxlength="6" required autocomplete="off">
        </div>
        <input type="hidden" name="reg_code_session" id="regCodeSession" value="">
        <button type="submit" class="btn-primary w-full">验证并注册</button>
        <p class="text-gray-500 text-xs mt-3 text-center">未收到？<a href="#" class="text-indigo-400" onclick="resendCode();return false">重新发送</a></p>
      </div>
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
