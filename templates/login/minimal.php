<?php
/**
 * 登录/注册模板 - 极简白
 */
?>
<style>
body{background:#f8fafc;min-height:100vh;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
.login-card{background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.08),0 8px 32px rgba(0,0,0,0.04);width:100%;max-width:400px;padding:40px}
.input-field{background:#f1f5f9;border:1px solid #e2e8f0;color:#1e293b;border-radius:10px;padding:12px 16px;width:100%;outline:none;transition:all 0.2s;font-size:14px}
.input-field:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,0.1);background:#fff}
.input-field::placeholder{color:#94a3b8}
.btn-primary{background:#6366f1;color:#fff;border:none;border-radius:10px;padding:12px;width:100%;font-weight:600;cursor:pointer;transition:all 0.2s;font-size:15px}
.btn-primary:hover{background:#4f46e5;box-shadow:0 4px 12px rgba(99,102,241,0.3)}
.btn-secondary{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:10px;padding:10px;width:100%;font-weight:500;cursor:pointer;transition:all 0.2s;font-size:14px}
.btn-secondary:hover{background:#e2e8f0}
.tab-btn{background:none;border:none;color:#94a3b8;padding:10px 20px;cursor:pointer;font-size:14px;font-weight:500;position:relative;transition:color 0.2s}
.tab-btn.active{color:#6366f1}
.tab-btn.active::after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:32px;height:2.5px;background:#6366f1;border-radius:3px}
.guest-section{border-top:1px solid #e2e8f0;padding-top:16px;margin-top:16px}
</style>
<div class="login-card">
  <div class="text-center mb-8">
    <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl font-bold">L</div>
    <h1 class="text-xl font-bold text-gray-900" id="formTitle">用户登录</h1>
    <p class="text-gray-500 text-sm mt-1"><?=h(getSiteName($db))?></p>
  </div>
  
  <?php if ($error): ?>
  <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-5 text-sm"><?=h($error)?></div>
  <?php endif; ?>
  <?php if ($regSuccess): ?>
  <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-lg mb-5 text-sm"><?=h($regSuccess)?></div>
  <?php endif; ?>
  <?php if (isset($_GET['expired'])): ?>
  <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-5 text-sm flex items-center gap-2">⏰ 游客账号已过期，账号数据已自动删除，请重新登录或注册。</div>
  <?php endif; ?>
  
  <div class="flex justify-center mb-5 border-b border-gray-100">
    <button class="tab-btn active" onclick="switchTab('login')">登录</button>
    <button class="tab-btn" onclick="switchTab('register')">注册</button>
    <?php if ($guestMode): ?>
    <button class="tab-btn" onclick="guestLogin(event)">游客</button>
    <?php endif; ?>
  </div>
  
  <form method="POST" id="loginForm">
    <input type="hidden" name="action" value="login">
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-medium mb-1.5">账号 / 邮箱</label>
      <input type="text" name="login" class="input-field" placeholder="请输入用户名或邮箱" required>
    </div>
    <div class="mb-5">
      <label class="block text-gray-700 text-sm font-medium mb-1.5">密码</label>
      <input type="password" name="password" class="input-field" placeholder="请输入密码" required>
    </div>
    <button type="submit" class="btn-primary">登 录</button>
    <div class="mt-3 text-right">
      <a href="./forgot_password.php" class="text-indigo-500 hover:text-indigo-700 text-xs transition">忘记密码？</a>
    </div>
  </form>
  
  <form method="POST" id="registerForm" style="display:none">
    <input type="hidden" name="action" value="register">
    <div id="regStep1">
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-medium mb-1.5">用户名</label>
        <input type="text" name="reg_username" class="input-field" placeholder="2-20位，支持中文/字母/数字" required>
      </div>
      <?php if ($showEmailField): ?>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-medium mb-1.5">邮箱 <span class="text-red-500 text-xs">(必填)</span></label>
        <input type="email" name="reg_email" id="regEmail" class="input-field" placeholder="example@email.com" required>
      </div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-medium mb-1.5">密码</label>
        <input type="password" name="reg_password" class="input-field" placeholder="至少6位" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-medium mb-1.5">确认密码</label>
        <input type="password" name="reg_confirm" class="input-field" placeholder="再次输入密码" required>
      </div>
      <div class="mb-5">
        <label class="block text-gray-700 text-sm font-medium mb-1.5">个性后缀</label>
        <div class="flex items-center gap-2">
          <span class="text-gray-400 text-sm whitespace-nowrap"><?=BASE_URL?>/</span>
          <input type="text" name="reg_suffix" class="input-field flex-1" placeholder="mypage" required>
        </div>
      </div>
      <button type="button" class="btn-primary w-full" onclick="showRegStep2()">发送验证码</button>
    </div>
    <div id="regStep2" style="display:none">
      <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 mb-5 text-center">
        <p class="text-sm text-gray-600 mb-1">验证码已发送至</p>
        <p class="text-base font-semibold text-indigo-700" id="regEmailDisplay"></p>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-medium mb-1.5">邮箱验证码</label>
        <input type="text" name="reg_code" class="input-field text-center text-lg tracking-widest" placeholder="输入6位验证码" maxlength="6" required>
      </div>
      <input type="hidden" name="reg_code_session" id="regCodeSession" value="">
      <button type="submit" class="btn-primary w-full">验证并注册</button>
      <p class="text-gray-500 text-xs mt-3 text-center">未收到？<a href="#" class="text-indigo-500" onclick="resendCode();return false">重新发送</a></p>
    </div>
  </form>
  
  <div class="mt-5 text-center">
    <a href="../admin/index.php" class="text-gray-400 hover:text-indigo-500 text-xs transition">管理员登录</a>
  </div>
</div>
