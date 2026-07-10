<?php
/**
 * 登录/注册模板 - 霓虹赛博风
 */
?>
<style>
body{background:#0a0a0f;min-height:100vh;font-family:'Courier New',monospace}
.neon-card{background:rgba(10,10,20,0.85);border:1px solid rgba(0,255,255,0.25);border-radius:10px;box-shadow:0 0 30px rgba(0,255,255,0.08),inset 0 0 30px rgba(0,255,255,0.02);width:100%;max-width:420px;padding:36px;position:relative}
.neon-card::before{content:'';position:absolute;top:-1px;left:20%;right:20%;height:2px;background:linear-gradient(90deg,transparent,#00ffff,transparent);filter:blur(2px)}
.neon-card::after{content:'';position:absolute;bottom:-1px;left:20%;right:20%;height:2px;background:linear-gradient(90deg,transparent,#ff00ff,transparent);filter:blur(2px)}
.input-field{background:rgba(0,255,255,0.04);border:1px solid rgba(0,255,255,0.2);color:#e0ffff;border-radius:8px;padding:12px 16px;width:100%;outline:none;transition:all 0.3s;font-family:'Courier New',monospace;font-size:14px}
.input-field:focus{border-color:#00ffff;box-shadow:0 0 12px rgba(0,255,255,0.15),inset 0 0 12px rgba(0,255,255,0.05)}
.input-field::placeholder{color:rgba(0,255,255,0.25)}
.btn-primary{background:transparent;color:#00ffff;border:1px solid #00ffff;border-radius:8px;padding:12px;width:100%;font-weight:700;cursor:pointer;transition:all 0.3s;font-family:'Orbitron',monospace;font-size:13px;text-transform:uppercase;letter-spacing:2px}
.btn-primary:hover{background:rgba(0,255,255,0.1);box-shadow:0 0 20px rgba(0,255,255,0.2),inset 0 0 20px rgba(0,255,255,0.05)}
.btn-secondary{background:transparent;color:#ff00ff;border:1px solid rgba(255,0,255,0.3);border-radius:8px;padding:10px;width:100%;font-weight:500;cursor:pointer;transition:all 0.3s;font-family:'Courier New',monospace;font-size:12px}
.btn-secondary:hover{background:rgba(255,0,255,0.08);border-color:#ff00ff;box-shadow:0 0 15px rgba(255,0,255,0.15)}
.tab-btn{background:none;border:none;color:rgba(0,255,255,0.35);padding:10px 20px;cursor:pointer;font-size:12px;font-weight:700;position:relative;transition:all 0.3s;font-family:'Orbitron',monospace;text-transform:uppercase;letter-spacing:1px}
.tab-btn.active{color:#00ffff;text-shadow:0 0 10px rgba(0,255,255,0.5)}
.tab-btn.active::after{content:'';position:absolute;bottom:0;left:20%;right:20%;height:1px;background:#00ffff;box-shadow:0 0 8px #00ffff}
.guest-section{border-top:1px solid rgba(255,0,255,0.15);padding-top:16px;margin-top:16px}
.neon-title{font-family:'Orbitron',monospace;font-weight:900;font-size:24px;background:linear-gradient(90deg,#00ffff,#ff00ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.scan-line{position:fixed;top:0;left:0;width:100%;height:2px;background:linear-gradient(90deg,transparent,#00ffff,transparent);opacity:0.15;animation:scan 4s linear infinite;pointer-events:none;z-index:9999}
@keyframes scan{0%{top:0}100%{top:100vh}}
</style>
<div class="scan-line"></div>
<div class="neon-card">
  <div class="text-center mb-8">
    <div class="w-14 h-14 mx-auto mb-3 rounded-lg border border-cyan-400/30 flex items-center justify-center text-cyan-400 text-xl font-bold" style="box-shadow:0 0 20px rgba(0,255,255,0.1)">L</div>
    <h1 class="neon-title" id="formTitle">// LOGIN</h1>
    <p class="text-cyan-400/50 text-xs mt-1 font-mono"><?=h(getSiteName($db))?> v2.0</p>
  </div>
  
  <?php if ($error): ?>
  <div class="border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-5 text-sm font-mono" style="background:rgba(255,0,0,0.05);box-shadow:0 0 10px rgba(255,0,0,0.05)">⚠ <?=h($error)?></div>
  <?php endif; ?>
  <?php if ($regSuccess): ?>
  <div class="border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-5 text-sm font-mono" style="background:rgba(0,255,0,0.05);box-shadow:0 0 10px rgba(0,255,0,0.05)">✓ <?=h($regSuccess)?></div>
  <?php endif; ?>
  <?php if (isset($_GET['expired'])): ?>
  <div class="border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-5 text-sm font-mono flex items-center gap-2" style="background:rgba(255,0,0,0.05);box-shadow:0 0 10px rgba(255,0,0,0.05)">⏰ 游客账号已过期，数据已自动删除，请重新登录或注册。</div>
  <?php endif; ?>
  
  <div class="flex justify-center mb-5 border-b border-cyan-400/10">
    <button class="tab-btn active" onclick="switchTab('login')">Login</button>
    <button class="tab-btn" onclick="switchTab('register')">Register</button>
    <?php if ($guestMode): ?>
    <button class="tab-btn" onclick="guestLogin(event)">Guest</button>
    <?php endif; ?>
  </div>
  
  <form method="POST" id="loginForm">
    <input type="hidden" name="action" value="login">
    <div class="mb-4">
      <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> USERNAME / EMAIL</label>
      <input type="text" name="login" class="input-field" placeholder="enter credential..." required>
    </div>
    <div class="mb-5">
      <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> PASSWORD</label>
      <input type="password" name="password" class="input-field" placeholder="********" required>
    </div>
    <button type="submit" class="btn-primary">[ Login ]</button>
    <div class="mt-3 text-right">
      <a href="./forgot_password.php" class="text-fuchsia-400/50 hover:text-fuchsia-400 text-xs font-mono transition">forgot password?</a>
    </div>
  </form>
  
  <form method="POST" id="registerForm" style="display:none">
    <input type="hidden" name="action" value="register">
    <div id="regStep1">
      <div class="mb-4">
        <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> USERNAME</label>
        <input type="text" name="reg_username" class="input-field" placeholder="2-20 chars" required>
      </div>
      <?php if ($showEmailField): ?>
      <div class="mb-4">
        <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> EMAIL</label>
        <input type="email" name="reg_email" id="regEmail" class="input-field" placeholder="user@domain.com" required>
      </div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> PASSWORD</label>
        <input type="password" name="reg_password" class="input-field" placeholder="min 6 chars" required>
      </div>
      <div class="mb-4">
        <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> CONFIRM</label>
        <input type="password" name="reg_confirm" class="input-field" placeholder="confirm password" required>
      </div>
      <div class="mb-5">
        <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> SUFFIX</label>
        <div class="flex items-center gap-2">
          <span class="text-cyan-400/40 text-xs font-mono whitespace-nowrap"><?=BASE_URL?>/</span>
          <input type="text" name="reg_suffix" class="input-field flex-1" placeholder="your-page" required>
        </div>
      </div>
      <button type="button" class="btn-primary w-full" onclick="showRegStep2()">[ Send Code ]</button>
    </div>
    <div id="regStep2" style="display:none">
      <div class="border border-cyan-400/20 rounded-lg p-4 mb-5 text-center font-mono" style="background:rgba(0,255,255,0.03)">
        <p class="text-xs text-cyan-400/60 mb-1">code sent to</p>
        <p class="text-sm font-bold text-cyan-300" id="regEmailDisplay"></p>
      </div>
      <div class="mb-4">
        <label class="block text-cyan-400/70 text-xs font-mono mb-1.5">> VERIFICATION CODE</label>
        <input type="text" name="reg_code" class="input-field text-center text-lg tracking-widest" placeholder="000000" maxlength="6" required>
      </div>
      <input type="hidden" name="reg_code_session" id="regCodeSession" value="">
      <button type="submit" class="btn-primary w-full">[ Verify ]</button>
      <p class="text-cyan-400/40 text-xs mt-3 text-center font-mono">not received? <a href="#" class="text-fuchsia-400/60 hover:text-fuchsia-400" onclick="resendCode();return false">resend</a></p>
    </div>
  </form>
  
  <div class="mt-5 text-center">
    <a href="../admin/index.php" class="text-cyan-400/30 hover:text-cyan-400/60 text-xs font-mono transition">admin access</a>
  </div>
</div>
