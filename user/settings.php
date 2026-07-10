<?php
/**
 * 用户 - 主页设置（动态模版选择 + 自定义背景 + 按钮 + 社交 + 公告 + 音乐 + 平台提示 + 页脚）
 * 预设主题已由 templates/user/ 下的文件模版动态替代
 * 【注意】此文件必须通过 dashboard.php?page=settings 访问
 */
// 防止直接访问：若未通过 dashboard.php 加载则自动跳转
if (!defined('IN_DASHBOARD')) {
    // 尝试自举加载环境
    $bootstrapFile = __DIR__ . '/../config.php';
    if (file_exists($bootstrapFile)) {
        require_once $bootstrapFile;
    }
    if (!isset($_SESSION)) {
        session_start();
    }
    // 如果仍无 user_id，跳转到登录页
    if (empty($_SESSION['user_id'])) {
        $loginUrl = (defined('BASE_URL') ? BASE_URL : '') . '/user/';
        header("Location: $loginUrl");
        exit;
    }
}

$uid = (int)$_SESSION['user_id'];
$msg = '';
$success = false;
$guestSetupMsg = '';

// ===== 游客升级账号处理 =====
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action_guest_setup'])) {
    $newUsername = trim($_POST['guest_username'] ?? '');
    $newEmail = trim($_POST['guest_email'] ?? '');
    $newPassword = $_POST['guest_password'] ?? '';
    $newConfirm = $_POST['guest_confirm'] ?? '';
    
    if (empty($newUsername) || empty($newPassword) || empty($newConfirm)) {
        $guestSetupMsg = '请填写用户名和密码';
    } elseif (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$/u', $newUsername)) {
        $guestSetupMsg = '用户名2-20位，支持中文、字母、数字、下划线';
    } elseif (strlen($newPassword) < 6) {
        $guestSetupMsg = '密码至少6位';
    } elseif ($newPassword !== $newConfirm) {
        $guestSetupMsg = '两次密码不一致';
    } elseif (!empty($newEmail) && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $guestSetupMsg = '邮箱格式不正确';
    } else {
        // 检查用户名是否被占用
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$newUsername, $uid]);
        if ($stmt->fetch()) {
            $guestSetupMsg = '用户名已被注册';
        } elseif (!empty($newEmail)) {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$newEmail, $uid]);
            if ($stmt->fetch()) {
                $guestSetupMsg = '邮箱已被其他账号绑定';
            }
        }
    }
    
    if (empty($guestSetupMsg)) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!empty($newEmail)) {
            $stmt = $db->prepare("UPDATE users SET username = ?, password = ?, email = ?, nickname = ?, is_guest = 0, email_verified = 0, guest_expires_at = NULL WHERE id = ?");
            $stmt->execute([$newUsername, $hash, $newEmail, $newUsername, $uid]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = ?, password = ?, nickname = ?, is_guest = 0, guest_expires_at = NULL WHERE id = ?");
            $stmt->execute([$newUsername, $hash, $newUsername, $uid]);
        }
        $_SESSION['user_name'] = $newUsername;
        unset($_SESSION['is_guest']);
        $guestSetupMsg = '✅ 账号设置成功！现在可以使用账号密码登录了。';
        $guestSetupSuccess = true;
        // 刷新用户数据
        $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
    }
}

// ===== 动态扫描用户模版 =====
$userTplDir = __DIR__ . '/../templates/user';
$templateFiles = [];
if (is_dir($userTplDir)) {
    foreach (glob($userTplDir . '/*.php') as $f) {
        $tplName = basename($f, '.php');
        $templateFiles[] = $tplName;
    }
    sort($templateFiles);
}
if (empty($templateFiles)) $templateFiles = ['default']; // 兜底

// ===== 模版预览色 / 图标映射（可扩展） =====
$tplPreviewMap = [
    'default'  => ['color' => '#0f172a', 'icon' => '<i class="fas fa-moon"></i>', 'label' => '深邃夜空'],
    'modern'   => ['color' => '#f1f5f9', 'icon' => '<i class="fas fa-sun"></i>', 'label' => '清爽现代'],
    'vibrant'  => ['color' => 'linear-gradient(135deg,#667eea,#f093fb)', 'icon' => '<i class="fas fa-rainbow"></i>', 'label' => '绚丽活力'],
    'elegant'  => ['color' => '#faf6f0', 'icon' => '<i class="fas fa-sparkles"></i>', 'label' => '优雅质感'],
    'neon'     => ['color' => 'linear-gradient(135deg,#0f0c29,#302b63,#24243e)', 'icon' => '<i class="fas fa-heart" style="color:#a78bfa"></i>', 'label' => '赛博霓虹'],
    'ocean'    => ['color' => 'linear-gradient(135deg,#0c2340,#4ecdc4)', 'icon' => '<i class="fas fa-water"></i>', 'label' => '海洋蓝调'],
    'forest'   => ['color' => 'linear-gradient(160deg,#1a2e1f,#6b8c5c)', 'icon' => '<i class="fas fa-tree"></i>', 'label' => '森林绿意'],
    'sunset'   => ['color' => 'linear-gradient(135deg,#2d1b2e,#f4a460,#ffd8a8)', 'icon' => '<i class="fas fa-sunrise"></i>', 'label' => '日落暖色'],
    'dracula'  => ['color' => '#282a36', 'icon' => '<i class="fas fa-user-tie"></i>', 'label' => '暗紫高对比'],
    'nord'     => ['color' => '#2e3440', 'icon' => '<i class="fas fa-snowflake"></i>', 'label' => '北欧极简冷色'],
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $bgPreset   = safeSubstr(trim($_POST['bg_preset'] ?? 'default'), 30);
    $bgColor    = safeSubstr(trim($_POST['bg_color'] ?? '#0f172a'), 20);
    $bgImage    = safeSubstr(trim($_POST['bg_image'] ?? ''), 255);
    $customBgType   = in_array($_POST['custom_bg_type'] ?? 'color', ['color','gradient','image']) ? $_POST['custom_bg_type'] ?? '' : 'color';
    $customGradFrom = safeSubstr(trim($_POST['custom_gradient_from'] ?? '#667eea'), 10);
    $customGradTo   = safeSubstr(trim($_POST['custom_gradient_to'] ?? '#764ba2'), 10);
    $customGradDir  = safeSubstr(trim($_POST['custom_gradient_dir'] ?? '135deg'), 10);
    $themeMode  = in_array($_POST['theme_mode'] ?? 'auto', ['auto','light','dark']) ? $_POST['theme_mode'] ?? '' : 'auto';
    $cardStyle  = in_array($_POST['card_style'] ?? 'glass', ['glass','neumorphism','minimal']) ? $_POST['card_style'] ?? '' : 'glass';
    $showStats  = (int)(!empty($_POST['show_stats'] ?? ''));
    $footerText = safeSubstr(trim($_POST['footer_text'] ?? 'Powered by Leaffox主页系统'), 200);
    $footerAlign= in_array($_POST['footer_align'] ?? 'center', ['left','center','right']) ? $_POST['footer_align'] ?? '' : 'center';
    $btnBg      = safeSubstr(trim($_POST['btn_bg'] ?? ''), 20);
    $btnColor   = safeSubstr(trim($_POST['btn_color'] ?? ''), 20);
    $btnOutline = safeSubstr(trim($_POST['btn_outline'] ?? ''), 20);
    $btnArrow   = (int)(!empty($_POST['btn_arrow'] ?? ''));
    // 模版选择：动态校验（必须是在模板目录中存在的文件）
    $pageTemplate = in_array($_POST['page_template'] ?? 'default', $templateFiles) ? $_POST['page_template'] ?? '' : 'default';
    
    // 公告
    $announcement = trim($_POST['announcement'] ?? '');
    $announcementEnabled = (int)(!empty($_POST['announcement_enabled'] ?? ''));
    
    // 音乐播放器
    $customMusic        = safeSubstr(trim($_POST['custom_music'] ?? ''), 500);
    $customMusicLoop    = (int)(!empty($_POST['custom_music_loop'] ?? ''));
    $customMusicAutoplay= (int)(!empty($_POST['custom_music_autoplay'] ?? ''));
    $customMusicIcon    = in_array($tmpIcon = ($_POST['custom_music_icon'] ?? 'b'), ['b','h']) ? $tmpIcon : 'b';
    
    // 平台提示
    $openTipWechat = (int)(!empty($_POST['open_tip_wechat'] ?? ''));
    $openTipQq     = (int)(!empty($_POST['open_tip_qq'] ?? ''));
    $openTipDouyin = (int)(!empty($_POST['open_tip_douyin'] ?? ''));
    $openTipWeibo  = (int)(!empty($_POST['open_tip_weibo'] ?? ''));
    
    // 打赏
    $tippingEnabled = (int)(!empty($_POST['tipping_enabled'] ?? ''));
    $videoAutoExpand = (int)(!empty($_POST['video_auto_expand'] ?? ''));
    $tippingQrcode  = safeSubstr(trim($_POST['tipping_qrcode'] ?? ''), 500);
    $tippingTitle   = safeSubstr(trim($_POST['tipping_title'] ?? '感谢支持 <i class="fas fa-heart" style="color:#ef4444"></i>'), 100);

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
        theme_mode=?, card_style=?, page_template=?, show_stats=?, footer_text=?, footer_align=?, 
        btn_bg=?, btn_color=?, btn_outline=?, btn_arrow=?, 
        announcement=?, announcement_enabled=?, 
        custom_music=?, custom_music_loop=?, custom_music_autoplay=?, custom_music_icon=?,
        open_tip_wechat=?, open_tip_qq=?, open_tip_douyin=?, open_tip_weibo=?,
        tipping_enabled=?, tipping_qrcode=?, tipping_title=?,
        video_auto_expand=?,
        social_data=? WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $bgPreset, $bgColor, $bgImage, $customBgType, $customGradFrom, $customGradTo, $customGradDir,
        $themeMode, $cardStyle, $pageTemplate, $showStats, $footerText, $footerAlign,
        $btnBg, $btnColor, $btnOutline, $btnArrow,
        $announcement, $announcementEnabled,
        $customMusic, $customMusicLoop, $customMusicAutoplay, $customMusicIcon,
        $openTipWechat, $openTipQq, $openTipDouyin, $openTipWeibo,
        $tippingEnabled, $tippingQrcode, $tippingTitle,
        $videoAutoExpand,
        $socialJson, $uid
    ]);
    $msg = '主页设置已保存'; $success = true;

    // 刷新
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
}

$social = ($user['social_data'] ?? '') ? json_decode($user['social_data'] ?? '', true) : [];

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
?>
<h1 class="text-xl font-bold mb-2"><i class="fas fa-palette"></i> 主页风格</h1>
<p class="text-gray-500 text-sm mb-6">自定义你的个人主页外观和内容</p>

<?php if ($msg): ?>
<div class="bg-<?=$success?'emerald':'red'?>-500/10 border border-<?=$success?'emerald':'red'?>-500/30 text-<?=$success?'emerald':'red'?>-300 px-4 py-3 rounded-xl mb-4 text-sm"><?php if ($success): ?><i class="fas fa-check-circle" style="color:#10b981"></i> <?php endif; ?><?=h($msg)?></div>
<?php endif; ?>

<?php
// ---- 邮箱绑定处理 ----
$emailMsg = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action_email_bind'])) {
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
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action_send_verify'])) {
    if (empty($user['email'] ?? '')) {
        $emailMsg = '请先绑定邮箱';
    } elseif ($user['email_verified'] ?? 0) {
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
        $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>邮箱验证</title></head><body style="margin:0;padding:0;background:#f4f6f9;font-family:sans-serif;"><div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);"><div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;"><h1 style="color:#fff;margin:0;font-size:22px;"><i class="fas fa-envelope"></i> 邮箱验证</h1></div><div style="padding:35px 30px;"><p style="color:#333;font-size:15px;line-height:1.7;">您好，<strong>' . h($user['username'] ?? '') . '</strong>！</p><p style="color:#333;font-size:15px;line-height:1.7;">请点击下方按钮验证您的邮箱：</p><div style="text-align:center;margin:30px 0;"><a href="' . $verifyUrl . '" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:14px 40px;border-radius:30px;text-decoration:none;font-size:16px;font-weight:600;"><i class="fas fa-check-circle" style="color:#10b981"></i> 验证邮箱</a></div><p style="color:#999;font-size:13px;">链接有效期30分钟。</p></div><div style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #eee;"><p style="color:#aaa;font-size:12px;margin:0;">' . $siteName . '</p></div></div></body></html>';
        $result = sendMail($user['email'] ?? '', $subject, $body);
        $emailMsg = $result['success'] ? '验证邮件已发送，请检查邮箱' : '发送失败: ' . $result['message'];
    }
}
?>

<!-- 游客升级账号提示 -->
<?php if (!empty($_SESSION['is_guest']) || isset($_GET['setup'])): ?>
<div class="card-base mb-5 border border-amber-500/30 bg-gradient-to-r from-amber-500/5 to-orange-500/5">
  <h3 class="text-amber-300"><i class="fas fa-key text-amber-400"></i> 设置账号密码 <span class="text-gray-500 text-xs font-normal">(游客身份 → 正式账号)</span></h3>
  <p class="text-amber-200/70 text-xs mb-4">设置用户名、邮箱和密码后，即可使用账号密码登录，数据永久保存。</p>
  
  <?php if (!empty($guestSetupMsg)): ?>
  <div class="bg-<?=!empty($guestSetupSuccess)?'emerald':'red'?>-500/10 border border-<?=!empty($guestSetupSuccess)?'emerald':'red'?>-500/30 text-<?=!empty($guestSetupSuccess)?'emerald':'red'?>-300 px-4 py-2 rounded-xl mb-3 text-sm"><?=h($guestSetupMsg)?></div>
  <?php endif; ?>
  
  <form method="POST" class="space-y-3 ml-1">
    <div>
      <label class="block text-gray-300 text-xs font-medium mb-1">用户名 <span class="text-red-400">*</span></label>
      <input type="text" name="guest_username" value="<?=h($user['username'] ?? '')?>" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white outline-none focus:border-amber-500/50 w-full max-w-xs" placeholder="2-20位，中文/字母/数字" pattern="^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,20}$" required>
    </div>
    <div>
      <label class="block text-gray-300 text-xs font-medium mb-1">邮箱 <span class="text-gray-500 text-xs">(选填，用于找回密码)</span></label>
      <input type="email" name="guest_email" value="<?=h($user['email'] ?? '')?>" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white outline-none focus:border-amber-500/50 w-full max-w-xs" placeholder="your@email.com">
    </div>
    <div>
      <label class="block text-gray-300 text-xs font-medium mb-1">密码 <span class="text-red-400">*</span></label>
      <input type="password" name="guest_password" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white outline-none focus:border-amber-500/50 w-full max-w-xs" placeholder="至少6位" required>
    </div>
    <div>
      <label class="block text-gray-300 text-xs font-medium mb-1">确认密码 <span class="text-red-400">*</span></label>
      <input type="password" name="guest_confirm" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white outline-none focus:border-amber-500/50 w-full max-w-xs" placeholder="再次输入密码" required>
    </div>
    <button type="submit" name="action_guest_setup" value="1" class="bg-amber-500 hover:bg-amber-400 text-amber-900 font-semibold px-5 py-2 rounded-xl text-sm transition-all"><i class="fas fa-check-circle"></i> 立即升级账号</button>
  </form>
</div>
<?php endif; ?>

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
        <span class="text-white"><?=h($user['email'] ?? '未设置')?></span>
        <?php if (!empty($user['email'] ?? '')): ?>
          <span class="text-xs px-2 py-0.5 rounded-full <?=($user['email_verified'] ?? 0)?'bg-emerald-500/20 text-emerald-300':'bg-yellow-500/20 text-yellow-300'?>"><?=($user['email_verified'] ?? 0)?'<i class="fas fa-check-circle" style="color:#10b981"></i> 已验证':'待验证'?></span>
        <?php endif; ?>
      </div>
    </div>
    <form method="POST" class="flex gap-2 items-end">
      <input type="email" name="bind_email" value="<?=h($user['email'] ?? '')?>" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white outline-none focus:border-indigo-500/50 w-48" placeholder="your@email.com">
      <button type="submit" name="action_email_bind" value="1" class="bg-indigo-500/20 text-indigo-300 px-4 py-2 rounded-xl text-sm hover:bg-indigo-500/30 transition whitespace-nowrap">保存邮箱</button>
    </form>
    <?php if (!empty($user['email'] ?? '') && !($user['email_verified'] ?? 0)): ?>
    <form method="POST">
      <button type="submit" name="action_send_verify" value="1" class="bg-yellow-500/20 text-yellow-300 px-4 py-2 rounded-xl text-sm hover:bg-yellow-500/30 transition whitespace-nowrap">发送验证邮件</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<form method="POST" class="space-y-5">

  <!-- ===== 模版选择（动态扫描 templates/user/） ===== -->
  <div class="card-base">
    <h3><i class="fas fa-palette text-indigo-400"></i> 页面模版 <span class="text-gray-500 text-xs font-normal">(选择后自动应用整体风格)</span></h3>
    <p class="text-gray-500 text-xs mb-4">模版完全覆盖页面配色/字体/卡片样式，管理员可在后台「模版管理」上传新模版</p>
    <input type="hidden" name="bg_preset" value="custom">
    <div class="grid grid-cols-4 md:grid-cols-6 gap-3">
      <?php $currentTpl = $user['page_template'] ?? 'default'; ?>
      <?php foreach ($templateFiles as $tpl):
        $preview = $tplPreviewMap[$tpl] ?? ['color'=>'#334155', 'icon'=>'<i class="fas fa-file-alt"></i>', 'label'=>ucfirst($tpl)];
        $sel = $currentTpl === $tpl ? 'ring-2 ring-indigo-500 ring-offset-2 ring-offset-[#0f172a]' : 'hover:ring-1 hover:ring-white/20';
      ?>
      <label class="flex flex-col items-center gap-1.5 cursor-pointer group">
        <input type="radio" name="page_template" value="<?=h($tpl)?>" <?=$currentTpl===$tpl?'checked':''?> class="hidden">
        <div class="w-full aspect-square rounded-xl border border-white/10 <?=$sel?> flex items-center justify-center text-2xl transition-all duration-200 group-hover:scale-105" style="background:<?=$preview['color']?>">
          <span class="<?=strpos($preview['color'],'#f1f5f9')!==false||strpos($preview['color'],'#faf6f0')!==false?'text-gray-800':'text-white'?> drop-shadow-lg"><?=$preview['icon']?></span>
        </div>
        <span class="text-xs text-gray-400 text-center leading-tight"><?=h($preview['label'])?></span>
      </label>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ===== 自定义背景（始终可见） ===== -->
  <div class="card-base">
    <h3><i class="fas fa-paint-brush text-indigo-400"></i> 自定义背景</h3>
    <p class="text-gray-500 text-xs mb-4">精细调整背景颜色/渐变/图片，独立于模版之外</p>
    <div class="flex gap-3 mb-4">
      <?php foreach (['color'=>'<i class="fas fa-palette"></i> 纯色','gradient'=>'<i class="fas fa-rainbow"></i> 渐变','image'=>'<i class="fas fa-image"></i> 图片'] as $k=>$v): ?>
      <label class="flex items-center gap-2 cursor-pointer bg-white/5 px-4 py-2 rounded-xl text-sm <?=($user['custom_bg_type'] ?? 'color')===$k?'ring-1 ring-indigo-500':''?>">
        <input type="radio" name="custom_bg_type" value="<?=$k?>" <?=($user['custom_bg_type'] ?? 'color')===$k?'checked':''?> class="accent-indigo-500"> <?=$v?>
      </label>
      <?php endforeach; ?>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">背景颜色</label><input type="text" name="bg_color" value="<?=h($user['bg_color'] ?? '#0f172a')?>" placeholder="#0f172a"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">背景图片URL</label><input type="text" name="bg_image" value="<?=h($user['bg_image'] ?? '')?>" placeholder="留空使用纯色"></div>
      <div class="grid grid-cols-2 gap-2">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">渐变起始</label><input type="text" name="custom_gradient_from" value="<?=h($user['custom_gradient_from'] ?? '#667eea')?>"></div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">渐变结束</label><input type="text" name="custom_gradient_to" value="<?=h($user['custom_gradient_to'] ?? '#764ba2')?>"></div>
      </div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">渐变方向</label>
        <select name="custom_gradient_dir">
          <option value="135deg" <?=($user['custom_gradient_dir'] ?? '135deg')==='135deg'?'selected':''?>>↘ 右下</option>
          <option value="45deg" <?=($user['custom_gradient_dir'] ?? '135deg')==='45deg'?'selected':''?>>↗ 右上</option>
          <option value="90deg" <?=($user['custom_gradient_dir'] ?? '135deg')==='90deg'?'selected':''?>>→ 向右</option>
          <option value="180deg" <?=($user['custom_gradient_dir'] ?? '135deg')==='180deg'?'selected':''?>>↓ 向下</option>
          <option value="0deg" <?=($user['custom_gradient_dir'] ?? '135deg')==='0deg'?'selected':''?>>↑ 向上</option>
        </select>
      </div>
    </div>
  </div>

  <!-- 按钮样式 & 卡片风格 -->
  <div class="card-base">
    <h3><i class="fas fa-square text-indigo-400"></i> 按钮 & 卡片</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">按钮底色</label><input type="text" name="btn_bg" value="<?=h($user['btn_bg'] ?? '')?>" placeholder="默认"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">文字颜色</label><input type="text" name="btn_color" value="<?=h($user['btn_color'] ?? '')?>" placeholder="默认"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">边框颜色</label><input type="text" name="btn_outline" value="<?=h($user['btn_outline'] ?? '')?>" placeholder="默认"></div>
      <div class="flex items-end pb-3">
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300">
          <input type="checkbox" name="btn_arrow" value="1" <?=($user['btn_arrow']??1)?'checked':''?> class="accent-indigo-500 w-4 h-4"> 显示箭头
        </label>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">主题模式</label>
        <select name="theme_mode" class="w-full md:w-auto" style="max-width:100%">
          <option value="auto" <?=($user['theme_mode'] ?? 'auto')=='auto'?'selected':''?>><i class="fas fa-circle-half-stroke"></i> 跟随系统</option>
          <option value="light" <?=($user['theme_mode'] ?? 'auto')=='light'?'selected':''?>><i class="fas fa-sun"></i> 浅色</option>
          <option value="dark" <?=($user['theme_mode'] ?? 'auto')=='dark'?'selected':''?>><i class="fas fa-moon"></i> 深色</option>
        </select>
      </div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">卡片风格</label>
        <select name="card_style" class="w-full md:w-auto" style="max-width:100%">
          <option value="glass" <?=($user['card_style'] ?? 'glass')=='glass'?'selected':''?>><i class="fas fa-window-restore"></i> 玻璃质感</option>
          <option value="neumorphism" <?=($user['card_style'] ?? 'glass')=='neumorphism'?'selected':''?>><i class="fas fa-border-all"></i> 新拟态</option>
          <option value="minimal" <?=($user['card_style'] ?? 'glass')=='minimal'?'selected':''?>><i class="fas fa-square"></i> 极简</option>
        </select>
      </div>
    </div>
  </div>

  <!-- 公告 -->
  <div class="card-base">
    <h3><i class="fas fa-bullhorn text-indigo-400"></i> 公告</h3>
    <label class="flex items-center gap-3 cursor-pointer mt-2 mb-3">
      <input type="checkbox" name="announcement_enabled" value="1" <?=($user['announcement_enabled'] ?? 0)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">显示公告</span>
    </label>
    <textarea name="announcement" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 resize-none" placeholder="支持HTML标签"><?=h($user['announcement'] ?? '')?></textarea>
  </div>

  <!-- 音乐播放器 -->
  <div class="card-base">
    <h3><i class="fas fa-music text-indigo-400"></i> 背景音乐</h3>
    <p class="text-gray-500 text-xs mb-4">支持 MP3 直链，显示在页面右下角的浮动按钮</p>
    <div><label class="block text-gray-300 text-xs font-medium mb-1.5">音乐URL（MP3直链）</label><input type="text" name="custom_music" value="<?=h($user['custom_music'] ?? '')?>" placeholder="https://example.com/music.mp3"></div>
    <div class="flex gap-4 mt-3">
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="custom_music_loop" value="1" <?=($user['custom_music_loop'] ?? 0)?'checked':''?> class="accent-indigo-500"> 单曲循环</label>
      <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300"><input type="checkbox" name="custom_music_autoplay" value="1" <?=($user['custom_music_autoplay'] ?? 0)?'checked':''?> class="accent-indigo-500"> 自动播放</label>
    </div>
    <div class="mt-3">
      <label class="block text-gray-300 text-xs font-medium mb-1.5">音乐图标样式</label>
      <div class="flex gap-3">
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300 px-3 py-1.5 rounded-lg <?=($user['custom_music_icon']??'b')==='b'?'bg-indigo-500/20 text-indigo-300':'bg-white/5'?>">
          <input type="radio" name="custom_music_icon" value="b" <?=($user['custom_music_icon']??'b')==='b'?'checked':''?> class="accent-indigo-500" onchange="this.closest('form').submit()"> <i class="fas fa-music"></i> 图标B
        </label>
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300 px-3 py-1.5 rounded-lg <?=($user['custom_music_icon']??'b')==='h'?'bg-indigo-500/20 text-indigo-300':'bg-white/5'?>">
          <input type="radio" name="custom_music_icon" value="h" <?=($user['custom_music_icon']??'b')==='h'?'checked':''?> class="accent-indigo-500" onchange="this.closest('form').submit()"> <i class="fas fa-music"></i> 图标H
        </label>
      </div>
    </div>
  </div>

  <!-- 视频自动展开 -->
  <div class="card-base">
    <h3><i class="fas fa-video text-indigo-400"></i> 视频播放设置</h3>
    <p class="text-gray-500 text-xs mb-4">开启后，您个人主页中的视频（B站/文件视频）将直接以内嵌播放器展开显示，无需点击弹窗</p>
    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300">
      <input type="checkbox" name="video_auto_expand" value="1" <?=!empty($user['video_auto_expand'])?'checked':''?> class="accent-indigo-500">
      视频自动展开播放
    </label>
  </div>

  <!-- 平台提示 -->
  <div class="card-base">
    <h3><i class="fas fa-globe text-indigo-400"></i> 内置浏览器打开提示</h3>
    <p class="text-gray-500 text-xs mb-4">在指定平台内打开时会提示跳转浏览器</p>
    <div class="flex gap-4 flex-wrap">
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 10px;border-radius:8px;background:var(--user-card-bg,rgba(255,255,255,0.04));border:1px solid var(--user-card-border,rgba(255,255,255,0.08))"><input type="checkbox" name="open_tip_wechat" value="1" <?=($user['open_tip_wechat'] ?? 0)?'checked':''?> class="accent-indigo-500 flex-shrink-0"> <span class="text-sm" style="white-space:nowrap"><i class="fab fa-weixin"></i> 微信</span></label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 10px;border-radius:8px;background:var(--user-card-bg,rgba(255,255,255,0.04));border:1px solid var(--user-card-border,rgba(255,255,255,0.08))"><input type="checkbox" name="open_tip_qq" value="1" <?=($user['open_tip_qq'] ?? 0)?'checked':''?> class="accent-indigo-500 flex-shrink-0"> <span class="text-sm" style="white-space:nowrap"><i class="fab fa-qq"></i> QQ</span></label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 10px;border-radius:8px;background:var(--user-card-bg,rgba(255,255,255,0.04));border:1px solid var(--user-card-border,rgba(255,255,255,0.08))"><input type="checkbox" name="open_tip_douyin" value="1" <?=($user['open_tip_douyin'] ?? 0)?'checked':''?> class="accent-indigo-500 flex-shrink-0"> <span class="text-sm" style="white-space:nowrap"><i class="fab fa-tiktok"></i> 抖音</span></label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 10px;border-radius:8px;background:var(--user-card-bg,rgba(255,255,255,0.04));border:1px solid var(--user-card-border,rgba(255,255,255,0.08))"><input type="checkbox" name="open_tip_weibo" value="1" <?=($user['open_tip_weibo'] ?? 0)?'checked':''?> class="accent-indigo-500 flex-shrink-0"> <span class="text-sm" style="white-space:nowrap"><i class="fab fa-weibo"></i> 微博</span></label>
      </div>
    </div>
  </div>

  <!-- 打赏/赞赏 -->
  <div class="card-base">
    <h3><i class="fas fa-heart text-red-400"></i> 打赏 / 赞赏 <span class="text-gray-500 text-xs font-normal">(访客扫码支付支持你)</span></h3>
    <label class="flex items-center gap-3 cursor-pointer mt-2 mb-4">
      <input type="checkbox" name="tipping_enabled" value="1" <?=($user['tipping_enabled'] ?? 0)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">开启打赏功能</span>
    </label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">打赏标题</label>
        <input type="text" name="tipping_title" value="<?=h($user['tipping_title']??'感谢支持 <i class="fas fa-heart" style="color:#ef4444"></i>')?>" placeholder="感谢支持 <i class="fas fa-heart" style="color:#ef4444"></i>" maxlength="100">
      </div>
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">打赏二维码图片URL</label>
        <input type="text" name="tipping_qrcode" id="tippingQrcode" value="<?=h($user['tipping_qrcode'] ?? '')?>" placeholder="上传或粘贴收款码图片链接">
        <div class="flex gap-2 mt-2">
          <input type="file" accept="image/*" id="tippingUpload" onchange="uploadTippingQr(this)" class="block w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 cursor-pointer">
        </div>
        <div id="tippingUploadProgress" class="text-xs text-indigo-400 hidden mt-1"><i class="fas fa-spinner fa-spin"></i> 上传中...</div>
        <?php if (!empty($user['tipping_qrcode'] ?? '')): ?>
        <div class="mt-3">
          <p class="text-xs text-gray-500 mb-1">当前二维码预览：</p>
          <img src="<?=h($user['tipping_qrcode'] ?? '')?>" style="max-width:160px;border-radius:12px;border:2px solid rgba(255,255,255,0.1)">
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
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5"><i class="<?=$item['icon'] ?? ''?> mr-1"></i> <?=$item['label'] ?? ''?></label>
      <input type="text" name="social_<?=$key?>" value="<?=h($social[$key] ?? '')?>" placeholder="<?=$item['ph']?>"></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 页脚 & 统计 -->
  <div class="card-base">
    <h3><i class="fas fa-cog text-indigo-400"></i> 页脚 & 统计</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">页脚文字</label><input type="text" name="footer_text" value="<?=h($user['footer_text'] ?? '')?>" maxlength="200"></div>
      <div><label class="block text-gray-300 text-xs font-medium mb-1.5">页脚对齐</label>
        <select name="footer_align">
          <option value="left" <?=($user['footer_align'] ?? 'center')==='left'?'selected':''?>>⬅ 左对齐</option>
          <option value="center" <?=($user['footer_align'] ?? 'center')==='center'?'selected':''?>>⬛ 居中</option>
          <option value="right" <?=($user['footer_align'] ?? 'center')==='right'?'selected':''?>> <i class="fas fa-arrow-right"></i> 右对齐</option>
        </select>
      </div>
    </div>
    <label class="flex items-center gap-3 cursor-pointer mt-4">
      <input type="checkbox" name="show_stats" value="1" <?=($user['show_stats'] ?? 0)?'checked':''?> class="w-4 h-4 accent-indigo-500">
      <span class="text-gray-300 text-sm">前台显示访问统计</span>
    </label>
  </div>

  <div class="flex gap-3">
    <button type="submit" class="btn-sm btn-primary px-8 py-3"><i class="fas fa-save"></i> 保存全部设置</button>
    <a href="<?=BASE_URL?>/page/index.php?id=<?=$uid?>" target="_blank" class="btn-sm btn-ghost px-6 py-3"><i class="fas fa-eye"></i> 预览主页</a>
  </div>
</form>

<script>
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
