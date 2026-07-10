<?php
/**
 * 登录/注册模板 - 默认玻璃卡片风格
 * 变量: $error, $regSuccess, $settings, $showEmailField, $guestMode
 */
?>
<style>
body{background:linear-gradient(135deg,#0f0f1a 0%,#1a1a2e 50%,#0f0f1a 100%);min-height:100vh}
.glass-card{background:rgba(255,255,255,0.04);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.08);border-radius:10px;box-shadow:0 4px 24px rgba(0,0,0,0.2)}
.input-field{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.10);color:#e2e8f0;border-radius:8px;padding:12px 16px;width:100%;outline:none;transition:all 0.2s;font-size:14px}
.input-field:focus{border-color:#818cf8;box-shadow:0 0 0 2px rgba(129,140,248,0.15)}
.input-field::placeholder{color:rgba(255,255,255,0.25)}
.btn-primary{background:#6366f1;color:#fff;border:none;border-radius:8px;padding:12px;width:100%;font-weight:600;cursor:pointer;transition:all 0.2s;font-size:14px}
.btn-primary:hover{background:#4f46e5}
.btn-secondary{background:rgba(255,255,255,0.06);color:#a5b4fc;border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:10px;width:100%;font-weight:500;cursor:pointer;transition:all 0.2s;font-size:14px}
.btn-secondary:hover{background:rgba(255,255,255,0.10)}
.tab-btn{background:none;border:none;color:rgba(255,255,255,0.4);padding:10px 20px;cursor:pointer;font-size:14px;font-weight:500;position:relative;transition:color 0.2s}
.tab-btn.active{color:#a5b4fc}
.tab-btn.active::after{content:'';position:absolute;bottom:-1px;left:50%;transform:translateX(-50%);width:28px;height:2px;background:#6366f1;border-radius:2px}
.guest-section{border-top:1px solid rgba(255,255,255,0.06);padding-top:14px;margin-top:14px}
</style>
<div class="glass-card w-full max-w-sm p-6">
  <div class="text-center mb-8">
    <div class="w-14 h-14 mx-auto mb-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">L</div>
    <h1 class="text-xl font-bold text-white" id="formTitle">用户登录</h1>
    <p class="text-gray-400 text-sm mt-1"><?=h(getSiteName($db))?> 个人主页系统</p>
  </div>
  
  <?php if ($error): ?>
  <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-3 py-2.5 rounded-lg mb-5 text-sm"><?=h($error)?></div>
  <?php endif; ?>
  <?php if ($regSuccess): ?>
  <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-3 py-2.5 rounded-lg mb-5 text-sm"><?=h($regSuccess)?></div>
  <?php endif; ?>
  <?php if (isset($_GET['expired'])): ?>
  <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-3 py-2.5 rounded-lg mb-5 text-sm flex items-center gap-2">
    <i class="fas fa-clock flex-shrink-0"></i>
    <span>游客账号已过期，账号数据已自动删除，请重新登录或注册。</span>
  </div>
  <?php endif; ?>
  
  <!-- 选项卡 -->
  <div class="flex justify-center mb-6 border-b border-white/10">
    <button class="tab-btn active" onclick="switchTab('login')">登录</button>
    <button class="tab-btn" onclick="switchTab('register')">注册</button>
    <?php if ($guestMode): ?>
    <button class="tab-btn" onclick="guestLogin(event)">游客</button>
    <?php endif; ?>
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
    <div id="regStep1">
      <div class="mb-4">
        <label class="block text-gray-300 text-sm font-medium mb-2">用户名</label>
        <input type="text" name="reg_username" class="input-field" placeholder="2-20位，支持中文/字母/数字" pattern="^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$" required>
      </div>
      <?php if ($showEmailField): ?>
      <div class="mb-4">
        <label class="block text-gray-300 text-sm font-medium mb-2">邮箱 <span class="text-red-400 text-xs">(必填，用于登录和找回密码)</span></label>
        <input type="email" name="reg_email" id="regEmail" class="input-field" placeholder="example@email.com" required>
      </div>
      <?php endif; ?>
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
      <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-3 mb-4 text-center">
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
  
  <div class="mt-6 text-center flex justify-center gap-4">
    <a href="../admin/index.php" class="text-gray-400 hover:text-indigo-400 text-sm transition">← 管理员登录</a>
  </div>
</div>
