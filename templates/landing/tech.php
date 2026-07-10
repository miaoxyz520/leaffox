<?php
/**
 * Leaffox 系统首页模版 - Tech（内置）
 * ============================================
 * 模版类型：landing（系统落地页）
 * 文件路径：templates/landing/tech.php
 * 风格：暗色科技风，代码终端元素，等宽字体
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
<link rel="stylesheet" href="../../assets/css/fontawesome.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  background:#0d1117;color:#c9d1d9;
  font-family:'JetBrains Mono','Inter',monospace,sans-serif;
  min-height:100vh;overflow-x:hidden;
}
.wrap{position:relative;z-index:1;max-width:960px;margin:0 auto;padding:0 24px}
.scanline{
  position:fixed;top:0;left:0;width:100%;height:100%;
  background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,255,65,0.015) 2px,rgba(0,255,65,0.015) 4px);
  pointer-events:none;z-index:2;
}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:20px 0;border-bottom:1px solid rgba(48,54,61,0.8)}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:800;color:#58a6ff;text-decoration:none;font-family:'JetBrains Mono',monospace}
.nav-logo .logo-icon{padding:4px 8px;border:1px solid #58a6ff;border-radius:4px;font-size:13px;color:#58a6ff;background:rgba(88,166,255,0.08)}
.nav-logo .logo-cursor{animation:blink 1s step-end infinite;color:#58a6ff}
@keyframes blink{50%{opacity:0}}
.nav-actions{display:flex;gap:8px}
.nav-btn{padding:8px 18px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none;font-family:'JetBrains Mono',monospace}
.nav-btn-outline{border:1px solid #30363d;color:#8b949e;background:transparent}
.nav-btn-outline:hover{border-color:#58a6ff;color:#58a6ff}
.nav-btn-solid{background:#238636;color:#fff;border:none}
.nav-btn-solid:hover{background:#2ea043}

.hero{text-align:center;padding:90px 0 60px}
.hero-badge{display:inline-block;padding:4px 14px;border-radius:4px;background:rgba(88,166,255,0.08);border:1px solid rgba(88,166,255,0.2);color:#58a6ff;font-size:11px;font-weight:500;margin-bottom:24px;font-family:'JetBrains Mono',monospace}
.hero h1{font-size:40px;font-weight:800;line-height:1.2;margin-bottom:16px;color:#f0f6fc;font-family:'JetBrains Mono',monospace}
.hero h1 .highlight{color:#58a6ff}
.hero h1 .cursor{animation:blink 1s step-end infinite;color:#58a6ff;font-weight:400}
.hero p{font-size:14px;color:#8b949e;max-width:520px;margin:0 auto;line-height:1.7;font-family:'JetBrains Mono',monospace}
.hero-actions{display:flex;gap:10px;justify-content:center;margin-top:36px;flex-wrap:wrap}
.hero-btn{padding:12px 28px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace}
.hero-btn-primary{background:#238636;color:#fff;border:none}
.hero-btn-primary:hover{background:#2ea043}
.hero-btn-secondary{border:1px solid #30363d;color:#8b949e;background:transparent}
.hero-btn-secondary:hover{border-color:#8b949e;color:#f0f6fc}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:22px;font-weight:800;color:#f0f6fc;font-family:'JetBrains Mono',monospace}
.section-title h2::before{content:'## ';color:#58a6ff}
.section-title p{color:#8b949e;font-size:13px}

.features{padding:60px 0;border-top:1px solid #21262d}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.feature-card{background:#161b22;border:1px solid #30363d;border-radius:8px;padding:22px 20px;transition:all 0.2s}
.feature-card:hover{border-color:#58a6ff;background:#1c2128}
.feature-icon{width:40px;height:40px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:14px;background:rgba(88,166,255,0.08);color:#58a6ff;border:1px solid rgba(88,166,255,0.15)}
.feature-card h3{font-size:14px;font-weight:700;margin-bottom:6px;color:#f0f6fc;font-family:'JetBrains Mono',monospace}
.feature-card p{font-size:12px;color:#8b949e;line-height:1.7}

.steps{padding:60px 0;border-top:1px solid #21262d}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.step-card{text-align:center;padding:20px 12px;background:#161b22;border:1px solid #30363d;border-radius:8px}
.step-num{font-family:'JetBrains Mono',monospace;width:36px;height:36px;border-radius:4px;background:rgba(88,166,255,0.1);color:#58a6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:16px;font-weight:800;border:1px solid rgba(88,166,255,0.15)}
.step-card h3{font-size:13px;font-weight:700;margin-bottom:6px;color:#f0f6fc;font-family:'JetBrains Mono',monospace}
.step-card p{font-size:12px;color:#8b949e;line-height:1.6}

.cta{padding:60px 0 80px;text-align:center}
.cta-box{background:#161b22;border:1px solid #30363d;border-radius:12px;padding:44px 32px;max-width:560px;margin:0 auto}
.cta-box h2{font-size:22px;font-weight:800;color:#f0f6fc;margin-bottom:8px;font-family:'JetBrains Mono',monospace}
.cta-box h2::before{content:'$ ';color:#3fb950}
.cta-box p{color:#8b949e;font-size:13px;margin-bottom:28px}
.footer{border-top:1px solid #21262d;padding:24px 0;text-align:center;font-size:12px;color:#484f58;font-family:'JetBrains Mono',monospace}
.footer a{color:#58a6ff;text-decoration:none}

.terminal-line{display:inline-block;padding:2px 10px;background:rgba(88,166,255,0.05);border-radius:3px;color:#58a6ff;font-size:12px;margin-top:20px;border:1px solid rgba(88,166,255,0.1)}

@media(max-width:768px){
  .hero h1{font-size:28px}.hero p{font-size:12px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}.step-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
  .hero{padding:60px 0 40px}.hero h1{font-size:22px}
  .feature-grid,.step-grid{grid-template-columns:1fr}
  .navbar{flex-wrap:wrap;gap:12px}.nav-actions{width:100%;justify-content:flex-end}
}
</style>
<link rel="stylesheet" href="../assets/css/fonts.css">
</head>
<body>
<div class="scanline"></div>
<div class="wrap">
  <nav class="navbar">
    <a href="/" class="nav-logo"><span class="logo-icon">&gt;_</span><span><?=h($siteName)?><span class="logo-cursor">▊</span></span></a>
    <div class="nav-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="nav-btn nav-btn-solid"><i class="fas fa-user"></i> dashboard</a>
      <?php else: ?>
        <a href="./user/index.php" class="nav-btn nav-btn-outline">login</a>
        <a href="./user/index.php" class="nav-btn nav-btn-solid">sign up</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-badge">// <?=h($siteName)?> v1.0</div>
    <h1>build your <span class="highlight">page</span><span class="cursor">_</span></h1>
    <p>&gt; <?=h($siteDesc)?></p>
    <div class="hero-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> ~/dashboard</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-rocket"></i> ./deploy</a>
        <a href="./user/index.php" class="hero-btn hero-btn-secondary"><i class="fas fa-sign-in-alt"></i> ./login</a>
      <?php endif; ?>
    </div>
    <div class="terminal-line">$ echo "每个人都有自己的专属主页"</div>
  </section>

  <section class="features" id="features">
    <div class="section-title"><h2>功能特性</h2><p>模块化设计，自由组合</p></div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-link"></i></div><h3>links</h3><p>自定义链接卡片，支持图标、颜色、密码保护</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-font"></i></div><h3>text</h3><p>纯文字展示模块，点击弹出大图预览</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-image"></i></div><h3>image</h3><p>图片直接展示，点击查看大图</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-images"></i></div><h3>popup</h3><p>弹窗式图片展示，适合海报和证书</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-video"></i></div><h3>video</h3><p>嵌入视频链接，弹窗播放</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-palette"></i></div><h3>customize</h3><p>自定义背景、卡片样式、主题模式</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-music"></i></div><h3>music</h3><p>背景音乐，支持循环和自动播放</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-share-alt"></i></div><h3>social</h3><p>微信、QQ、GitHub 等社交渠道集成</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-simple"></i></div><h3>analytics</h3><p>访问量和点击量实时统计</p></div>
    </div>
  </section>

  <section class="steps" id="howto">
    <div class="section-title"><h2>快速开始</h2><p>4 步完成部署</p></div>
    <div class="step-grid">
      <div class="step-card"><div class="step-num">01</div><h3>注册</h3><p>免费创建账号，无需邮箱验证</p></div>
      <div class="step-card"><div class="step-num">02</div><h3>编辑</h3><p>添加模块，自定义外观</p></div>
      <div class="step-card"><div class="step-num">03</div><h3>部署</h3><p>设置专属后缀</p></div>
      <div class="step-card"><div class="step-num">04</div><h3>分享</h3><p>一键分享到社交平台</p></div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-box">
      <h2>$ npm create page</h2>
      <p>免费创建，即刻拥有你的专属主页</p>
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> ~/dashboard</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-rocket"></i> ./deploy</a>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <?=date('Y')?> <a href="<?=h($baseUrl)?>"><?=h($siteName)?></a> · everyone has a page</p>
  </footer>
</div>
</body>
</html>