<?php
/**
 * 登录/注册模板 - 樱花和风
 */
?>
<style>
body{background:linear-gradient(180deg,#fff5f7 0%,#ffe8ec 30%,#fce4ec 60%,#f8e8f0 100%);min-height:100vh;font-family:'Noto Serif SC','STSong','SimSun',serif;position:relative;overflow-x:hidden}
body::before{content:'';position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(ellipse at 20% 50%,rgba(255,182,193,0.15) 0%,transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(255,105,180,0.08) 0%,transparent 50%);pointer-events:none;z-index:0}
.sakura-card{background:rgba(255,255,255,0.85);backdrop-filter:blur(20px);border-radius:12px;box-shadow:0 4px 30px rgba(255,105,180,0.1),0 1px 3px rgba(0,0,0,0.04);width:100%;max-width:400px;padding:36px;position:relative;z-index:1;border:1px solid rgba(255,182,193,0.3)}
.input-field{background:rgba(255,255,255,0.8);border:1.5px solid #fce4ec;color:#4a3034;border-radius:12px;padding:12px 16px;width:100%;outline:none;transition:all 0.3s;font-family:'Noto Serif SC',serif;font-size:14px}
.input-field:focus{border-color:#f48fb1;box-shadow:0 0 0 4px rgba(244,143,177,0.12)}
.input-field::placeholder{color:#d7b8be}
.btn-primary{background:linear-gradient(135deg,#f48fb1,#ec407a);color:#fff;border:none;border-radius:12px;padding:12px;width:100%;font-weight:700;cursor:pointer;transition:all 0.3s;font-family:'Noto Serif SC',serif;font-size:15px;letter-spacing:2px}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(236,64,122,0.3)}
.btn-secondary{background:rgba(244,143,177,0.08);color:#ec407a;border:1px solid #fce4ec;border-radius:12px;padding:10px;width:100%;font-weight:500;cursor:pointer;transition:all 0.2s;font-family:'Noto Serif SC',serif;font-size:14px}
.btn-secondary:hover{background:rgba(244,143,177,0.15)}
.tab-btn{background:none;border:none;color:#d7b8be;padding:10px 20px;cursor:pointer;font-size:14px;font-weight:700;position:relative;transition:color 0.2s;font-family:'Noto Serif SC',serif}
.tab-btn.active{color:#ec407a}
.tab-btn.active::after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:36px;height:2.5px;background:linear-gradient(90deg,#f48fb1,#ec407a);border-radius:3px}
.guest-section{border-top:1px solid #fce4ec;padding-top:16px;margin-top:16px}
.sakura-logo{width:56px;height:56px;margin:0 auto 12px;background:linear-gradient(135deg,#fce4ec,#f48fb1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;box-shadow:0 4px 15px rgba(244,143,177,0.3)}
/* 樱花飘落 */
.sakura-petal{position:fixed;width:12px;height:12px;background:#ffb7c5;border-radius:50% 0 50% 0;opacity:0.3;pointer-events:none;animation:fall linear infinite;z-index:0}
.sakura-petal:nth-child(1){left:10%;animation-duration:12s;animation-delay:0s}
.sakura-petal:nth-child(2){left:25%;animation-duration:15s;animation-delay:2s;width:8px;height:8px}
.sakura-petal:nth-child(3){left:45%;animation-duration:10s;animation-delay:4s}
.sakura-petal:nth-child(4){left:65%;animation-duration:14s;animation-delay:1s;width:10px;height:10px}
.sakura-petal:nth-child(5){left:80%;animation-duration:11s;animation-delay:3s;width:7px;height:7px}
.sakura-petal:nth-child(6){left:90%;animation-duration:13s;animation-delay:5s}
@keyframes fall{0%{transform:translateY(-20px) rotate(0deg);opacity:0.4}100%{transform:translateY(100vh) rotate(720deg);opacity:0}}
</style>
<div class="sakura-petal"></div><div class="sakura-petal"></div><div class="sakura-petal"></div>
<div class="sakura-petal"></div><div class="sakura-petal"></div><div class="sakura-petal"></div>
<div class="sakura-card">
  <div class="text-center mb-7">
    <div class="sakura-logo">🌸</div>
    <h1 class="text-xl font-bold text-gray-700" id="formTitle" style="letter-spacing:4px">用户登录</h1>
    <p class="text-pink-300 text-sm mt-0.5"><?=h(getSiteName($db))?></p>
  </div>
  
  <?php if ($error): ?>
  <div class="bg-red-50 border border-red-100 text-rose-500 px-4 py-3 rounded-lg mb-5 text-sm"><?=h($error)?></div>
  <?php endif; ?>
  <?php if ($regSuccess): ?>
  <div class="bg-emerald-50 border border-emerald-100 text-emerald-500 px-4 py-3 rounded-lg mb-5 text-sm"><?=h($regSuccess)?></div>
  <?php endif; ?>
  <?php if (isset($_GET['expired'])): ?>
  <div class="bg-red-50 border border-red-100 text-rose-500 px-4 py-3 rounded-lg mb-5 text-sm flex items-center gap-2">⏰ 游客账号已过期，账号数据已自动删除，请重新登录或注册。</div>
  <?php endif; ?>
  
  <div class="flex justify-center mb-5 border-b border-pink-100">
    <button class="tab-btn active" onclick="switchTab('login')">登录</button>
    <button class="tab-btn" onclick="switchTab('register')">注册</button>
    <?php if ($guestMode): ?>
    <button class="tab-btn" onclick="guestLogin(event)">游客</button>
    <?php endif; ?>
  </div>
  
  <form method="POST" id="loginForm">
    <input type="hidden" name="action" value="login">
    <div class="mb-4">
      <label class="block text-gray-600 text-sm font-medium mb-1.5">账号 / 邮箱</label>
      <input type="text" name="login" class="input-field" placeholder="请输入用户名或邮箱" required>
    </div>
    <div class="mb-5">
      <label class="block text-gray-600 text-sm font-medium mb-1.5">密码</label>
      <input type="password" name="password" class="input-field" placeholder="请输入密码" required>
    </div>
    <button type="submit" class="btn-primary">登 录</button>
    <div class="mt-3 text-right">
      <a href="./forgot_password.php" class="text-pink-300 hover:text-pink-500 text-xs transition">忘记密码？</a>
    </div>
  </form>
  
  <form method="POST" id="registerForm" style="display:none">
    <input type="hidden" name="action" value="register">
    <div id="regStep1">
      <div class="mb-4">
        <label class="block text-gray-600 text-sm font-medium mb-1.5">用户名</label>
        <input type="text" name="reg_username" class="input-field" placeholder="2-20位，支持中文/字母/数字" required>
      </div>
      <?php if ($showEmailField): ?>
      <div class="mb-4">
        <label class="block text-gray-600 text-sm font-medium mb-1.5">邮箱 <span class="text-rose-400 text-xs">(必填)</span></label>
        <input type="email" name="reg_email" id="regEmail" class="input-field" placeholder="example@email.com" required>
      </div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-gray-600 text-sm font-medium mb-1.5">密码</label>
        <input type="password" name="reg_password" class="input-field" placeholder="至少6位" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-600 text-sm font-medium mb-1.5">确认密码</label>
        <input type="password" name="reg_confirm" class="input-field" placeholder="再次输入密码" required>
      </div>
      <div class="mb-5">
        <label class="block text-gray-600 text-sm font-medium mb-1.5">个性后缀</label>
        <div class="flex items-center gap-2">
          <span class="text-pink-200 text-sm whitespace-nowrap"><?=BASE_URL?>/</span>
          <input type="text" name="reg_suffix" class="input-field flex-1" placeholder="mypage" required>
        </div>
      </div>
      <button type="button" class="btn-primary w-full" onclick="showRegStep2()">发送验证码</button>
    </div>
    <div id="regStep2" style="display:none">
      <div class="bg-pink-50 border border-pink-100 rounded-xl p-4 mb-5 text-center">
        <p class="text-sm text-gray-600 mb-1">验证码已发送至</p>
        <p class="text-base font-semibold text-pink-600" id="regEmailDisplay"></p>
      </div>
      <div class="mb-4">
        <label class="block text-gray-600 text-sm font-medium mb-1.5">邮箱验证码</label>
        <input type="text" name="reg_code" class="input-field text-center text-lg tracking-widest" placeholder="输入6位验证码" maxlength="6" required>
      </div>
      <input type="hidden" name="reg_code_session" id="regCodeSession" value="">
      <button type="submit" class="btn-primary w-full">验证并注册</button>
      <p class="text-gray-500 text-xs mt-3 text-center">未收到？<a href="#" class="text-pink-400" onclick="resendCode();return false">重新发送</a></p>
    </div>
  </form>
  
  <div class="mt-5 text-center">
    <a href="../admin/index.php" class="text-pink-200 hover:text-pink-400 text-xs transition">管理员登录</a>
  </div>
</div>
