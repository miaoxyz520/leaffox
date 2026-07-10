<?php
/**
 * 前台访客页面 - 完整版（旧系统全功能移植）
 * 支持：5种模块类型 · 音乐播放器 · 公告 · 密码链接 · 平台提示 · 预设/自定义背景 · 社交 · 页脚
 * 路径: /page/index.php?id={用户ID}
 */
require_once __DIR__ . '/../config.php';

$userId = max(0, (int)($_GET['id'] ?? 0));
if ($userId <= 0) { die('缺少用户ID'); }

$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) { die('用户不存在或已封禁'); }

// 视频自动展开播放开关
$videoAutoExpand = $user['video_auto_expand'] ?? 0;

// 获取全站设置
$siteSettings = getSettings($db);

// 获取链接模块（全部类型）
$links = $db->prepare("SELECT * FROM links WHERE user_id = ? AND is_hidden = 0 AND is_violation = 0 ORDER BY sort_order ASC, id DESC");
$links->execute([$userId]);
$linkList = $links->fetchAll();

// 社交数据
$socialData = $user['social_data'] ?? '[]' ?? '[]' ? json_decode($user['social_data'] ?? '[]' ?? '[]', true) : [];
$socialIcons = [
    'wechat' => ['icon'=>'fa-brands fa-weixin', 'color'=>'#07C160'],
    'qq' => ['icon'=>'fa-brands fa-qq', 'color'=>'#12B7F5'],
    'telegram' => ['icon'=>'fa-brands fa-telegram', 'color'=>'#0088CC'],
    'dy' => ['icon'=>'fa-brands fa-tiktok', 'color'=>'#fff'],
    'bilibili' => ['icon'=>'fa-brands fa-bilibili', 'color'=>'#FB7299'],
    'xiaohongshu' => ['icon'=>'fa-solid fa-book', 'color'=>'#FE2C55'],
    'weibo' => ['icon'=>'fa-brands fa-weibo', 'color'=>'#E6162D'],
    'github' => ['icon'=>'fa-brands fa-github', 'color'=>'#fff'],
    'email' => ['icon'=>'fa-solid fa-envelope', 'color'=>'#EA4335'],
];

// ===== 当前访问者登录态 =====
$visitorLoggedIn = !empty($_SESSION['visitor_id']) && !empty($_SESSION['visitor_login']);

// ===== 解析背景 =====
$bgPreset = $user['bg_preset'] ?? 'default';
$bgCss = '';
$presetBgMap = [
    'default'=> '#0f172a', 'black'=> '#111111', 'purple'=> '#7c3aed',
    'pink'=> '#be185d', 'deep'=> '#0c4a6e', 'cyber'=> '#1e293b', 'gold'=> '#1a1a2e',
];

if ($bgPreset === 'custom') {
    $bgType = $user['custom_bg_type'] ?? 'color';
    if ($bgType === 'gradient') {
        $from = $user['custom_gradient_from'] ?? '#667eea' ?: '#667eea';
        $to   = $user['custom_gradient_to'] ?? '#764ba2' ?: '#764ba2';
        $dir  = $user['custom_gradient_dir'] ?? '135deg' ?: '135deg';
        $bgCss = "background:linear-gradient($dir, $from, $to);";
    } elseif ($bgType === 'image' && ($user['bg_image'] ?? '')) {
        $bgCss = 'background:' . ($user['bg_color'] ?? '#0f172a') . " url('" . ($user['bg_image'] ?? '') . "') center/cover fixed;";
    } else {
        $bgCss = 'background:' . ($user['bg_color'] ?? '#0f172a') . ';';
    }
} else {
    $presetColor = $presetBgMap[$bgPreset] ?? '#0f172a';
    if ($bgPreset === 'deep') {
        $bgCss = "background:$presetColor url('assets/img/background.png') no-repeat center center fixed;background-size:cover;";
    } elseif ($bgPreset === 'gold') {
        $bgCss = "background:#0a0a0a;position:relative;";
        $goldOverlay = true;
    } else {
        $bgCss = "background:$presetColor;";
    }
}

// ===== 按钮样式 =====
$btnInline = '';
if ($user['btn_bg'] ?? '') $btnInline .= 'background:' . ($user['btn_bg'] ?? '') . ';';
if ($user['btn_color'] ?? '') $btnInline .= 'color:' . ($user['btn_color'] ?? '') . ';';
$btnOutlineInline = '';
if ($user['btn_outline'] ?? '') $btnOutlineInline .= 'border-color:' . ($user['btn_outline'] ?? '') . ';color:' . ($user['btn_outline'] ?? '') . ';';

// ===== 检测内置浏览器 =====
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$openTip = '';
if (strpos($ua, 'MicroMessenger') !== false && !empty($user['open_tip_wechat'])) $openTip = '微信';
elseif (strpos($ua, 'QQ/') !== false && !empty($user['open_tip_qq'])) $openTip = 'QQ';
elseif (strpos($ua, 'aweme') !== false && !empty($user['open_tip_douyin'])) $openTip = '抖音';
elseif (strpos($ua, 'Weibo') !== false && !empty($user['open_tip_weibo'])) $openTip = '微博';

// ===== 统计 =====
$totalViews = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$userId AND type='view'")->fetchColumn();
$totalClicks = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$userId AND type='click'")->fetchColumn();

// ===== 配置快捷 =====
$themeMode = $user['theme_mode'] ?? 'auto';
$cardStyle = $user['card_style'] ?? 'glass';
$showStats = $user['show_stats'] ?? 1;
$footerText = $user['footer_text'] ?? '' ?? '' ?: 'Powered by Leaffox主页系统';
$footerAlign = $user['footer_align'] ?? 'center';
$showArrow = $user['btn_arrow'] ?? 1;
$musicUrl = $user['custom_music'] ?? '';
$musicLoop = $user['custom_music_loop'] ?? 0;
$musicAutoplay = $user['custom_music_autoplay'] ?? 0;
$musicIcon = $user['custom_music_icon'] ?? 'b';

// ===== 加载用户主页模版 =====
$userPageTemplate = $user['page_template'] ?? 'default';
$templateCssData = [];
$templateFilePath = __DIR__ . '/../templates/user/' . $userPageTemplate . '.php';
if (file_exists($templateFilePath)) {
    $templateCssData = require $templateFilePath;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?=h($user['nickname'] ?? $user['username'] ?? '')?> 的主页</title>
<link rel="stylesheet" href="<?=rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')?>/assets/css/fontawesome.min.css">
<link rel="stylesheet" href="<?=rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')?>/assets/css/tailwind.css">
<script src="<?=rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')?>/assets/js/qrcode.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
/* ===== 昼夜模式 CSS 变量 ===== */
:root,.dark-mode{
  /* 文字色 */
  --text-primary:#fff;
  --text-secondary:rgba(255,255,255,0.65);
  --text-tertiary:rgba(255,255,255,0.55);
  --text-muted:rgba(255,255,255,0.35);
  --text-muted2:rgba(255,255,255,0.25);
  --text-body:#e2e8f0;
  --text-card-title:#1f2937;
  --text-card-sub:#6b7280;
  --text-card-arrow:#9ca3af;
  /* 背景色 */
  --bg-glass:#fff;
  --bg-glass-hover:#f1f3f5;
  --bg-subtle:#fff;
  --bg-hover:#f1f3f5;
  --bg-overlay:rgba(0,0,0,0.85);
  --bg-modal:#fff;
  --bg-report:#fff;
  --bg-input:#f1f3f5;
  --bg-btn:#fff;
  --bg-social:#fff;
  /* 边框色 */
  --border:rgba(0,0,0,0.08);
  --border-subtle:rgba(0,0,0,0.05);
  --border-faint:rgba(0,0,0,0.03);
  /* 遮罩 */
  --overlay:rgba(0,0,0,0.65);
  /* 图标 */
  --icon-color:var(--text-secondary);
  --icon-hover:#fff;
  /* 加载 */
  --loader-bg:rgba(255,255,255,0.1);
  /* 阴影 */
  --shadow-sm:0 2px 8px rgba(0,0,0,0.12);
  --shadow-md:0 4px 20px rgba(0,0,0,0.15);
  --shadow-lg:0 8px 32px rgba(0,0,0,0.2);
  /* 音乐播放器 & 免费制作 */
  --float-bg:#fff;
  --float-border:rgba(0,0,0,0.08);
}
.light-mode{
  --text-primary:#1e293b;
  --text-secondary:#475569;
  --text-tertiary:#64748b;
  --text-muted:#94a3b8;
  --text-muted2:#b0b8c4;
  --text-body:#334155;
  --text-card-title:#1e293b;
  --text-card-sub:#64748b;
  --text-card-arrow:#94a3b8;
  --bg-glass:#fff;
  --bg-glass-hover:#f1f3f5;
  --bg-subtle:rgba(0,0,0,0.04);
  --bg-hover:#f1f3f5;
  --bg-overlay:rgba(255,255,255,0.9);
  --bg-modal:#fff;
  --bg-report:#fff;
  --bg-input:#f1f3f5;
  --bg-btn:#f1f3f5;
  --bg-social:#f1f3f5;
  --border:rgba(0,0,0,0.1);
  --border-subtle:rgba(0,0,0,0.06);
  --border-faint:rgba(0,0,0,0.04);
  --overlay:rgba(255,255,255,0.7);
  --icon-color:#475569;
  --icon-hover:#1e293b;
  --loader-bg:rgba(0,0,0,0.06);
  --shadow-sm:0 2px 8px rgba(0,0,0,0.06);
  --shadow-md:0 4px 20px rgba(0,0,0,0.08);
  --shadow-lg:0 8px 30px rgba(0,0,0,0.1);
  --float-bg:#fff;
  --float-border:rgba(0,0,0,0.08);
}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
/* 全屏加载等待 */
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
@keyframes slideDown{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:translateY(0)}}
@keyframes zoomIn{from{opacity:0;transform:scale(0.9)}to{opacity:1;transform:scale(1)}}
@keyframes glowPulse{0%,100%{box-shadow:0 0 8px rgba(99,102,241,0.1)}50%{box-shadow:0 0 20px rgba(99,102,241,0.25)}}
@keyframes shimmerBorder{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
@keyframes dotBounce{0%,100%{opacity:0.3;transform:translateY(0)}50%{opacity:1;transform:translateY(-3px)}}
@keyframes cardFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
@keyframes ripple{to{transform:scale(4);opacity:0}}
@keyframes poweredGlow{0%,100%{text-shadow:0 0 4px rgba(99,102,241,0.3)}50%{text-shadow:0 0 12px rgba(99,102,241,0.6)}}

body{
  min-height:100vh;<?=$bgCss?>
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"PingFang SC","Microsoft YaHei",sans-serif;
  display:flex;flex-direction:column;align-items:center;
  color:var(--text-body);-webkit-font-smoothing:antialiased;overflow-x:hidden;
}
body::before{
  content:'';position:fixed;inset:0;
  background:linear-gradient(180deg,rgba(0,0,0,0.35) 0%,rgba(0,0,0,0.05) 40%,rgba(0,0,0,0.35) 100%);
  pointer-events:none;z-index:0;
}
<?php if (!empty($goldOverlay)): ?>
body::after{
  content:'';position:fixed;top:0;left:50%;transform:translateX(-50%);
  width:600px;height:600px;
  background:radial-gradient(ellipse at center, rgba(255,215,0,0.12) 0%, transparent 70%);
  pointer-events:none;z-index:0;
}
<?php endif; ?>
.page-wrap{
  position:relative;z-index:1;width:100%;max-width:480px;
  padding:50px 24px 50px;display:flex;flex-direction:column;
  align-items:center;min-height:100vh;
  animation:fadeUp 0.6s ease;
}

/* 外部浏览器提示条 */
.open-tip-bar{
  width:100%;max-width:480px;position:fixed;top:0;z-index:999;
  background:var(--bg-overlay);backdrop-filter:blur(20px);
  padding:12px 20px;text-align:center;font-size:13px;color:var(--text-body);
  animation:slideDown 0.4s ease;
  border-bottom:1px solid var(--border-faint);
}
.open-tip-bar .tip-btn{
  display:inline-block;margin-left:10px;padding:5px 16px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);color:var(--text-primary);
  border-radius:12px;text-decoration:none;font-size:12px;font-weight:600;
  transition:opacity 0.2s;
}
.open-tip-bar .tip-btn:hover{opacity:0.85}
.open-tip-bar .tip-close{
  float:right;cursor:pointer;opacity:0.5;padding:2px 8px;font-size:16px;
}
.open-tip-bar .tip-arrow-icon{
  width:16px;height:16px;vertical-align:middle;margin-right:4px;opacity:0.7;
}

/* 头像 */
.avatar-wrap{
  width:120px;height:120px;border-radius:50%;
  background:var(--bg-glass);backdrop-filter:blur(10px);
  border:3px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;margin-bottom:20px;
  animation:fadeUp 0.6s ease both;
  box-shadow:var(--shadow-lg);
  transition:all 0.5s cubic-bezier(0.34,1.56,0.64,1);
}
.avatar-wrap:hover{
  transform:scale(1.08) rotate(-3deg);
  border-color:rgba(99,102,241,0.5);
  box-shadow:0 12px 40px rgba(99,102,241,0.2);
}
.avatar-wrap img{width:100%;height:100%;object-fit:cover;transition:transform 0.5s}
.avatar-wrap:hover img{transform:scale(1.1)}
.avatar-wrap .no-avatar{font-size:40px;color:var(--text-primary);font-weight:700}

.profile-name{font-size:25px;font-weight:900;color:var(--text-primary);margin-bottom:4px;animation:fadeUp 0.6s ease 0.1s both;transition:color 0.3s}
.profile-name:hover{background:linear-gradient(90deg,#6366f1,#a78bfa,#6366f1);background-size:200% auto;background-clip:text;-webkit-background-clip:text;-webkit-text-fill-color:transparent}

.profile-bio{font-size:16px;font-weight:500;color:var(--text-secondary);text-align:center;max-width:380px;line-height:1.65;margin-bottom:30px;animation:fadeUp 0.6s ease 0.15s both}

/* 公告区域 */
.announcement-box{
  width:100%;padding:16px 22px;border-radius:16px;margin-bottom:24px;
  background:var(--bg-subtle);border:1px solid var(--border-subtle);
  font-size:14px;line-height:1.7;color:var(--text-primary);
  animation:fadeUp 0.6s ease 0.2s both;
  transition:all 0.3s;
  position:relative;overflow:hidden;
}
.announcement-box::before{
  content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;
  background:linear-gradient(90deg,transparent,var(--border-faint),transparent);
  transition:left 0.8s;
}
.announcement-box:hover::before{left:150%}
.announcement-box:hover{background:var(--bg-hover);border-color:rgba(99,102,241,0.3);transform:translateY(-1px);box-shadow:var(--shadow-md)}

/* ---- 链接卡片 ---- */
.links-wrap{width:100%;display:flex;flex-direction:column;gap:12px}

/* Glass */
.card-glass{
  display:flex;align-items:center;gap:16px;
  padding:18px 24px;border-radius:12px;text-decoration:none;
  animation:fadeUp 0.5s ease both;
  
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  background:var(--bg-glass);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid var(--border);position:relative;overflow:hidden;cursor:pointer;
}
.card-glass::before{
  content:'';position:absolute;top:0;left:-100%;width:60%;height:100%;
  background:linear-gradient(90deg,transparent,var(--border-faint),transparent);
  transition:left 0.6s;
}
.card-glass:hover::before{left:150%}
.card-glass:hover{transform:translateY(-3px) scale(1.02);background:var(--bg-glass-hover);border-color:rgba(99,102,241,0.3);box-shadow:var(--shadow-lg),0 0 20px rgba(99,102,241,0.1)}
.card-glass:active{transform:scale(0.97)}
.card-glass.outline{background:var(--bg-glass);border:2px solid var(--border)}
.card-glass.outline:hover{background:var(--bg-glass-hover);border-color:rgba(99,102,241,0.4);box-shadow:0 0 20px rgba(99,102,241,0.12)}

/* Neumorphism */
.card-neumorphism{
  display:flex;align-items:center;gap:16px;
  padding:18px 24px;border-radius:16px;text-decoration:none;
  animation:fadeUp 0.5s ease both;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);cursor:pointer;
  background:var(--bg-glass);
  box-shadow:0 4px 20px rgba(0,0,0,0.08);
  position:relative;overflow:hidden;
}
.card-neumorphism::before{
  content:'';position:absolute;top:0;left:-100%;width:60%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.05),transparent);
  transition:left 0.6s;
}
.card-neumorphism:hover::before{left:150%}
.card-neumorphism:hover{box-shadow:0 6px 24px rgba(0,0,0,0.12),0 0 20px rgba(99,102,241,0.08);transform:translateY(-3px) scale(1.01)}
.card-neumorphism:active{transform:scale(0.97)}
.card-neumorphism.outline{border:2px solid var(--border);box-shadow:none}
.card-neumorphism.outline:hover{border-color:rgba(99,102,241,0.35);background:rgba(99,102,241,0.04)}

/* Minimal */
.card-minimal{
  display:flex;align-items:center;gap:16px;
  padding:14px 16px;border-radius:12px;text-decoration:none;
  animation:fadeUp 0.5s ease both;
  transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1);cursor:pointer;
  border-bottom:1px solid var(--border-faint);
  position:relative;overflow:hidden;
}
.card-minimal::before{
  content:'';position:absolute;left:0;top:0;width:3px;height:100%;
  background:linear-gradient(180deg,#6366f1,#a78bfa);
  transform:scaleY(0);transition:transform 0.3s;
  border-radius:0 2px 2px 0;
}
.card-minimal:hover::before{transform:scaleY(1)}
.card-minimal:hover{background:var(--bg-glass-hover);padding-left:20px;transform:translateX(2px)}
.card-minimal:active{transform:translateX(0) scale(0.98)}
.card-minimal.outline{border:2px solid var(--border-subtle);border-radius:8px;border-bottom:2px solid var(--border-subtle)}
.card-minimal.outline:hover{border-color:rgba(99,102,241,0.3);background:var(--bg-glass-hover);box-shadow:0 0 15px rgba(99,102,241,0.08)}

.text-center{justify-content:center}
.card-icon{font-size:26px;width:34px;text-align:center;flex-shrink:0;line-height:1;transition:transform 0.3s;display:flex;align-items:center;justify-content:center}
.card-icon img{width:32px;height:32px;border-radius:8px;object-fit:cover}
.card-glass:hover .card-icon,.card-neumorphism:hover .card-icon{transform:scale(1.15) rotate(-5deg)}
.card-info{flex:1;min-width:0}
.card-title{font-size:16px;font-weight:700;color:var(--text-card-title);margin-bottom:2px;transition:color 0.3s}
.card-glass:hover .card-title,.card-neumorphism:hover .card-title,.card-minimal:hover .card-title{color:var(--text-card-title)}
.card-sub{font-size:12px;font-weight:500;color:var(--text-card-sub);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.card-arrow{color:var(--text-card-arrow);font-size:15px;font-weight:600;transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);flex-shrink:0;display:<?=$showArrow?'flex':'none'?>}
.card-glass:hover .card-arrow,.card-neumorphism:hover .card-arrow,.card-minimal:hover .card-arrow{transform:translateX(6px) scale(1.2);color:rgba(99,102,241,0.7)}
.card-lock{font-size:12px;margin-left:4px;color:var(--text-card-sub)}
.card-tag{font-size:11px;padding:2px 8px;border-radius:10px;background:var(--bg-glass);color:var(--text-muted);margin-left:8px}

/* 文字模块 */
.text-block{
  padding:18px 24px;font-size:15px;line-height:1.8;color:var(--text-secondary);
  animation:fadeUp 0.5s ease both;
  border-radius:16px;cursor:pointer;transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1);
  border:1px solid transparent;
}
.text-block:hover{background:var(--bg-hover);transform:translateY(-1px);border-color:var(--border-faint);box-shadow:var(--shadow-md)}

/* 图片模块 */
.picture-block{
  width:100%;border-radius:16px;overflow:hidden;
  background:var(--bg-glass);padding:8px;
  animation:fadeUp 0.5s ease both;cursor:pointer;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  position:relative;
}
.picture-block:hover{transform:scale(1.03);box-shadow:0 12px 40px rgba(0,0,0,0.25)}
.picture-block:active{transform:scale(0.99)}
.picture-block img{width:100%;display:block;border-radius:4px;transition:filter 0.3s}
.picture-block:hover img{filter:brightness(1.05)}

/* 视频自动展开播放 */
.video-expand-block{width:100%;margin:8px 0;background:var(--bg-glass);border-radius:8px;padding:12px;animation:fadeUp 0.5s ease both}
.video-expand-title{font-size:14px;font-weight:600;margin-bottom:8px;padding:0 4px}
.video-expand-player{width:100%}
.video-expand-player video{width:100%;display:block;background:#000}
.video-expand-player iframe{width:100%;height:100%;display:block}

/* 社交 */
.social-wrap{
  display:flex;gap:10px;flex-wrap:wrap;justify-content:center;
  margin-top:32px;animation:fadeUp 0.6s ease 0.4s both;
}
.social-item{
  width:48px;height:48px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  text-decoration:none;font-size:22px;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  background:var(--bg-social);backdrop-filter:blur(10px);
  border:1px solid var(--border-subtle);
  color:var(--text-secondary);
}
.social-item:hover{
  transform:translateY(-4px) scale(1.1);
  background:rgba(255,255,255,0.14);color:var(--text-primary);
  box-shadow:0 8px 25px rgba(0,0,0,0.2),0 0 15px rgba(99,102,241,0.15);
  border-color:rgba(99,102,241,0.3);
}
.social-item:active{transform:scale(0.9)}

/* 打赏 */
.tipping-btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:12px 28px;border-radius:50px;
  background:linear-gradient(135deg,rgba(244,63,94,0.2),rgba(244,63,94,0.1));
  border:1px solid rgba(244,63,94,0.25);
  color:var(--text-primary);font-size:14px;font-weight:500;
  cursor:pointer;text-decoration:none;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  backdrop-filter:blur(10px);
}
.tipping-btn:hover{
  transform:translateY(-3px) scale(1.05);
  background:linear-gradient(135deg,rgba(244,63,94,0.35),rgba(244,63,94,0.2));
  border-color:rgba(244,63,94,0.5);
  box-shadow:0 8px 25px rgba(244,63,94,0.2);
  color:var(--text-primary);
}
.tipping-btn:active{transform:scale(0.95)}

/* 打赏弹窗 */
.tipping-qr-img{max-width:220px;border-radius:16px;margin:16px auto;display:block;}

/* 统计 */
.stats-bar{display:flex;gap:24px;margin-top:24px;animation:fadeUp 0.6s ease 0.45s both;padding:12px 20px;border-radius:16px;background:var(--bg-subtle);backdrop-filter:blur(10px);border:1px solid var(--border-faint)}
.stats-bar span{font-size:14px;font-weight:600;color:var(--text-muted);transition:color 0.3s}
.stats-bar span:hover{color:var(--text-tertiary)}

/* 页脚 */
.footer-text{
  font-size:12px;color:var(--text-muted);
  margin-top:20px;width:100%;
  animation:fadeUp 0.6s ease 0.5s both;
}
.footer-text .has-powered{
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.03),transparent);
  background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;
  border-top:1px solid var(--border-faint);padding-top:16px;margin-top:16px;
  position:relative;
}
.footer-text .powered-icon{display:inline-block;margin-right:4px;font-size:10px;animation:poweredGlow 2s ease-in-out infinite}
.footer-text a{color:var(--text-muted);text-decoration:none;transition:color 0.3s;position:relative}
.footer-text a::after{
  content:'';position:absolute;bottom:-2px;left:0;width:0;height:1px;
  background:linear-gradient(90deg,#6366f1,#a78bfa);
  transition:width 0.3s;
}
.footer-text a:hover::after{width:100%}
.footer-text a:hover{color:var(--text-secondary);text-decoration:none}

/* ---- 免费制作悬浮按钮 ---- */
.free-make-wrap{
  position:fixed;bottom:24px;left:0;right:0;z-index:100;
  display:flex;justify-content:center;pointer-events:none;
}
.free-make-btn{
  pointer-events:auto;
  display:flex;align-items:center;gap:8px;
  padding:12px 26px;border-radius:50px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);
  color:var(--text-primary);font-size:14px;font-weight:600;
  cursor:pointer;text-decoration:none;
  box-shadow:0 4px 20px rgba(99,102,241,0.35);
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  animation:zoomIn 0.6s ease 0.6s both;
  border:1px solid var(--float-border);
  white-space:nowrap;
}
.free-make-btn:hover{
  transform:translateY(-3px) scale(1.04);
  box-shadow:0 8px 30px rgba(99,102,241,0.5);
}
.free-make-btn:active{
  transform:scale(0.97);
}
.free-make-btn .free-sparkle{
  font-size:16px;animation:poweredGlow 2s ease-in-out infinite;
}
/* 有音乐播放器时，整个容器往上挪避免重叠 */
.music-player-btn ~ .free-make-wrap,
.free-make-wrap + .music-player-btn { bottom:80px; }
@media(max-width:480px){
  .free-make-btn{padding:8px 16px;font-size:12px}
  .music-player-btn ~ .free-make-wrap,
  .free-make-wrap + .music-player-btn { bottom:68px; }
}

/* ---- 举报弹窗 ---- */
.report-overlay{
  position:fixed;inset:0;z-index:9999;
  background:var(--overlay);
  display:none;align-items:center;justify-content:center;
  backdrop-filter:blur(4px);
  animation:fadeIn 0.2s ease;
}
.report-box{
  background:var(--bg-modal);border:1px solid var(--border-subtle);box-shadow:var(--shadow-lg);
  border-radius:12px;padding:28px 24px 20px;
  width:90%;max-width:380px;
  animation:scaleIn 0.25s ease;
}
@keyframes scaleIn{
  from{transform:scale(0.92);opacity:0}
  to{transform:scale(1);opacity:1}
}
.report-box h3{
  color:var(--text-primary);font-size:17px;font-weight:600;margin:0 0 4px;
}
.report-box .sub{
  color:var(--text-secondary);font-size:12px;margin:0 0 18px;
}
.report-types{
  display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;
}
.report-type-btn{
  padding:10px 8px;border-radius:16px;
  background:var(--bg-subtle);border:1px solid var(--border-faint);
  color:var(--text-secondary);font-size:13px;cursor:pointer;
  transition:all 0.2s;text-align:center;
}
.report-type-btn:hover{
  background:rgba(99,102,241,0.12);border-color:rgba(99,102,241,0.25);color:var(--text-primary);
}
.report-type-btn.selected{
  background:rgba(99,102,241,0.2);border-color:#6366f1;color:var(--text-primary);font-weight:500;
}
.report-reason{
  width:100%;padding:12px 16px;border-radius:16px;
  background:var(--bg-input);border:1.5px solid var(--border-subtle);
  color:var(--text-body);font-size:14px;outline:none;resize:none;
  box-sizing:border-box;margin-bottom:16px;font-family:inherit;
  transition:border-color 0.2s;
}
.report-reason:focus{border-color:#6366f1;}
.report-reason::placeholder{color:var(--text-muted);}
.report-actions{
  display:flex;gap:10px;
}
.report-actions button{
  flex:1;padding:12px;border-radius:14px;font-size:14px;font-weight:500;
  cursor:pointer;transition:all 0.2s;border:none;outline:none;
}
.report-cancel-btn{
  background:var(--bg-hover);color:var(--text-muted);
}
.report-cancel-btn:hover{background:var(--bg-glass-hover);color:var(--text-primary);border-color:var(--border);}
.report-submit-btn{
  background:var(--bg-glass);color:var(--text-primary);
  box-shadow:var(--shadow-sm);
  border:1px solid var(--border-subtle);
}
.report-submit-btn:hover{background:var(--bg-glass-hover);box-shadow:var(--shadow-md);}
.report-submit-btn:disabled{opacity:0.4;cursor:not-allowed;box-shadow:none;}
.report-toast{
  position:fixed;bottom:80px;left:50%;transform:translateX(-50%);
  background:rgba(16,185,129,0.9);color:var(--text-primary);padding:10px 24px;
  border-radius:50px;font-size:13px;font-weight:500;
  z-index:99999;animation:fadeUp 0.3s ease;
  backdrop-filter:blur(8px);white-space:nowrap;
}

/* 音乐播放器按钮 */
.music-player-btn{
  position:fixed;bottom:28px;right:28px;z-index:100;
  width:54px;height:54px;border-radius:50%;
  background:var(--float-bg);backdrop-filter:blur(20px);
  border:1px solid var(--float-border);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:all 0.3s;
  animation:zoomIn 0.5s ease 0.8s both;
  box-shadow:0 6px 24px rgba(0,0,0,0.2);
}
.music-player-btn:hover{transform:scale(1.1);background:var(--bg-hover)}
.music-player-btn.playing{animation:spin 4s linear infinite}
.music-player-btn .m-icon{font-size:22px;color:var(--text-secondary)}
.music-player-btn .m-icon-img{width:22px;height:22px;display:block;opacity:0.85}

/* ---- 主页顶部链接栏 ---- */
.top-link-bar{
  width:100%;
  display:flex;align-items:center;gap:8px;
  padding:12px 18px;margin-bottom:20px;
  border-radius:14px;
  background:var(--bg-subtle);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  border:1px solid var(--border-subtle);
  animation:fadeUp 0.6s ease 0.2s both;
  transition:all 0.3s;
}
.top-link-bar:hover{border-color:rgba(99,102,241,0.25);background:rgba(255,255,255,0.08)}
.top-link-bar .link-text{
  flex:1;min-width:0;
  font-size:12px;color:var(--text-tertiary);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  font-family:monospace;
  cursor:pointer;padding:2px 4px;border-radius:4px;transition:background 0.2s;
  -webkit-user-select:all;user-select:all;
}
.top-link-bar .link-text:hover{background:rgba(255,255,255,0.06)}
.top-link-bar .bar-btn{
  display:inline-flex;align-items:center;gap:4px;
  padding:6px 12px;border-radius:8px;
  font-size:11px;font-weight:600;border:none;cursor:pointer;
  transition:all 0.3s;white-space:nowrap;flex-shrink:0;
  background:rgba(255,255,255,0.08);color:var(--text-secondary);
  text-decoration:none;
}
.top-link-bar .bar-btn:hover{background:rgba(99,102,241,0.2);color:#818cf8;transform:translateY(-1px)}
.top-link-bar .bar-btn:active{transform:scale(0.95)}
.top-link-bar .bar-btn.copy-ok{background:rgba(16,185,129,0.2);color:#34d399}

/* ---- 分享弹窗 ---- */
.share-modal-overlay{
  position:fixed;inset:0;z-index:2000;
  background:var(--overlay);backdrop-filter:blur(15px);-webkit-backdrop-filter:blur(15px);
  display:flex;align-items:center;justify-content:center;
  animation:fadeUp 0.3s ease;
}
.share-modal-box{
  background:var(--bg-modal);backdrop-filter:blur(20px);
  border:1px solid var(--border-subtle);
  border-radius:12px;padding:30px 26px 22px;max-width:360px;width:90%;
  text-align:center;animation:zoomIn 0.3s ease;
}
.share-modal-box h3{font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:4px}
.share-modal-box .sub{font-size:14px;font-weight:500;color:var(--text-secondary);margin-bottom:20px}
.share-modal-box .share-url-row{
  display:flex;align-items:center;gap:8px;
  background:var(--bg-input);border:1px solid var(--border-subtle);
  border-radius:12px;padding:10px 12px;margin-bottom:16px;
}
.share-modal-box .share-url-row .url-text{
  flex:1;min-width:0;
  font-size:12px;color:var(--text-body);font-family:monospace;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  -webkit-user-select:all;user-select:all;text-align:left;
}
.share-modal-box .share-url-row .copy-url-btn{
  flex-shrink:0;padding:6px 14px;border-radius:8px;
  font-size:11px;font-weight:600;border:none;cursor:pointer;
  background:rgba(99,102,241,0.2);color:#818cf8;
  transition:all 0.3s;white-space:nowrap;
}
.share-modal-box .share-url-row .copy-url-btn:hover{background:rgba(99,102,241,0.3)}
.share-modal-box .share-url-row .copy-url-btn.copied{background:rgba(16,185,129,0.2);color:#34d399}
.share-modal-box .qrcode-area{
  display:flex;justify-content:center;margin-bottom:16px;
}
.share-modal-box .qrcode-area .qr-wrap{
  position:relative;display:inline-block;
  background:#fff;padding:10px;border-radius:8px;
  box-shadow:0 8px 30px rgba(0,0,0,0.2);
}
.share-modal-box .close-share-btn{
  margin-top:8px;padding:10px 0;width:100%;border-radius:12px;
  border:none;cursor:pointer;font-size:14px;font-weight:600;
  background:var(--bg-hover);color:var(--text-muted);
  transition:all 0.3s;
}
.share-modal-box .close-share-btn:hover{background:var(--bg-input);color:var(--text-primary)}

/* 密码弹窗 */
.modal-overlay{
  position:fixed;inset:0;z-index:1000;
  background:var(--overlay);backdrop-filter:blur(10px);
  display:flex;align-items:center;justify-content:center;
  animation:fadeUp 0.3s ease;
}
.modal-box{
  background:var(--bg-modal);backdrop-filter:blur(20px);
  border:1px solid var(--border);
  border-radius:12px;padding:32px 28px;max-width:340px;width:90%;
  text-align:center;
}
.modal-box h3{font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:8px}
.modal-box p{font-size:14px;font-weight:500;color:var(--text-secondary);margin-bottom:20px}
.modal-box input[type=password]{
  width:100%;padding:12px 16px;border-radius:16px;
  border:1.5px solid var(--border-subtle);
  background:var(--bg-input);color:var(--text-body);font-size:16px;text-align:center;
  outline:none;transition:all 0.3s;
  letter-spacing:12px;
  box-shadow:inset 0 2px 4px rgba(0,0,0,0.1);
}
.modal-box input[type=password]:focus{border-color:rgba(99,102,241,0.5);background:#eef2ff;box-shadow:0 0 0 3px rgba(99,102,241,0.15),inset 0 2px 4px rgba(0,0,0,0.1)}
.modal-box input[type=password]::placeholder{letter-spacing:0;font-size:14px;color:var(--text-muted2)}
.modal-box .modal-err{font-size:12px;color:#ef4444;margin-top:10px;display:none}
.modal-box .modal-btn{
  margin-top:16px;width:100%;padding:14px;border-radius:14px;
  background:var(--bg-glass);color:var(--text-primary);
  border:1px solid var(--border-subtle);font-size:15px;font-weight:700;cursor:pointer;
  transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  letter-spacing:0.5px;
  position:relative;overflow:hidden;
  box-shadow:var(--shadow-sm);
}
.modal-box .modal-btn:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg);opacity:1}
.modal-box .modal-btn:active{transform:translateY(0) scale(0.98)}
.modal-box .modal-btn::after{
  content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(0,0,0,0.03),transparent);
  transition:left 0.5s;
}
.modal-box .modal-btn:hover::after{left:100%}

/* ============================================================
   响应式布局 — 全面适配手机/平板/电脑/大屏
   ============================================================ */
/* ---------- 手机 (≤480px) ---------- */
@media(max-width:480px){
  .page-wrap{padding:40px 16px 40px}
  .avatar-wrap{width:80px;height:80px}
  .profile-name{font-size:22px}
  .open-tip-bar{font-size:12px;padding:10px 14px}
}
/* ---------- 小平板 / 大手机 (481-768px) ---------- */
@media(min-width:481px){
  .page-wrap{max-width:540px;padding:60px 28px 50px}
  .avatar-wrap{width:100px;height:100px}
  .profile-name{font-size:26px}
  .profile-bio{font-size:17px;max-width:420px}
  .card-glass,.card-neumorphism,.card-minimal{padding:18px 22px}
  .card-title{font-size:17px}
  .card-sub{font-size:13px}
  .card-icon{font-size:30px;width:38px}
  .card-icon img{width:36px;height:36px}
  .interact-btn{padding:9px 20px;font-size:15px}
  .text-block{font-size:15px;padding:18px 22px}
  .stats-bar{gap:24px}
  .social-item{width:48px;height:48px;font-size:22px}
  .open-tip-bar{max-width:540px}
  .free-make-btn{padding:10px 24px;font-size:14px}
}
/* ---------- 电脑 (769-1024px) ---------- */
@media(min-width:769px){
  .page-wrap{max-width:600px;padding:70px 32px 60px}
  .avatar-wrap{width:120px;height:120px;border-width:3px}
  .profile-name{font-size:28px}
  .profile-bio{font-size:18px;max-width:460px}
  .card-glass,.card-neumorphism,.card-minimal{padding:20px 26px;border-radius:12px}
  .card-title{font-size:18px}
  .card-sub{font-size:14px}
  .card-icon{font-size:32px;width:42px}
  .card-icon img{width:40px;height:40px;border-radius:12px}
  .card-arrow{font-size:17px}
  .interact-btn{padding:10px 24px;font-size:16px;border-radius:36px}
  .text-block{font-size:16px;padding:20px 26px;border-radius:12px}
  .links-wrap{gap:14px}
  .stats-bar{gap:28px;padding:12px 20px}
  .social-item{width:52px;height:52px;font-size:24px;border-radius:14px}
  .announcement-box{padding:16px 22px;font-size:14px}
  .top-link-bar{padding:12px 18px}
  .top-link-bar .link-text{font-size:13px}
  .top-link-bar .bar-btn{font-size:12px;padding:7px 14px}
  .open-tip-bar{max-width:600px}
  .free-make-btn{padding:12px 28px;font-size:14px;border-radius:60px}
  .music-player-btn{width:54px;height:54px;bottom:28px;right:28px}
  .music-player-btn .m-icon{font-size:24px}
  .music-player-btn .m-icon-img{width:24px;height:24px}
}
/* ---------- 大屏 (≥1025px) ---------- */
@media(min-width:1025px){
  .page-wrap{max-width:680px;padding:80px 40px 70px}
  .avatar-wrap{width:130px;height:130px;border-width:4px}
  .profile-name{font-size:34px}
  .profile-bio{font-size:20px;max-width:500px}
  .card-glass,.card-neumorphism,.card-minimal{padding:22px 30px;border-radius:12px}
  .card-title{font-size:19px}
  .card-sub{font-size:15px}
  .card-icon{font-size:36px;width:46px}
  .card-icon img{width:44px;height:44px;border-radius:14px}
  .card-arrow{font-size:18px}
  .interact-btn{padding:12px 28px;font-size:17px}
  .text-block{font-size:17px;padding:22px 30px;border-radius:12px}
  .links-wrap{gap:16px}
  .social-item{width:56px;height:56px;font-size:26px}
  .free-make-btn{padding:12px 32px;font-size:15px}
}
<?php if (!empty($templateCssData['css'])): ?>
/* === 用户主页模版: <?=h($userPageTemplate)?> === */
<?=$templateCssData['css']?>

/* ===== 昼夜切换按钮 ===== */
.theme-toggle{
  position:fixed;top:18px;right:18px;z-index:9998;
  width:44px;height:44px;border-radius:50%;
  background:var(--float-bg);backdrop-filter:blur(12px);
  border:1px solid var(--float-border);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  box-shadow:var(--shadow-md);
  font-size:18px;color:var(--text-tertiary);
  animation:fadeUp 0.6s ease 0.1s both;
}
.theme-toggle:hover{
  transform:scale(1.12) rotate(15deg);
  background:var(--bg-glass-hover);
  color:var(--text-primary);
  box-shadow:var(--shadow-lg);
}
.theme-toggle:active{transform:scale(0.9)}
@keyframes themeIconSpin{
  0%{transform:rotate(0deg) scale(0.5);opacity:0}
  100%{transform:rotate(360deg) scale(1);opacity:1}
}
.theme-toggle.switching #themeIcon{
  animation:themeIconSpin 0.4s ease;
}
@media(max-width:480px){
  .theme-toggle{top:12px;right:12px;width:36px;height:36px;font-size:16px}
}
@media(min-width:769px){
  .theme-toggle{top:20px;right:24px;width:44px;height:44px;font-size:20px}
}
@media(min-width:1025px){
  .theme-toggle{top:24px;right:32px;width:48px;height:48px;font-size:22px}
}


/* ===== 互动功能 ===== */
.interaction-bar{
  display:flex;align-items:center;justify-content:center;gap:24px;
  margin-top:20px;padding:10px 16px;width:100%;
  animation:fadeUp 0.6s ease 0.45s both;
}
.interact-btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 22px;border-radius:50px;
  background:var(--bg-glass);border:1px solid var(--border-subtle);
  color:var(--text-secondary);font-size:14px;cursor:pointer;
  transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1);
  text-decoration:none;user-select:none;
  box-shadow:var(--shadow-sm);
}
.interact-btn:hover{background:var(--bg-glass-hover);border-color:var(--border);transform:translateY(-2px);box-shadow:var(--shadow-md)}
.interact-btn:active{transform:scale(0.93)}
.interact-btn .ib-icon{font-size:16px;transition:transform 0.3s}
.interact-btn:hover .ib-icon{transform:scale(1.15)}
.interact-btn .ib-count{font-size:14px;font-weight:700;min-width:20px;text-align:center}
.interact-btn.liked{background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);color:#f43f5e}
.interact-btn.liked .ib-icon{color:#f43f5e}
.interact-btn.liked:hover{background:rgba(239,68,68,0.18);border-color:rgba(239,68,68,0.4)}
.interact-btn.favorited{background:rgba(250,204,21,0.12);border-color:rgba(250,204,21,0.3);color:#eab308}
.interact-btn.favorited .ib-icon{color:#eab308}
.interact-btn.favorited:hover{background:rgba(250,204,21,0.18);border-color:rgba(250,204,21,0.4)}
.interact-btn.needs-login{opacity:0.6}
/* 评论区域 */
.comment-section{
  width:100%;margin-top:12px;
  animation:fadeUp 0.5s ease both;
}
.comment-toggle{
  display:flex;align-items:center;justify-content:center;gap:6px;
  width:100%;padding:10px;border-radius:12px;
  background:var(--bg-subtle);border:1px solid var(--border-faint);
  color:var(--text-tertiary);font-size:13px;cursor:pointer;
  transition:all 0.3s;
}
.comment-toggle:hover{background:var(--bg-hover);border-color:var(--border-subtle);color:var(--text-secondary)}
.comment-box{
  width:100%;margin-top:10px;display:none;
  border-radius:16px;background:var(--bg-subtle);
  border:1px solid var(--border-subtle);overflow:hidden;
}
.comment-box.open{display:block}
.comment-list-wrap{max-height:360px;overflow-y:auto;padding:12px 14px;scrollbar-width:thin}
.comment-list-wrap::-webkit-scrollbar{width:4px}
.comment-list-wrap::-webkit-scrollbar-thumb{background:var(--border-scrollbar,var(--border-faint));border-radius:4px}
.comment-item{
  display:flex;gap:10px;padding:10px 0;
  border-bottom:1px solid var(--border-faint);animation:fadeUp 0.3s ease both;
}
.comment-item:last-child{border-bottom:none}
.comment-avatar{width:32px;height:32px;border-radius:50%;background:var(--bg-glass);flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center}
.comment-avatar img{width:100%;height:100%;object-fit:cover}
.comment-avatar .ca-placeholder{font-size:14px;font-weight:700;color:var(--text-primary)}
.comment-body{flex:1;min-width:0}
.comment-name{font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:2px}
.comment-text{font-size:14px;color:var(--text-secondary);line-height:1.6;word-break:break-word}
.comment-time{font-size:12px;color:var(--text-muted);margin-top:4px}
.comment-input-wrap{
  display:flex;gap:10px;padding:12px 16px;
  border-top:1px solid var(--border-faint);
  background:var(--bg-hover);
}
.comment-input{
  flex:1;padding:12px 16px;border-radius:14px;
  background:var(--bg-input);border:1.5px solid var(--border-subtle);
  color:var(--text-body);font-size:14px;outline:none;
  transition:border-color 0.2s;
  font-family:inherit;
}
.comment-input:focus{border-color:rgba(99,102,241,0.4)}
.comment-input::placeholder{color:var(--text-muted2)}
.comment-submit-btn{
  padding:12px 22px;border-radius:14px;border:1px solid var(--border-subtle);
  background:var(--bg-glass);color:var(--text-secondary);
  font-size:14px;font-weight:600;cursor:pointer;
  transition:all 0.3s;white-space:nowrap;
  opacity:0.5;
  box-shadow:var(--shadow-sm);
}
.comment-submit-btn.active{opacity:1;background:var(--bg-glass);color:var(--text-primary);}
.comment-submit-btn:hover{box-shadow:var(--shadow-md);transform:translateY(-1px);background:var(--bg-glass-hover)}
.comment-submit-btn:active{transform:scale(0.96)}
.comment-empty{padding:24px;text-align:center;color:var(--text-muted);font-size:13px}
.comment-loading{text-align:center;padding:16px;color:var(--text-muted);font-size:13px}
/* 登录弹窗 */
.login-modal-overlay{
  position:fixed;inset:0;z-index:9999;
  background:var(--overlay);backdrop-filter:blur(15px);-webkit-backdrop-filter:blur(15px);
  display:none;align-items:center;justify-content:center;
  animation:fadeUp 0.3s ease;
}
.login-modal-box{
  background:var(--bg-modal);backdrop-filter:blur(20px);
  border:1px solid var(--border-subtle);
  border-radius:12px;padding:32px 28px;max-width:360px;width:90%;
  text-align:center;animation:zoomIn 0.3s ease;
}
.login-modal-box .lm-icon{font-size:44px;margin-bottom:12px;display:block}
.login-modal-box h3{font-size:22px;font-weight:800;color:var(--text-primary);margin-bottom:4px}
.login-modal-box .lm-sub{font-size:14px;font-weight:500;color:var(--text-secondary);margin-bottom:22px}
.login-modal-box .lm-input{
  width:100%;padding:12px 16px;border-radius:16px;
  background:var(--bg-input);border:1.5px solid var(--border-subtle);
  color:var(--text-body);font-size:14px;outline:none;
  transition:all 0.2s;box-sizing:border-box;margin-bottom:10px;font-family:inherit;
}
.login-modal-box .lm-input:focus{border-color:rgba(99,102,241,0.4)}
.login-modal-box .lm-input::placeholder{color:var(--text-muted2)}
.login-modal-box .lm-btn{
  width:100%;padding:13px;border-radius:8px;border:1px solid var(--border-subtle);
  background:var(--bg-glass);color:var(--text-primary);
  font-size:15px;font-weight:700;cursor:pointer;
  transition:all 0.3s;margin-top:4px;
  box-shadow:var(--shadow-sm);
}
.login-modal-box .lm-btn:hover{box-shadow:var(--shadow-lg);transform:translateY(-1px);background:var(--bg-glass-hover)}
.login-modal-box .lm-btn:active{transform:scale(0.97)}
.login-modal-box .lm-btn:disabled{opacity:0.5;cursor:not-allowed;transform:none;box-shadow:none}
.login-modal-box .lm-err{font-size:12px;color:#ef4444;margin-top:10px;display:none}
.login-modal-box .lm-close{
  margin-top:14px;padding:8px 0;display:inline-block;
  font-size:13px;color:var(--text-muted);cursor:pointer;transition:color 0.2s;
}
.login-modal-box .lm-close:hover{color:var(--text-secondary)}

<?php endif; ?>
</style>
</head>
<body>

<?php if ($openTip): ?>
<div class="open-tip-bar" id="openTipBar">
  <img src="assets/img/jiantou.png" class="tip-arrow-icon" alt="↓">
  <span><i class="fas fa-link"></i> 建议在浏览器中打开，以获得更好的体验</span>
  <a href="javascript:void(0)" onclick="tryOpenBrowser()" class="tip-btn">在浏览器中打开 →</a>
  <span class="tip-close" onclick="document.getElementById('openTipBar').style.display='none'"><i class="fas fa-times"></i></span>
</div>
<script>
function tryOpenBrowser(){
  var ua = navigator.userAgent;
  if(ua.indexOf('MicroMessenger') > -1){
    window.location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=&redirect_uri='+encodeURIComponent(window.location.href)+'&response_type=code&scope=snsapi_base#wechat_redirect';
  } else {
    window.open(window.location.href,'_blank');
  }
}
</script>
<?php endif; ?>

<div class="page-wrap">
  <!-- 主题切换 -->
  <div class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="切换白天/黑夜模式">
    <i class="fas fa-moon" id="themeIcon"></i>
  </div>
  

  <!-- 头像 -->
  <div class="avatar-wrap">
    <?php if ($user['avatar'] ?? ''): ?>
      <img src="<?=BASE_URL.'/'.($user['avatar'] ?? '')?>" alt="avatar" loading="lazy">
    <?php else: ?>
      <span class="no-avatar"><?=h(mb_substr($user['nickname'] ?? $user['username'] ?? '',0,1))?></span>
    <?php endif; ?>
  </div>

  <!-- 姓名 + 简介 -->
  <div class="profile-name"><?=h($user['nickname'] ?? $user['username'] ?? '')?></div>
  <?php if ($user['bio'] ?? ''): ?>
  <div class="profile-bio"><?=h($user['bio'] ?? '')?></div>
  <?php endif; ?>

  <!-- 主页链接栏（复制 + 分享） -->
  <?php
    $homeSuffix = $user['suffix'] ?? '';
    $homeUrl = $homeSuffix ? (BASE_URL . '/' . $homeSuffix) : (BASE_URL . '/page/index.php?id=' . $userId);
  ?>
  <div class="top-link-bar">
    <span class="link-text" id="page-home-url" title="点击全选"><?=h($homeUrl)?></span>
    <button class="bar-btn" id="top-copy-btn" onclick="copyPageUrl()"><i class="fas fa-copy"></i> 复制</button>
    <button class="bar-btn" onclick="openShareModal()"><i class="fas fa-share-alt"></i> 分享</button>
  </div>

  <!-- 公告 -->
  <?php if (!empty($user['announcement_enabled']) && !empty($user['announcement'] ?? '' ?? '')): ?>
  <div class="announcement-box"><?=$user['announcement'] ?? '' ?? ''?></div>
  <?php endif; ?>

  <!-- 链接模块 -->
  <div class="links-wrap">
    <?php if (empty($linkList)): ?>
      <div class="profile-bio" style="margin-top:30px"><i class="fas fa-star" style="color:#fbbf24"></i> 暂无内容</div>
    <?php else: ?>
      <?php foreach ($linkList as $i => $l):
        $delay = 0.2 + $i * 0.06;
        $cardClass = "card-".$cardStyle;
        $customBg = $l['card_color'] ? "background:{$l['card_color']};" : 'background:#ffffff;';
        $customColor = $l['text_color'] ? "color:{$l['text_color']};" : 'color:var(--text-card-title);';
        $isOutline = $l['outline'] ? ' outline' : '';
        $isCenter = $l['text_center'] ? ' text-center' : '';
        $radius = !empty($l['btn_radius_on']) ? 'border-radius:'.(int)$l['btn_radius'].'px;' : '';
        $finalStyle = $customBg . $customColor;
        if ($btnInline && $l['type']==='link') $finalStyle = $btnInline;
        $linkUrl = $l['url'];
        $hasPasscode = !empty(trim($l['passcode'] ?? ''));
        // Favicon自动解析
        $iconHtml = '';
        $faviconType = $l['favicon_type'] ?? 'emoji';
        $iconValue = $l['icon'] ?? '';
        if ($faviconType === 'favicon' && !empty($linkUrl)) {
            $domain = parse_url($linkUrl, PHP_URL_HOST);
            if ($domain) {
                $faviconUrl = 'https://www.google.com/s2/favicons?domain='.$domain.'&sz=64';
                if (empty($iconValue)) $iconValue = $faviconUrl;
                $iconHtml = '<img src="'.h($faviconUrl).'" class="w-7 h-7 rounded-md object-cover" onerror="this.style.display=&quot;none&quot;" alt="">';
            }
        } elseif (!empty($iconValue) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)(\?.*)?$/i', $iconValue)) {
                $iconHtml = '<img src="'.h($iconValue).'" class="w-7 h-7 rounded-md object-cover" onerror="this.style.display=&quot;none&quot;" alt="">';
        } elseif (!empty($iconValue)) {
            $iconHtml = h($iconValue);
        }
        $isVideo = $l['type'] === 'video';
        // 平台内置浏览器：直接跳转（不再弹窗提示复制链接）
        $linkHref = $hasPasscode
          ? "javascript:checkPass({$l['id']},'".h(addslashes($linkUrl))."')"
          : BASE_URL."/api/record.php?action=click&link_id={$l['id']}&user_id={$userId}&url=".urlencode($linkUrl);
        $linkAttrs = $hasPasscode ? '' : 'target="_blank" rel="noopener"';
      ?>

      <?php if ($l['type'] === 'link'): ?>
        <a href="<?=$linkHref?>"
           class="<?=$cardClass.$isOutline?>"
           style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>"
           <?=$linkAttrs?>>
          <?php if($iconHtml):?><span class="card-icon"><?=$iconHtml?></span><?php endif?>
          <div class="card-info">
            <div class="card-title" style="<?=$customColor?>"><?=h($l['title'])?>
              <?php if($hasPasscode):?><span class="card-lock"><i class="fas fa-lock"></i></span><?php endif?>
            </div>
            <div class="card-sub"><?=h(parse_url($linkUrl, PHP_URL_HOST) ?: $linkUrl)?></div>
          </div>
          <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>

      <?php elseif ($l['type'] === 'image'): ?>
        <a href="javascript:void(0)" onclick="showPopupImg('<?=h($l['popup_img'] ?: $l['icon'])?>')"
           class="<?=$cardClass.$isOutline?>"
           style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>">
          <?php if($l['icon']):?><span class="card-icon"><?=$iconHtml?></span><?php endif?>
          <div class="card-info">
            <div class="card-title" style="<?=$customColor?>"><?=h($l['title'])?></div>
            <div class="card-sub"><i class="fas fa-camera"></i> 点击查看大图</div>
          </div>
          <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>

      <?php elseif ($l['type'] === 'video'):
        $videoSource = $l['video_source'] ?? 'file';
        $videoSub = '<i class="fas fa-play"></i> 点击播放视频';
        $isBilibili = false;
        $bilibiliEmbedUrl = '';
        $videoOnclick = "playVideo('".h($l['video_file'])."','".h($l['video_poster'])."',".($l['video_loop']?1:0).")";
        if ($videoSource === 'bilibili') {
          // 提取B站BV号，支持iframe内嵌
          $bvid = '';
          if (preg_match('/(?:bilibili\.com\/video\/|BV)([a-zA-Z0-9]{10,12})/i', $l['url'], $m)) {
            $bvid = $m[1];
          }
          if ($bvid) {
            $embedUrl = 'https://player.bilibili.com/player.html?bvid=' . $bvid . '&autoplay=1';
            $isBilibili = true;
            $bilibiliEmbedUrl = h($embedUrl);
            $videoOnclick = "openBilibiliPlayer('".h($embedUrl)."','".h($l['video_poster'])."','".h($l['title'])."')";
            $videoSub = '<i class="fas fa-tv"></i> B站视频';
          } else {
            // 解析失败，直接跳转
            $videoOnclick = "window.open('".h($l['url'])."','_blank')";
            $videoSub = '<i class="fas fa-tv"></i> 前往B站观看';
          }
        } elseif ($videoSource === 'douyin') {
          $videoSub = '<i class="fas fa-music"></i> 抖音视频';
          $videoOnclick = "window.open('".h($l['url'])."','_blank')";
        } elseif ($videoSource === 'kuaishou') {
          $videoSub = '<i class="fas fa-mobile-alt"></i> 快手视频';
          $videoOnclick = "window.open('".h($l['url'])."','_blank')";
        }

        // 视频自动展开播放
        if (!empty($videoAutoExpand)):
          // B站内嵌
          if ($isBilibili && $bilibiliEmbedUrl): ?>
          <div class="video-expand-block" style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>">
            <div class="video-expand-title" style="<?=$customColor?>"><?=h($l['title'])?></div>
            <div class="video-expand-player" style="aspect-ratio:16/9">
              <iframe src="<?=$bilibiliEmbedUrl?>" frameborder="0" allowfullscreen allow="autoplay; fullscreen" style="width:100%;height:100%;border-radius:12px"></iframe>
            </div>
          </div>
          <?php elseif ($videoSource === 'file' && !empty($l['video_file'])):
            $videoSrc = h($l['video_file']);
            $videoPosterAttr = !empty($l['video_poster']) ? ' poster="'.h($l['video_poster']).'"' : '';
            $videoLoopAttr = $l['video_loop'] ? ' loop' : ''; ?>
          <div class="video-expand-block" style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>">
            <div class="video-expand-title" style="<?=$customColor?>"><?=h($l['title'])?></div>
            <div class="video-expand-player">
              <video controls<?=$videoPosterAttr?><?=$videoLoopAttr?> playsinline preload="metadata" style="width:100%;border-radius:12px">
                <source src="<?=$videoSrc?>" type="video/mp4">
                您的浏览器不支持视频播放
              </video>
            </div>
          </div>
          <?php else: ?>
        <a href="javascript:void(0)" onclick="<?=$videoOnclick?>"
           class="<?=$cardClass.$isOutline?>"
           style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>">
          <?php if($l['icon']):?><span class="card-icon"><?=$iconHtml?></span><?php endif?>
          <div class="card-info">
            <div class="card-title" style="<?=$customColor?>"><?=h($l['title'])?></div>
            <div class="card-sub"><?=$videoSub?></div>
          </div>
          <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
          <?php endif;
        else: // 不展开，传统卡片 ?>
        <a href="javascript:void(0)" onclick="<?=$videoOnclick?>"
           class="<?=$cardClass.$isOutline?>"
           style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>">
          <?php if($l['icon']):?><span class="card-icon"><?=$iconHtml?></span><?php endif?>
          <div class="card-info">
            <div class="card-title" style="<?=$customColor?>"><?=h($l['title'])?></div>
            <div class="card-sub"><?=$videoSub?></div>
          </div>
          <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
        <?php endif; ?>

      <?php elseif ($l['type'] === 'picture'): ?>
        <div class="picture-block" style="animation-delay:<?=$delay?>s;cursor:pointer" onclick="showPopupImg('<?=h($l['icon'])?>')">
          <?php if($l['icon']):?><img src="<?=h($l['icon'])?>" alt="<?=h($l['title'])?>" loading="lazy"><?php endif?>
        </div>

      <?php elseif ($l['type'] === 'text'): ?>
        <div class="text-block" style="animation-delay:<?=$delay?>s;text-align:<?=$isCenter?'center':'left'?>" onclick="showTextPopup('<?=h(addslashes($l['title']))?>')">
          <?=nl2br(h($l['title']))?>
        </div>
      <?php endif; ?>

      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- 社交 -->
  <!-- 打赏 -->
  <?php if (!empty($user['tipping_enabled']) && !empty($user['tipping_qrcode'] ?? '' ?? '')): ?>
  <div class="tipping-wrap" style="margin-top:28px;text-align:center;animation:fadeUp 0.6s ease 0.3s both">
    <a href="javascript:void(0)" onclick="openTippingModal()" class="tipping-btn">
      <i class="fas fa-heart" style="color:#f43f5e"></i> 打赏支持
    </a>
  </div>
  <?php endif; ?>

  <?php if (!empty($socialData)): ?>
  <div class="social-wrap">
    <?php foreach ($socialData as $key => $val):
      $info = $socialIcons[$key] ?? null;
      if (!$info) continue;
      $href = ($key === 'email') ? "mailto:$val" : $val;
      $label = ['wechat'=>'微信','qq'=>'QQ','telegram'=>'Telegram','dy'=>'抖音','bilibili'=>'B站',
                'xiaohongshu'=>'小红书','weibo'=>'微博','github'=>'GitHub','email'=>'邮箱'][$key] ?? $key;
    ?>
    <a href="javascript:void(0)" class="social-item" title="<?=h($label)?>" onclick="showSocialPopup('<?=h(addslashes($label))?>','<?=h(addslashes($val))?>','<?=$key?>')">
      <i class="<?=$info['icon']?>" style="color:<?=$info['color']?>"></i>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 统计 -->
  <?php if ($showStats): ?>
  <div class="stats-bar">
    <span><i class="fas fa-eye"></i> 访问 <?=number_format($totalViews)?></span>
    <span><i class="fas fa-hand-pointer"></i> 点击 <?=number_format($totalClicks)?></span>
  </div>
  <?php endif; ?>


  <!-- ===== 互动功能：点赞 · 评论 · 收藏 ===== -->
  <div class="interaction-bar" id="interactionBar">
    <!-- 点赞 -->
    <button class="interact-btn<?= $visitorLoggedIn ? '' : ' needs-login' ?>" id="likeBtn" onclick="handleLike()" title="点赞">
      <span class="ib-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      </span>
      <span class="ib-count" id="likeCount">0</span>
    </button>
    <!-- 评论 -->
    <button class="interact-btn" id="commentToggleBtn" onclick="toggleCommentSection()" title="评论">
      <span class="ib-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </span>
      <span class="ib-count" id="commentCount">0</span>
    </button>
    <!-- 收藏 -->
    <button class="interact-btn<?= $visitorLoggedIn ? '' : ' needs-login' ?>" id="favBtn" onclick="handleFavorite()" title="收藏">
      <span class="ib-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </span>
      <span class="ib-count" id="favCount">0</span>
    </button>
  </div>

  <!-- 评论区域 -->
  <div class="comment-section" id="commentSection" style="display:none">
    <div class="comment-box open" id="commentBox">
      <div class="comment-list-wrap" id="commentList">
        <div class="comment-loading">加载评论中...</div>
      </div>
      <div class="comment-input-wrap">
        <input class="comment-input" id="commentInput" type="text" placeholder="说点什么..." maxlength="500" oninput="document.getElementById('commentSubmitBtn').classList.toggle('active',this.value.trim().length>0)">
        <button class="comment-submit-btn" id="commentSubmitBtn" onclick="submitComment()">发送</button>
      </div>
    </div>
  </div>

  <!-- 登录弹窗 -->
  <div class="login-modal-overlay" id="loginModal">
    <div class="login-modal-box">
      <h3>登录后互动</h3>
      <div class="lm-sub">使用你的账号登录，即可点赞、评论和收藏</div>
      <input class="lm-input" id="loginUsername" type="text" placeholder="用户名 / 邮箱" autocomplete="username" onkeydown="if(event.key==='Enter')document.getElementById('loginPassword').focus()">
      <input class="lm-input" id="loginPassword" type="password" placeholder="密码" autocomplete="current-password" onkeydown="if(event.key==='Enter')doLogin()">
      <div class="lm-err" id="loginError">账号或密码错误</div>
      <button class="lm-btn" id="loginBtn" onclick="doLogin()">登 录</button>
      <div style="margin-top:16px;text-align:center;font-size:13px;color:var(--text-muted)">还没有账号？<a href="<?=BASE_URL?>/register.php" style="color:var(--text-primary);text-decoration:underline;font-weight:600" target="_blank">立即注册</a></div>
      <div class="lm-close" onclick="closeLoginModal()">取消</div>
    </div>
  </div>

  <!-- 页脚：由XXX提供创建服务 -->
  <div class="footer-text" style="text-align:<?=$footerAlign?>">
    <?php if (!empty($siteSettings['powered_by_enabled'])): ?>
    <div class="has-powered">
      <span class="powered-icon"><i class="fas fa-bolt"></i></span>
      本页面由 <a href="<?=BASE_URL?>" target="_blank"><?=h($siteSettings['powered_by_name'] ?: getPoweredBy($db))?></a> 提供创建服务
    </div>
    <?php endif; ?>
    <span style="margin-top:<?=$siteSettings['powered_by_enabled']?'8':'0'?>px;display:inline-block"><?=h($footerText)?></span>
    <div style="margin-top:14px">
      <span onclick="openReportModal()" style="display:inline-flex;align-items:center;gap:5px;padding:6px 16px;border-radius:12px;font-size:12px;color:var(--text-muted);background:var(--bg-subtle);border:1px solid var(--border-subtle);cursor:pointer;transition:all 0.25s" onmouseover="this.style.background='rgba(239,68,68,0.12)';this.style.borderColor='rgba(239,68,68,0.25)';this.style.color='rgba(239,68,68,0.7)'" onmouseout="this.style.background='rgba(255,255,255,0.05)';this.style.borderColor='rgba(255,255,255,0.08)';this.style.color='rgba(255,255,255,0.5)'"><i class="fas fa-flag"></i> 举报</span>
    </div>
  </div>
</div>

<!-- 免费制作悬浮按钮居中容器（受管理员开关控制） -->
<?php if (!empty($siteSettings['show_free_make_btn'])): ?>
<div class="free-make-wrap">
<a href="<?=BASE_URL?>" class="free-make-btn">
  <span class="free-sparkle"><i class="fas fa-star" style="color:#fbbf24"></i></span> 免费制作同款聚合页
  <span class="free-sparkle"><i class="fas fa-star" style="color:#fbbf24"></i></span>
</a>
</div>
<?php endif; ?>

<!-- 音乐播放器 -->
<?php if (!empty($musicUrl)): ?>
<audio id="bgMusic" src="<?=h($musicUrl)?>" <?=$musicLoop?'loop':''?> <?=$musicAutoplay?'autoplay':''?>></audio>
<div class="music-player-btn <?=$musicAutoplay?'playing':''?>" id="musicBtn" onclick="toggleMusic()">
  <?php if ($musicIcon === 'h'): ?>
  <img src="assets/img/music_h.png" class="m-icon-img" alt="music">
  <?php else: ?>
  <img src="assets/img/music_b.png" class="m-icon-img" alt="music">
  <?php endif; ?>
</div>
<script>
var music = document.getElementById('bgMusic');
var musicBtn = document.getElementById('musicBtn');
var musicPlaying = <?=$musicAutoplay?'true':'false'?>;
function toggleMusic(){
  if(music.paused){ music.play(); musicBtn.classList.add('playing'); musicPlaying = true; }
  else{ music.pause(); musicBtn.classList.remove('playing'); musicPlaying = false; }
}
music.addEventListener('ended', function(){ musicBtn.classList.remove('playing'); musicPlaying = false; });
</script>
<?php endif; ?>

<!-- 分享弹窗（链接+二维码） -->
<div id="shareModal" class="share-modal-overlay" style="display:none" onclick="if(event.target===this)closeShareModal()">
  <div class="share-modal-box" onclick="event.stopPropagation()">
    <h3><i class="fas fa-share-alt"></i> 分享本主页</h3>
    <p class="sub">扫一扫或复制链接分享给好友</p>
    <div class="share-url-row">
      <span class="url-text" id="share-url-text"><?=h($homeUrl)?></span>
      <button class="copy-url-btn" id="share-copy-btn" onclick="copyShareUrl()"><i class="fas fa-copy"></i> 复制</button>
    </div>
    <div class="qrcode-area">
      <div class="qr-wrap">
        <div id="share-qrcode" style="width:160px;height:160px;"></div>
        <?php if (!empty($user['avatar'] ?? '')): ?>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
          <div style="width:36px;height:36px;border-radius:8px;overflow:hidden;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.2);background:#fff">
            <img src="<?=BASE_URL.'/'.($user['avatar'] ?? '')?>" style="width:100%;height:100%;object-fit:cover" alt="">
          </div>
        </div>
        <?php else: ?>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
          <div style="width:36px;height:36px;border-radius:8px;overflow:hidden;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.2);background:linear-gradient(135deg,#6366f1,#a78bfa);display:flex;align-items:center;justify-content:center;color:var(--text-primary);font-size:14px;font-weight:700"><?=h(mb_substr($user['nickname'] ?? $user['username'] ?? '',0,1))?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <button class="close-share-btn" onclick="closeShareModal()">关闭</button>
  </div>
</div>

<!-- 举报弹窗 -->
<div id="reportOverlay" class="report-overlay" onclick="if(event.target===this)closeReportModal()">
  <div class="report-box" onclick="event.stopPropagation()">
    <h3><i class="fas fa-flag"></i> 举报该主页</h3>
    <p class="sub">请选择举报原因，我们将尽快处理</p>
    <div class="report-types" id="reportTypes">
      <div class="report-type-btn" data-type="violation" onclick="selectReportType(this)"><i class="fas fa-ban"></i> 违规内容</div>
      <div class="report-type-btn" data-type="spam" onclick="selectReportType(this)"><i class="fas fa-bullhorn"></i> 垃圾营销</div>
      <div class="report-type-btn" data-type="copyright" onclick="selectReportType(this)"><i class="fas fa-copyright"></i> 侵权投诉</div>
      <div class="report-type-btn" data-type="pornographic" onclick="selectReportType(this)"><i class="fas fa-exclamation-triangle"></i> 色情低俗</div>
      <div class="report-type-btn" data-type="fraud" onclick="selectReportType(this)"><i class="fas fa-exclamation-triangle"></i> 欺诈信息</div>
      <div class="report-type-btn" data-type="other" onclick="selectReportType(this)"><i class="fas fa-comment"></i> 其他</div>
    </div>
    <textarea class="report-reason" id="reportReason" rows="2" placeholder="补充说明（选填）" maxlength="500"></textarea>
    <div class="report-actions">
      <button class="report-cancel-btn" onclick="closeReportModal()">取消</button>
      <button class="report-submit-btn" id="reportSubmitBtn" onclick="submitReport()" disabled>提交举报</button>
    </div>
  </div>
</div>

<!-- 密码验证弹窗 -->
<div id="passModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closePassModal()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <h3><i class="fas fa-lock"></i> 密码保护</h3>
    <p>此链接已加密，请输入访问密码</p>
    <input type="password" id="passInput" maxlength="10" autocomplete="off" onkeydown="if(event.key==='Enter')verifyPass()">
    <div class="modal-err" id="passErr">密码错误，请重试</div>
    <button class="modal-btn" onclick="verifyPass()">确认</button>
  </div>
</div>

<!-- 图片弹窗 -->
<div id="imgModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeImgModal()">
  <div class="modal-box" style="background:transparent;border:none;padding:0;max-width:90vw" onclick="event.stopPropagation()">
    <img id="popupImg" src="" style="max-width:100%;max-height:85vh;border-radius:12px;box-shadow:0 40px rgba(0,0,0,0.4);cursor:zoom-out" onclick="closeImgModal()">
  </div>
</div>

<!-- 视频弹窗 -->
<div id="videoModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeVideoModal()">
  <div class="modal-box" style="background:transparent;border:none;padding:0;max-width:90vw" onclick="event.stopPropagation()">
    <video id="popupVideo" src="" controls style="max-width:100%;max-height:85vh;border-radius:12px;box-shadow:0 40px rgba(0,0,0,0.4)" poster=""></video>
  </div>
</div>

<!-- B站 iframe 弹窗 -->
<div id="bilibiliModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeBilibiliModal()">
  <div class="modal-box" style="background:#000;border:none;padding:0;max-width:90vw;border-radius:16px;overflow:hidden" onclick="event.stopPropagation()">
    <div style="position:relative;padding:56.25% 0 0 0">
      <iframe id="bilibiliPlayer" src="" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;border-radius:16px" allowfullscreen></iframe>
    </div>
  </div>
</div>

<!-- 打赏弹窗 -->
<div id="tippingModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeTippingModal()">
  <div class="modal-box" onclick="event.stopPropagation()" style="max-width:340px;text-align:center">
    <div style="display:flex;justify-content:flex-end">
      <span onclick="closeTippingModal()" style="cursor:pointer;color:var(--text-muted);font-size:22px;line-height:1">&times;</span>
    </div>
    <i class="fas fa-heart" style="font-size:36px;color:#f43f5e;margin-bottom:8px"></i>
    <h3 id="tippingModalTitle" style="color:var(--text-primary);font-size:18px;font-weight:600;margin:0"><?=h(!empty($user['tipping_title'])?$user['tipping_title']:'感谢支持 <i class="fas fa-heart" style="color:#ef4444"></i>')?></h3>
    <p style="color:var(--text-muted);font-size:13px;margin:6px 0 10px">扫描下方二维码，支持创作者</p>
    <img src="<?=h($user['tipping_qrcode'] ?? '' ?? '')?>" class="tipping-qr-img" alt="打赏二维码">
  </div>
</div>

<!-- 文字弹窗 -->
<div id="textModal" class="modal-overlay" style="display:none" onclick="closeTextModal()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <h3 style="color:var(--text-primary);font-size:16px;font-weight:600"><i class="fas fa-pencil-alt"></i> 文字内容</h3>
      <span onclick="closeTextModal()" style="cursor:pointer;color:var(--text-muted);font-size:20px;line-height:1">&times;</span>
    </div>
    <div id="textModalContent" style="color:var(--text-primary);font-size:15px;line-height:1.8;text-align:left;max-height:60vh;overflow-y:auto;white-space:pre-wrap;word-break:break-word"></div>
  </div>
</div>

<!-- 社交弹窗 -->
<div id="socialModal" class="modal-overlay" style="display:none" onclick="closeSocialModal()">
  <div class="modal-box" onclick="event.stopPropagation()" style="max-width:360px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
      <div style="display:flex;align-items:center;gap:10px">
        <span id="socialModalIcon" style="font-size:28px;width:40px;height:40px;border-radius:10px;background:var(--bg-hover);display:flex;align-items:center;justify-content:center"></span>
        <h3 id="socialModalTitle" style="color:var(--text-primary);font-size:17px;font-weight:600;margin:0"></h3>
      </div>
      <span onclick="closeSocialModal()" style="cursor:pointer;color:var(--text-muted);font-size:22px;line-height:1">&times;</span>
    </div>
    <div style="margin-top:20px;padding:16px;border-radius:16px;background:var(--bg-subtle);border:1px solid var(--border-faint)">
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px">联系方式</div>
      <div id="socialModalContent" style="color:var(--text-body);font-size:16px;font-weight:600;word-break:break-all;user-select:all"></div>
    </div>
    <div id="socialModalAction" style="margin-top:14px;display:none">
      <a id="socialModalBtn" href="#" target="_blank" rel="noopener" class="modal-btn" style="display:block;text-align:center;text-decoration:none">打开链接 →</a>
    </div>
    <div style="margin-top:16px;font-size:11px;color:var(--text-muted)"><i class="fas fa-lightbulb"></i> 长按可复制联系方式</div>
  </div>
</div>

<!-- 记录访问 -->
<img src="<?=BASE_URL?>/api/record.php?action=view&user_id=<?=$userId?>" style="display:none" alt="">

<script>
// 密码验证
var currentPassLinkId = 0;
var currentPassUrl = '';
function checkPass(id, url){
  currentPassLinkId = id; currentPassUrl = url;
  document.getElementById('passInput').value = '';
  document.getElementById('passErr').style.display = 'none';
  document.getElementById('passModal').style.display = 'flex';
}
function closePassModal(){ document.getElementById('passModal').style.display = 'none'; }
function verifyPass(){
  var pass = document.getElementById('passInput').value;
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '<?=BASE_URL?>/api/record.php?action=verify_pass&link_id='+currentPassLinkId+'&pass='+encodeURIComponent(pass), true);
  xhr.onload = function(){
    var r = JSON.parse(xhr.responseText);
    if(r.success){ closePassModal(); window.open('<?=BASE_URL?>/api/record.php?action=click&link_id='+currentPassLinkId+'&user_id=<?=$userId?>&url='+encodeURIComponent(currentPassUrl), '_blank'); }
    else{ document.getElementById('passErr').style.display = 'block'; }
  };
  xhr.send();
}

// 图片弹窗
function showPopupImg(src){ document.getElementById('popupImg').src = src; document.getElementById('imgModal').style.display = 'flex'; }
function closeImgModal(){ document.getElementById('imgModal').style.display = 'none'; }

// 视频弹窗
function playVideo(src, poster, loop){
  var v = document.getElementById('popupVideo');
  v.src = src; v.poster = poster; v.loop = !!loop;
  document.getElementById('videoModal').style.display = 'flex';
  v.play();
}
function closeVideoModal(){
  var v = document.getElementById('popupVideo');
  v.pause(); v.src = '';
  document.getElementById('videoModal').style.display = 'none';
}

// 文字弹窗
function showTextPopup(content){
  document.getElementById('textModalContent').textContent = content;
  document.getElementById('textModal').style.display = 'flex';
}
function closeTextModal(){
  document.getElementById('textModal').style.display = 'none';
}

// 社交弹窗
var socialIcons = {
  'wechat': '<i class="fa-brands fa-weixin" style="color:#07C160;font-size:24px"></i>',
  'qq': '<i class="fa-brands fa-qq" style="color:#12B7F5;font-size:24px"></i>',
  'telegram': '<i class="fa-brands fa-telegram" style="color:#0088CC;font-size:24px"></i>',
  'dy': '<i class="fa-brands fa-tiktok" style="color:var(--text-primary);font-size:24px"></i>',
  'bilibili': '<i class="fa-brands fa-bilibili" style="color:#FB7299;font-size:24px"></i>',
  'xiaohongshu': '<i class="fa-solid fa-book" style="color:#FE2C55;font-size:24px"></i>',
  'weibo': '<i class="fa-brands fa-weibo" style="color:#E6162D;font-size:24px"></i>',
  'github': '<i class="fa-brands fa-github" style="color:var(--text-primary);font-size:24px"></i>',
  'email': '<i class="fa-solid fa-envelope" style="color:#EA4335;font-size:24px"></i>',
};
var socialLabels = {
  'wechat':'微信','qq':'QQ','telegram':'Telegram','dy':'抖音','bilibili':'B站',
  'xiaohongshu':'小红书','weibo':'微博','github':'GitHub','email':'邮箱'
};
function showSocialPopup(label, val, key){
  document.getElementById('socialModalIcon').innerHTML = socialIcons[key] || '';
  document.getElementById('socialModalTitle').textContent = label;
  var content = document.getElementById('socialModalContent');
  var action = document.getElementById('socialModalAction');
  var btn = document.getElementById('socialModalBtn');
  
  var isUrl = val.startsWith('http://') || val.startsWith('https://');
  if(isUrl){
    content.textContent = val;
    btn.href = val;
    action.style.display = 'block';
  } else if(key === 'email'){
    content.textContent = val;
    btn.href = 'mailto:' + val;
    action.style.display = 'block';
  } else {
    content.textContent = val;
    action.style.display = 'none';
  }
  document.getElementById('socialModal').style.display = 'flex';
}
function closeSocialModal(){
  document.getElementById('socialModal').style.display = 'none';
}

// ===== 主页顶部栏：一键复制 =====
function copyPageUrl() {
  var url = document.getElementById('page-home-url').textContent.trim();
  var btn = document.getElementById('top-copy-btn');
  doCopy(url, btn);
}
// ===== 分享弹窗：复制链接 =====
function copyShareUrl() {
  var url = document.getElementById('share-url-text').textContent.trim();
  var btn = document.getElementById('share-copy-btn');
  doCopy(url, btn);
}
// ===== 通用复制 =====
function doCopy(text, btn) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(function(){
      copyBtnFeedback(btn);
    }).catch(function(){
      fallbackCopyPage(text, btn);
    });
  } else {
    fallbackCopyPage(text, btn);
  }
}
function fallbackCopyPage(text, btn) {
  var ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'fixed';
  ta.style.opacity = '0';
  ta.style.left = '-999px';
  document.body.appendChild(ta);
  ta.select();
  try { document.execCommand('copy'); copyBtnFeedback(btn); } catch(e) {}
  document.body.removeChild(ta);
}
function copyBtnFeedback(btn) {
  var html = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-check"></i> 已复制';
  btn.classList.add('copy-ok','copied');
  setTimeout(function(){
    btn.innerHTML = html;
    btn.classList.remove('copy-ok','copied');
  }, 1800);
}

// ===== 分享弹窗 =====
var shareQrGenerated = false;
function openShareModal() {
  document.getElementById('shareModal').style.display = 'flex';
  // 首次打开生成二维码
  if (!shareQrGenerated) {
    setTimeout(function(){
      var container = document.getElementById('share-qrcode');
      container.innerHTML = '';
      var url = document.getElementById('share-url-text').textContent.trim();
      try {
        new QRCode(container, {
          text: url,
          width: 140,
          height: 140,
          colorDark: '#1e293b',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.H
        });
      } catch(e) {}
      shareQrGenerated = true;
    }, 200);
  }
}
function closeShareModal() {
  document.getElementById('shareModal').style.display = 'none';
}

// ===== 举报弹窗 =====
var selectedReportType = '';
function openReportModal(){
  selectedReportType = '';
  document.getElementById('reportSubmitBtn').disabled = true;
  document.querySelectorAll('.report-type-btn').forEach(function(el){ el.classList.remove('selected'); });
  document.getElementById('reportReason').value = '';
  document.getElementById('reportOverlay').style.display = 'flex';
}
function closeReportModal(){
  document.getElementById('reportOverlay').style.display = 'none';
}
function selectReportType(el){
  document.querySelectorAll('.report-type-btn').forEach(function(e){ e.classList.remove('selected'); });
  el.classList.add('selected');
  selectedReportType = el.getAttribute('data-type');
  document.getElementById('reportSubmitBtn').disabled = false;
}
function submitReport(){
  if(!selectedReportType) return;
  var btn = document.getElementById('reportSubmitBtn');
  btn.disabled = true;
  btn.textContent = '提交中...';
  var reason = document.getElementById('reportReason').value.trim();
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '<?=BASE_URL?>/api/report.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(){
    btn.disabled = false;
    btn.textContent = '提交举报';
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          closeReportModal();
          showReportToast('<i class="fas fa-check-circle" style="color:#10b981"></i> 举报已提交，感谢您的反馈');
        } else {
          showReportToast('<i class="fas fa-times-circle" style="color:#ef4444"></i> ' + (r.message || '提交失败'));
        }
      } catch(e) {
        showReportToast('<i class="fas fa-times-circle" style="color:#ef4444"></i> 提交失败，请稍后重试');
      }
    } else {
      showReportToast('<i class="fas fa-times-circle" style="color:#ef4444"></i> 网络错误，请稍后重试');
    }
  };
  xhr.onerror = function(){
    btn.disabled = false;
    btn.textContent = '提交举报';
    showReportToast('<i class="fas fa-times-circle" style="color:#ef4444"></i> 网络错误，请稍后重试');
  };
  xhr.send('user_id=<?=$userId?>&type=' + encodeURIComponent(selectedReportType) + '&reason=' + encodeURIComponent(reason));
}
function showReportToast(msg){
  var t = document.createElement('div');
  t.className = 'report-toast';
  t.innerHTML = msg;
  document.body.appendChild(t);
  setTimeout(function(){ t.remove(); }, 3000);
}
// 打赏弹窗
function openTippingModal(){
  document.getElementById('tippingModal').style.display = 'flex';
}
function closeTippingModal(){
  document.getElementById('tippingModal').style.display = 'none';
}
// B站内嵌播放器
function openBilibiliPlayer(embedUrl, poster, title){
  document.getElementById('bilibiliPlayer').src = embedUrl;
  document.getElementById('bilibiliModal').style.display = 'flex';
}
function closeBilibiliModal(){
  document.getElementById('bilibiliPlayer').src = '';
  document.getElementById('bilibiliModal').style.display = 'none';
}


// ===== 昼夜模式切换 =====
(function(){
  var saved = localStorage.getItem('page_theme');
  if(saved === 'light'){
    document.documentElement.classList.remove('dark-mode');
    document.documentElement.classList.add('light-mode');
    var icon = document.getElementById('themeIcon');
    if(icon) icon.className = 'fas fa-sun';
  } else {
    document.documentElement.classList.add('dark-mode');
  }
})();
function toggleTheme(){
  var html = document.documentElement;
  var icon = document.getElementById('themeIcon');
  var btn = document.getElementById('themeToggle');
  btn && btn.classList.add('switching');
  if(html.classList.contains('light-mode')){
    html.classList.remove('light-mode');
    html.classList.add('dark-mode');
    localStorage.setItem('page_theme', 'dark');
    if(icon) icon.className = 'fas fa-moon';
  } else {
    html.classList.remove('dark-mode');
    html.classList.add('light-mode');
    localStorage.setItem('page_theme', 'light');
    if(icon) icon.className = 'fas fa-sun';
  }
  setTimeout(function(){ btn && btn.classList.remove('switching'); }, 500);
}


</script>


<script>
// ===== 互动功能：点赞 · 评论 · 收藏 =====
var PAGE_USER_ID = <?=$userId?>;
var $ = function(id){ return document.getElementById(id); };

// 加载互动状态
function loadInteractionStatus(callback){
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '<?=BASE_URL?>/api/interaction_status.php?page_user_id=' + PAGE_USER_ID, true);
  xhr.onload = function(){
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          updateInteractionUI(r);
          if(callback) callback(r);
        }
      } catch(e){}
    }
  };
  xhr.send();
}
function updateInteractionUI(r){
  if(!$('likeCount')) return;
  $('likeCount').textContent = r.like_count || 0;
  $('commentCount').textContent = r.comment_count || 0;
  $('favCount').textContent = r.favorite_count || 0;
  var likeBtn = $('likeBtn'), favBtn = $('favBtn');
  if(r.logged_in){
    likeBtn.classList.remove('needs-login');
    favBtn.classList.remove('needs-login');
    if(r.liked) likeBtn.classList.add('liked');
    else likeBtn.classList.remove('liked');
    if(r.favorited) favBtn.classList.add('favorited');
    else favBtn.classList.remove('favorited');
  } else {
    likeBtn.classList.add('needs-login');
    favBtn.classList.add('needs-login');
    likeBtn.classList.remove('liked');
    favBtn.classList.remove('favorited');
  }
  if(r.settings){
    $('likeBtn').style.display = r.settings.enable_likes ? '' : 'none';
    $('favBtn').style.display = r.settings.enable_favorites ? '' : 'none';
    $('commentToggleBtn').style.display = r.settings.enable_comments ? '' : 'none';
  }
}

// 登录弹窗
function requireLogin(callback){
  var likeBtn = $('likeBtn');
  if(likeBtn && !likeBtn.classList.contains('needs-login')){
    if(callback) callback();
    return;
  }
  // 兜底：再向服务器确认一次登录状态
  var x = new XMLHttpRequest();
  x.open('GET', '<?=BASE_URL?>/api/interaction_auth.php?action=check', false);
  x.send();
  try {
    var c = JSON.parse(x.responseText);
    if(c.logged_in){
      if(likeBtn) likeBtn.classList.remove('needs-login');
      if($('favBtn')) $('favBtn').classList.remove('needs-login');
      if(callback) callback();
      return;
    }
  } catch(e){}
  window._loginCallback = callback;
  $('loginModal').style.display = 'flex';
  setTimeout(function(){ $('loginUsername').focus(); }, 200);
}
function closeLoginModal(){
  $('loginModal').style.display = 'none';
  $('loginError').style.display = 'none';
}
function doLogin(){
  var btn = $('loginBtn'), username = $('loginUsername').value.trim(), password = $('loginPassword').value;
  if(!username || !password){ showLoginError('请输入账号和密码'); return; }
  btn.disabled = true; btn.textContent = '登录中...';
  $('loginError').style.display = 'none';
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '<?=BASE_URL?>/api/interaction_auth.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(){
    btn.disabled = false; btn.textContent = '登 录';
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          closeLoginModal();
          loadInteractionStatus(function(){
            if(window._loginCallback){
              var cb = window._loginCallback;
              window._loginCallback = null;
              cb();
            }
          });
        } else {
          showLoginError(r.message || '登录失败');
        }
      } catch(e){ showLoginError('服务器错误'); }
    } else { showLoginError('网络错误'); }
  };
  xhr.onerror = function(){ btn.disabled = false; btn.textContent = '登 录'; showLoginError('网络错误'); };
  xhr.send('action=login&username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password));
}
function showLoginError(msg){
  var el = $('loginError');
  el.textContent = msg; el.style.display = 'block';
}

// 点赞
function handleLike(){
  if($('likeBtn').classList.contains('needs-login')){
    requireLogin(function(){ handleLike(); });
    return;
  }
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '<?=BASE_URL?>/api/interaction_like.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(){
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          $('likeCount').textContent = r.count;
          if(r.liked){ $('likeBtn').classList.add('liked'); } else { $('likeBtn').classList.remove('liked'); }
        } else if(r.need_login){
          $('likeBtn').classList.add('needs-login');
          requireLogin(function(){ handleLike(); });
        }
      } catch(e){}
    }
  };
  xhr.send('page_user_id=' + PAGE_USER_ID);
}

// 收藏
function handleFavorite(){
  if($('favBtn').classList.contains('needs-login')){
    requireLogin(function(){ handleFavorite(); });
    return;
  }
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '<?=BASE_URL?>/api/interaction_favorite.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(){
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          $('favCount').textContent = r.count;
          if(r.favorited){ $('favBtn').classList.add('favorited'); } else { $('favBtn').classList.remove('favorited'); }
        } else if(r.need_login){
          $('favBtn').classList.add('needs-login');
          requireLogin(function(){ handleFavorite(); });
        }
      } catch(e){}
    }
  };
  xhr.send('page_user_id=' + PAGE_USER_ID);
}

// 评论
var commentsLoaded = false;
function toggleCommentSection(){
  var section = $('commentSection');
  if(section.style.display !== 'none' && $('commentBox').classList.contains('open')){
    $('commentBox').classList.remove('open');
    return;
  }
  section.style.display = 'block';
  $('commentBox').classList.add('open');
  if(!commentsLoaded) loadComments();
}
function loadComments(page){
  page = page || 1;
  $('commentList').innerHTML = '<div class="comment-loading">加载评论中...</div>';
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '<?=BASE_URL?>/api/interaction_comment.php?page_user_id=' + PAGE_USER_ID + '&page=' + page, true);
  xhr.onload = function(){
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          commentsLoaded = true;
          renderComments(r);
        } else {
          $('commentList').innerHTML = '<div class="comment-empty">加载失败</div>';
        }
      } catch(e){ $('commentList').innerHTML = '<div class="comment-empty">加载失败</div>'; }
    } else { $('commentList').innerHTML = '<div class="comment-empty">网络错误</div>'; }
  };
  xhr.send();
}
function renderComments(r){
  var html = '';
  if(r.total === 0){
    html = '<div class="comment-empty">暂无评论，来写第一条吧</div>';
  } else {
    for(var i=0; i<r.comments.length; i++){
      var c = r.comments[i];
      var name = c.visitor_name || c.nickname || c.username || '匿名';
      var avatarHtml = c.avatar
        ? '<img src="'+c.avatar+'" alt="">'
        : '<span class="ca-placeholder">'+name.charAt(0).toUpperCase()+'</span>';
      html += '<div class="comment-item">';
      html += '  <div class="comment-avatar">'+avatarHtml+'</div>';
      html += '  <div class="comment-body">';
      html += '    <div class="comment-name">'+escapeHtml(name)+'</div>';
      html += '    <div class="comment-text">'+escapeHtml(c.content)+'</div>';
      html += '    <div class="comment-time">'+(c.time_ago||'')+'</div>';
      html += '  </div>';
      html += '</div>';
    }
  }
  $('commentList').innerHTML = html;
  $('commentCount').textContent = r.total;
}
function submitComment(){
  var input = $('commentInput'), content = input.value.trim();
  if(!content || content.length < 1) return;
  requireLogin(function(){
    var btn = $('commentSubmitBtn');
    btn.disabled = true; btn.textContent = '发送中...';
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?=BASE_URL?>/api/interaction_comment.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function(){
      btn.disabled = false; btn.textContent = '发送';
      if(xhr.status === 200){
        try {
          var r = JSON.parse(xhr.responseText);
          if(r.success){
            input.value = '';
            btn.classList.remove('active');
            if(r.need_audit){
              showToast(r.message || '评论已提交，等待审核通过后展示');
            } else {
              loadComments();
            }
            $('commentCount').textContent = r.total;
          } else {
            showToast(r.message || '评论失败');
          }
        } catch(e){}
      }
    };
    xhr.send('page_user_id=' + PAGE_USER_ID + '&content=' + encodeURIComponent(content));
  });
}
function escapeHtml(s){
  if(!s) return '';
  var d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}
// 页面加载后拉取互动状态
// 页面加载后立即检测登录态
loadInteractionStatus();
setTimeout(function(){
  // 二次确认（兼容性兜底）
  if(!document.querySelector('#likeBtn.needs-login') && !document.querySelector('#favBtn.needs-login')){
    // 已登录，控制台可见
  }
}, 800);
</script>
</body>
</html>

