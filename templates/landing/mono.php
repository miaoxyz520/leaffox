<?php
/**
 * Leaffox 系统首页模版 - Mono
 * ============================================
 * 风格：黑白极简，摄影式排版，单色调，艺术杂志感
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
  background:#f5f5f5;color:#1a1a1a;
  font-family:"Georgia","Times New Roman","Noto Serif SC",serif;
  min-height:100vh;overflow-x:hidden;
}
/* 噪点纹理 */
body::before{
  content:"";position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
  background-size:256px 256px;
}
.wrap{position:relative;z-index:2;max-width:880px;margin:0 auto;padding:0 28px}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:28px 0;border-bottom:1px solid rgba(0,0,0,0.04)}
.nav-logo{display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700;color:#1a1a1a;text-decoration:none;letter-spacing:3px;font-family:"Helvetica Neue",Arial,sans-serif;text-transform:uppercase}
.nav-logo .logo-icon{width:28px;height:28px;border:1px solid #1a1a1a;display:flex;align-items:center;justify-content:center;font-size:12px;opacity:0.4}
.nav-actions{display:flex;gap:20px;align-items:center}
.nav-btn{font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none;letter-spacing:2px;text-transform:uppercase;font-family:"Helvetica Neue",Arial,sans-serif}
.nav-btn-outline{color:rgba(26,26,26,0.35)}
.nav-btn-outline:hover{color:#1a1a1a}
.nav-btn-solid{color:#1a1a1a;border-bottom:1px solid #1a1a1a;padding-bottom:2px}
.nav-btn-solid:hover{opacity:0.6}

.hero{text-align:center;padding:110px 0 50px}
.hero h1{font-size:48px;font-weight:400;line-height:1.15;margin-bottom:24px;letter-spacing:2px;font-family:"Georgia",serif}
.hero .sub{display:block;font-size:14px;color:rgba(26,26,26,0.25);margin-top:16px;font-weight:400;letter-spacing:4px;text-transform:uppercase;font-family:"Helvetica Neue",Arial,sans-serif}
.hero p{font-size:15px;color:rgba(26,26,26,0.3);max-width:440px;margin:0 auto;line-height:1.8;font-weight:400;font-style:italic}
.hero-actions{display:flex;gap:24px;justify-content:center;margin-top:44px;flex-wrap:wrap}
.hero-btn{font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;letter-spacing:3px;text-transform:uppercase;font-family:"Helvetica Neue",Arial,sans-serif;padding:0 0 4px}
.hero-btn-primary{color:#1a1a1a;border-bottom:1px solid #1a1a1a}
.hero-btn-primary:hover{opacity:0.5}
.hero-btn-secondary{color:rgba(26,26,26,0.25)}
.hero-btn-secondary:hover{color:#1a1a1a}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:22px;font-weight:400;margin-bottom:8px;letter-spacing:3px;font-family:"Georgia",serif}
.section-title p{color:rgba(26,26,26,0.2);font-size:12px;letter-spacing:3px;text-transform:uppercase;font-family:"Helvetica Neue",Arial,sans-serif}
.features{padding:70px 0;border-top:1px solid rgba(0,0,0,0.03)}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0}
.feature-card{padding:32px 24px;border-bottom:1px solid rgba(0,0,0,0.02);transition:all 0.3s}
.feature-card:nth-child(1),.feature-card:nth-child(2),.feature-card:nth-child(3){border-top:1px solid rgba(0,0,0,0.02)}
.feature-card:nth-child(3n+1),.feature-card:nth-child(3n+2),.feature-card:nth-child(3n){border-right:1px solid rgba(0,0,0,0.02)}
.feature-card:nth-child(3n){border-right:none}
.feature-card:hover{background:rgba(0,0,0,0.01)}
.feature-icon{width:36px;height:36px;border:1px solid rgba(0,0,0,0.04);display:flex;align-items:center;justify-content:center;font-size:14px;margin-bottom:16px;color:rgba(26,26,26,0.3)}
.feature-card h3{font-size:13px;font-weight:600;margin-bottom:6px;letter-spacing:2px;font-family:"Helvetica Neue",Arial,sans-serif;text-transform:uppercase}
.feature-card p{font-size:12px;color:rgba(26,26,26,0.2);line-height:1.7;font-style:italic}

.steps{padding:60px 0;border-top:1px solid rgba(0,0,0,0.03)}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0}
.step-card{text-align:center;padding:32px 16px;border-right:1px solid rgba(0,0,0,0.02)}
.step-card:last-child{border-right:none}
.step-num{font-size:40px;font-weight:400;color:rgba(26,26,26,0.02);margin-bottom:12px;font-family:"Georgia",serif;line-height:1}
.step-card h3{font-size:12px;font-weight:600;margin-bottom:6px;letter-spacing:2px;text-transform:uppercase;font-family:"Helvetica Neue",Arial,sans-serif}
.step-card p{font-size:11px;color:rgba(26,26,26,0.2);line-height:1.6;font-style:italic}

.cta{padding:80px 0;text-align:center;border-top:1px solid rgba(0,0,0,0.03)}
.cta-box{padding:48px 32px;max-width:480px;margin:0 auto}
.cta-box h2{font-size:22px;font-weight:400;margin-bottom:10px;letter-spacing:2px}
.cta-box p{color:rgba(26,26,26,0.2);font-size:13px;margin-bottom:32px;font-style:italic}

.footer{border-top:1px solid rgba(0,0,0,0.02);padding:28px 0;text-align:center;font-size:11px;color:rgba(26,26,26,0.1);letter-spacing:2px}
.footer a{color:rgba(26,26,26,0.12);text-decoration:none}

@media(max-width:768px){
  .hero h1{font-size:34px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}.step-grid{grid-template-columns:repeat(2,1fr)}
  .feature-card:nth-child(3n){border-right:1px solid rgba(0,0,0,0.02)}
  .feature-card:nth-child(2n){border-right:none}
}
@media(max-width:480px){
  .hero{padding:80px 0 40px}.hero h1{font-size:26px}
  .feature-grid,.step-grid{grid-template-columns:1fr}
  .feature-card:nth-child(n){border-right:none}
}
</style>
</head>
<body>
<div class="wrap">
  <nav class="navbar">
    <a href="/" class="nav-logo"><span class="logo-icon">L</span><span><?=h($siteName)?></span></a>
    <div class="nav-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="nav-btn nav-btn-solid">控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="nav-btn nav-btn-outline">登录</a>
        <a href="./user/index.php" class="nav-btn nav-btn-solid">注册</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero">
    <h1><?=h($siteName)?><span class="sub">专属个人主页</span></h1>
    <p><?=h($siteDesc)?></p>
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
    <div class="section-title"><h2>Features</h2><p>精心雕琢的功能</p></div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-link"></i></div><h3>链接</h3><p>自定义图标、色彩与密码保护，多款卡片样式可选。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-font"></i></div><h3>文字</h3><p>纯文字展示，支持居中与弹出大图预览。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-image"></i></div><h3>图片</h3><p>原图展示，点击查看高清大图。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-images"></i></div><h3>弹窗图</h3><p>卡片触发弹窗，优雅展示大图。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-video"></i></div><h3>视频</h3><p>嵌入视频链接，弹窗播放，自定义封面图。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-music"></i></div><h3>音乐</h3><p>背景音乐，循环播放，营造氛围。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-share-alt"></i></div><h3>社交</h3><p>微信、QQ、微博、B站、GitHub一键链接。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-palette"></i></div><h3>装扮</h3><p>自由定义背景、卡片样式与主题色调。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-simple"></i></div><h3>统计</h3><p>实时访问与点击数据，一目了然。</p></div>
    </div>
  </section>

  <section class="steps" id="howto">
    <div class="section-title"><h2>How to</h2><p>开始你的主页</p></div>
    <div class="step-grid">
      <div class="step-card"><div class="step-num">01</div><h3>注册</h3><p>免费注册，即刻加入。</p></div>
      <div class="step-card"><div class="step-num">02</div><h3>编辑</h3><p>添加模块，自由排版。</p></div>
      <div class="step-card"><div class="step-num">03</div><h3>发布</h3><p>设置后缀，分享链接。</p></div>
      <div class="step-card"><div class="step-num">04</div><h3>传播</h3><p>分享至社交平台。</p></div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-box">
      <h2>开始创作</h2>
      <p>免费创建，即刻拥有</p>
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