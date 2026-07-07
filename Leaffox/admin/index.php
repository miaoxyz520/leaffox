<?php
/**
 * 管理员登录页（支持用户名/邮箱登录）
 */
require_once __DIR__ . '/../config.php';

// 数据库未连接时跳转安装向导
if (!$db) { header("Location: ../install.php"); exit; }

// 已登录则跳转
if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin_login'])) {
    redirect('./dashboard.php');
}

$settings = getSettings($db);
$allowEmailLogin = $settings['admin_email_login'] ?? 0;

$error = '';
$loginMode = 'username'; // username | email

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = '请输入账号/邮箱和密码';
    } else {
        // 判断是邮箱还是用户名
        if (filter_var($login, FILTER_VALIDATE_EMAIL) && $allowEmailLogin) {
            $loginMode = 'email';
            $stmt = $db->prepare("SELECT * FROM admin WHERE email = ? AND status = 1 LIMIT 1");
            $stmt->execute([$login]);
        } else {
            $stmt = $db->prepare("SELECT * FROM admin WHERE username = ? AND status = 1 LIMIT 1");
            $stmt->execute([$login]);
        }
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']    = (int)$admin['id'];
            $_SESSION['admin_name']  = $admin['nickname'] ?: $admin['username'];
            $_SESSION['admin_role']  = $admin['role'];
            $_SESSION['admin_login'] = true;
            
            // 更新登录信息
            $stmt = $db->prepare("UPDATE admin SET last_ip = ?, last_login = ? WHERE id = ?");
            $stmt->execute([getClientIP(), date('Y-m-d H:i:s'), $admin['id']]);
            
            adminLog($db, '管理员登录', 'admin', $admin['id'], "账号: {$admin['username']}");
            redirect('./dashboard.php');
        } else {
            $error = '账号或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $sn = getSiteName($db ?? null); ?><title>管理员登录 - <?=h($sn)?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);min-height:100vh}
.glass-card{background:rgba(255,255,255,0.05);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.1);border-radius:24px}
.input-field{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff;border-radius:12px;padding:14px 18px;width:100%;outline:none;transition:all 0.3s}
.input-field:focus{border-color:#818cf8;box-shadow:0 0 0 3px rgba(129,140,248,0.2)}
.input-field::placeholder{color:rgba(255,255,255,0.35)}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:14px;width:100%;font-weight:600;cursor:pointer;transition:all 0.3s}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(99,102,241,0.35)}
</style>
</head>
<body class="flex items-center justify-center p-4">
  <div class="glass-card w-full max-w-md p-8">
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">L</div>
      <h1 class="text-2xl font-bold text-white">管理员登录</h1>
      <p class="text-gray-400 text-sm mt-1"><?=h(getSiteName($db))?> 后台管理系统</p>
    </div>
    
    <?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-6 text-sm"><?=h($error)?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="mb-5">
        <label class="block text-gray-300 text-sm font-medium mb-2"><?=$allowEmailLogin?'账号 / 邮箱':'管理员账号'?></label>
        <input type="text" name="login" class="input-field" placeholder="<?=$allowEmailLogin?'请输入账号或邮箱':'请输入账号'?>" required autofocus>
      </div>
      <div class="mb-6">
        <label class="block text-gray-300 text-sm font-medium mb-2">登录密码</label>
        <input type="password" name="password" class="input-field" placeholder="请输入密码" required>
      </div>
      <button type="submit" class="btn-primary">登 录</button>
    </form>
    
    <div class="mt-6 text-center">
      <a href="../user/index.php" class="text-gray-400 hover:text-indigo-400 text-sm transition">← 普通用户登录</a>
    </div>
  </div>
</body>
</html>
