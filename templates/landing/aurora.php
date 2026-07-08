<?php
/**
 * Leaffox 系统首页模版 - Aurora
 * ============================================
 * 风格：极光主题，流动极光渐变，深邃星空，高级沉浸感
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
  background:#050a1a;color:#c8d0e0;
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"PingFang SC","Microsoft YaHei",sans-serif;
  min-height:100vh;overflow-x:hidden;
}
/* 极光背景 */
.aurora-bg{
  position:fixed;inset:0;overflow:hidden;z-index:0;
  background:linear-gradient(180deg,#050a1a 0%,#0a1030 30%,#0d1540 60%,#050a1a 100%);
}
.aurora-layer{
  position:absolute;border-radius:50%;filter:blur(80px);opacity:0.4;
  animation:auroraFlow 8s ease-in-out infinite alternate;
}
.aurora-layer:nth-child(1){
  width:600px;height:400px;top:-100px;left:10%;
  background:linear-gradient(90deg,rgba(0,200,255,0.3),rgba(100,0,255,0.2));
  animation-duration:10s;animation-delay:0s;
}
.aurora-layer:nth-child(2){
  width:500px;height:300px;top:20%;right:15%;
  background:linear-gradient(90deg,rgba(0,255,180,0.2),rgba(0,100,255,0.3));
  animation-duration:12s;animation-delay:-3s;
}
.aurora-layer:nth-child(3){
  width:400px;height:500px;bottom:10%;left:30%;
  background:linear-gradient(90deg,rgba(150,0,255,0.15),rgba(0,200,200,0.2));
  animation-duration:9s;animation-delay:-6s;
}
.aurora-layer:nth-child(4){
  width:300px;height:300px;top:50%;left:5%;
  background:linear-gradient(90deg,rgba(0,255,200,0.15),rgba(50,100,255,0.2));
  animation-duration:11s;animation-delay:-2s;
}
@keyframes auroraFlow{
  0%{transform:translate(0,0) scale(1) rotate(0deg);opacity:0.3}
  33%{transform:translate(40px,-30px) scale(1.1) rotate(3deg);opacity:0.5}
  66%{transform:translate(-20px,20px) scale(0.95) rotate(-2deg);opacity:0.35}
  100%{transform:translate(30px,-10px) scale(1.05) rotate(1deg);opacity:0.45}
}
/* 星星 */
.stars{
  position:fixed;inset:0;z-index:1;pointer-events:none;
}
.star{
  position:absolute;width:2px;height:2px;background:#fff;border-radius:50%;
  animation:twinkle 3s ease-in-out infinite;
}
@keyframes twinkle{
  0%,100%{opacity:0.15}
  50%{opacity:0.6}
}
.wrap{position:relative;z-index:2;max-width:1000px;margin:0 auto;padding:0 24px}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:20px 0}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:800;color:#e0e8ff;text-decoration:none;letter-spacing:2px}
.nav-logo .logo-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0088ff,#6600ff);display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;box-shadow:0 0 20px rgba(0,136,255,0.3)}
.nav-actions{display:flex;gap:10px;align-items:center}
.nav-btn{padding:8px 22px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none}
.nav-btn-outline{border:1px solid rgba(255,255,255,0.08);color:rgba(255,255,255,0.6);background:rgba(255,255,255,0.03)}
.nav-btn-outline:hover{border-color:rgba(0,136,255,0.2);color:#0088ff;background:rgba(0,136,255,0.05)}
.nav-btn-solid{background:linear-gradient(135deg,#0088ff,#6600ff);color:#fff;border:none;box-shadow:0 4px 20px rgba(0,136,255,0.2)}
.nav-btn-solid:hover{transform:translateY(-1px);box-shadow:0 8px 30px rgba(0,136,255,0.3)}

.hero{text-align:center;padding:100px 0 60px}
.hero-badge{display:inline-block;padding:6px 20px;border-radius:20px;background:rgba(0,136,255,0.08);border:1px solid rgba(0,136,255,0.15);color:rgba(0,200,255,0.7);font-size:12px;font-weight:500;margin-bottom:28px;letter-spacing:2px;text-transform:uppercase}
.hero h1{font-size:52px;font-weight:900;line-height:1.15;margin-bottom:20px}
.hero h1 .gradient{background:linear-gradient(135deg,#0088ff,#00d4ff,#6600ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;background-size:200% 200%;animation:gradientShift 4s ease infinite}
@keyframes gradientShift{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
.hero p{font-size:17px;color:rgba(200,208,224,0.45);max-width:540px;margin:0 auto;line-height:1.8}
.hero-actions{display:flex;gap:14px;justify-content:center;margin-top:40px;flex-wrap:wrap}
.hero-btn{padding:14px 38px;border-radius:14px;font-size:16px;font-weight:700;cursor:pointer;transition:all 0.3s;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.hero-btn-primary{background:linear-gradient(135deg,#0088ff,#6600ff);color:#fff;box-shadow:0 8px 30px rgba(0,136,255,0.2)}
.hero-btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(0,136,255,0.3)}
.hero-btn-secondary{background:rgba(255,255,255,0.03);color:rgba(255,255,255,0.6);border:1px solid rgba(255,255,255,0.06)}
.hero-btn-secondary:hover{background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);color:rgba(255,255,255,0.8)}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:28px;font-weight:800;margin-bottom:8px;color:#e0e8ff}
.section-title p{color:rgba(200,208,224,0.35);font-size:14px}
.features{padding:80px 0}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feature-card{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.04);border-radius:16px;padding:30px 24px;transition:all 0.4s;position:relative;overflow:hidden}
.feature-card::before{content:"";position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(0,136,255,0.15),transparent);opacity:0;transition:opacity 0.4s}
.feature-card:hover::before{opacity:1}
.feature-card:hover{background:rgba(255,255,255,0.03);border-color:rgba(0,136,255,0.06);transform:translateY(-4px)}
.feature-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px}
.feature-card h3{font-size:16px;font-weight:700;margin-bottom:8px;color:#d0d8f0}
.feature-card p{font-size:13px;color:rgba(200,208,224,0.4);line-height:1.7}

.steps{padding:60px 0}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.step-card{text-align:center;padding:24px 16px}
.step-num{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#0088ff,#6600ff);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:16px;font-weight:800;color:#fff;box-shadow:0 4px 15px rgba(0,136,255,0.2)}
.step-card h3{font-size:15px;font-weight:700;margin-bottom:6px;color:#d0d8f0}
.step-card p{font-size:13px;color:rgba(200,208,224,0.4);line-height:1.6}

.cta{padding:60px 0 80px;text-align:center}
.cta-box{background:linear-gradient(135deg,rgba(0,136,255,0.04),rgba(102,0,255,0.03));border:1px solid rgba(0,136,255,0.08);border-radius:24px;padding:48px 32px;max-width:600px;margin:0 auto}
.cta-box h2{font-size:24px;font-weight:800;margin-bottom:10px;color:#e0e8ff}
.cta-box p{color:rgba(200,208,224,0.4);font-size:14px;margin-bottom:28px}

.footer{border-top:1px solid rgba(255,255,255,0.02);padding:28px 0;text-align:center;font-size:12px;color:rgba(200,208,224,0.12)}
.footer a{color:rgba(200,208,224,0.18);text-decoration:none}

@media(max-width:768px){
  .hero h1{font-size:32px}.hero p{font-size:15px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}.step-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
  .hero{padding:60px 0 40px}.hero h1{font-size:24px}
  .feature-grid,.step-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="aurora-bg">
  <div class="aurora-layer"></div>
  <div class="aurora-layer"></div>
  <div class="aurora-layer"></div>
  <div class="aurora-layer"></div>
</div>
<div class="stars" id="stars"></div>
<script>
for(let i=0;i<80;i++){
  const s=document.createElement('div');s.className='star';
  s.style.left=Math.random()*100+'%';s.style.top=Math.random()*100+'%';
  s.style.animationDelay=Math.random()*3+'s';s.style.width=s.style.height=Math.random()*2+1+'px';
  document.getElementById('stars').appendChild(s);
}
</script>
<div class="wrap">
  <nav class="navbar">
    <a href="/" class="nav-logo"><span class="logo-icon">✦</span><span><?=h($siteName)?></span></a>
    <div class="nav-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="nav-btn nav-btn-solid"><i class="fas fa-user"></i> 控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="nav-btn nav-btn-outline">登录</a>
        <a href="./user/index.php" class="nav-btn nav-btn-solid">创建主页</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-badge">✦ <?=h($siteName)?></div>
    <h1>你的专属 <span class="gradient">星际主页</span></h1>
    <p><?=h($siteDesc)?> — 在浩瀚网络宇宙中，点亮属于你的那颗星。</p>
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
    <div class="section-title"><h2> 功能星系</h2><p>探索无限可能</p></div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon" style="background:rgba(0,136,255,0.06);color:#0088ff"><i class="fas fa-link"></i></div><h3>链接模块</h3><p>添加网页链接，支持自定义图标、卡片颜色、密码保护。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(0,200,180,0.06);color:#00c8b4"><i class="fas fa-font"></i></div><h3>文字模块</h3><p>展示纯文字内容，支持居中显示，点击弹出大图预览。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(255,180,0,0.06);color:#ffb400"><i class="fas fa-image"></i></div><h3>图片模块</h3><p>直接展示图片，点击查看大图，适合作品展示。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(255,80,180,0.06);color:#ff50b4"><i class="fas fa-images"></i></div><h3>弹窗图</h3><p>点击卡片弹出图片大图预览，适合展示海报、二维码。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(150,50,255,0.06);color:#9632ff"><i class="fas fa-video"></i></div><h3>视频模块</h3><p>嵌入视频，弹窗播放，支持封面图和循环播放。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(0,200,255,0.06);color:#00c8ff"><i class="fas fa-music"></i></div><h3>背景音乐</h3><p>添加自定义音乐，支持循环、自动播放，增添氛围感。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(200,180,0,0.06);color:#c8b400"><i class="fas fa-share-alt"></i></div><h3>社交链接</h3><p>集成微信、QQ、抖音、B站、GitHub等社交渠道。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(0,180,255,0.06);color:#00b4ff"><i class="fas fa-palette"></i></div><h3>个性化装扮</h3><p>自定义背景、卡片样式、主题模式，风格随心变。</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:rgba(100,200,255,0.06);color:#64c8ff"><i class="fas fa-chart-simple"></i></div><h3>数据统计</h3><p>实时统计访问量和链接点击量，了解受欢迎程度。</p></div>
    </div>
  </section>

  <section class="steps" id="howto">
    <div class="section-title"><h2>🚀 四步升空</h2><p>即刻拥有你的星际主页</p></div>
    <div class="step-grid">
      <div class="step-card"><div class="step-num">1</div><h3>注册账号</h3><p>免费注册，无需手机号或邮箱验证。</p></div>
      <div class="step-card"><div class="step-num">2</div><h3>编辑主页</h3><p>添加链接、文字、图片等模块，自定义样式。</p></div>
      <div class="step-card"><div class="step-num">3</div><h3>设置后缀</h3><p>设置专属后缀：你的域名/后缀</p></div>
      <div class="step-card"><div class="step-num">4</div><h3>分享星空</h3><p>一键分享，让更多人看到你的主页！</p></div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-box">
      <h2> 开启你的星际之旅</h2>
      <p>免费创建，即刻拥有你的专属空间</p>
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-rocket"></i> 免费创建</a>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <?=date('Y')?> <a href="<?=h($baseUrl)?>"><?=h($siteName)?></a> | 每个人都有自己的专属主页</p>
  </footer>
</div>
</body>
</html>