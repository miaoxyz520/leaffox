<?php
/**
 * Leaffox 系统首页模版 - Glass（内置）
 * ============================================
 * 模版类型：landing（系统落地页）
 * 文件路径：templates/landing/glass.php
 * 风格：毛玻璃 + 赛博霓虹，暗色透明层叠效果
 * 变量：参见 default.php 头部注释
 * ============================================
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?=h($siteName)?> - 专属个人主页</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  background:#0a0a1a;
  color:rgba(255,255,255,0.85);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"PingFang SC","Microsoft YaHei",sans-serif;
  min-height:100vh;overflow-x:hidden;
}
.bg-grid{
  position:fixed;inset:0;
  background-image:linear-gradient(rgba(99,102,241,0.06) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,0.06) 1px,transparent 1px);
  background-size:60px 60px;z-index:0;
}
.bg-orb{
  position:fixed;width:500px;height:500px;border-radius:50%;
  filter:blur(100px);opacity:0.4;z-index:0;pointer-events:none;
}
.bg-orb-1{top:-150px;right:-100px;background:#6366f1}
.bg-orb-2{bottom:-200px;left:-150px;background:#a78bfa}
.wrap{position:relative;z-index:1;max-width:960px;margin:0 auto;padding:0 24px}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:20px 0}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:800;color:#fff;text-decoration:none}
.nav-logo .logo-icon{width:34px;height:34px;border-radius:8px;background:rgba(99,102,241,0.3);backdrop-filter:blur(12px);border:1px solid rgba(99,102,241,0.3);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;color:#fff}
.nav-actions{display:flex;gap:10px}
.nav-btn{padding:9px 22px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none}
.nav-btn-outline{background:rgba(255,255,255,0.04);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.7)}
.nav-btn-outline:hover{border-color:rgba(255,255,255,0.2);background:rgba(255,255,255,0.08);color:#fff}
.nav-btn-solid{background:rgba(99,102,241,0.25);backdrop-filter:blur(12px);border:1px solid rgba(99,102,241,0.3);color:#fff}
.nav-btn-solid:hover{background:rgba(99,102,241,0.35);border-color:rgba(99,102,241,0.5)}

.hero{text-align:center;padding:90px 0 60px}
.hero-badge{display:inline-block;padding:6px 20px;border-radius:20px;background:rgba(99,102,241,0.12);backdrop-filter:blur(8px);border:1px solid rgba(99,102,241,0.2);color:#a5b4fc;font-size:13px;font-weight:500;margin-bottom:24px}
.hero h1{font-size:48px;font-weight:900;line-height:1.15;margin-bottom:16px;color:#fff}
.hero h1 .highlight{background:linear-gradient(135deg,#818cf8,#c084fc,#e879f9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:18px;color:rgba(255,255,255,0.5);max-width:520px;margin:0 auto;line-height:1.7}
.hero-actions{display:flex;gap:14px;justify-content:center;margin-top:36px;flex-wrap:wrap}
.hero-btn{padding:14px 36px;border-radius:14px;font-size:16px;font-weight:700;cursor:pointer;transition:all 0.3s;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.hero-btn-primary{background:rgba(99,102,241,0.2);backdrop-filter:blur(12px);border:1px solid rgba(99,102,241,0.3);color:#fff}
.hero-btn-primary:hover{background:rgba(99,102,241,0.3);border-color:rgba(99,102,241,0.5);transform:translateY(-2px)}
.hero-btn-secondary{background:rgba(255,255,255,0.03);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.08);color:rgba(255,255,255,0.6)}
.hero-btn-secondary:hover{background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.15);color:#fff}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:30px;font-weight:800;color:#fff}
.section-title p{color:rgba(255,255,255,0.4);font-size:15px}

.features{padding:60px 0;border-top:1px solid rgba(255,255,255,0.05)}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feature-card{background:rgba(255,255,255,0.03);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.06);border-radius:20px;padding:26px 22px;transition:all 0.3s}
.feature-card:hover{background:rgba(255,255,255,0.06);border-color:rgba(99,102,241,0.2);transform:translateY(-4px)}
.feature-icon{width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px;background:rgba(99,102,241,0.12);color:#818cf8}
.feature-card h3{font-size:17px;font-weight:700;margin-bottom:6px;color:#fff}
.feature-card p{font-size:13px;color:rgba(255,255,255,0.45);line-height:1.7}

.steps{padding:60px 0;border-top:1px solid rgba(255,255,255,0.05)}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.step-card{text-align:center;padding:24px 16px;background:rgba(255,255,255,0.02);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.04);border-radius:16px}
.step-num{width:40px;height:40px;border-radius:50%;background:rgba(99,102,241,0.2);color:#818cf8;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:16px;font-weight:800}
.step-card h3{font-size:15px;font-weight:700;margin-bottom:6px;color:#fff}
.step-card p{font-size:13px;color:rgba(255,255,255,0.45);line-height:1.6}

.cta{padding:60px 0 80px;text-align:center}
.cta-box{background:rgba(255,255,255,0.02);backdrop-filter:blur(20px);border:1px solid rgba(99,102,241,0.15);border-radius:24px;padding:48px 32px;max-width:560px;margin:0 auto}
.cta-box h2{font-size:26px;font-weight:800;margin-bottom:8px;color:#fff}
.cta-box p{color:rgba(255,255,255,0.4);font-size:14px;margin-bottom:28px}

.footer{border-top:1px solid rgba(255,255,255,0.04);padding:24px 0;text-align:center;font-size:13px;color:rgba(255,255,255,0.25)}
.footer a{color:rgba(255,255,255,0.35);text-decoration:none}

@media(max-width:768px){
  .hero h1{font-size:32px}.hero p{font-size:15px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}.step-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
  .hero{padding:60px 0 40px}.hero h1{font-size:26px}
  .feature-grid,.step-grid{grid-template-columns:1fr}
  .navbar{flex-wrap:wrap;gap:12px}.nav-actions{width:100%;justify-content:flex-end}
}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>
<div class="wrap">
  <nav class="navbar">
    <a href="/" class="nav-logo"><span class="logo-icon">L</span><span><?=h($siteName)?></span></a>
    <div class="nav-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="nav-btn nav-btn-solid"><i class="fas fa-user"></i> 控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="nav-btn nav-btn-outline">登录</a>
        <a href="./user/index.php" class="nav-btn nav-btn-solid">免费注册</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-badge"><i class="fas fa-window-restore"></i> <?=h($siteName)?></div>
    <h1>模糊之间，<span class="highlight">自有锋芒</span></h1>
    <p><?=h($siteDesc)?></p>
    <div class="hero-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-rocket"></i> 免费创建</a>
        <a href="./user/index.php" class="hero-btn hero-btn-secondary"><i class="fas fa-sign-in-alt"></i> 登录</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="features" id="features">
    <div class="section-title"><h2> 功能介绍</h2><p>丰富多样的模块，打造独一无二的个人主页</p></div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-link"></i></div><h3><i class="fas fa-link"></i> 链接模块</h3><p>添加网页链接，支持自定义图标、卡片颜色、边框样式，还可设置密码保护隐私。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-font"></i></div><h3><i class="fas fa-edit"></i> 文字模块</h3><p>展示纯文字内容，支持居中显示，点击弹出大图预览，适合展示公告、说明等信息。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-image"></i></div><h3><i class="fas fa-image"></i> 图片模块</h3><p>在主页直接展示图片，点击可查看大图，适合作品展示、摄影集等场景。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-images"></i></div><h3><i class="fas fa-image"></i> 弹窗图模块</h3><p>点击卡片弹出图片大图预览，适合展示海报、二维码、证书等需要放大的内容。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-video"></i></div><h3><i class="fas fa-video"></i> 视频模块</h3><p>嵌入视频链接，弹窗播放，支持设置封面图和循环播放，适配移动端体验。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-palette"></i></div><h3><i class="fas fa-palette"></i> 个性化装扮</h3><p>自定义背景、卡片样式、主题模式，打造独一无二的个人主页风格。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-music"></i></div><h3><i class="fas fa-music"></i> 背景音乐</h3><p>添加自定义音乐链接，支持循环播放、自动播放，让主页更有氛围感。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-share-alt"></i></div><h3><i class="fas fa-link"></i> 社交链接</h3><p>集成微信、QQ、Telegram、抖音、B站、小红书、微博、GitHub、邮箱等社交渠道。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-simple"></i></div><h3><i class="fas fa-chart-bar"></i> 数据统计</h3><p>实时统计主页访问量和链接点击量，了解你的主页受欢迎程度。</p></div>
    </div>
  </section>

  <section class="steps" id="howto">
    <div class="section-title"><h2>🚀 三步创建你的主页</h2><p>简单快速，即刻拥有</p></div>
    <div class="step-grid">
      <div class="step-card"><div class="step-num">1</div><h3>注册账号</h3><p>填写用户名和密码，免费注册，无需手机号或邮箱验证。</p></div>
      <div class="step-card"><div class="step-num">2</div><h3>编辑主页</h3><p>添加链接、文字、图片、视频等模块，自定义背景和样式。</p></div>
      <div class="step-card"><div class="step-num">3</div><h3>设置后缀</h3><p>设置专属后缀，分享给朋友：你的域名/你的后缀</p></div>
      <div class="step-card"><div class="step-num">4</div><h3>分享出去</h3><p>一键分享到社交平台，让更多人看到你的专属主页！</p></div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-box">
      <h2> 开始打造你的主页</h2>
      <p>免费创建，无需任何费用，即刻拥有你的专属空间</p>
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-rocket"></i> 免费创建</a>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <?=date('Y')?> <a href="<?=h($baseUrl)?>"><?=h($siteName)?></a> · 每个人都有自己的专属主页</p>
  </footer>
</div>
</body>
</html>