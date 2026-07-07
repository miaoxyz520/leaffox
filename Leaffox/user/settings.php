<?php
/**
 * 用户 - 主页设置（完整版：预设 + 自定义 + 按钮 + 社交 + 公告 + 音乐 + 平台提示 + 页脚）
 */
$uid = (int)$_SESSION['user_id'];
$msg = '';

$presets = [
  ['val'=>'default','label'=>'🌙 深邃星空','bg'=>'#0f172a'],
  ['val'=>'black','label'=>'⬛ 纯黑','bg'=>'#111111'],
  ['val'=>'purple','label'=>'🟣 炫彩紫','bg'=>'#7c3aed'],
  ['val'=>'pink','label'=>'🩷 粉红','bg'=>'#be185d'],
  ['val'=>'deep','label'=>'🌊 深海','bg'=>"url('assets/img/background.png') center/cover"],
  ['val'=>'cyber','label'=>'🔷 蓝格','bg'=>'#1e293b'],
  ['val'=>'gold','label'=>'🌟 黑金','bg'=>'#1a1a2e'],
  ['val'=>'custom','label'=>'🎨 自定义','bg'=>'#0f172a'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bgPreset   = in_array($_POST['bg_preset'] ?? 'default', array_column($presets,'val')) ? $_POST['bg_preset'] : 'default';
    $bgColor    = safeSubstr(trim($_POST['bg_color'] ?? '#0f172a'), 20);
    $bgImage    = safeSubstr(trim($_POST['bg_image'] ?? ''), 255);
    $customBgType   = in_array($_POST['custom_bg_type'] ?? 'color', ['color','gradient','image']) ? $_POST['custom_bg_type'] : 'color';
    $customGradFrom = safeSubstr(trim($_POST['custom_gradient_from'] ?? '#667eea'), 10);
    $customGradTo   = safeSubstr(trim($_POST['custom_gradient_to'] ?? '#764ba2'), 10);
    $customGradDir  = safeSubstr(trim($_POST['custom_gradient_dir'] ?? '135deg'), 10);
    $themeMode  = in_array($_POST['theme_mode'] ?? 'auto', ['auto','light','dark']) ? $_POST['theme_mode'] : 'auto';
    $cardStyle  = in_array($_POST['card_style'] ?? 'glass', ['glass','neumorphism','minimal']) ? $_POST['card_style'] : 'glass';
    $showStats  = (int)(!empty($_POST['show_stats']));
    $footerText = safeSubstr(trim($_POST['footer_text'] ?? 'Powered by Leaffox主页系统'), 200);
    $footerAlign= in_array($_POST['footer_align'] ?? 'center', ['left','center','right']) ? $_POST['footer_align'] : 'center';
    $btnBg      = safeSubstr(trim($_POST['btn_bg'] ?? ''), 20);
    $btnColor   = safeSubstr(trim($_POST['btn_color'] ?? ''), 20);
    $btnOutline = safeSubstr(trim($_POST['btn_outline'] ?? ''), 20);
    $btnArrow   = (int)(!empty($_POST['btn_arrow']));
    
    // 公告
    $announcement = trim($_POST['announcement'] ?? '');
    $announcementEnabled = (int)(!empty($_POST['announcement_enabled']));
    
    // 音乐播放器
    $customMusic        = safeSubstr(trim($_POST['custom_music'] ?? ''), 500);
    $customMusicLoop    = (int)(!empty($_POST['custom_music_loop']));
    $customMusicAutoplay= (int)(!empty($_POST['custom_music_autoplay']));
    $customMusicIcon    = in_array($tmpIcon = ($_POST['custom_music_icon'] ?? 'b'), ['b','h']) ? $tmpIcon : 'b';
    
    // 平台提示
    $openTipWechat = (int)(!empty($_POST['open_tip_wechat']));
    $openTipQq     = (int)(!empty($_POST['open_tip_qq']));
    $openTipDouyin = (int)(!empty($_POST['open_tip_douyin']));
    $openTipWeibo  = (int)(!empty($_POST['open_tip_weibo']));
    
    // 打赏
    $tippingEnabled = (int)(!empty($_POST['tipping_enabled']));
    $tippingQrcode  = safeSubstr(trim($_POST['tipping_qrcode'] ?? ''), 500);
    $tippingTitle   = safeSubstr(trim($_POST['tipping_title'] ?? '感谢支持 ❤️'), 100);

    // 社交
    $socialFields = ['wechat','qq','telegram','dy','bilibili','xiaohongshu','weibo','github','email'];
    $socialData = [];
    foreach ($socialFields as $s) {
        $val = trim($_POST["social_$s"] ?? '');
        if ($val) $socialData[$s] = $val;
    }
    $socialJson = empty($socialData) ? null : json_encode($socialData, JSON_UNESCAPED_UNICODE);

    $sql = "UPDATE users SET 
        bg_preset=?, bg_color=?, bg_image=?, custom_bg_type=?, custom_gradient_from=?, custom_gradient_to=?, custom_gradient_dir=?, 
        theme_mode=?, card_style=?, show_stats=?, footer_text=?, footer_align=?, 
        btn_bg=?, btn_color=?, btn_outline=?, btn_arrow=?, 
        announcement=?, announcement_enabled=?, 
        custom_music=?, custom_music_loop=?, custom_music_autoplay=?, custom_music_icon=?,
        open_tip_wechat=?, open_tip_qq=?, open_tip_douyin=?, open_tip_weibo=?,
        tipping_enabled=?, tipping_qrcode=?, tipping_title=?,
        social_data=? WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $bgPreset, $bgColor, $bgImage, $customBgType, $customGradFrom, $customGradTo, $customGradDir,
        $themeMode, $cardStyle, $showStats, $footerText, $footerAlign,
        $btnBg, $btnColor, $btnOutline, $btnArrow,
        $announcement, $announcementEnabled,
        $customMusic, $customMusicLoop, $customMusicAutoplay, $customMusicIcon,
        $openTipWechat, $openTipQq, $openTipDouyin, $openTipWeibo,
        $tippingEnabled, $tippingQrcode, $tippingTitle,
        $socialJson, $uid
    ]);
    $msg = '主页设置已保存 ✓';

    // 刷新
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
}

$social = $user['social_data'] ? json_decode($user['social_data'], true) : [];

$socialIcons = [
    'wechat' => ['label'=>'微信', 'icon'=>'fa-brands fa-weixin', 'ph'=>'微信号/二维码URL'],
    'qq' => ['label'=>'QQ', 'icon'=>'fa-brands fa-qq', 'ph'=>'QQ号/二维码URL'],
    'telegram' => ['label'=>'Telegram', 'icon'=>'fa-brands fa-telegram', 'ph'=>'t.me/xxx'],
    'dy' => ['label'=>'抖音', 'icon'=>'fa-brands fa-tiktok', 'ph'=>'抖音号/主页链接'],
    'bilibili' => ['label'=>'B站', 'icon'=>'fa-brands fa-bilibili', 'ph'=>'B站UID/空间链接'],
    'xiaohongshu' => ['label'=>'小红书', 'icon'=>'fa-solid fa-book', 'ph'=>'小红书号/主页链接'],
    'weibo' => ['label'=>'微博', 'icon'=>'fa-brands fa-weibo', 'ph'=>'微博ID'],
    'github' => ['label'=>'GitHub', 'icon'=>'fa-brands fa-github', 'ph'=>'github.com/xxx'],
    'email' => ['label'=>'邮箱', 'icon'=>'fa-solid fa-envelope', 'ph'=>'your@email.com'],
];

$presetColors = [
    'default'=>'#0f172a','black'=>'#111111','purple'=>'#7c3aed',
    'pink'=>'#be185d','deep'=>"url('assets/img/background.png') center/cover",'cyber'=>'#1e293b','gold'=>'#1a1a2e',
];
?>
<h1 class="text-xl font-bold mb-2">🎨 主页风格</h1>
<p class="text-gray-500 text-sm mb-6">自定义你的个人主页外观和内容</p>

<?php if ($msg): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($msg)?></div>
<?php endif; ?>

<?php
// ---- 邮箱绑定处理 ----
$emailMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_email_bind'])) {
    $newEmail = trim($_POST['bind_email'] ?? '');
    if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $emailMsg = '请输入有效的邮箱地址';
    } else {
        // 检查邮箱是否已被使用
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->execute([$newEmail, $uid]);
        if ($stmt->fetch()) {
            $emailMsg = '该邮箱已被其他账号绑定';
        } else {
            $stmt = $db->prepare("UPDATE users SET email = ?, email_verified = 0, email_verify_token = '', verify_token_expires = NULL WHERE id = ?");
            $stmt->execute([$newEmail, $uid]);
            $emailMsg = '邮箱已更新，请发送验证邮件完成验证';
            // 刷新用户数据
            $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$uid]);
            $user = $stmt->fetch();
        }
    }
}
// ---- 发送验证邮件 ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_send_verify'])) {
    if (empty($user['email'])) {
        $emailMsg = '请先绑定邮箱';
    } elseif ($user['email_verified']) {
        $emailMsg = '邮箱已验证，无需重复验证';
    } else {
        require_once __DIR__ . '/../api/mail.php';
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 1800);
        $stmt = $db->prepare("UPDATE users SET email_verify_token = ?, verify_token_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $uid]);
        $verifyUrl = BASE_URL . '/api/verify_email.php?token=' . urlencode($token) . '&uid=' . $uid;
        $siteName = h($settings['site_name'] ?? 'Leaffox主页系统');
        $subject = "验证您的邮箱 - {$siteName}";
        $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>邮箱验证</title></head><body style="margin:0;padding:0;background:#f4f6f9;font-family:sans-serif;"><div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);"><div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;"><h1 style="color:#fff;margin:0;font-size:22px;">📧 邮箱验证</h1></div><div style="padding:35px 30px;"><p style="color:#333;font-size:15px;line-height:1.7;">您好，<strong>' . h($user['username']) . '</strong>！</p><p style="color:#333;font-size:15px;line-height:1.7;">请点击下方按钮验证您的邮箱：</p><div style="text-align:center;margin:30px 0;"><a href="' . $verifyUrl . '" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:14px 40px;border-radius:30px;text-decoration:none;font-size:16px;font-weight:600;">✅ 验证邮箱</a></div><p style="color:#999;font-size:13px;">链接有效期30分钟。</p></div><div style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #eee;"><p style="color:#aaa;font-size:12px;margin:0;">' . $siteName . '</p></div></div></body></html>';
        $result = sendMail($user['email'], $subject, $body);
        $emailMsg = $result['success'] ? '验证邮件已发送，请检查邮箱' : '发送失败: ' . $result['message'];
    }
}
?>

<!-- 邮箱绑定区域 -->
<div class="card-base mb-5">
  <h3><i class="fas fa-envelope text-yellow-400"></i> 邮箱绑定 <span class="text-gray-500 text-xs font-normal">(用于登录和找回密码)</span></h3>
  <?php if ($emailMsg): ?>
  <div class="bg-<?=strpos($emailMsg,'失败')!==false||strpos($emailMsg,'无效')!==false||strpos($emailMsg,'已被')!==false?'red':'emerald'?>-500/10 border border-<?=strpos($emailMsg,'失败')!==false||strpos($emailMsg,'无效')!==false||strpos($emailMsg,'已被')!==false?'red':'emerald'?>-500/30 text-<?=strpos($emailMsg,'失败')!==false||strpos($emailMsg,'无效')!==false||strpos($emailMsg,'已被')!==false?'red':'emerald'?>-300 px-4 py-2 rounded-xl mb-3 text-sm"><?=h($emailMsg)?></div>
  <?php endif; ?>
  <div class="flex items-center gap-4 flex-wrap">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-gray-300 text-xs font-medium mb-1.5">当前邮箱</label>
      <div class="flex items-center gap-2">
        <span class="text-white"><?=h($user['email']??'未设置')?></span>
        <?php if (!empty($user['email'])): ?>
          <span class="text-xs px-2 py-0.5 rounded-full <?=($user['email_verified']??0)?'bg-emerald-500/20 text-emerald-300':'bg-yellow-500/20 text-yellow-300'?>"><?=($user['email_verified']??0)?'✓ 已验证':'待验证'?></span>
        <?php endif; ?>
      </div>
    </div>
    <form method="POST" class="flex gap-2 items-end">
      <input type="email" name="bind_email" value="<?=h($user['email']??'')?>" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white outline-none focus:border-indigo-500/50 w-48" placeholder="your@email.com">
      <button type="submit" name="action_email_bind" value="1" class="bg-indigo-500/20 text-indigo-300 px-4 py-2 rounded-xl text-sm hover:bg-indigo-500/30 transition whitespace-nowrap">保存邮箱</button>
    </form>
    <?php if (!empty($user['email']) && !($user['email_verified']??0)): ?>
    <form method="POST">
      <button type="submit" name="action_send_verify" value="1" class="bg-yellow-500/20 text-yellow-300 px-4 py-2 rounded-xl text-sm hover:bg-yellow-500/30 transition whitespace-nowrap">发送验证邮件</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<form method="POST" class="space-y-5">

  <!-- 预设主题 -->
  <div class="card-base">
    <h3><i class="fas fa-palette text-indigo-400"></i> 预设主题</h3>
    <p class="text-gray-500 text-xs mb-4">快速切换整体风格，选择「自定义」后可自由调配</p>
    <div class="grid grid-cols-4 md:grid-cols-8 gap-3">
      <?php foreach ($presets as $p):
        $sel = ($user['bg_preset'] ?? 'default') === $p['val'] ? 'ring-2 ring-indigo-500 ring-offset-2 ring-offset-[#0f172a]' : 'hover:ring-1 hover:ring-white/20';
      ?>
      <label class="flex flex-col items-center gap-1.5 cursor-pointer">
        <input type="radio" name="bg_preset" value="<?=$p['val']?>" <?=($user['bg_preset']??'default')===$p['val']?'checked':''?> class="hidden" onchange="this.form.submit()">
        <div class="w-full aspect-square rounded-xl border border-white/10 <?=$sel?> flex items-center justify-center text-2xl transition-all" style="background:<?=$presetColors[$p['val']] ?? $p['bg']?>">
          <?php if ($p['val'] === 'custom'): ?>🎨<?php endif; ?>
        </div>
        <span class="text-xs text-gray-400 text-center"><?=$p['label']?></span>
      </label>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 自定义背景 -->
  <div class="card-base" id="customBgSection" style="<?=($user['bg_preset']??'default')==='custom'?'':'opacity:40'?>">
    <h3><i class="fas fa-paint-brush text-indigo-400"></i> 自定义背景 <span class="text-gray-500 text-xs font-normal">(仅「自定义」预设生效)</span></h3>
    <div class="flex gap-3 mb-4">
      <?php foreach (['color'=>'🎨 纯色','gradient'=>'🌈 渐变','image'=>'🖼️ 图片'] as $k=>$v): ?>
      <label class="flex items-center gap-2 cursor-pointer bg-white/5 px-4 py-2 rounded-xl text-sm <?=$user['custom_bg_type']===$k?'ring-1 ring-indigo-500':''?>">
        <input type="radio" name="custom_bg_type" value="<?=$k?>" <?=$user['custom_bg_type']===$k?'checked':''?> class="accent-indigo-500"> <?=$v?>
      </label>
      <?php endforeach; ?>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">背景颜色</label><input type="text" name="bg_color" value="<?=h($user['bg_color'])?>" placeholder="#0f172a"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">背景图片URL</label><input type="text" name="bg_image" value="<?=h($user['bg_image'])?>" placeholder="留空使用纯色"></div>
      <div class="grid grid-cols-2 gap-2">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">渐变起始</label><input type="text" name="custom_gradient_from" value="<?=h($user['custom_gradient_from']?:'#667eea')?>"></div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">渐变结束</label><input type="text" name="custom_gradient_to" value="<?=h($user['custom_gradient_to']?:'#764ba2')?>"></div>
      </div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">渐变方向</label>
        <select name="custom_gradient_dir">
          <option value="135deg" <?=$user['custom_gradient_dir']==='135deg'?'selected':''?>>↘ 右下</option>
          <option value="45deg" <?=$user['custom_gradient_dir']==='45deg'?'selected':''?>>↗ 右上</option>
          <option value="90deg" <?=$user['custom_gradient_dir']==='90deg'?'selected':''?>>→ 向右</option>
          <option value="180deg" <?=$user['custom_gradient_dir']==='180deg'?'selected':''?>>↓ 向下</option>
          <option value="0deg" <?=$user['custom_gradient_dir']==='0deg'?'selected':''?>>↑ 向上</option>
        </select>
      </div>
    </div>
  </div>

  <!-- 按钮样式 & 卡片风格 -->
  <div class="card-base">
    <h3><i class="fas fa-square text-indigo-400"></i> 按钮 & 卡片</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">按钮底色</label><input type="text" name="btn_bg" value="<?=h($user['btn_bg'])?>" placeholder="默认"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">文字颜色</label><input type="text" name="btn_color" value="<?=h($user['btn_color'])?>" placeholder="默认"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">边框颜色</label><input type="text" name="btn_outline" value="<?=h($user['btn_outline'])?>" placeholder="默认"></div>
      <div class="flex items-end pb-3">
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300">
          <input type="checkbox" name="btn_arrow" value="1" <?=$user['btn_arrow']??1?'checked':''?> class="accent-indigo-500 w-4 h-4"> 显示箭头
        </label>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">主题模式</label>
        <select name="theme_mode">
          <option value="auto" <?=$user['theme_mode']=='auto'?'selected':''?>>🌓 跟随系统</option>
          <option value="light" <?=$user['theme_mode']=='light'?'selected':''?>>☀️ 浅色</option>
          <option value="dark" <?=$user['theme_mode']=='dark'?'selected':''?>>🌙 深色</option>
        </select>
      </div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">卡片风格</label>
        <select name="card_style">
          <option value="glass" <?=$user['card_style']=='glass'?'selected':''?>>🪟 玻璃质感</option>
          <option value="neumorphism" <?=$user['card_style']=='neumorphism'?'selected':''?>>🔲 新拟态</option>
          <option value="minimal" <?=$user['card_style']=='minimal'?'selected':''?>>⬜ 极简</option>
        </select>
      </div>
    </div>
  </div>

  <!-- 公告 -->
  <div class="card-base">
    <h3><i class="fas fa-bullhorn text-indigo-400"></i> 公告</h3>
    <label class="flex items-center gap-3 cursor-pointer mt-2 mb-3">
      <input type="checkbox" name="announcement_enabled" value="1" <?=$user['announcement_enabled']?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">显示公告</span>
    </label>
    <textarea name="announcement" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 resize-none" placeholder="支持HTML标签"><?=h($user['announcement'] ?? '')?></textarea>
  </div>

  <!-- 音乐播放器 -->
  <div class="card-base">
    <h3><i class="fas fa-music text-indigo-400"></i> 背景音乐</h3>
    <p class="text-gray-500 text-xs mb-4">支持 MP3 直链，显示在页面右下角的浮动按钮</p>
    <div><label class="block text-gray-300 text-xs font-medium mb-1.5">音乐URL（MP3直链）</label><input type="text" name="custom_music" value="<?=h($user['custom_music'])?>" placeholder="https://example.com/music.mp3"></div>
    <div class="flex gap-4 mt-3">
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="custom_music_loop" value="1" <?=$user['custom_music_loop']?'checked':''?> class="accent-indigo-500"> 单曲循环</label>
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="custom_music_autoplay" value="1" <?=$user['custom_music_autoplay']?'checked':''?> class="accent-indigo-500"> 自动播放</label>
    </div>
    <div class="mt-3">
      <label class="block text-gray-300 text-xs font-medium mb-1.5">音乐图标样式</label>
      <div class="flex gap-3">
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300 px-3 py-1.5 rounded-lg <?=($user['custom_music_icon']??'b')==='b'?'bg-indigo-500/20 text-indigo-300':'bg-white/5'?>">
          <input type="radio" name="custom_music_icon" value="b" <?=($user['custom_music_icon']??'b')==='b'?'checked':''?> class="accent-indigo-500" onchange="this.closest('form').submit()"> 🎵 图标B
        </label>
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300 px-3 py-1.5 rounded-lg <?=($user['custom_music_icon']??'b')==='h'?'bg-indigo-500/20 text-indigo-300':'bg-white/5'?>">
          <input type="radio" name="custom_music_icon" value="h" <?=($user['custom_music_icon']??'b')==='h'?'checked':''?> class="accent-indigo-500" onchange="this.closest('form').submit()"> 🎵 图标H
        </label>
      </div>
    </div>
  </div>

  <!-- 平台提示 -->
  <div class="card-base">
    <h3><i class="fas fa-globe text-indigo-400"></i> 内置浏览器打开提示</h3>
    <p class="text-gray-500 text-xs mb-4">在指定平台内打开时会提示跳转浏览器</p>
    <div class="flex gap-4 flex-wrap">
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="open_tip_wechat" value="1" <?=$user['open_tip_wechat']?'checked':''?> class="accent-indigo-500"> 💬 微信</label>
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="open_tip_qq" value="1" <?=$user['open_tip_qq']?'checked':''?> class="accent-indigo-500"> 💬 QQ</label>
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="open_tip_douyin" value="1" <?=$user['open_tip_douyin']?'checked':''?> class="accent-indigo-500"> 🎵 抖音</label>
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="open_tip_weibo" value="1" <?=$user['open_tip_weibo']?'checked':''?> class="accent-indigo-500"> 📱 微博</label>
    </div>
  </div>

  <!-- 打赏/赞赏 -->
  <div class="card-base">
    <h3><i class="fas fa-heart text-red-400"></i> 打赏 / 赞赏 <span class="text-gray-500 text-xs font-normal">(访客扫码支付支持你)</span></h3>
    <label class="flex items-center gap-3 cursor-pointer mt-2 mb-4">
      <input type="checkbox" name="tipping_enabled" value="1" <?=$user['tipping_enabled']?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">开启打赏功能</span>
    </label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">打赏标题</label>
        <input type="text" name="tipping_title" value="<?=h($user['tipping_title']??'感谢支持 ❤️')?>" placeholder="感谢支持 ❤️" maxlength="100">
      </div>
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">打赏二维码图片URL</label>
        <input type="text" name="tipping_qrcode" id="tippingQrcode" value="<?=h($user['tipping_qrcode']??'')?>" placeholder="上传或粘贴收款码图片链接">
        <div class="flex gap-2 mt-2">
          <input type="file" accept="image/*" id="tippingUpload" onchange="uploadTippingQr(this)" class="block w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 cursor-pointer">
        </div>
        <div id="tippingUploadProgress" class="text-xs text-indigo-400 hidden mt-1"><i class="fas fa-spinner fa-spin"></i> 上传中...</div>
        <?php if (!empty($user['tipping_qrcode'])): ?>
        <div class="mt-3">
          <p class="text-xs text-gray-500 mb-1">当前二维码预览：</p>
          <img src="<?=h($user['tipping_qrcode'])?>" style="max-width:160px;border-radius:12px;border:2px solid rgba(255,255,255,0.1)">
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- 社交 -->
  <div class="card-base">
    <h3><i class="fas fa-share-alt text-indigo-400"></i> 社交渠道</h3>
    <p class="text-gray-500 text-xs mb-4">将在主页底部显示图标按钮</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <?php foreach ($socialIcons as $key => $item): ?>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5"><i class="<?=$item['icon']?> mr-1"></i> <?=$item['label']?></label>
      <input type="text" name="social_<?=$key?>" value="<?=h($social[$key] ?? '')?>" placeholder="<?=$item['ph']?>"></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 页脚 & 统计 -->
  <div class="card-base">
    <h3><i class="fas fa-cog text-indigo-400"></i> 页脚 & 统计</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">页脚文字</label><input type="text" name="footer_text" value="<?=h($user['footer_text'])?>" maxlength="200"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">页脚对齐</label>
        <select name="footer_align">
          <option value="left" <?=$user['footer_align']==='left'?'selected':''?>>⬅ 左对齐</option>
          <option value="center" <?=$user['footer_align']==='center'?'selected':''?>>⬛ 居中</option>
          <option value="right" <?=$user['footer_align']==='right'?'selected':''?>>➡ 右对齐</option>
        </select>
      </div>
    </div>
    <label class="flex items-center gap-3 cursor-pointer mt-4">
      <input type="checkbox" name="show_stats" value="1" <?=$user['show_stats']?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">前台显示访问统计</span>
    </label>
  </div>

  <div class="flex gap-3">
    <button type="submit" class="btn-sm btn-primary px-8 py-3"><i class="fas fa-save"></i> 保存全部设置</button>
    <a href="<?=BASE_URL?>/page/index.php?id=<?=$uid?>" target="_blank" class="btn-sm btn-ghost px-6 py-3"><i class="fas fa-eye"></i> 预览主页</a>
  </div>
</form>

<script>
document.querySelectorAll('input[name="bg_preset"]').forEach(function(el){
  el.addEventListener('change', function(){
    var section = document.getElementById('customBgSection');
    section.style.opacity = this.value === 'custom' ? '1' : '0.4';
  });
});
// 打赏二维码上传
function uploadTippingQr(input){
  var file = input.files[0];
  if(!file) return;
  var progress = document.getElementById('tippingUploadProgress');
  progress.classList.remove('hidden');
  var formData = new FormData();
  formData.append('file', file);
  formData.append('type', 'image');
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '<?=BASE_URL?>/api/upload.php', true);
  xhr.onload = function(){
    progress.classList.add('hidden');
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          document.getElementById('tippingQrcode').value = r.url;
        } else {
          alert('上传失败：' + r.msg);
        }
      } catch(e){
        alert('上传返回异常');
      }
    } else {
      alert('上传失败（' + xhr.status + '）');
    }
  };
  xhr.onerror = function(){ progress.classList.add('hidden'); alert('网络异常'); };
  xhr.send(formData);
}
</script>
