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

// 获取全站设置
$siteSettings = getSettings($db);

// 获取链接模块（全部类型）
$links = $db->prepare("SELECT * FROM links WHERE user_id = ? AND is_hidden = 0 AND is_violation = 0 ORDER BY sort_order ASC, id DESC");
$links->execute([$userId]);
$linkList = $links->fetchAll();

// 社交数据
$socialData = $user['social_data'] ? json_decode($user['social_data'], true) : [];
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
        $from = $user['custom_gradient_from'] ?: '#667eea';
        $to   = $user['custom_gradient_to'] ?: '#764ba2';
        $dir  = $user['custom_gradient_dir'] ?: '135deg';
        $bgCss = "background:linear-gradient($dir, $from, $to);";
    } elseif ($bgType === 'image' && $user['bg_image']) {
        $bgCss = "background:{$user['bg_color']} url('{$user['bg_image']}') center/cover fixed;";
    } else {
        $bgCss = "background:{$user['bg_color']};";
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
if ($user['btn_bg']) $btnInline .= "background:{$user['btn_bg']};";
if ($user['btn_color']) $btnInline .= "color:{$user['btn_color']};";
$btnOutlineInline = '';
if ($user['btn_outline']) $btnOutlineInline .= "border-color:{$user['btn_outline']};color:{$user['btn_outline']};";

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
$footerText = $user['footer_text'] ?: 'Powered by Leaffox主页系统';
$footerAlign = $user['footer_align'] ?? 'center';
$showArrow = $user['btn_arrow'] ?? 1;
$musicUrl = $user['custom_music'] ?? '';
$musicLoop = $user['custom_music_loop'] ?? 0;
$musicAutoplay = $user['custom_music_autoplay'] ?? 0;
$musicIcon = $user['custom_music_icon'] ?? 'b';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?=h($user['nickname']?:$user['username'])?> 的主页</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
@keyframes slideDown{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:translateY(0)}}
@keyframes zoomIn{from{opacity:0;transform:scale(0.9)}to{opacity:1;transform:scale(1)}}
@keyframes glowPulse{0%,100%{box-shadow:0 0 8px rgba(99,102,241,0.1)}50%{box-shadow:0 0 20px rgba(99,102,241,0.25)}}
@keyframes shimmerBorder{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
@keyframes cardFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
@keyframes ripple{to{transform:scale(4);opacity:0}}
@keyframes poweredGlow{0%,100%{text-shadow:0 0 4px rgba(99,102,241,0.3)}50%{text-shadow:0 0 12px rgba(99,102,241,0.6)}}

body{
  min-height:100vh;<?=$bgCss?>
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"PingFang SC","Microsoft YaHei",sans-serif;
  display:flex;flex-direction:column;align-items:center;
  color:#e2e8f0;-webkit-font-smoothing:antialiased;overflow-x:hidden;
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
.page-wrap{position:relative;z-index:1;width:100%;max-width:480px;padding:50px 24px 50px;display:flex;flex-direction:column;align-items:center;min-height:100vh}

/* 外部浏览器提示条 */
.open-tip-bar{
  width:100%;max-width:480px;position:fixed;top:0;z-index:999;
  background:rgba(0,0,0,0.85);backdrop-filter:blur(20px);
  padding:12px 20px;text-align:center;font-size:13px;color:#e2e8f0;
  animation:slideDown 0.4s ease;
  border-bottom:1px solid rgba(255,255,255,0.06);
}
.open-tip-bar .tip-btn{
  display:inline-block;margin-left:10px;padding:5px 16px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);color:#fff;
  border-radius:20px;text-decoration:none;font-size:12px;font-weight:600;
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
  width:100px;height:100px;border-radius:50%;
  background:rgba(255,255,255,0.08);backdrop-filter:blur(10px);
  border:3px solid rgba(255,255,255,0.18);
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;margin-bottom:16px;
  animation:fadeUp 0.6s ease both;
  box-shadow:0 8px 32px rgba(0,0,0,0.15);
  transition:all 0.5s cubic-bezier(0.34,1.56,0.64,1);
}
.avatar-wrap:hover{
  transform:scale(1.08) rotate(-3deg);
  border-color:rgba(99,102,241,0.5);
  box-shadow:0 12px 40px rgba(99,102,241,0.2);
}
.avatar-wrap img{width:100%;height:100%;object-fit:cover;transition:transform 0.5s}
.avatar-wrap:hover img{transform:scale(1.1)}
.avatar-wrap .no-avatar{font-size:36px;color:rgba(255,255,255,0.8);font-weight:700}

.profile-name{font-size:22px;font-weight:800;color:#fff;margin-bottom:4px;animation:fadeUp 0.6s ease 0.1s both;transition:color 0.3s}
.profile-name:hover{background:linear-gradient(90deg,#6366f1,#a78bfa,#6366f1);background-size:200% auto;background-clip:text;-webkit-background-clip:text;-webkit-text-fill-color:transparent}

.profile-bio{font-size:14px;color:rgba(255,255,255,0.65);text-align:center;max-width:360px;line-height:1.6;margin-bottom:30px;animation:fadeUp 0.6s ease 0.15s both}

/* 公告区域 */
.announcement-box{
  width:100%;padding:14px 18px;border-radius:14px;margin-bottom:24px;
  background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);
  font-size:13px;line-height:1.7;color:rgba(255,255,255,0.8);
  animation:fadeUp 0.6s ease 0.2s both;
  transition:all 0.3s;
  position:relative;overflow:hidden;
}
.announcement-box::before{
  content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.04),transparent);
  transition:left 0.8s;
}
.announcement-box:hover::before{left:150%}
.announcement-box:hover{background:rgba(255,255,255,0.08);border-color:rgba(99,102,241,0.2);transform:translateY(-1px);box-shadow:0 4px 15px rgba(0,0,0,0.1)}

/* ---- 链接卡片 ---- */
.links-wrap{width:100%;display:flex;flex-direction:column;gap:12px}

/* Glass */
.card-glass{
  display:flex;align-items:center;gap:14px;
  padding:16px 20px;border-radius:16px;text-decoration:none;
  animation:fadeUp 0.5s ease both;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  background:rgba(255,255,255,0.08);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid rgba(255,255,255,0.12);position:relative;overflow:hidden;cursor:pointer;
}
.card-glass::before{
  content:'';position:absolute;top:0;left:-100%;width:60%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.06),transparent);
  transition:left 0.6s;
}
.card-glass:hover::before{left:150%}
.card-glass:hover{transform:translateY(-3px) scale(1.02);background:rgba(255,255,255,0.14);border-color:rgba(99,102,241,0.3);box-shadow:0 8px 30px rgba(0,0,0,0.15),0 0 20px rgba(99,102,241,0.1)}
.card-glass:active{transform:scale(0.97)}
.card-glass.outline{background:transparent;border:2px solid rgba(255,255,255,0.2)}
.card-glass.outline:hover{background:rgba(99,102,241,0.06);border-color:rgba(99,102,241,0.4);box-shadow:0 0 20px rgba(99,102,241,0.12)}

/* Neumorphism */
.card-neumorphism{
  display:flex;align-items:center;gap:14px;
  padding:16px 20px;border-radius:16px;text-decoration:none;
  animation:fadeUp 0.5s ease both;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);cursor:pointer;
  background:rgba(255,255,255,0.05);
  box-shadow:6px 6px 12px rgba(0,0,0,0.2),-6px -6px 12px rgba(255,255,255,0.03);
  position:relative;overflow:hidden;
}
.card-neumorphism::before{
  content:'';position:absolute;top:0;left:-100%;width:60%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.05),transparent);
  transition:left 0.6s;
}
.card-neumorphism:hover::before{left:150%}
.card-neumorphism:hover{box-shadow:3px 3px 8px rgba(0,0,0,0.25),-3px -3px 8px rgba(255,255,255,0.05),0 0 20px rgba(99,102,241,0.08);transform:translateY(-3px) scale(1.01)}
.card-neumorphism:active{transform:scale(0.97)}
.card-neumorphism.outline{border:2px solid rgba(255,255,255,0.15);box-shadow:none}
.card-neumorphism.outline:hover{border-color:rgba(99,102,241,0.35);background:rgba(99,102,241,0.04)}

/* Minimal */
.card-minimal{
  display:flex;align-items:center;gap:14px;
  padding:14px 16px;border-radius:12px;text-decoration:none;
  animation:fadeUp 0.5s ease both;
  transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1);cursor:pointer;
  border-bottom:1px solid rgba(255,255,255,0.08);
  position:relative;overflow:hidden;
}
.card-minimal::before{
  content:'';position:absolute;left:0;top:0;width:3px;height:100%;
  background:linear-gradient(180deg,#6366f1,#a78bfa);
  transform:scaleY(0);transition:transform 0.3s;
  border-radius:0 2px 2px 0;
}
.card-minimal:hover::before{transform:scaleY(1)}
.card-minimal:hover{background:rgba(255,255,255,0.06);padding-left:20px;transform:translateX(2px)}
.card-minimal:active{transform:translateX(0) scale(0.98)}
.card-minimal.outline{border:2px solid rgba(255,255,255,0.12);border-radius:12px;border-bottom:2px solid rgba(255,255,255,0.12)}
.card-minimal.outline:hover{border-color:rgba(99,102,241,0.3);box-shadow:0 0 15px rgba(99,102,241,0.08)}

.text-center{justify-content:center}
.card-icon{font-size:26px;width:34px;text-align:center;flex-shrink:0;line-height:1;transition:transform 0.3s}
.card-glass:hover .card-icon,.card-neumorphism:hover .card-icon{transform:scale(1.15) rotate(-5deg)}
.card-info{flex:1;min-width:0}
.card-title{font-size:15px;font-weight:600;color:#fff;margin-bottom:2px;transition:color 0.3s}
.card-glass:hover .card-title,.card-neumorphism:hover .card-title,.card-minimal:hover .card-title{color:rgba(255,255,255,1)}
.card-sub{font-size:11px;color:rgba(255,255,255,0.35);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.card-arrow{color:rgba(255,255,255,0.25);font-size:13px;transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);flex-shrink:0;display:<?=$showArrow?'flex':'none'?>}
.card-glass:hover .card-arrow,.card-neumorphism:hover .card-arrow,.card-minimal:hover .card-arrow{transform:translateX(6px) scale(1.2);color:rgba(99,102,241,0.7)}
.card-lock{font-size:12px;margin-left:4px;color:rgba(255,255,255,0.4)}
.card-tag{font-size:11px;padding:2px 8px;border-radius:10px;background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.5);margin-left:8px}

/* 文字模块 */
.text-block{
  padding:16px 20px;font-size:14px;line-height:1.8;color:rgba(255,255,255,0.7);
  animation:fadeUp 0.5s ease both;
  border-radius:12px;cursor:pointer;transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1);
  border:1px solid transparent;
}
.text-block:hover{background:rgba(255,255,255,0.06);transform:translateY(-1px);border-color:rgba(255,255,255,0.08);box-shadow:0 4px 15px rgba(0,0,0,0.08)}

/* 图片模块 */
.picture-block{
  width:100%;border-radius:16px;overflow:hidden;
  animation:fadeUp 0.5s ease both;cursor:pointer;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  position:relative;
}
.picture-block:hover{transform:scale(1.03);box-shadow:0 12px 40px rgba(0,0,0,0.25)}
.picture-block:active{transform:scale(0.99)}
.picture-block img{width:100%;display:block;border-radius:16px;transition:filter 0.3s}
.picture-block:hover img{filter:brightness(1.05)}

/* 社交 */
.social-wrap{
  display:flex;gap:10px;flex-wrap:wrap;justify-content:center;
  margin-top:32px;animation:fadeUp 0.6s ease 0.4s both;
}
.social-item{
  width:44px;height:44px;border-radius:14px;
  display:flex;align-items:center;justify-content:center;
  text-decoration:none;font-size:20px;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  background:rgba(255,255,255,0.06);backdrop-filter:blur(10px);
  border:1px solid rgba(255,255,255,0.08);
  color:rgba(255,255,255,0.65);
}
.social-item:hover{
  transform:translateY(-4px) scale(1.1);
  background:rgba(255,255,255,0.14);color:#fff;
  box-shadow:0 8px 25px rgba(0,0,0,0.2),0 0 15px rgba(99,102,241,0.15);
  border-color:rgba(99,102,241,0.3);
}
.social-item:active{transform:scale(0.9)}

/* 打赏 */
.tipping-btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:10px 24px;border-radius:30px;
  background:linear-gradient(135deg,rgba(244,63,94,0.2),rgba(244,63,94,0.1));
  border:1px solid rgba(244,63,94,0.25);
  color:rgba(255,255,255,0.8);font-size:14px;font-weight:500;
  cursor:pointer;text-decoration:none;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  backdrop-filter:blur(10px);
}
.tipping-btn:hover{
  transform:translateY(-3px) scale(1.05);
  background:linear-gradient(135deg,rgba(244,63,94,0.35),rgba(244,63,94,0.2));
  border-color:rgba(244,63,94,0.5);
  box-shadow:0 8px 25px rgba(244,63,94,0.2);
  color:#fff;
}
.tipping-btn:active{transform:scale(0.95)}

/* 打赏弹窗 */
.tipping-qr-img{max-width:220px;border-radius:16px;margin:16px auto;display:block;}

/* 统计 */
.stats-bar{display:flex;gap:20px;margin-top:24px;animation:fadeUp 0.6s ease 0.45s both;padding:10px 16px;border-radius:12px;background:rgba(255,255,255,0.03);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.05)}
.stats-bar span{font-size:12px;color:rgba(255,255,255,0.35);transition:color 0.3s}
.stats-bar span:hover{color:rgba(255,255,255,0.6)}

/* 页脚 */
.footer-text{
  font-size:12px;color:rgba(255,255,255,0.25);
  margin-top:20px;width:100%;
  animation:fadeUp 0.6s ease 0.5s both;
}
.footer-text .has-powered{
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.03),transparent);
  background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;
  border-top:1px solid rgba(255,255,255,0.04);padding-top:16px;margin-top:16px;
  position:relative;
}
.footer-text .powered-icon{display:inline-block;margin-right:4px;font-size:10px;animation:poweredGlow 2s ease-in-out infinite}
.footer-text a{color:rgba(255,255,255,0.35);text-decoration:none;transition:color 0.3s;position:relative}
.footer-text a::after{
  content:'';position:absolute;bottom:-2px;left:0;width:0;height:1px;
  background:linear-gradient(90deg,#6366f1,#a78bfa);
  transition:width 0.3s;
}
.footer-text a:hover::after{width:100%}
.footer-text a:hover{color:rgba(255,255,255,0.7);text-decoration:none}

/* ---- 免费制作悬浮按钮 ---- */
.free-make-wrap{
  position:fixed;bottom:24px;left:0;right:0;z-index:100;
  display:flex;justify-content:center;pointer-events:none;
}
.free-make-btn{
  pointer-events:auto;
  display:flex;align-items:center;gap:7px;
  padding:10px 22px;border-radius:50px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);
  color:#fff;font-size:13px;font-weight:600;
  cursor:pointer;text-decoration:none;
  box-shadow:0 4px 20px rgba(99,102,241,0.35);
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  animation:zoomIn 0.6s ease 0.6s both;
  border:1px solid rgba(255,255,255,0.15);
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
  background:rgba(0,0,0,0.65);
  display:none;align-items:center;justify-content:center;
  backdrop-filter:blur(4px);
  animation:fadeIn 0.2s ease;
}
.report-box{
  background:#1e293b;border:1px solid rgba(255,255,255,0.08);
  border-radius:20px;padding:28px 24px 20px;
  width:90%;max-width:380px;
  animation:scaleIn 0.25s ease;
}
@keyframes scaleIn{
  from{transform:scale(0.92);opacity:0}
  to{transform:scale(1);opacity:1}
}
.report-box h3{
  color:#fff;font-size:17px;font-weight:600;margin:0 0 4px;
}
.report-box .sub{
  color:rgba(255,255,255,0.4);font-size:12px;margin:0 0 18px;
}
.report-types{
  display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;
}
.report-type-btn{
  padding:10px 8px;border-radius:12px;
  background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);
  color:rgba(255,255,255,0.65);font-size:13px;cursor:pointer;
  transition:all 0.2s;text-align:center;
}
.report-type-btn:hover{
  background:rgba(99,102,241,0.12);border-color:rgba(99,102,241,0.25);color:#fff;
}
.report-type-btn.selected{
  background:rgba(99,102,241,0.2);border-color:#6366f1;color:#fff;font-weight:500;
}
.report-reason{
  width:100%;padding:10px 14px;border-radius:12px;
  background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);
  color:#e2e8f0;font-size:13px;outline:none;resize:none;
  box-sizing:border-box;margin-bottom:14px;font-family:inherit;
  transition:border-color 0.2s;
}
.report-reason:focus{border-color:#6366f1;}
.report-reason::placeholder{color:rgba(255,255,255,0.25);}
.report-actions{
  display:flex;gap:10px;
}
.report-actions button{
  flex:1;padding:10px;border-radius:12px;font-size:14px;font-weight:500;
  cursor:pointer;transition:all 0.2s;border:none;outline:none;
}
.report-cancel-btn{
  background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.5);
}
.report-cancel-btn:hover{background:rgba(255,255,255,0.1);color:#fff;}
.report-submit-btn{
  background:linear-gradient(135deg,#6366f1,#a78bfa);color:#fff;
  box-shadow:0 2px 12px rgba(99,102,241,0.25);
}
.report-submit-btn:hover{box-shadow:0 4px 20px rgba(99,102,241,0.4);}
.report-submit-btn:disabled{opacity:0.4;cursor:not-allowed;box-shadow:none;}
.report-toast{
  position:fixed;bottom:80px;left:50%;transform:translateX(-50%);
  background:rgba(16,185,129,0.9);color:#fff;padding:10px 24px;
  border-radius:50px;font-size:13px;font-weight:500;
  z-index:99999;animation:fadeUp 0.3s ease;
  backdrop-filter:blur(8px);white-space:nowrap;
}

/* ---- 浏览器打开提示弹窗 ---- */
.browser-tip-box{
  background:#1e293b;border:1px solid rgba(255,255,255,0.08);
  border-radius:20px;padding:28px 24px 20px;
  width:90%;max-width:380px;
  animation:scaleIn 0.25s ease;
}
.browser-tip-header{
  display:flex;align-items:center;gap:8px;
  color:#fff;font-size:16px;font-weight:600;margin-bottom:8px;
}
.browser-tip-icon{font-size:22px}
.browser-tip-desc{
  color:rgba(255,255,255,0.45);font-size:12px;margin:0 0 16px;line-height:1.5;
}
.browser-tip-url-wrap{
  display:flex;align-items:center;gap:8px;
  background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);
  border-radius:12px;padding:10px 12px;margin-bottom:16px;
}
.browser-tip-url{
  flex:1;color:rgba(255,255,255,0.6);font-size:12px;
  word-break:break-all;line-height:1.4;max-height:48px;overflow-y:auto;
}
.browser-tip-copy-btn{
  flex-shrink:0;padding:6px 14px;border-radius:8px;
  background:rgba(99,102,241,0.15);color:#818cf8;
  border:1px solid rgba(99,102,241,0.2);cursor:pointer;
  font-size:12px;font-weight:500;transition:all 0.2s;
  white-space:nowrap;
}
.browser-tip-copy-btn:hover{background:rgba(99,102,241,0.25)}
.browser-tip-copy-btn.copied{background:rgba(16,185,129,0.2);color:#34d399;border-color:rgba(16,185,129,0.3)}
.browser-tip-actions{
  display:flex;gap:10px;margin-bottom:12px;
}
.browser-tip-btn-secondary{
  flex:1;padding:10px;border-radius:12px;background:rgba(255,255,255,0.06);
  color:rgba(255,255,255,0.6);border:none;cursor:pointer;font-size:13px;
  transition:background 0.2s;
}
.browser-tip-btn-secondary:hover{background:rgba(255,255,255,0.1)}
.browser-tip-btn-primary{
  flex:1;padding:10px;border-radius:12px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);
  color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:600;
  transition:opacity 0.2s;text-decoration:none;text-align:center;
}
.browser-tip-btn-primary:hover{opacity:0.9}
.browser-tip-hint{
  font-size:11px;color:rgba(255,255,255,0.2);text-align:center;
}

/* 音乐播放器按钮 */
.music-player-btn{
  position:fixed;bottom:24px;right:24px;z-index:100;
  width:50px;height:50px;border-radius:50%;
  background:rgba(255,255,255,0.1);backdrop-filter:blur(20px);
  border:1px solid rgba(255,255,255,0.15);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:all 0.3s;
  animation:zoomIn 0.5s ease 0.8s both;
  box-shadow:0 4px 20px rgba(0,0,0,0.2);
}
.music-player-btn:hover{transform:scale(1.1);background:rgba(255,255,255,0.18)}
.music-player-btn.playing{animation:spin 4s linear infinite}
.music-player-btn .m-icon{font-size:22px;color:rgba(255,255,255,0.7)}
.music-player-btn .m-icon-img{width:22px;height:22px;display:block;opacity:0.85}

/* ---- 主页顶部链接栏 ---- */
.top-link-bar{
  width:100%;
  display:flex;align-items:center;gap:8px;
  padding:10px 14px;margin-bottom:20px;
  border-radius:14px;
  background:rgba(255,255,255,0.05);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  border:1px solid rgba(255,255,255,0.08);
  animation:fadeUp 0.6s ease 0.2s both;
  transition:all 0.3s;
}
.top-link-bar:hover{border-color:rgba(99,102,241,0.25);background:rgba(255,255,255,0.08)}
.top-link-bar .link-text{
  flex:1;min-width:0;
  font-size:12px;color:rgba(255,255,255,0.55);
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
  background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.7);
  text-decoration:none;
}
.top-link-bar .bar-btn:hover{background:rgba(99,102,241,0.2);color:#818cf8;transform:translateY(-1px)}
.top-link-bar .bar-btn:active{transform:scale(0.95)}
.top-link-bar .bar-btn.copy-ok{background:rgba(16,185,129,0.2);color:#34d399}

/* ---- 分享弹窗 ---- */
.share-modal-overlay{
  position:fixed;inset:0;z-index:2000;
  background:rgba(0,0,0,0.65);backdrop-filter:blur(15px);-webkit-backdrop-filter:blur(15px);
  display:flex;align-items:center;justify-content:center;
  animation:fadeUp 0.3s ease;
}
.share-modal-box{
  background:rgba(25,25,45,0.96);backdrop-filter:blur(20px);
  border:1px solid rgba(255,255,255,0.08);
  border-radius:24px;padding:28px 24px 20px;max-width:360px;width:90%;
  text-align:center;animation:zoomIn 0.3s ease;
}
.share-modal-box h3{font-size:18px;color:#fff;margin-bottom:4px}
.share-modal-box .sub{font-size:12px;color:rgba(255,255,255,0.35);margin-bottom:20px}
.share-modal-box .share-url-row{
  display:flex;align-items:center;gap:8px;
  background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);
  border-radius:12px;padding:10px 12px;margin-bottom:16px;
}
.share-modal-box .share-url-row .url-text{
  flex:1;min-width:0;
  font-size:12px;color:rgba(255,255,255,0.7);font-family:monospace;
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
  background:#fff;padding:10px;border-radius:16px;
  box-shadow:0 8px 30px rgba(0,0,0,0.2);
}
.share-modal-box .close-share-btn{
  margin-top:8px;padding:10px 0;width:100%;border-radius:12px;
  border:none;cursor:pointer;font-size:14px;font-weight:600;
  background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.5);
  transition:all 0.3s;
}
.share-modal-box .close-share-btn:hover{background:rgba(255,255,255,0.1);color:#fff}

/* 密码弹窗 */
.modal-overlay{
  position:fixed;inset:0;z-index:1000;
  background:rgba(0,0,0,0.6);backdrop-filter:blur(10px);
  display:flex;align-items:center;justify-content:center;
  animation:fadeUp 0.3s ease;
}
.modal-box{
  background:rgba(30,30,50,0.95);backdrop-filter:blur(20px);
  border:1px solid rgba(255,255,255,0.1);
  border-radius:20px;padding:32px 28px;max-width:340px;width:90%;
  text-align:center;
}
.modal-box h3{font-size:18px;color:#fff;margin-bottom:8px}
.modal-box p{font-size:13px;color:rgba(255,255,255,0.5);margin-bottom:20px}
.modal-box input[type=password]{
  width:100%;padding:12px 16px;border-radius:12px;
  border:1.5px solid rgba(255,255,255,0.12);
  background:rgba(255,255,255,0.06);color:#fff;font-size:16px;text-align:center;
  outline:none;transition:all 0.3s;
  letter-spacing:12px;
  box-shadow:inset 0 2px 4px rgba(0,0,0,0.1);
}
.modal-box input[type=password]:focus{border-color:rgba(99,102,241,0.5);background:rgba(99,102,241,0.06);box-shadow:0 0 0 3px rgba(99,102,241,0.15),inset 0 2px 4px rgba(0,0,0,0.1)}
.modal-box input[type=password]::placeholder{letter-spacing:0;font-size:14px;color:rgba(255,255,255,0.2)}
.modal-box .modal-err{font-size:12px;color:#ef4444;margin-top:10px;display:none}
.modal-box .modal-btn{
  margin-top:16px;width:100%;padding:14px;border-radius:14px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);color:#fff;
  border:none;font-size:15px;font-weight:700;cursor:pointer;
  transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  letter-spacing:0.5px;
  position:relative;overflow:hidden;
}
.modal-box .modal-btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(99,102,241,0.35);opacity:1}
.modal-box .modal-btn:active{transform:translateY(0) scale(0.98)}
.modal-box .modal-btn::after{
  content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.15),transparent);
  transition:left 0.5s;
}
.modal-box .modal-btn:hover::after{left:100%}

/* 响应式 */
@media(max-width:480px){
  .page-wrap{padding:40px 16px 40px}
  .avatar-wrap{width:80px;height:80px}
  .profile-name{font-size:19px}
  .open-tip-bar{font-size:12px;padding:10px 14px}
}
</style>
</head>
<body>

<?php if ($openTip): ?>
<div class="open-tip-bar" id="openTipBar">
  <img src="assets/img/jiantou.png" class="tip-arrow-icon" alt="↓">
  <span>🔗 建议在浏览器中打开，以获得更好的体验</span>
  <a href="javascript:void(0)" onclick="tryOpenBrowser()" class="tip-btn">在浏览器中打开 →</a>
  <span class="tip-close" onclick="document.getElementById('openTipBar').style.display='none'">✕</span>
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

  <!-- 头像 -->
  <div class="avatar-wrap">
    <?php if ($user['avatar']): ?>
      <img src="<?=BASE_URL.'/'.$user['avatar']?>" alt="avatar" loading="lazy">
    <?php else: ?>
      <span class="no-avatar"><?=h(mb_substr($user['nickname']?:$user['username'],0,1))?></span>
    <?php endif; ?>
  </div>

  <!-- 姓名 + 简介 -->
  <div class="profile-name"><?=h($user['nickname']?:$user['username'])?></div>
  <?php if ($user['bio']): ?>
  <div class="profile-bio"><?=h($user['bio'])?></div>
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
  <?php if (!empty($user['announcement_enabled']) && !empty($user['announcement'])): ?>
  <div class="announcement-box"><?=$user['announcement']?></div>
  <?php endif; ?>

  <!-- 链接模块 -->
  <div class="links-wrap">
    <?php if (empty($linkList)): ?>
      <div class="profile-bio" style="margin-top:30px">✨ 暂无内容</div>
    <?php else: ?>
      <?php foreach ($linkList as $i => $l):
        $delay = 0.2 + $i * 0.06;
        $cardClass = "card-".$cardStyle;
        $customBg = $l['card_color'] ? "background:{$l['card_color']};" : '';
        $customColor = $l['text_color'] ? "color:{$l['text_color']};" : '';
        $isOutline = $l['outline'] ? ' outline' : '';
        $isCenter = $l['text_center'] ? ' text-center' : '';
        $radius = !empty($l['btn_radius_on']) ? 'border-radius:'.(int)$l['btn_radius'].'px;' : '';
        $finalStyle = $customBg . $customColor;
        if ($btnInline && $l['type']==='link') $finalStyle = $btnInline;
        $linkUrl = $l['url'];
        $hasPasscode = !empty(trim($l['passcode'] ?? ''));
        $isVideo = $l['type'] === 'video';
        // 平台内置浏览器拦截：直接跳转改为弹窗提示复制链接+去浏览器打开
        if ($openTip && !$hasPasscode) {
          $_url = htmlspecialchars(addslashes($linkUrl), ENT_NOQUOTES, 'UTF-8');
          $linkHref = "javascript:void(0)";
          $linkAttrs = "onclick=\"promptOpenBrowser('{$_url}',{$l['id']},{$userId})\"";
        } else {
          $linkHref = $hasPasscode
            ? "javascript:checkPass({$l['id']},'".h(addslashes($linkUrl))."')"
            : BASE_URL."/api/record.php?action=click&link_id={$l['id']}&user_id={$userId}&url=".urlencode($linkUrl);
          $linkAttrs = $hasPasscode ? '' : 'target="_blank" rel="noopener"';
        }
      ?>

      <?php if ($l['type'] === 'link'): ?>
        <a href="<?=$linkHref?>"
           class="<?=$cardClass.$isOutline?>"
           style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>"
           <?=$linkAttrs?>>
          <?php if($l['icon']):?><span class="card-icon"><?=h($l['icon'])?></span><?php endif?>
          <div class="card-info">
            <div class="card-title" style="<?=$customColor?>"><?=h($l['title'])?>
              <?php if($hasPasscode):?><span class="card-lock">🔒</span><?php endif?>
            </div>
            <div class="card-sub"><?=h(parse_url($linkUrl, PHP_URL_HOST) ?: $linkUrl)?></div>
          </div>
          <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>

      <?php elseif ($l['type'] === 'image'): ?>
        <a href="javascript:void(0)" onclick="showPopupImg('<?=h($l['popup_img'] ?: $l['icon'])?>')"
           class="<?=$cardClass.$isOutline?>"
           style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>">
          <?php if($l['icon']):?><span class="card-icon"><?=h($l['icon'])?></span><?php endif?>
          <div class="card-info">
            <div class="card-title" style="<?=$customColor?>"><?=h($l['title'])?></div>
            <div class="card-sub">📸 点击查看大图</div>
          </div>
          <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>

      <?php elseif ($l['type'] === 'video'):
        $videoSource = $l['video_source'] ?? 'file';
        $videoSub = '▶ 点击播放视频';
        $videoOnclick = "playVideo('".h($l['video_file'])."','".h($l['video_poster'])."',".($l['video_loop']?1:0).")";
        if ($videoSource === 'bilibili') {
          // 提取B站BV号，支持iframe内嵌
          $bvid = '';
          if (preg_match('/(?:bilibili\.com\/video\/|BV)([a-zA-Z0-9]{10,12})/i', $l['url'], $m)) {
            $bvid = $m[1];
          }
          if ($bvid) {
            $embedUrl = 'https://player.bilibili.com/player.html?bvid=' . $bvid . '&autoplay=0';
            $videoOnclick = "openBilibiliPlayer('".h($embedUrl)."','".h($l['video_poster'])."','".h($l['title'])."')";
            $videoSub = '📺 B站视频';
          } else {
            // 解析失败，直接跳转
            $videoOnclick = "window.open('".h($l['url'])."','_blank')";
            $videoSub = '📺 前往B站观看';
          }
        } elseif ($videoSource === 'douyin') {
          $videoSub = '🎵 抖音视频';
          $videoOnclick = "window.open('".h($l['url'])."','_blank')";
        } elseif ($videoSource === 'kuaishou') {
          $videoSub = '📱 快手视频';
          $videoOnclick = "window.open('".h($l['url'])."','_blank')";
        }
      ?>
        <a href="javascript:void(0)" onclick="<?=$videoOnclick?>"
           class="<?=$cardClass.$isOutline?>"
           style="animation-delay:<?=$delay?>s;<?=$finalStyle?><?=$radius?>">
          <?php if($l['icon']):?><span class="card-icon"><?=h($l['icon'])?></span><?php endif?>
          <div class="card-info">
            <div class="card-title" style="<?=$customColor?>"><?=h($l['title'])?></div>
            <div class="card-sub"><?=$videoSub?></div>
          </div>
          <span class="card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>

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
  <?php if (!empty($user['tipping_enabled']) && !empty($user['tipping_qrcode'])): ?>
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
    <span>👁️ 访问 <?=number_format($totalViews)?></span>
    <span>👆 点击 <?=number_format($totalClicks)?></span>
  </div>
  <?php endif; ?>

  <!-- 页脚：由XXX提供创建服务 -->
  <div class="footer-text" style="text-align:<?=$footerAlign?>">
    <?php if (!empty($siteSettings['powered_by_enabled'])): ?>
    <div class="has-powered">
      <span class="powered-icon">⚡</span>
      本页面由 <a href="<?=BASE_URL?>" target="_blank"><?=h($siteSettings['powered_by_name'] ?: getPoweredBy($db))?></a> 提供创建服务
    </div>
    <?php endif; ?>
    <span style="margin-top:<?=$siteSettings['powered_by_enabled']?'8':'0'?>px;display:inline-block"><?=h($footerText)?></span>
    <div style="margin-top:14px">
      <span onclick="openReportModal()" style="display:inline-flex;align-items:center;gap:5px;padding:6px 16px;border-radius:20px;font-size:12px;color:rgba(255,255,255,0.5);background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);cursor:pointer;transition:all 0.25s" onmouseover="this.style.background='rgba(239,68,68,0.12)';this.style.borderColor='rgba(239,68,68,0.25)';this.style.color='rgba(239,68,68,0.7)'" onmouseout="this.style.background='rgba(255,255,255,0.05)';this.style.borderColor='rgba(255,255,255,0.08)';this.style.color='rgba(255,255,255,0.5)'">🚩 举报</span>
    </div>
  </div>
</div>

<!-- 免费制作悬浮按钮居中容器（受管理员开关控制） -->
<?php if (!empty($siteSettings['show_free_make_btn'])): ?>
<div class="free-make-wrap">
<a href="<?=BASE_URL?>" class="free-make-btn">
  <span class="free-sparkle">✨</span> 免费制作同款聚合页
  <span class="free-sparkle">✨</span>
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
    <h3>📤 分享本主页</h3>
    <p class="sub">扫一扫或复制链接分享给好友</p>
    <div class="share-url-row">
      <span class="url-text" id="share-url-text"><?=h($homeUrl)?></span>
      <button class="copy-url-btn" id="share-copy-btn" onclick="copyShareUrl()"><i class="fas fa-copy"></i> 复制</button>
    </div>
    <div class="qrcode-area">
      <div class="qr-wrap">
        <div id="share-qrcode" style="width:160px;height:160px;"></div>
        <?php if (!empty($user['avatar'])): ?>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
          <div style="width:36px;height:36px;border-radius:8px;overflow:hidden;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.2);background:#fff">
            <img src="<?=BASE_URL.'/'.$user['avatar']?>" style="width:100%;height:100%;object-fit:cover" alt="">
          </div>
        </div>
        <?php else: ?>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
          <div style="width:36px;height:36px;border-radius:8px;overflow:hidden;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.2);background:linear-gradient(135deg,#6366f1,#a78bfa);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:700"><?=h(mb_substr($user['nickname']?:$user['username'],0,1))?></div>
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
    <h3>🚩 举报该主页</h3>
    <p class="sub">请选择举报原因，我们将尽快处理</p>
    <div class="report-types" id="reportTypes">
      <div class="report-type-btn" data-type="violation" onclick="selectReportType(this)">🚫 违规内容</div>
      <div class="report-type-btn" data-type="spam" onclick="selectReportType(this)">📢 垃圾营销</div>
      <div class="report-type-btn" data-type="copyright" onclick="selectReportType(this)">©️ 侵权投诉</div>
      <div class="report-type-btn" data-type="pornographic" onclick="selectReportType(this)">🔞 色情低俗</div>
      <div class="report-type-btn" data-type="fraud" onclick="selectReportType(this)">⚠️ 欺诈信息</div>
      <div class="report-type-btn" data-type="other" onclick="selectReportType(this)">💬 其他</div>
    </div>
    <textarea class="report-reason" id="reportReason" rows="2" placeholder="补充说明（选填）" maxlength="500"></textarea>
    <div class="report-actions">
      <button class="report-cancel-btn" onclick="closeReportModal()">取消</button>
      <button class="report-submit-btn" id="reportSubmitBtn" onclick="submitReport()" disabled>提交举报</button>
    </div>
  </div>
</div>

<!-- 浏览器打开提示弹窗（当平台内置浏览器限制时） -->
<div id="browserTipOverlay" class="modal-overlay" onclick="if(event.target===this)closeBrowserTip()">
  <div class="browser-tip-box">
    <div class="browser-tip-header">
      <span class="browser-tip-icon">🌐</span>
      <span>请在浏览器中打开</span>
    </div>
    <p class="browser-tip-desc">当前环境无法直接操作，请复制链接后在系统浏览器中打开</p>
    <div class="browser-tip-url-wrap">
      <div class="browser-tip-url" id="browserTipUrl"></div>
      <button class="browser-tip-copy-btn" id="browserTipCopyBtn" onclick="copyBrowserTipUrl()">复制链接</button>
    </div>
    <div class="browser-tip-actions">
      <button class="browser-tip-btn-secondary" onclick="closeBrowserTip()">取消</button>
      <button class="browser-tip-btn-primary" onclick="tryOpenBrowserExternal()">去浏览器打开 →</button>
    </div>
    <div class="browser-tip-hint">💡 复制链接后粘贴到 Safari/Chrome 等浏览器地址栏访问</div>
  </div>
</div>

<!-- 密码验证弹窗 -->
<div id="passModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closePassModal()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <h3>🔒 密码保护</h3>
    <p>此链接已加密，请输入访问密码</p>
    <input type="password" id="passInput" maxlength="10" autocomplete="off" onkeydown="if(event.key==='Enter')verifyPass()">
    <div class="modal-err" id="passErr">密码错误，请重试</div>
    <button class="modal-btn" onclick="verifyPass()">确认</button>
  </div>
</div>

<!-- 图片弹窗 -->
<div id="imgModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeImgModal()">
  <div class="modal-box" style="background:transparent;border:none;padding:0;max-width:90vw" onclick="event.stopPropagation()">
    <img id="popupImg" src="" style="max-width:100%;max-height:85vh;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,0.4);cursor:zoom-out" onclick="closeImgModal()">
  </div>
</div>

<!-- 视频弹窗 -->
<div id="videoModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeVideoModal()">
  <div class="modal-box" style="background:transparent;border:none;padding:0;max-width:90vw" onclick="event.stopPropagation()">
    <video id="popupVideo" src="" controls style="max-width:100%;max-height:85vh;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,0.4)" poster=""></video>
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
      <span onclick="closeTippingModal()" style="cursor:pointer;color:rgba(255,255,255,0.4);font-size:22px;line-height:1">&times;</span>
    </div>
    <i class="fas fa-heart" style="font-size:36px;color:#f43f5e;margin-bottom:8px"></i>
    <h3 id="tippingModalTitle" style="color:#fff;font-size:18px;font-weight:600;margin:0"><?=h(!empty($user['tipping_title'])?$user['tipping_title']:'感谢支持 ❤️')?></h3>
    <p style="color:rgba(255,255,255,0.4);font-size:13px;margin:6px 0 10px">扫描下方二维码，支持创作者</p>
    <img src="<?=h($user['tipping_qrcode'])?>" class="tipping-qr-img" alt="打赏二维码">
  </div>
</div>

<!-- 文字弹窗 -->
<div id="textModal" class="modal-overlay" style="display:none" onclick="closeTextModal()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <h3 style="color:#fff;font-size:16px;font-weight:600">📝 文字内容</h3>
      <span onclick="closeTextModal()" style="cursor:pointer;color:rgba(255,255,255,0.4);font-size:20px;line-height:1">&times;</span>
    </div>
    <div id="textModalContent" style="color:rgba(255,255,255,0.8);font-size:15px;line-height:1.8;text-align:left;max-height:60vh;overflow-y:auto;white-space:pre-wrap;word-break:break-word"></div>
  </div>
</div>

<!-- 社交弹窗 -->
<div id="socialModal" class="modal-overlay" style="display:none" onclick="closeSocialModal()">
  <div class="modal-box" onclick="event.stopPropagation()" style="max-width:360px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
      <div style="display:flex;align-items:center;gap:10px">
        <span id="socialModalIcon" style="font-size:28px;width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center"></span>
        <h3 id="socialModalTitle" style="color:#fff;font-size:17px;font-weight:600;margin:0"></h3>
      </div>
      <span onclick="closeSocialModal()" style="cursor:pointer;color:rgba(255,255,255,0.4);font-size:22px;line-height:1">&times;</span>
    </div>
    <div style="margin-top:20px;padding:16px;border-radius:12px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06)">
      <div style="font-size:12px;color:rgba(255,255,255,0.35);margin-bottom:6px">联系方式</div>
      <div id="socialModalContent" style="color:#e2e8f0;font-size:16px;font-weight:600;word-break:break-all;user-select:all"></div>
    </div>
    <div id="socialModalAction" style="margin-top:14px;display:none">
      <a id="socialModalBtn" href="#" target="_blank" rel="noopener" class="modal-btn" style="display:block;text-align:center;text-decoration:none">打开链接 →</a>
    </div>
    <div style="margin-top:16px;font-size:11px;color:rgba(255,255,255,0.25)">💡 长按可复制联系方式</div>
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
  'dy': '<i class="fa-brands fa-tiktok" style="color:#fff;font-size:24px"></i>',
  'bilibili': '<i class="fa-brands fa-bilibili" style="color:#FB7299;font-size:24px"></i>',
  'xiaohongshu': '<i class="fa-solid fa-book" style="color:#FE2C55;font-size:24px"></i>',
  'weibo': '<i class="fa-brands fa-weibo" style="color:#E6162D;font-size:24px"></i>',
  'github': '<i class="fa-brands fa-github" style="color:#fff;font-size:24px"></i>',
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
<?php if ($openTip): ?>
    btn.href = 'javascript:void(0)';
    btn.onclick = function(){ promptOpenBrowser(val, 0, <?=$userId?>); return false; };
    action.style.display = 'block';
<?php else: ?>
    btn.href = val;
    action.style.display = 'block';
<?php endif; ?>
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
          showReportToast('✅ 举报已提交，感谢您的反馈');
        } else {
          showReportToast('❌ ' + (r.message || '提交失败'));
        }
      } catch(e) {
        showReportToast('❌ 提交失败，请稍后重试');
      }
    } else {
      showReportToast('❌ 网络错误，请稍后重试');
    }
  };
  xhr.onerror = function(){
    btn.disabled = false;
    btn.textContent = '提交举报';
    showReportToast('❌ 网络错误，请稍后重试');
  };
  xhr.send('user_id=<?=$userId?>&type=' + encodeURIComponent(selectedReportType) + '&reason=' + encodeURIComponent(reason));
}
function showReportToast(msg){
  var t = document.createElement('div');
  t.className = 'report-toast';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(function(){ t.remove(); }, 3000);
}

// ===== 平台内置浏览器 → 复制链接+去浏览器打开 =====
var _browserTipUrl = '';
function promptOpenBrowser(url, linkId, userId){
  _browserTipUrl = url;
  document.getElementById('browserTipUrl').textContent = url;
  document.getElementById('browserTipCopyBtn').textContent = '复制链接';
  document.getElementById('browserTipCopyBtn').className = 'browser-tip-copy-btn';
  document.getElementById('browserTipOverlay').style.display = 'flex';
  // 记录点击统计
  if(linkId > 0 && userId > 0){
    var img = new Image();
    img.src = '<?=BASE_URL?>/api/record.php?action=click&link_id=' + linkId + '&user_id=' + userId + '&url=' + encodeURIComponent(url);
  }
}
function closeBrowserTip(){
  document.getElementById('browserTipOverlay').style.display = 'none';
}
function copyBrowserTipUrl(){
  var url = document.getElementById('browserTipUrl').textContent;
  var btn = document.getElementById('browserTipCopyBtn');
  if(navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(url).then(function(){
      btn.textContent = '✅ 已复制';
      btn.className = 'browser-tip-copy-btn copied';
    }).catch(function(){ fallbackCopyBrowser(url, btn); });
  } else {
    fallbackCopyBrowser(url, btn);
  }
}
function fallbackCopyBrowser(text, btn){
  var ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'fixed'; ta.style.opacity = '0';
  document.body.appendChild(ta);
  ta.select();
  try {
    document.execCommand('copy');
    btn.textContent = '✅ 已复制';
    btn.className = 'browser-tip-copy-btn copied';
  } catch(e){
    btn.textContent = '❌ 复制失败，请长按手动复制';
  }
  document.body.removeChild(ta);
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
function tryOpenBrowserExternal(){
  var ua = navigator.userAgent;
  closeBrowserTip();
  if(ua.indexOf('MicroMessenger') > -1){
    window.location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=&redirect_uri='+encodeURIComponent(window.location.href)+'&response_type=code&scope=snsapi_base#wechat_redirect';
  } else {
    window.open(_browserTipUrl, '_blank');
  }
}
</script>

</body>
</html>
