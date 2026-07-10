<?php
/**
 * Leaffox 系统首页模版 - Sakura
 * ============================================
 * 风格：樱花和风，粉色系，飘落花瓣，日式雅致
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
  background:linear-gradient(160deg, #2a1828 0%, #3d1f35 25%, #4a2538 50%, #3d1f35 75%, #2a1828 100%);
  color:#e8d0d8;
  font-family:"Yu Gothic","YuGothic","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif;
  min-height:100vh;overflow-x:hidden;
}
/* 樱花花瓣 */
.petal-container{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden}
.petal{position:absolute;width:12px;height:12px;background:rgba(255,180,200,0.25);border-radius:50% 0 50% 0;animation:fall linear infinite}
@keyframes fall{
  0%{transform:translateY(-20px) rotate(0deg);opacity:0}
  10%{opacity:0.8}
  90%{opacity:0.4}
  100%{transform:translateY(100vh) rotate(360deg);opacity:0}
}
/* 底部光晕 */
.glow-bottom{position:fixed;bottom:-200px;left:50%;transform:translateX(-50%);width:700px;height:400px;background:radial-gradient(ellipse at center,rgba(200,100,150,0.06) 0%,transparent 70%);pointer-events:none;z-index:0}

.wrap{position:relative;z-index:2;max-width:960px;margin:0 auto;padding:0 24px}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:20px 0;border-bottom:1px solid rgba(255,180,200,0.06)}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:700;color:#f0d8e0;text-decoration:none;letter-spacing:4px}
.nav-logo .logo-icon{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,rgba(255,180,200,0.2),rgba(200,100,150,0.2));border:1px solid rgba(255,180,200,0.15);display:flex;align-items:center;justify-content:center;font-size:16px;color:#f0b8c8}
.nav-actions{display:flex;gap:8px;align-items:center}
.nav-btn{padding:8px 24px;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none;letter-spacing:2px}
.nav-btn-outline{border:1px solid rgba(255,180,200,0.15);color:rgba(240,200,210,0.6);background:transparent}
.nav-btn-outline:hover{border-color:rgba(255,180,200,0.3);color:#f0b8c8;background:rgba(255,180,200,0.04)}
.nav-btn-solid{background:rgba(255,180,200,0.12);color:#f0b8c8;border:1px solid rgba(255,180,200,0.15)}
.nav-btn-solid:hover{background:rgba(255,180,200,0.18);transform:translateY(-1px)}

.hero{text-align:center;padding:90px 0 60px}
.hero-badge{display:inline-block;padding:6px 24px;border-radius:12px;background:rgba(255,180,200,0.06);border:1px solid rgba(255,180,200,0.1);color:rgba(240,200,210,0.5);font-size:11px;font-weight:500;margin-bottom:28px;letter-spacing:3px}
.hero h1{font-size:44px;font-weight:900;line-height:1.2;margin-bottom:16px;letter-spacing:6px}
.hero h1 .accent{color:#f0b8c8;position:relative}
.hero h1 .accent::after{content:"";position:absolute;bottom:4px;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,180,200,0.3),transparent)}
.hero p{font-size:15px;color:rgba(232,208,216,0.35);max-width:480px;margin:0 auto;line-height:2;letter-spacing:2px}
.hero-actions{display:flex;gap:12px;justify-content:center;margin-top:36px;flex-wrap:wrap}
.hero-btn{padding:12px 36px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;letter-spacing:2px}
.hero-btn-primary{background:rgba(255,180,200,0.1);color:#f0b8c8;border:1px solid rgba(255,180,200,0.12)}
.hero-btn-primary:hover{background:rgba(255,180,200,0.15);transform:translateY(-2px)}
.hero-btn-secondary{background:transparent;color:rgba(240,200,210,0.4);border:1px solid rgba(255,180,200,0.06)}
.hero-btn-secondary:hover{color:rgba(240,200,210,0.6);border-color:rgba(255,180,200,0.1)}

.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:26px;font-weight:700;margin-bottom:8px;letter-spacing:4px;color:#edd0d8}
.section-title p{color:rgba(232,208,216,0.25);font-size:13px;letter-spacing:3px}
.features{padding:70px 0;border-top:1px solid rgba(255,180,200,0.04)}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.feature-card{background:rgba(255,255,255,0.02);border:1px solid rgba(255,180,200,0.03);border-radius:10px;padding:28px 22px;transition:all 0.4s}
.feature-card:hover{background:rgba(255,180,200,0.02);border-color:rgba(255,180,200,0.06);transform:translateY(-3px)}
.feature-icon{width:44px;height:44px;border-radius:50%;border:1px solid rgba(255,180,200,0.08);display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:16px;color:#f0b8c8}
.feature-card h3{font-size:15px;font-weight:700;margin-bottom:8px;color:#edd0d8;letter-spacing:2px}
.feature-card p{font-size:12px;color:rgba(232,208,216,0.3);line-height:1.8;letter-spacing:1px}

.steps{padding:60px 0;border-top:1px solid rgba(255,180,200,0.04)}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.step-card{text-align:center;padding:28px 16px}
.step-num{width:40px;height:40px;border-radius:50%;border:1px solid rgba(255,180,200,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:14px;font-weight:700;color:#f0b8c8;font-family:"Times New Roman",serif}
.step-card h3{font-size:14px;font-weight:700;margin-bottom:6px;color:#edd0d8;letter-spacing:2px}
.step-card p{font-size:12px;color:rgba(232,208,216,0.3);line-height:1.7;letter-spacing:1px}

.cta{padding:60px 0 80px;text-align:center}
.cta-box{background:rgba(255,180,200,0.02);border:1px solid rgba(255,180,200,0.04);border-radius:12px;padding:48px 32px;max-width:520px;margin:0 auto}
.cta-box h2{font-size:22px;font-weight:700;margin-bottom:10px;letter-spacing:4px;color:#edd0d8}
.cta-box p{color:rgba(232,208,216,0.3);font-size:13px;margin-bottom:28px;letter-spacing:2px}

.footer{border-top:1px solid rgba(255,180,200,0.03);padding:24px 0;text-align:center;font-size:11px;color:rgba(232,208,216,0.12);letter-spacing:2px}
.footer a{color:rgba(232,208,216,0.15);text-decoration:none}

@media(max-width:768px){
  .hero h1{font-size:30px;letter-spacing:4px}.hero p{font-size:13px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}.step-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
  .hero{padding:60px 0 40px}.hero h1{font-size:24px}
  .feature-grid,.step-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="petal-container" id="petals"></div>
<script>
(function(){
  const c=document.getElementById('petals');
  for(let i=0;i<25;i++){
    const p=document.createElement('div');p.className='petal';
    p.style.left=Math.random()*100+'%';
    p.style.animationDuration=(8+Math.random()*12)+'s';
    p.style.animationDelay=Math.random()*15+'s';
    p.style.width=p.style.height=(6+Math.random()*10)+'px';
    p.style.opacity=0.1+Math.random()*0.2;
    c.appendChild(p);
  }
})();
</script>
<div class="glow-bottom"></div>
<div class="wrap">
  <nav class="navbar">
    <a href="/" class="nav-logo"><span class="logo-icon"></span><span><?=h($siteName)?></span></a>
    <div class="nav-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="nav-btn nav-btn-solid"><i class="fas fa-user"></i> 庭</a>
      <?php else: ?>
        <a href="./user/index.php" class="nav-btn nav-btn-outline">訪れる</a>
        <a href="./user/index.php" class="nav-btn nav-btn-solid">創る</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-badge">✦ <?=h($siteName)?></div>
    <h1>作る <span class="accent">あなたの</span><br>特別な場所</h1>
    <p><?=h($siteDesc)?> — 如樱花般绽放，属于你的独特空间。</p>
    <div class="hero-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-pen"></i> 免费创建</a>
        <a href="./user/index.php" class="hero-btn hero-btn-secondary"><i class="fas fa-sign-in-alt"></i> 登录</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="features" id="features">
    <div class="section-title"><h2> 雅趣</h2><p>精致而不繁复</p></div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-link"></i></div><h3>链接</h3><p>自定义图标与色彩，密码守护隐私。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-font"></i></div><h3>文字</h3><p>淡雅排版，点击弹出大图预览。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-image"></i></div><h3>图片</h3><p>展示你的摄影与画作。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-images"></i></div><h3>弹窗图</h3><p>优雅展示高分辨率作品。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-video"></i></div><h3>视频</h3><p>嵌入视频，自定义封面。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-music"></i></div><h3>音乐</h3><p>背景旋律，余音绕梁。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-share-alt"></i></div><h3>社交</h3><p>微信、QQ、微博等一键直达。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-palette"></i></div><h3>装扮</h3><p>和风背景，雅致卡片，随心搭配。</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-simple"></i></div><h3>统计</h3><p>访客与点击，尽在掌握。</p></div>
    </div>
  </section>

  <section class="steps" id="howto">
    <div class="section-title"><h2> 四步成章</h2><p>简单优雅</p></div>
    <div class="step-grid">
      <div class="step-card"><div class="step-num">一</div><h3>注册</h3><p>免费加入，无需验证。</p></div>
      <div class="step-card"><div class="step-num">二</div><h3>编辑</h3><p>添加模块，布置你的空间。</p></div>
      <div class="step-card"><div class="step-num">三</div><h3>发布</h3><p>设置后缀，独一无二。</p></div>
      <div class="step-card"><div class="step-num">四</div><h3>分享</h3><p>让美好被看见。</p></div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-box">
      <h2> 此刻绽放</h2>
      <p>免费创造你的专属空间</p>
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入庭园</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-pen"></i> 免费创建</a>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <?=date('Y')?> <a href="<?=h($baseUrl)?>"><?=h($siteName)?></a> | 一期一会</p>
  </footer>
</div>
</body>
</html>