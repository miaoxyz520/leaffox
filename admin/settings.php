<?php
/**
 * 管理员 - 全站设置
 */
$settings = getSettings($db);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['site_name', 'site_logo', 'site_desc', 'icp_record', 'announcement', 'powered_by_name', 'banned_suffixes'];
    $setParts = []; $params = [];
    foreach ($fields as $f) {
        $setParts[] = "$f = ?";
        $params[] = trim($_POST[$f] ?? '');
    }
    $setParts[] = "reg_enabled = ?";        $params[] = (int)(!empty($_POST['reg_enabled']));
    $setParts[] = "reg_invite = ?";         $params[] = (int)(!empty($_POST['reg_invite']));
    $setParts[] = "powered_by_enabled = ?"; $params[] = (int)(!empty($_POST['powered_by_enabled']));
    // 邮箱相关
    $setParts[] = "reg_email_verify = ?";   $params[] = (int)(!empty($_POST['reg_email_verify']));
    $setParts[] = "user_email_login = ?";   $params[] = (int)(!empty($_POST['user_email_login']));
    $setParts[] = "admin_email_login = ?";  $params[] = (int)(!empty($_POST['admin_email_login']));
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
    $params[] = 1;
    
    $sql = "UPDATE settings SET " . implode(', ', $setParts) . " WHERE id = ?";
    $db->prepare($sql)->execute($params);
    adminLog($db, '更新全站设置', 'setting', 1);
    $msg = '设置已保存';
    $settings = getSettings($db); // 刷新
}
?>
<h1 class="text-2xl font-bold mb-2">全站设置</h1>
<p class="text-gray-500 mb-6">管理网站基本配置</p>

<?php if ($msg): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($msg)?></div>
<?php endif; ?>

<form method="POST" class="space-y-6 max-w-2xl">
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-globe text-indigo-400"></i> 基本信息</h3>
    
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">网站名称</label>
      <input type="text" name="site_name" value="<?=h($settings['site_name'])?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
    </div>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">网站LOGO URL</label>
      <input type="text" name="site_logo" value="<?=h($settings['site_logo'])?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="留空使用默认">
    </div>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">网站描述</label>
      <input type="text" name="site_desc" value="<?=h($settings['site_desc'])?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
    </div>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">ICP备案号</label>
      <input type="text" name="icp_record" value="<?=h($settings['icp_record'])?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50" placeholder="如: 京ICP备xxxxxx号">
    </div>
  </div>

  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-user-plus text-indigo-400"></i> 注册设置</h3>
    
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="reg_enabled" value="1" <?=$settings['reg_enabled']?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">允许新用户注册</span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="reg_email_verify" value="1" <?=($settings['reg_email_verify']??0)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">注册验证码 <span class="text-gray-500">(邮箱必填，注册时输入验证码，需配置SMTP)</span></span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="reg_invite" value="1" <?=$settings['reg_invite']?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">注册需要邀请码 <span class="text-gray-500">(暂未实现)</span></span>
    </label>
  </div>

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
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">管理员邮箱</label>
      <input type="email" name="admin_email" value="<?=h($settings['admin_email']??'')?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 max-w-sm" placeholder="admin@example.com">
    </div>
  </div>

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
  </div>

  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-file-code text-purple-400"></i> 邮件模版</h3>
    <p class="text-gray-500 text-xs -mt-2">自定义发送的邮件HTML内容，留空则使用系统默认模版。支持 HTML 标签和以下变量：</p>
    
    <!-- 变量说明 -->
    <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-4 overflow-x-auto">
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

    <!-- 模版标签切换 -->
    <div class="flex gap-2 flex-wrap">
      <button type="button" class="email-tpl-tab active" data-tpl="register" onclick="switchEmailTpl('register')">注册验证码</button>
      <button type="button" class="email-tpl-tab" data-tpl="resetpwd" onclick="switchEmailTpl('resetpwd')">重置密码</button>
      <button type="button" class="email-tpl-tab" data-tpl="verify" onclick="switchEmailTpl('verify')">邮箱验证</button>
    </div>

    <!-- 注册模版 -->
    <div class="email-tpl-editor" id="tplEditorRegister">
      <label class="block text-gray-300 text-sm font-medium mb-2">注册验证码邮件模版</label>
      <textarea name="email_tpl_register" rows="14" class="w-full bg-[#1e1e2e] text-green-300 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-indigo-500/50 font-mono text-xs leading-relaxed" placeholder="留空使用默认模版"><?=h($settings['email_tpl_register']??'')?></textarea>
      <p class="text-gray-500 text-xs mt-1">可用变量：<code class="text-indigo-300 bg-white/5 px-1 rounded">{username}</code> <code class="text-indigo-300 bg-white/5 px-1 rounded">{code}</code> <code class="text-indigo-300 bg-white/5 px-1 rounded">{site_name}</code></p>
    </div>

    <!-- 重置密码模版 -->
    <div class="email-tpl-editor" id="tplEditorResetpwd" style="display:none">
      <label class="block text-gray-300 text-sm font-medium mb-2">重置密码邮件模版</label>
      <textarea name="email_tpl_resetpwd" rows="14" class="w-full bg-[#1e1e2e] text-green-300 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-indigo-500/50 font-mono text-xs leading-relaxed" placeholder="留空使用默认模版"><?=h($settings['email_tpl_resetpwd']??'')?></textarea>
      <p class="text-gray-500 text-xs mt-1">可用变量：<code class="text-indigo-300 bg-white/5 px-1 rounded">{username}</code> <code class="text-indigo-300 bg-white/5 px-1 rounded">{reset_url}</code> <code class="text-indigo-300 bg-white/5 px-1 rounded">{site_name}</code></p>
    </div>

    <!-- 邮箱验证模版 -->
    <div class="email-tpl-editor" id="tplEditorVerify" style="display:none">
      <label class="block text-gray-300 text-sm font-medium mb-2">邮箱验证邮件模版</label>
      <textarea name="email_tpl_verify" rows="14" class="w-full bg-[#1e1e2e] text-green-300 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-indigo-500/50 font-mono text-xs leading-relaxed" placeholder="留空使用默认模版"><?=h($settings['email_tpl_verify']??'')?></textarea>
      <p class="text-gray-500 text-xs mt-1">可用变量：<code class="text-indigo-300 bg-white/5 px-1 rounded">{username}</code> <code class="text-indigo-300 bg-white/5 px-1 rounded">{verify_url}</code> <code class="text-indigo-300 bg-white/5 px-1 rounded">{site_name}</code></p>
    </div>

    <style>
      .email-tpl-tab{
        padding:6px 16px;border-radius:8px;font-size:12px;font-weight:500;
        background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);
        color:rgba(255,255,255,0.5);cursor:pointer;transition:all 0.2s
      }
      .email-tpl-tab:hover{background:rgba(255,255,255,0.08);color:#fff}
      .email-tpl-tab.active{background:rgba(99,102,241,0.2);border-color:rgba(99,102,241,0.3);color:#a5b4fc}
    </style>
    <script>
      function switchEmailTpl(type){
        document.querySelectorAll('.email-tpl-tab').forEach(function(t){t.classList.remove('active')});
        document.querySelector('.email-tpl-tab[data-tpl="'+type+'"]').classList.add('active');
        document.querySelectorAll('.email-tpl-editor').forEach(function(e){e.style.display='none'});
        document.getElementById('tplEditor'+type.charAt(0).toUpperCase()+type.slice(1)).style.display='block';
      }
    </script>
  </div>

  
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-bolt text-yellow-400"></i> 底部版权信息</h3>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="powered_by_enabled" value="1" <?=($settings['powered_by_enabled']??1)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm"><i class="fas fa-sparkles"></i> 在用户主页底部显示系统署名</span>
    </label>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">系统名称</label>
      <input type="text" name="powered_by_name" value="<?=h($settings['powered_by_name']??getPoweredBy($db))?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 max-w-xs" placeholder="Leaffox主页系统">
      <p class="text-gray-500 text-xs mt-1">用户页面底部显示：本页面由 <strong>XXX</strong> 提供创建服务（带流光动画<i class="fas fa-sparkles"></i>）</p>
    </div>
  </div>

  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-magic text-purple-400"></i> 推广设置</h3>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="show_free_make_btn" value="1" <?=(($settings['show_free_make_btn']??1)?'checked':'')?> class="w-4 h-4 accent-purple-500">
      <span class="text-gray-300 text-sm"><i class="fas fa-sparkles"></i> 在用户主页底部显示「免费制作同款聚合页」悬浮按钮</span>
    </label>
    <p class="text-gray-500 text-xs mt-1 ml-7">关闭后所有用户的专属主页上将不再显示推广悬浮按钮</p>
  </div>

  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-paint-roller text-pink-400"></i> 系统首页模版</h3>
    <p class="text-gray-500 text-xs -mt-3">选择首页（/）的展示风格，所有访客可见</p>
    <?php
    $templateDir = __DIR__ . '/../templates/landing/';
    $templates = [];
    if (is_dir($templateDir)) {
        $files = glob($templateDir . '*.php');
        foreach ($files as $f) {
            $name = basename($f, '.php');
            $label = [
                'default'  => 'Default · 深色典雅',
                'minimal'  => 'Minimal · 极简明亮',
                'gradient' => 'Gradient · 绚丽渐变',
                'glass'    => 'Glass · 毛玻璃赛博',
                'tech'     => 'Tech · 暗色科技风',
                'aurora'   => 'Aurora · 极光星际',
                'brutalist'=> 'Brutalist · 粗野主义',
                'sakura'   => 'Sakura · 樱花和风',
                'geometric'=> 'Geometric · 几何动态',
                'mono'     => 'Mono · 黑白杂志',
            ];
            $templates[$name] = $label[$name] ?? $name;
        }
    }
    $current = $settings['site_template'] ?? 'default';
    ?>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
      <?php foreach ($templates as $key => $label): ?>
      <label class="cursor-pointer <?=$key===$current?'ring-2 ring-indigo-500 ring-offset-2 ring-offset-[#0f172a]':''?> bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:bg-white/10 transition <?=$key===$current?'border-indigo-500/50':''?>" onclick="this.querySelector('input[type=radio]').checked=true;this.closest('.grid').querySelectorAll('label').forEach(l=>{l.classList.remove('ring-2','ring-indigo-500','ring-offset-2','border-indigo-500/50');});this.classList.add('ring-2','ring-indigo-500','ring-offset-2','border-indigo-500/50');">
        <input type="radio" name="site_template" value="<?=$key?>" <?=$key===$current?'checked':''?> class="hidden">
        <div class="text-2xl mb-2">
          <?php
          $icons = ['default'=>'<i class="fas fa-moon"></i>','minimal'=>'<i class="fas fa-sun"></i>','gradient'=>'<i class="fas fa-rainbow"></i>','glass'=>'<i class="fas fa-window-restore"></i>','tech'=>'<i class="fas fa-laptop"></i>','aurora'=>'<i class="fas fa-stars"></i>','brutalist'=>'<i class="fas fa-square"></i>','sakura'=>'<i class="fas fa-flower"></i>','geometric'=>'<i class="fas fa-diamond"></i>','mono'=>'<i class="fas fa-square"></i>'];
          echo $icons[$key] ?? '<i class="fas fa-file-alt"></i>';
          ?>
        </div>
        <div class="text-sm font-medium text-white"><?=h($label)?></div>
        <div class="text-xs text-gray-500 mt-1">/templates/landing/<?=$key?>.php</div>
      </label>
      <?php endforeach; ?>
    </div>
    <p class="text-gray-500 text-xs mt-3"><i class="fas fa-lightbulb"></i> 模版文件位于 <code class="text-indigo-300 bg-white/5 px-1 rounded">/templates/landing/</code> 目录，可自行添加新模版</p>
  </div>

  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-bullhorn text-indigo-400"></i> 全站公告</h3>
    <textarea name="announcement" rows="4" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 resize-none"><?=h($settings['announcement'] ?? '')?></textarea>
    <p class="text-gray-500 text-xs">支持HTML标签</p>
  </div>

  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-ban text-red-400"></i> 禁止使用的后缀</h3>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">保留后缀列表 <span class="text-gray-500 font-normal">(一行一个，用户不得使用)</span></label>
      <textarea name="banned_suffixes" rows="6" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 resize-none font-mono text-sm" placeholder="admin&#10;user&#10;page&#10;api&#10;..."><?=h($settings['banned_suffixes'] ?? '')?></textarea>
      <p class="text-gray-500 text-xs mt-1"><i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i>️ 系统会同时自动禁止保留路径前缀: <code class="text-yellow-400 bg-white/5 px-1 rounded">page/</code> <code class="text-yellow-400 bg-white/5 px-1 rounded">user/</code> <code class="text-yellow-400 bg-white/5 px-1 rounded">admin/</code> <code class="text-yellow-400 bg-white/5 px-1 rounded">api/</code></p>
    </div>
  </div>

  <button type="submit" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-3 rounded-xl font-medium hover:shadow-lg hover:shadow-indigo-500/25 transition">保存全部设置</button>
</form>
