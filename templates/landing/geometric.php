<?php
/**
 * Leaffox 系统首页模版 - Geometric
 * ============================================
 * 风格：几何动态，多边形背景动画，现代科技，锐利摩登
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
  background:#0c0e12;color:#d0d4da;
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"PingFang SC","Microsoft YaHei",sans-serif;
  min-height:100vh;overflow-x:hidden;
}
/* 几何背景 */
.geo-bg{position:fixed;inset:0;z-index:0;overflow:hidden;background:#0c0e12}
.geo-shape{position:absolute;opacity:0.03;animation:geoFloat 20s ease-in-out infinite alternate}
.geo-shape:nth-child(1){width:400px;height:400px;top:-100px;left:-100px;border:2px solid #4a8eff;border-radius:43% 57% 48% 52%;animation-duration:25s}
.geo-shape:nth-child(2){width:300px;height:300px;bottom:10%;right:5%;border:2px solid #ff6a4a;border-radius:62% 38% 55% 45%;animation-duration:20s;animation-delay:-5s}
.geo-shape:nth-child(3){width:200px;height:200px;top:40%;left:60%;border:2px solid #50d890;border-radius:30% 70% 40% 60%;animation-duration:18s;animation-delay:-10s}
.geo-shape:nth-child(4){width:250px;height:250px;top:15%;right:30%;border:2px solid #aa6aff;border-radius:50% 50% 40% 60%;animation-duration:22s;animation-delay:-3s}
.geo-shape:nth-child(5){width:350px;height:350px;bottom:-50px;left:20%;border:2px solid #ffc84a;border-radius:55% 45% 60% 40%;animation-duration:28s;animation-delay:-8s}
@keyframes geoFloat{0%{transform:translate(0,0) rotate(0deg) scale(1)}33%{transform:translate(30px,-20px) rotate(120deg) scale(1.05)}66%{transform:translate(-20px,30px) rotate(240deg) scale(0.95)}100%{transform:translate(20px,-10px) rotate(360deg) scale(1)}}
/* 网格线 */
.grid-overlay{position:fixed;inset:0;z-index:1;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,0.008) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.008) 1px,transparent 1px);background-size:80px 80px}

.wrap{position:relative;z-index:2;max-width:1000px;margin:0 auto;padding:0 24px}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:20px 0;border-bottom:1px solid rgba(255,255,255,0.02)}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:800;color:#e0e4ea;text-decoration:none;letter-spacing:1px}
.nav-logo .logo-icon{width:32px;height:32px;border:2px solid rgba(255,255,255,0.1);transform:rotate(45deg);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#e0e4ea}
.nav-actions{display:flex;gap:6px;align-items:center}
.nav-btn{padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none;letter-spacing:1px;border-radius:0}
.nav-btn-outline{border:1px solid rgba(255,255,255,0.04);color:rgba(255,255,255,0.4);background:transparent}
.nav-btn-outline:hover{border-color:rgba(255,255,255,0.08);color:rgba(255,255,255,0.6)}
.nav-btn-solid{border:1px solid rgba(255,255,255,0.08);color:rgba(255,255,255,0.7);background:rgba(255,255,255,0.02)}
.nav-btn-solid:hover{border-color:rgba(255,255,255,0.12);color:rgba(255,255,255,0.9);background:rgba(255,255,255,0.04)}

.hero{text-align:center;padding:100px 0 60px}
.hero h1{font-size:48px;font-weight:200;line-height:1.15;margin-bottom:20px;letter-spacing:4px;font-weight:300}
.hero h1 .bold{font-weight:800;background:linear-gradient(135deg,#e0e4ea,#8899aa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:15px;color:rgba(208,212,218,0.3);max-width:500px;margin:0 auto;line-height:1.8;font-weight:300;letter-spacing:2px}
.hero-actions{display:flex;gap:0;justify-content:center;margin-top:40px;flex-wrap:wrap}
.hero-btn{padding:14px 36px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;letter-spacing:2px;border:1px solid rgba(255,255,255,0.06);margin:-1px}
.hero-btn-primary{background:rgba(255,255,255,0.03);color:rgba(255,255,255,0.7)}
.hero-btn-primary:hover{background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.9)}
.hero-btn-secondary{background:transparent;color:rgba(255,255,255,0.3)}
.hero-btn-secondary:hover{background:rgba(255,255,255,0.02);color:rgba(255,255,255,0.5)}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:26px;font-weight:300;margin-bottom:8px;letter-spacing:4px;color:#e0e4ea}
.section-title p{color:rgba(208,212,218,0.2);font-size:13px;letter-spacing:3px;font-weight:300}
.features{padding:80px 0;border-top:1px solid rgba(255,255,255,0.02)}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:rgba(255,255,255,0.02)}
.feature-card{background:#0c0e12;padding:32px 24px;transition:all 0.3s;position:relative}
.feature-card:hover{background:rgba(255,255,255,0.01)}
.feature-card::after{content:"";position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:0;height:1px;background:rgba(255,255,255,0.06);transition:width 0.4s}
.feature-card:hover::after{width:80%}
.feature-icon{width:40px;height:40px;border:1px solid rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-size:16px;margin-bottom:16px;color:rgba(255,255,255,0.3)}
.feature-card h3{font-size:14px;font-weight:600;margin-bottom:8px;color:#d0d4da;letter-spacing:2px}
.feature-card p{font-size:12px;color:rgba(208,212,218,0.25);line-height:1.8;font-weight:300}

.steps{padding:60px 0;border-top:1px solid rgba(255,255,255,0.02)}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:rgba(255,255,255,0.02)}
.step-card{text-align:center;padding:32px 16px;background:#0c0e12}
.step-num{font-size:36px;font-weight:200;color:rgba(255,255,255,0.04);margin-bottom:12px;font-family:Georgia,serif}
.step-card h3{font-size:14px;font-weight:600;margin-bottom:6px;letter-spacing:2px}
.step-card p{font-size:12px;color:rgba(208,212,218,0.2);line-height:1.6;font-weight:300}
.cta{padding:80px 0;text-align:center}
.cta-box{border:1px solid rgba(255,255,255,0.02);padding:60px 32px;max-width:520px;margin:0 auto;background:rgba(255,255,255,0.005)}
.cta-box h2{font-size:22px;font-weight:300;margin-bottom:10px;letter-spacing:4px}
.cta-box p{color:rgba(208,212,218,0.2);font-size:13px;margin-bottom:32px;letter-spacing:2px;font-weight:300}
.footer{border-top:1px solid rgba(255,255,255,0.01);padding:24px 0;text-align:center;font-size:11px;color:rgba(208,212,218,0.08);letter-spacing:2px;font-weight:300}
.footer a{color:rgba(208,212,218,0.1);text-decoration:none}

@media(max-width:768px){
  .hero h1{font-size:32px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}.step-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
  .hero{padding:60px 0 40px}.hero h1{font-size:24px}
  .feature-grid,.step-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="geo-bg">
  <div class="geo-shape"></div><div class="geo-shape"></div>
  <div class="geo-shape"></div><div class="geo-shape"></div>
  <div class="geo-shape"></div>
</div>
<div class="grid-overlay"></div>
<div class="wrap">
  <nav class="navbar">
    <a href="/" class="nav-logo"><span class="logo-icon"><i class="fas fa-diamond"></i></span><span><?=h($siteName)?></span></a>
    <div class="nav-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="nav-btn nav-btn-solid"><i class="fas fa-user"></i> 控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="nav-btn nav-btn-outline">登录</a>
        <a href="./user/index.php" class="nav-btn nav-btn-solid">创建</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero">
    <h1><span class="bold">个人主页</span><br>最简单的形态</h1>
    <p><?=h($siteDesc)?> — 极简几何，无限可能。</p>
    <div class="hero-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-plus"></i> 免费创建</a>
        <a href="./user/index.php" class="hero-btn hero-btn-secondary"><i class="fas fa-sign-in-alt"></i> 登录</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="features" id="features">
    <div class="section-title"><h2>要素</h2><p>精心设计，不多不少</p></div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-link"></i></div><h3>链接</h3><p>自定义图标、密码保护、多款卡片样式。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-font"></i></div><h3>文字</h3><p>干净排版，点击弹出大图。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-image"></i></div><h3>图片</h3><p>直接展示，点击查看详情。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-images"></i></div><h3>弹窗图</h3><p>点击弹出，适合海报与证书。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-video"></i></div><h3>视频</h3><p>弹窗播放，自定义封面。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-music"></i></div><h3>音乐</h3><p>背景旋律，自动循环。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-share-alt"></i></div><h3>社交</h3><p>微信、微博、B站等社交渠道。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-palette"></i></div><h3>装扮</h3><p>背景、卡片、主题自定义。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-simple"></i></div><h3>统计</h3><p>访问与点击数据实时查看。</p></div>
    </div>
  </section>
  <section class="steps" id="howto">
    <div class="section-title"><h2>流程</h2><p>简单四步</p></div>
    <div class="step-grid">
      <div class="step-card"><div class="step-num">01</div><h3>注册</h3><p>免费注册，快速加入。</p></div>
      <div class="step-card"><div class="step-num">02</div><h3>编辑</h3><p>添加模块，随心搭配。</p></div>
      <div class="step-card"><div class="step-num">03</div><h3>发布</h3><p>专属后缀，即刻上线。</p></div>
      <div class="step-card"><div class="step-num">04</div><h3>分享</h3><p>让更多人看到你的主页。</p></div>
    </div>
  </section>
  <section class="cta">
    <div class="cta-box">
      <h2>开始构建</h2>
      <p>免费创建你的专属空间</p>
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-plus"></i> 免费创建</a>
      <?php endif; ?>
    </div>
  </section>
  <footer class="footer">
    <p>&copy; <?=date('Y')?> <a href="<?=h($baseUrl)?>"><?=h($siteName)?></a></p>
  </footer>
</div>
</body>
</html>