<?php
/**
 * 管理员 - 全站设置（优化版 v2.6）
 * CSRF防护 · 模板预览图 · 设置缓存清除 · 邮件模板预览
 */
require_once __DIR__ . '/../config.php';
requireAdmin();

$settings = getSettings($db);
$msg = '';
if (!empty($earlyPostMsg)) { $msg = $earlyPostMsg; }
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    requireCsrfToken();
    
    $fields = ['site_name', 'site_logo', 'site_desc', 'icp_record', 'announcement', 'powered_by_name', 'banned_suffixes'];
    $setParts = []; $params = [];
    foreach ($fields as $f) {
        $setParts[] = "$f = ?";
        $params[] = trim($_POST[$f] ?? '');
    }
    $setParts[] = "reg_enabled = ?";        $params[] = (int)(!empty($_POST['reg_enabled'] ?? ''));
    $setParts[] = "reg_invite = ?";         $params[] = (int)(!empty($_POST['reg_invite'] ?? ''));
    $setParts[] = "guest_mode = ?";         $params[] = (int)(!empty($_POST['guest_mode'] ?? ''));
    $setParts[] = "powered_by_enabled = ?"; $params[] = (int)(!empty($_POST['powered_by_enabled'] ?? ''));
    // 邮箱相关
    $setParts[] = "reg_email_verify = ?";   $params[] = (int)(!empty($_POST['reg_email_verify'] ?? ''));
    $setParts[] = "user_email_login = ?";   $params[] = (int)(!empty($_POST['user_email_login'] ?? ''));
    $setParts[] = "admin_email_login = ?";  $params[] = (int)(!empty($_POST['admin_email_login'] ?? ''));
    $setParts[] = "smtp_host = ?";          $params[] = trim($_POST['smtp_host'] ?? '');
    $setParts[] = "smtp_port = ?";          $params[] = (int)($_POST['smtp_port'] ?? 465);
    $setParts[] = "smtp_user = ?";          $params[] = trim($_POST['smtp_user'] ?? '');
    $setParts[] = "smtp_encrypt = ?";       $params[] = $_POST['smtp_encrypt'] ?? 'ssl';
    $setParts[] = "smtp_from_name = ?";     $params[] = trim($_POST['smtp_from_name'] ?? '');
    // 邮件模版
    $setParts[] = "email_tpl_register = ?"; $params[] = trim($_POST['email_tpl_register'] ?? '');
    $setParts[] = "email_tpl_resetpwd = ?"; $params[] = trim($_POST['email_tpl_resetpwd'] ?? '');
    $setParts[] = "email_tpl_verify = ?";   $params[] = trim($_POST['email_tpl_verify'] ?? '');
    $setParts[] = "admin_email = ?";        $params[] = trim($_POST['admin_email'] ?? '');
    // 密码单独处理（不传空值）
    $smtp_pass = $_POST['smtp_pass'] ?? '';
    if ($smtp_pass !== '') {
        $setParts[] = "smtp_pass = ?";
        $params[] = $smtp_pass;
    }
    $setParts[] = "site_template = ?";  $params[] = trim($_POST['site_template'] ?? 'default');
    $setParts[] = "login_template = ?"; $params[] = trim($_POST['login_template'] ?? 'default');
    $setParts[] = "register_template = ?"; $params[] = trim($_POST['register_template'] ?? 'default');
    $params[] = 1;
    
    // 自动迁移缺失列
    try {
        $cols = $db->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($setParts as $sp) {
            $colName = explode(' = ', $sp)[0];
            if (!in_array($colName, $cols)) {
                $safeCol = str_replace('`', '``', $colName);
                $db->exec("ALTER TABLE settings ADD COLUMN `$safeCol` TEXT DEFAULT ''");
            }
        }
    } catch (Exception $e) {}
    
    $sql = "UPDATE settings SET " . implode(', ', $setParts) . " WHERE id = ?";
    $db->prepare($sql)->execute($params);
    adminLog($db, '更新全站设置', 'setting', 1);
    
    // 清除设置缓存
    $cacheFile = __DIR__ . '/../cache/settings.cache.php';
    if (file_exists($cacheFile)) @unlink($cacheFile);
    
    $msg = '✓ 设置已保存';
    $settings = getSettings($db, true);
}

// 获取模板列表
$loginDir = __DIR__ . '/../templates/login/';
$loginTemplates = [];
if (is_dir($loginDir)) {
    $files = glob($loginDir . '*.php');
    sort($files);
    $label = [
        'default' => '默认玻璃卡片', 'neon' => '霓虹赛博', 'minimal' => '极简风格',
        'gradient' => '渐变炫彩', 'sakura' => '樱花和风'
    ];
    foreach ($files as $f) {
        $name = basename($f, '.php');
        $loginTemplates[$name] = $label[$name] ?? $name;
    }
}

// SMTP测试功能
$smtpTestResult = '';
if (!empty($_GET['test_smtp'] ?? '')) {
    require_once __DIR__ . '/../api/mail.php';
    $testEmail = $settings['admin_email'] ?? '' ?: 'test@example.com';
    $result = sendMail($testEmail, 'SMTP测试', '<h1>测试成功</h1><p>您的SMTP配置正常工作！</p>');
    $smtpTestResult = $result['success'] 
        ? '✓ 邮件发送成功！请检查 ' . h($testEmail) 
        : '✗ 发送失败：' . h($result['message']);
}
?>
<h1 class="text-2xl font-bold mb-2">全站设置</h1>
<p class="text-gray-500 mb-6">管理网站基本配置</p>

<?php if ($msg): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($msg)?></div>
<?php endif; ?>
<?php if ($smtpTestResult): ?>
<div class="bg-<?=$result['success']?'emerald':'red'?>-500/10 border border-<?=$result['success']?'emerald':'red'?>-500/30 text-<?=$result['success']?'emerald':'red'?>-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=$smtpTestResult?></div>
<?php endif; ?>

<form method="POST" class="space-y-6 max-w-2xl">
  <?php csrfField(); ?>
  
  <!-- 基本信息 -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-globe text-indigo-400"></i> 基本信息</h3>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">网站名称</label>
      <input type="text" name="site_name" value="<?=h($settings['site_name'] ?? 'Leaffox主页系统')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
    </div>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">网站LOGO URL</label>
      <input type="text" name="site_logo" value="<?=h($settings['site_logo'] ?? '')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="留空使用默认">
    </div>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">网站描述</label>
      <input type="text" name="site_desc" value="<?=h($settings['site_desc'] ?? '')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
    </div>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">ICP备案号</label>
      <input type="text" name="icp_record" value="<?=h($settings['icp_record'] ?? '')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="如: 京ICP备xxxxxx号">
    </div>
  </div>

  <!-- 注册设置 -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-user-plus text-indigo-400"></i> 注册设置</h3>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="reg_enabled" value="1" <?=($settings['reg_enabled'] ?? 1)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">允许新用户注册</span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="reg_email_verify" value="1" <?=($settings['reg_email_verify']??0)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">注册验证码 <span class="text-gray-500">(邮箱必填，注册时输入验证码，需配置SMTP)</span></span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer <?=($settings['reg_invite'] ?? 0)?'opacity-50':''?>">
      <input type="checkbox" name="reg_invite" value="1" <?=($settings['reg_invite'] ?? 0)?'checked':''?> class="w-4 h-4 accent-indigo-500" disabled>
      <span class="text-gray-300 text-sm">注册需要邀请码 <span class="text-gray-500">(即将上线)</span></span>
    </label>
  </div>

  <!-- 登录设置 -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-sign-in-alt text-green-400"></i> 登录设置</h3>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="user_email_login" value="1" <?=(($settings['user_email_login']??1)?'checked':'')?> class="w-4 h-4 accent-green-500">
      <span class="text-gray-300 text-sm">允许用户使用邮箱登录</span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="admin_email_login" value="1" <?=(($settings['admin_email_login']??0)?'checked':'')?> class="w-4 h-4 accent-green-500">
      <span class="text-gray-300 text-sm">允许管理员使用邮箱登录</span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer pt-2 border-t border-white/5">
      <input type="checkbox" name="guest_mode" value="1" <?=(($settings['guest_mode']??0)?'checked':'')?> class="w-4 h-4 accent-amber-500">
      <span class="text-gray-300 text-sm"><i class="fas fa-user-secret text-amber-400 mr-1"></i> 开启游客模式 <span class="text-gray-500">(用户可不注册直接使用，登录后可设置账号密码)</span></span>
    </label>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">管理员邮箱</label>
      <input type="email" name="admin_email" value="<?=h($settings['admin_email'] ?? '')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 max-w-sm" placeholder="admin@example.com">
    </div>
  </div>

  <!-- SMTP -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-envelope text-yellow-400"></i> 邮件发送 (SMTP)</h3>
    <p class="text-gray-500 text-xs -mt-3">配置后可用于发送验证邮件等。密码留空表示不修改。</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-gray-300 text-sm font-medium mb-2">SMTP 服务器</label>
        <input type="text" name="smtp_host" value="<?=h($settings['smtp_host']??'')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="smtp.qq.com">
      </div>
      <div>
        <label class="block text-gray-300 text-sm font-medium mb-2">端口</label>
        <input type="number" name="smtp_port" value="<?=h($settings['smtp_port']??'465')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="465">
      </div>
      <div>
        <label class="block text-gray-300 text-sm font-medium mb-2">邮箱账号</label>
        <input type="text" name="smtp_user" value="<?=h($settings['smtp_user']??'')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="your@qq.com">
      </div>
      <div>
        <label class="block text-gray-300 text-sm font-medium mb-2">邮箱密码/授权码</label>
        <input type="password" name="smtp_pass" value="" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="留空不修改" autocomplete="off">
      </div>
      <div>
        <label class="block text-gray-300 text-sm font-medium mb-2">加密方式</label>
        <select name="smtp_encrypt" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
          <option value="ssl" <?=($settings['smtp_encrypt']??'ssl')==='ssl'?'selected':''?>>SSL (端口465)</option>
          <option value="tls" <?=($settings['smtp_encrypt']??'')==='tls'?'selected':''?>>TLS/STARTTLS (端口587)</option>
        </select>
      </div>
      <div>
        <label class="block text-gray-300 text-sm font-medium mb-2">发件人名称</label>
        <input type="text" name="smtp_from_name" value="<?=h($settings['smtp_from_name']??'')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="默认为网站名称">
      </div>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">保存设置</button>
      <?php if (!empty($settings['smtp_host']) && !empty($settings['smtp_user'])): ?>
      <a href="?page=settings&test_smtp=1" class="bg-white/10 hover:bg-white/15 text-gray-300 px-4 py-2.5 rounded-xl text-sm transition" onclick="return confirm('发送测试邮件到 <?=h($settings['admin_email'] ?? ''?:'未设置管理员邮箱')?>？')">📧 测试SMTP</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- 邮件模版 -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-file-code text-purple-400"></i> 邮件模版</h3>
    <p class="text-gray-500 text-xs -mt-2">自定义发送的邮件HTML内容，留空则使用系统默认模版。支持 HTML 标签和以下变量：</p>
    <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-4">
      <div class="table-responsive">
      <table class="w-full text-xs">
        <thead><tr class="text-indigo-300 border-b border-indigo-500/10"><th class="text-left pb-2 pr-4 font-medium">变量</th><th class="text-left pb-2 pr-4 font-medium">说明</th><th class="text-left pb-2 font-medium">适用模版</th></tr></thead>
        <tbody class="text-gray-400">
          <tr><td class="py-1.5 pr-4 font-mono">{username}</td><td class="py-1.5 pr-4">用户名</td><td class="py-1.5">register, resetpwd, verify</td></tr>
          <tr><td class="py-1.5 pr-4 font-mono">{site_name}</td><td class="py-1.5 pr-4">网站名称</td><td class="py-1.5">全部</td></tr>
          <tr><td class="py-1.5 pr-4 font-mono">{code}</td><td class="py-1.5 pr-4">验证码 (6位数字)</td><td class="py-1.5">register</td></tr>
          <tr><td class="py-1.5 pr-4 font-mono">{reset_url}</td><td class="py-1.5 pr-4">重置密码链接</td><td class="py-1.5">resetpwd</td></tr>
          <tr><td class="py-1.5 pr-4 font-mono">{verify_url}</td><td class="py-1.5 pr-4">邮箱验证链接</td><td class="py-1.5">verify</td></tr>
        </tbody>
      </table>
      </div>
    </div>

    <div class="flex gap-2 flex-wrap">
      <button type="button" class="email-tpl-tab active" data-tpl="register" onclick="switchEmailTpl('register')">注册验证码</button>
      <button type="button" class="email-tpl-tab" data-tpl="resetpwd" onclick="switchEmailTpl('resetpwd')">重置密码</button>
      <button type="button" class="email-tpl-tab" data-tpl="verify" onclick="switchEmailTpl('verify')">邮箱验证</button>
    </div>

    <div class="email-tpl-editor" id="tplEditorRegister">
      <label class="block text-gray-300 text-sm font-medium mb-2">注册验证码邮件模版</label>
      <textarea name="email_tpl_register" rows="12" class="w-full bg-[var(--admin-input-bg)] text-[var(--admin-text)] border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-indigo-500/50 font-mono text-xs leading-relaxed" placeholder="留空使用默认模版"><?=h($settings['email_tpl_register']??'')?></textarea>
      <div class="flex gap-2 mt-2">
        <button type="button" onclick="previewEmailTpl('register')" class="text-xs text-indigo-400 hover:text-indigo-300 transition">👁 预览</button>
        <button type="button" onclick="resetEmailTpl('register')" class="text-xs text-gray-500 hover:text-gray-400 transition">↺ 恢复默认</button>
      </div>
    </div>

    <div class="email-tpl-editor" id="tplEditorResetpwd" style="display:none">
      <label class="block text-gray-300 text-sm font-medium mb-2">重置密码邮件模版</label>
      <textarea name="email_tpl_resetpwd" rows="12" class="w-full bg-[var(--admin-input-bg)] text-[var(--admin-text)] border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-indigo-500/50 font-mono text-xs leading-relaxed" placeholder="留空使用默认模版"><?=h($settings['email_tpl_resetpwd']??'')?></textarea>
      <div class="flex gap-2 mt-2">
        <button type="button" onclick="previewEmailTpl('resetpwd')" class="text-xs text-indigo-400 hover:text-indigo-300 transition">👁 预览</button>
        <button type="button" onclick="resetEmailTpl('resetpwd')" class="text-xs text-gray-500 hover:text-gray-400 transition">↺ 恢复默认</button>
      </div>
    </div>

    <div class="email-tpl-editor" id="tplEditorVerify" style="display:none">
      <label class="block text-gray-300 text-sm font-medium mb-2">邮箱验证邮件模版</label>
      <textarea name="email_tpl_verify" rows="12" class="w-full bg-[var(--admin-input-bg)] text-[var(--admin-text)] border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-indigo-500/50 font-mono text-xs leading-relaxed" placeholder="留空使用默认模版"><?=h($settings['email_tpl_verify']??'')?></textarea>
      <div class="flex gap-2 mt-2">
        <button type="button" onclick="previewEmailTpl('verify')" class="text-xs text-indigo-400 hover:text-indigo-300 transition">👁 预览</button>
        <button type="button" onclick="resetEmailTpl('verify')" class="text-xs text-gray-500 hover:text-gray-400 transition">↺ 恢复默认</button>
      </div>
    </div>

    <!-- 邮件预览弹窗 -->
    <div id="emailPreviewModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeEmailPreview()">
      <div style="background:var(--admin-card-bg,#1e293b);border:1px solid var(--admin-card-border);border-radius:16px;max-width:600px;width:90%;max-height:80vh;overflow:auto;" onclick="event.stopPropagation()">
        <div style="padding:16px 20px;border-bottom:1px solid var(--admin-card-border);display:flex;justify-content:space-between;align-items:center">
          <h3 style="margin:0;font-size:16px;color:var(--admin-text-primary,#fff)">邮件预览</h3>
          <button onclick="closeEmailPreview()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--admin-text-gray,#999)">&times;</button>
        </div>
        <div id="emailPreviewContent" style="padding:0;"></div>
      </div>
    </div>
  </div>

  <!-- 着陆页模版 -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-palette text-pink-400"></i> 着陆页模版</h3>
    <p class="text-gray-500 text-xs -mt-3">选择网站首页（未登录时）的展示风格</p>
    <?php
    $templateDir = __DIR__ . '/../templates/landing/';
    $templates = [];
    if (is_dir($templateDir)) {
        $files = glob($templateDir . '*.php');
        sort($files);
        $label = [
            'default' => '默认', 'glass' => '玻璃', 'gradient' => '渐变',
            'minimal' => '极简', 'tech' => '科技', 'aurora' => '极光',
            'brutalist' => '粗野', 'geometric' => '几何', 'mono' => '黑白杂志',
            'sakura' => '樱花'
        ];
        foreach ($files as $f) {
            $name = basename($f, '.php');
            $templates[$name] = $label[$name] ?? $name;
        }
    }
    $current = $settings['site_template'] ?? 'default';
    ?>
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
      <?php foreach ($templates as $key => $label): ?>
      <label class="cursor-pointer <?=$key===$current?'ring-2 ring-pink-500 ring-offset-2 ring-offset-[#0f172a]':''?> bg-white/5 border border-white/10 rounded-xl p-3 text-center hover:bg-white/10 transition" onclick="this.querySelector('input[name=site_template]').checked=true;this.closest('.grid').querySelectorAll('label[onclick]').forEach(l=>{l.classList.remove('ring-2','ring-pink-500','ring-offset-2');});this.classList.add('ring-2','ring-pink-500','ring-offset-2');">
        <input type="radio" name="site_template" value="<?=$key?>" <?=$key===$current?'checked':''?> class="hidden">
        <div class="w-full h-16 rounded-lg mb-2" style="background:linear-gradient(135deg,var(--tpl-<?=$key?>-1,#6366f1),var(--tpl-<?=$key?>-2,#8b5cf6))"></div>
        <div class="text-xs text-gray-300"><?=h($label)?></div>
      </label>
      <?php endforeach; ?>
    </div>
    <p class="text-gray-500 text-xs mt-3"><i class="fas fa-lightbulb"></i> 模版文件位于 <code class="text-indigo-300 bg-white/5 px-1 rounded">/templates/landing/</code> 目录，可自行添加新模版</p>
  </div>

  <!-- 登录/注册页模版 -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-sign-in-alt text-cyan-400"></i> 登录/注册页模版</h3>
    <p class="text-gray-500 text-xs -mt-3">选择用户登录和注册页面的展示风格</p>
    
    <?php $currentLogin = $settings['login_template'] ?? 'default'; ?>
    <?php $currentRegister = $settings['register_template'] ?? 'default'; ?>
    
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-3">登录页模板</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
        <?php foreach ($loginTemplates as $key => $label): ?>
        <label class="cursor-pointer <?=$key===$currentLogin?'ring-2 ring-cyan-500 ring-offset-2 ring-offset-[#0f172a]':''?> bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:bg-white/10 transition <?=$key===$currentLogin?'border-cyan-500/50':''?>" onclick="this.querySelector('input[name=login_template]').checked=true;this.closest('.grid').querySelectorAll('label[onclick]').forEach(l=>{l.classList.remove('ring-2','ring-cyan-500','ring-offset-2','border-cyan-500/50');});this.classList.add('ring-2','ring-cyan-500','ring-offset-2','border-cyan-500/50');">
          <input type="radio" name="login_template" value="<?=$key?>" <?=$key===$currentLogin?'checked':''?> class="hidden">
          <div class="w-full h-12 rounded-lg mb-2 flex items-center justify-center text-lg" style="background:<?php
            $colors = ['default'=>'linear-gradient(135deg,#6366f1,#8b5cf6)', 'neon'=>'#0a0a0f', 'minimal'=>'#f8fafc', 'gradient'=>'linear-gradient(135deg,#f093fb,#f5576c)', 'sakura'=>'linear-gradient(135deg,#ffe4e6,#fbcfe8)'];
            echo $colors[$key]??'#6366f1';
          ?>;<?=in_array($key,['minimal','sakura'])?'border:1px solid rgba(0,0,0,0.08)':''?>">
            <span style="<?=in_array($key,['minimal','sakura'])?'color:#333':'color:rgba(255,255,255,0.7)'?>;font-size:10px">⬡</span>
          </div>
          <div class="text-xs text-gray-300"><?=h($label)?></div>
          <div class="text-xs text-gray-500 mt-1">/templates/login/<?=$key?>.php</div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="mt-6">
      <label class="block text-gray-300 text-sm font-medium mb-3">注册页模板</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
        <?php foreach ($loginTemplates as $key => $label): ?>
        <label class="cursor-pointer <?=$key===$currentRegister?'ring-2 ring-pink-500 ring-offset-2 ring-offset-[#0f172a]':''?> bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:bg-white/10 transition <?=$key===$currentRegister?'border-pink-500/50':''?>" onclick="this.querySelector('input[name=register_template]').checked=true;this.closest('.grid').querySelectorAll('label[onclick]').forEach(l=>{l.classList.remove('ring-2','ring-pink-500','ring-offset-2','border-pink-500/50');});this.classList.add('ring-2','ring-pink-500','ring-offset-2','border-pink-500/50');">
          <input type="radio" name="register_template" value="<?=$key?>" <?=$key===$currentRegister?'checked':''?> class="hidden">
          <div class="w-full h-12 rounded-lg mb-2 flex items-center justify-center text-lg" style="background:<?php
            $rcolors = ['default'=>'linear-gradient(135deg,#6366f1,#8b5cf6)', 'neon'=>'#0a0a0f', 'minimal'=>'#f8fafc', 'gradient'=>'linear-gradient(135deg,#f093fb,#f5576c)', 'sakura'=>'linear-gradient(135deg,#ffe4e6,#fbcfe8)'];
            echo $rcolors[$key]??'#6366f1';
          ?>;<?=in_array($key,['minimal','sakura'])?'border:1px solid rgba(0,0,0,0.08)':''?>">
            <span style="<?=in_array($key,['minimal','sakura'])?'color:#333':'color:rgba(255,255,255,0.7)'?>;font-size:10px">✎</span>
          </div>
          <div class="text-xs text-gray-300"><?=h($label)?></div>
          <div class="text-xs text-gray-500 mt-1">/templates/login/<?=$key?>.php</div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    
    <p class="text-gray-500 text-xs mt-1"><i class="fas fa-lightbulb"></i> 模版文件位于 <code class="text-indigo-300 bg-white/5 px-1 rounded">/templates/login/</code> 目录</p>
  </div>

  <div class="flex gap-3">
    <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-8 py-3 rounded-xl text-sm font-medium transition-all hover:shadow-lg hover:shadow-indigo-500/25">💾 保存所有设置</button>
  </div>
</form>

<script>
function switchEmailTpl(type) {
    document.querySelectorAll('.email-tpl-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.email-tpl-editor').forEach(e => e.style.display = 'none');
    document.querySelector(`.email-tpl-tab[data-tpl="${type}"]`).classList.add('active');
    document.getElementById('tplEditor' + type.charAt(0).toUpperCase() + type.slice(1)).style.display = '';
}

function previewEmailTpl(type) {
    const textarea = document.querySelector(`textarea[name="email_tpl_${type}"]`);
    let html = textarea.value.trim();
    if (!html) {
        // 使用默认模板预览
        const titles = {register: '注册验证码', resetpwd: '重置密码', verify: '邮箱验证'};
        html = `<div style="padding:20px;text-align:center;color:#333;">
          <p style="font-size:16px;margin-bottom:20px;">${titles[type]}</p>
          <p style="color:#999;font-size:13px;">（未设置自定义模板，将使用系统默认模板）</p>
          <div style="margin:20px 0;padding:15px;background:#f3f4f6;border-radius:8px;font-size:24px;letter-spacing:4px;color:#6366f1;font-family:monospace;">123456</div>
        </div>`;
    }
    document.getElementById('emailPreviewContent').innerHTML = html;
    document.getElementById('emailPreviewModal').style.display = 'flex';
}

function closeEmailPreview() {
    document.getElementById('emailPreviewModal').style.display = 'none';
}

function resetEmailTpl(type) {
    if (!confirm('确定恢复默认模板？')) return;
    document.querySelector(`textarea[name="email_tpl_${type}"]`).value = '';
}
</script>
