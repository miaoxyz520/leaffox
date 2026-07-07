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
    $setParts[] = "admin_email = ?";        $params[] = trim($_POST['admin_email'] ?? '');
    // 密码单独处理（不传空值）
    $smtp_pass = $_POST['smtp_pass'] ?? '';
    if ($smtp_pass !== '') {
        $setParts[] = "smtp_pass = ?";
        $params[] = $smtp_pass;
    }
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
      <span class="text-gray-300 text-sm">注册需要邮箱验证 <span class="text-gray-500">(需先配置下方 SMTP)</span></span>
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
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-bolt text-yellow-400"></i> 底部版权信息</h3>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="powered_by_enabled" value="1" <?=($settings['powered_by_enabled']??1)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">✨ 在用户主页底部显示系统署名</span>
    </label>
    <div>
      <label class="block text-gray-300 text-sm font-medium mb-2">系统名称</label>
      <input type="text" name="powered_by_name" value="<?=h($settings['powered_by_name']??getPoweredBy($db))?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 max-w-xs" placeholder="Leaffox主页系统">
      <p class="text-gray-500 text-xs mt-1">用户页面底部显示：本页面由 <strong>XXX</strong> 提供创建服务（带流光动画✨）</p>
    </div>
  </div>

  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5">
    <h3 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-magic text-purple-400"></i> 推广设置</h3>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="show_free_make_btn" value="1" <?=(($settings['show_free_make_btn']??1)?'checked':'')?> class="w-4 h-4 accent-purple-500">
      <span class="text-gray-300 text-sm">✨ 在用户主页底部显示「免费制作同款聚合页」悬浮按钮</span>
    </label>
    <p class="text-gray-500 text-xs mt-1 ml-7">关闭后所有用户的专属主页上将不再显示推广悬浮按钮</p>
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
      <p class="text-gray-500 text-xs mt-1">⚠️ 系统会同时自动禁止保留路径前缀: <code class="text-yellow-400 bg-white/5 px-1 rounded">page/</code> <code class="text-yellow-400 bg-white/5 px-1 rounded">user/</code> <code class="text-yellow-400 bg-white/5 px-1 rounded">admin/</code> <code class="text-yellow-400 bg-white/5 px-1 rounded">api/</code></p>
    </div>
  </div>

  <button type="submit" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-3 rounded-xl font-medium hover:shadow-lg hover:shadow-indigo-500/25 transition">保存全部设置</button>
</form>
