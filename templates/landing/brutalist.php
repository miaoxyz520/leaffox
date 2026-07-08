<?php
/**
 * Leaffox 系统首页模版 - Brutalist
 * ============================================
 * 风格：粗野主义，超大排版，重边框，强烈视觉冲击
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
  background:#0a0a0a;color:#f0f0f0;
  font-family:"Impact","Arial Black","Helvetica Neue",-apple-system,sans-serif;
  min-height:100vh;overflow-x:hidden;
}
.wrap{max-width:1100px;margin:0 auto;padding:0 20px;position:relative;z-index:1}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:24px 0;border-bottom:4px solid #f0f0f0}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:22px;font-weight:900;color:#f0f0f0;text-decoration:none;letter-spacing:3px;text-transform:uppercase}
.nav-logo .logo-icon{width:40px;height:40px;border:3px solid #f0f0f0;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#f0f0f0}
.nav-actions{display:flex;gap:0;align-items:center}
.nav-btn{padding:12px 28px;font-size:14px;font-weight:900;cursor:pointer;transition:all 0.1s;text-decoration:none;text-transform:uppercase;letter-spacing:2px;border:3px solid #f0f0f0;margin-left:-1px}
.nav-btn-outline{background:transparent;color:#f0f0f0}
.nav-btn-outline:hover{background:#f0f0f0;color:#0a0a0a}
.nav-btn-solid{background:#f0f0f0;color:#0a0a0a;border-color:#f0f0f0}
.nav-btn-solid:hover{background:transparent;color:#f0f0f0}

.hero{text-align:center;padding:120px 0 80px}
.hero-badge{display:inline-block;padding:8px 24px;border:3px solid #f0f0f0;color:#f0f0f0;font-size:12px;font-weight:900;margin-bottom:32px;letter-spacing:4px;text-transform:uppercase;background:transparent}
.hero h1{font-size:80px;font-weight:900;line-height:0.95;margin-bottom:24px;text-transform:uppercase;letter-spacing:-2px}
.hero p{font-size:16px;color:rgba(240,240,240,0.5);max-width:500px;margin:0 auto;line-height:1.6;font-family:"Helvetica Neue",Arial,sans-serif;font-weight:400;letter-spacing:1px}
.hero-actions{display:flex;gap:0;justify-content:center;margin-top:48px;flex-wrap:wrap}
.hero-btn{padding:18px 48px;font-size:16px;font-weight:900;cursor:pointer;transition:all 0.1s;text-decoration:none;display:inline-flex;align-items:center;gap:10px;text-transform:uppercase;letter-spacing:3px;border:3px solid #f0f0f0;margin:-1px}
.hero-btn-primary{background:#f0f0f0;color:#0a0a0a}
.hero-btn-primary:hover{background:transparent;color:#f0f0f0}
.hero-btn-secondary{background:transparent;color:#f0f0f0}
.hero-btn-secondary:hover{background:#f0f0f0;color:#0a0a0a}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:36px;font-weight:900;margin-bottom:8px;text-transform:uppercase;letter-spacing:3px}
.section-title p{color:rgba(240,240,240,0.35);font-size:13px;letter-spacing:2px;text-transform:uppercase;font-family:"Helvetica Neue",Arial,sans-serif;font-weight:400}
.features{padding:80px 0;border-top:4px solid rgba(240,240,240,0.2)}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0}
.feature-card{border:2px solid rgba(240,240,240,0.1);padding:36px 28px;transition:all 0.2s;margin:-1px;position:relative}
.feature-card:hover{background:rgba(240,240,240,0.03);border-color:#f0f0f0;z-index:1}
.feature-icon{width:52px;height:52px;border:3px solid rgba(240,240,240,0.2);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:20px;color:#f0f0f0}
.feature-card h3{font-size:15px;font-weight:900;margin-bottom:10px;text-transform:uppercase;letter-spacing:2px}
.feature-card p{font-size:12px;color:rgba(240,240,240,0.35);line-height:1.8;font-family:"Helvetica Neue",Arial,sans-serif;font-weight:400}

.steps{padding:60px 0;border-top:4px solid rgba(240,240,240,0.2)}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0}
.step-card{text-align:center;padding:36px 20px;border:2px solid rgba(240,240,240,0.1);margin:-1px}
.step-num{width:64px;height:64px;border:3px solid #f0f0f0;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;font-weight:900;color:#f0f0f0}
.step-card h3{font-size:14px;font-weight:900;margin-bottom:8px;text-transform:uppercase;letter-spacing:2px}
.step-card p{font-size:12px;color:rgba(240,240,240,0.35);line-height:1.6;font-family:"Helvetica Neue",Arial,sans-serif;font-weight:400}

.cta{padding:80px 0;text-align:center}
.cta-box{border:4px solid #f0f0f0;padding:60px 40px;max-width:600px;margin:0 auto}
.cta-box h2{font-size:32px;font-weight:900;margin-bottom:12px;text-transform:uppercase;letter-spacing:2px}
.cta-box p{color:rgba(240,240,240,0.4);font-size:13px;margin-bottom:32px;letter-spacing:2px;font-family:"Helvetica Neue",Arial,sans-serif}

.footer{border-top:4px solid rgba(240,240,240,0.1);padding:24px 0;text-align:center;font-size:11px;color:rgba(240,240,240,0.15);letter-spacing:2px;text-transform:uppercase}
.footer a{color:rgba(240,240,240,0.2);text-decoration:none}

@media(max-width:768px){
  .hero h1{font-size:44px}.hero p{font-size:14px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}.step-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
  .hero{padding:60px 0 40px}.hero h1{font-size:32px}
  .feature-grid,.step-grid{grid-template-columns:1fr}
  .navbar{flex-direction:column;gap:16px}
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
        <a href="./user/index.php" class="nav-btn nav-btn-solid">注册</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-badge">✦ <?=h($siteName)?></div>
    <h1>你的主页<br>你定义</h1>
    <p><?=h($siteDesc)?> — 最纯粹的表达，最直接的方式。</p>
    <div class="hero-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary">进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary">免费创建</a>
        <a href="./user/index.php" class="hero-btn hero-btn-secondary">登录</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="features" id="features">
    <div class="section-title"><h2>功能</h2><p>强大而简洁</p></div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-link"></i></div><h3>链接</h3><p>自定义图标、颜色、密码保护，多种样式随心配。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-font"></i></div><h3>文字</h3><p>纯文字展示，支持居中、点击弹出大图预览。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-image"></i></div><h3>图片</h3><p>作品展示、摄影集，点击查看大图。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-images"></i></div><h3>弹窗图</h3><p>海报、二维码、证书，点击弹出大图。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-video"></i></div><h3>视频</h3><p>嵌入视频链接，弹窗播放，自定义封面。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-music"></i></div><h3>音乐</h3><p>背景音乐，自动循环播放，氛围感拉满。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-share-alt"></i></div><h3>社交</h3><p>微信、QQ、抖音、B站、GitHub一键直达。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-palette"></i></div><h3>装扮</h3><p>自定义背景、卡片样式、主题，风格随心。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-simple"></i></div><h3>统计</h3><p>访问量、点击量，数据一目了然。</p></div>
    </div>
  </section>

  <section class="steps" id="howto">
    <div class="section-title"><h2>开始</h2><p>三步搞定</p></div>
    <div class="step-grid">
      <div class="step-card"><div class="step-num">01</div><h3>注册</h3><p>免费注册，无需验证，即刻加入。</p></div>
      <div class="step-card"><div class="step-num">02</div><h3>编辑</h3><p>添加模块，自定义样式，打造专属主页。</p></div>
      <div class="step-card"><div class="step-num">03</div><h3>发布</h3><p>设置后缀，分享给你的朋友。</p></div>
      <div class="step-card"><div class="step-num">04</div><h3>传播</h3><p>一键分享到社交平台，让更多人看到。</p></div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-box">
      <h2>准备好了吗？</h2>
      <p>免费创建，即刻开始</p>
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary">进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary">免费创建</a>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <?=date('Y')?> <a href="<?=h($baseUrl)?>"><?=h($siteName)?></a></p>
  </footer>
</div>
</body>
</html>