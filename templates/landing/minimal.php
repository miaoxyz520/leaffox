<?php
/**
 * Leaffox 系统首页模版 - Minimal（内置）
 * ============================================
 * 模版类型：landing（系统落地页）
 * 文件路径：templates/landing/minimal.php
 * 风格：极致简约，白色/浅灰，SaaS 产品风，干净利落
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
body{background:#f8fafc;color:#0f172a;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;min-height:100vh;overflow-x:hidden}
.wrap{max-width:960px;margin:0 auto;padding:0 24px}
.navbar{display:flex;align-items:center;justify-content:space-between;padding:20px 0}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:800;color:#0f172a;text-decoration:none}
.nav-logo .logo-icon{width:34px;height:34px;border-radius:8px;background:#0f172a;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;color:#fff}
.nav-actions{display:flex;gap:10px}
.nav-btn{padding:9px 22px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none}
.nav-btn-outline{border:1px solid #e2e8f0;color:#475569;background:#fff}
.nav-btn-outline:hover{border-color:#94a3b8;background:#f1f5f9}
.nav-btn-solid{background:#0f172a;color:#fff;border:none}
.nav-btn-solid:hover{background:#1e293b}

.hero{text-align:center;padding:100px 0 70px}
.hero-badge{display:inline-block;padding:5px 16px;border-radius:20px;background:#eef2ff;color:#6366f1;font-size:13px;font-weight:600;margin-bottom:24px}
.hero h1{font-size:46px;font-weight:900;line-height:1.15;margin-bottom:18px;color:#0f172a;letter-spacing:-0.5px}
.hero h1 .highlight{background:linear-gradient(135deg,#6366f1,#0ea5e9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:18px;color:#64748b;max-width:520px;margin:0 auto;line-height:1.7}
.hero-actions{display:flex;gap:12px;justify-content:center;margin-top:36px;flex-wrap:wrap}
.hero-btn{padding:14px 34px;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.hero-btn-primary{background:#0f172a;color:#fff;box-shadow:0 4px 12px rgba(15,23,42,0.15)}
.hero-btn-primary:hover{background:#1e293b;transform:translateY(-2px);box-shadow:0 8px 24px rgba(15,23,42,0.2)}
.hero-btn-secondary{background:#fff;color:#475569;border:1px solid #e2e8f0}
.hero-btn-secondary:hover{background:#f8fafc;border-color:#cbd5e1}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:28px;font-weight:800;color:#0f172a;margin-bottom:8px}
.section-title p{color:#64748b;font-size:15px}
.features{padding:70px 0;border-top:1px solid #e2e8f0}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.feature-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px 24px;transition:all 0.2s}
.feature-card:hover{border-color:#cbd5e1;box-shadow:0 4px 20px rgba(0,0,0,0.04);transform:translateY(-3px)}
.feature-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px;color:#6366f1}
.feature-card h3{font-size:17px;font-weight:700;margin-bottom:6px;color:#0f172a}
.feature-card p{font-size:13px;color:#64748b;line-height:1.7}

.steps{padding:70px 0;border-top:1px solid #e2e8f0}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.step-card{text-align:center;padding:24px 16px}
.step-num{width:40px;height:40px;border-radius:50%;background:#eef2ff;color:#6366f1;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:16px;font-weight:800}
.step-card h3{font-size:15px;font-weight:700;margin-bottom:6px;color:#0f172a}
.step-card p{font-size:13px;color:#64748b;line-height:1.6}

.cta{padding:60px 0 80px;text-align:center}
.cta-box{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:48px 32px;max-width:580px;margin:0 auto}
.cta-box h2{font-size:24px;font-weight:800;color:#0f172a;margin-bottom:8px}
.cta-box p{color:#64748b;font-size:14px;margin-bottom:28px}
.footer{border-top:1px solid #e2e8f0;padding:24px 0;text-align:center;font-size:13px;color:#94a3b8}
.footer a{color:#64748b;text-decoration:none}
@keyframes fade{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.hero,.feature-card,.step-card{animation:fade 0.6s ease both}
.feature-card:nth-child(2){animation-delay:0.1s}.feature-card:nth-child(3){animation-delay:0.2s}
.step-card:nth-child(2){animation-delay:0.1s}.step-card:nth-child(3){animation-delay:0.2s}.step-card:nth-child(4){animation-delay:0.3s}

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
    <div class="hero-badge">🚀 <?=h($siteName)?></div>
    <h1>你的 <span class="highlight">专属个人主页</span><br>一键开启</h1>
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
      <div class="feature-card"><div class="feature-icon" style="background:#eef2ff"><i class="fas fa-link"></i></div><h3><i class="fas fa-link"></i> 链接模块</h3><p>添加网页链接，支持自定义图标、卡片颜色、边框样式，还可设置密码保护隐私。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#ecfdf5"><i class="fas fa-font"></i></div><h3><i class="fas fa-edit"></i> 文字模块</h3><p>展示纯文字内容，支持居中显示，点击弹出大图预览，适合展示公告、说明等信息。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#fff7ed"><i class="fas fa-image"></i></div><h3><i class="fas fa-image"></i> 图片模块</h3><p>在主页直接展示图片，点击可查看大图，适合作品展示、摄影集等场景。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#fdf2f8"><i class="fas fa-images"></i></div><h3><i class="fas fa-image"></i> 弹窗图模块</h3><p>点击卡片弹出图片大图预览，适合展示海报、二维码、证书等需要放大的内容。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#f5f3ff"><i class="fas fa-video"></i></div><h3><i class="fas fa-video"></i> 视频模块</h3><p>嵌入视频链接，弹窗播放，支持设置封面图和循环播放，适配移动端体验。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#ecfeff"><i class="fas fa-palette"></i></div><h3><i class="fas fa-palette"></i> 个性化装扮</h3><p>自定义背景、卡片样式、主题模式，打造独一无二的个人主页风格。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#fefce8"><i class="fas fa-music"></i></div><h3><i class="fas fa-music"></i> 背景音乐</h3><p>添加自定义音乐链接，支持循环播放、自动播放，让主页更有氛围感。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#fef2f2"><i class="fas fa-share-alt"></i></div><h3><i class="fas fa-link"></i> 社交链接</h3><p>集成微信、QQ、Telegram、抖音、B站、小红书、微博、GitHub、邮箱等社交渠道。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#eff6ff"><i class="fas fa-chart-simple"></i></div><h3><i class="fas fa-chart-bar"></i> 数据统计</h3><p>实时统计主页访问量和链接点击量，了解你的主页受欢迎程度。</p></div>
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