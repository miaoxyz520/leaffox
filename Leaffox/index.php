<?php
/**
 * Leaffox主页系统 - 前端口
 *
 * 访问方式:
 *   /               → 展示系统介绍（功能展示 + 注册/登录入口）
 *   /用户后缀       → 显示该用户的公开主页（URL 保持不变）
 *   /page/ /user/ /admin/ /api/ → 内部路由
 */
require_once __DIR__ . '/config.php';

// 数据库未连接时跳转安装向导
if (!$db) {
    header("Location: install.php");
    exit;
}

// ---- 路由解析 ----
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$basePath   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
$path       = '';

if (strpos($requestUri, '?') !== false) {
    $requestUri = substr($requestUri, 0, strpos($requestUri, '?'));
}

if ($basePath && $basePath !== '/') {
    $prefix = $basePath . '/';
    if (strncmp($requestUri, $prefix, strlen($prefix)) === 0) {
        $path = substr($requestUri, strlen($prefix));
    } elseif (strncmp($requestUri, $basePath, strlen($basePath)) === 0) {
        $path = substr($requestUri, strlen($basePath) + 1);
    }
} else {
    $path = ltrim($requestUri, '/');
}

$path = trim($path, '/');

// 内部保留路径——404
$skip_prefixes = ['page/', 'user/', 'admin/', 'api/'];
foreach ($skip_prefixes as $pfx) {
    if (strncmp($path, $pfx, strlen($pfx)) === 0) {
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>404 Not Found</title>';
        echo '<style>body{background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;flex-direction:column}</style>';
        echo '</head><body><h1 style="font-size:48px;margin:0">404</h1><p style="color:#64748b">页面不存在</p></body></html>';
        exit;
    }
}

// 如果有后缀 → 展示用户主页
if (!empty($path)) {
    $stmt = $db->prepare("SELECT id FROM users WHERE suffix = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$path]);
    $user = $stmt->fetch();

    if ($user) {
        $settings = getSettings($db);
        $bannedRaw = trim($settings['banned_suffixes'] ?? '');
        $bannedList = $bannedRaw ? array_map('trim', explode("\n", $bannedRaw)) : [];
        if (in_array($path, $bannedList)) {
            http_response_code(404);
            die('<h1 style="color:#e2e8f0;background:#0f172a;display:flex;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;margin:0">此页面已被管理员禁用</h1>');
        }
        $_GET['id'] = (int)$user['id'];
        require __DIR__ . '/page/index.php';
        exit;
    }

    // 后缀匹配不到 → 404
    http_response_code(404);
    ?>
    <!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><?php $sn = getSiteName($db); ?><title>404 - <?=h($sn)?></title>
    <style>*{margin:0;padding:0;box-sizing:border-box}body{background:#0f172a;color:#e2e8f0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;flex-direction:column;gap:16px;text-align:center;padding:20px}
    h1{font-size:72px;font-weight:900;background:linear-gradient(135deg,#6366f1,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    p{color:#64748b;font-size:16px}
    a{color:#818cf8;text-decoration:none;border:1px solid rgba(129,140,248,0.3);padding:10px 24px;border-radius:12px;font-size:14px;transition:all 0.2s;margin-top:8px;display:inline-block}
    a:hover{background:rgba(129,140,248,0.1);border-color:#818cf8}</style></head>
    <body><h1>404</h1><p>你访问的页面不存在</p><a href="<?=h(BASE_URL)?>">返回首页</a></body></html>
    <?php
    exit;
}

// ============================================================
// 空路径 → 展示着陆页（系统介绍 + 功能展示 + 注册/登录入口）
// ============================================================
$settings = getSettings($db);
$siteName = getSiteName($db);
$siteDesc = $settings['site_desc'] ?? '每个人都有自己的专属主页';
$isLogin  = !empty($_SESSION['user_id']) && !empty($_SESSION['user_login']);
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
  background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 40%,#0f172a 100%);
  color:#e2e8f0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"PingFang SC","Microsoft YaHei",sans-serif;
  min-height:100vh;overflow-x:hidden;
}
/* 背景装饰 */
.bg-glow{
  position:fixed;top:-200px;left:50%;transform:translateX(-50%);
  width:800px;height:800px;
  background:radial-gradient(ellipse at center, rgba(99,102,241,0.12) 0%, transparent 70%);
  pointer-events:none;z-index:0;
}
.bg-glow-2{
  position:fixed;bottom:-300px;right:-200px;
  width:600px;height:600px;
  background:radial-gradient(ellipse at center, rgba(168,85,247,0.08) 0%, transparent 70%);
  pointer-events:none;z-index:0;
}
.wrap{position:relative;z-index:1;max-width:1000px;margin:0 auto;padding:0 24px}

/* 导航栏 */
.navbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:18px 0;border-bottom:1px solid rgba(255,255,255,0.06);
}
.nav-logo{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:800;color:#fff;text-decoration:none}
.nav-logo .logo-icon{
  width:36px;height:36px;border-radius:10px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);
  display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#fff;
}
.nav-actions{display:flex;gap:10px;align-items:center}
.nav-btn{
  padding:8px 20px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;
  transition:all 0.3s;text-decoration:none;
}
.nav-btn-outline{
  border:1px solid rgba(255,255,255,0.2);color:rgba(255,255,255,0.8);background:transparent;
}
.nav-btn-outline:hover{border-color:#818cf8;color:#818cf8;background:rgba(129,140,248,0.08)}
.nav-btn-solid{
  background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;
}
.nav-btn-solid:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(99,102,241,0.3)}

/* Hero */
.hero{text-align:center;padding:80px 0 60px;animation:fadeUp 0.8s ease}
.hero-badge{
  display:inline-block;padding:6px 18px;border-radius:20px;
  background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.25);
  color:#a5b4fc;font-size:13px;font-weight:500;margin-bottom:24px;
}
.hero h1{font-size:48px;font-weight:900;line-height:1.2;margin-bottom:16px}
.hero h1 .gradient{background:linear-gradient(135deg,#6366f1,#c084fc,#f472b6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:18px;color:rgba(255,255,255,0.55);max-width:560px;margin:0 auto;line-height:1.7}
.hero-actions{display:flex;gap:14px;justify-content:center;margin-top:36px;flex-wrap:wrap}
.hero-btn{
  padding:14px 36px;border-radius:14px;font-size:16px;font-weight:700;cursor:pointer;
  transition:all 0.3s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;
}
.hero-btn-primary{
  background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;
  box-shadow:0 8px 30px rgba(99,102,241,0.3);
}
.hero-btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(99,102,241,0.4)}
.hero-btn-secondary{
  background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.8);
  border:1px solid rgba(255,255,255,0.12);
}
.hero-btn-secondary:hover{background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.2)}

/* 功能区块 */
.section-title{text-align:center;margin-bottom:48px}
.section-title h2{font-size:30px;font-weight:800;margin-bottom:10px}
.section-title p{color:rgba(255,255,255,0.45);font-size:15px}
.features{padding:60px 0;border-top:1px solid rgba(255,255,255,0.06)}
.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feature-card{
  background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);
  border-radius:20px;padding:28px 24px;transition:all 0.3s;
}
.feature-card:hover{background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.12);transform:translateY(-4px)}
.feature-icon{
  width:48px;height:48px;border-radius:14px;
  display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px;
}
.feature-card h3{font-size:17px;font-weight:700;margin-bottom:8px;color:#fff}
.feature-card p{font-size:13px;color:rgba(255,255,255,0.5);line-height:1.7}

/* 使用步骤 */
.steps{padding:60px 0;border-top:1px solid rgba(255,255,255,0.06)}
.step-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.step-card{text-align:center;padding:24px 16px}
.step-num{
  width:44px;height:44px;border-radius:50%;
  background:linear-gradient(135deg,#6366f1,#a78bfa);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 16px;font-size:18px;font-weight:800;color:#fff;
}
.step-card h3{font-size:15px;font-weight:700;margin-bottom:6px;color:#fff}
.step-card p{font-size:13px;color:rgba(255,255,255,0.5);line-height:1.6}

/* CTA */
.cta{padding:60px 0 80px;text-align:center}
.cta-box{
  background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(168,85,247,0.08));
  border:1px solid rgba(99,102,241,0.2);border-radius:28px;
  padding:48px 32px;max-width:600px;margin:0 auto;
}
.cta-box h2{font-size:26px;font-weight:800;margin-bottom:10px}
.cta-box p{color:rgba(255,255,255,0.5);font-size:14px;margin-bottom:28px}

/* 页脚 */
.footer{
  border-top:1px solid rgba(255,255,255,0.06);padding:24px 0;
  text-align:center;font-size:12px;color:rgba(255,255,255,0.25);
}

/* 动画 */
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

/* 响应式 */
@media(max-width:768px){
  .hero h1{font-size:32px}
  .hero p{font-size:15px}
  .feature-grid{grid-template-columns:repeat(2,1fr)}
  .step-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
  .hero{padding:50px 0 40px}
  .hero h1{font-size:26px}
  .feature-grid{grid-template-columns:1fr}
  .step-grid{grid-template-columns:1fr}
  .navbar{flex-wrap:wrap;gap:12px}
  .nav-actions{width:100%;justify-content:flex-end}
}
</style>
</head>
<body>
<div class="bg-glow"></div>
<div class="bg-glow-2"></div>
<div class="wrap">

  <!-- 导航栏 -->
  <nav class="navbar">
    <a href="/" class="nav-logo">
      <span class="logo-icon">L</span>
      <span><?=h($siteName)?></span>
    </a>
    <div class="nav-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="nav-btn nav-btn-solid"><i class="fas fa-user"></i> 控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="nav-btn nav-btn-outline">登录</a>
        <a href="./user/index.php" class="nav-btn nav-btn-solid">免费注册</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-badge">🚀 <?=h($siteName)?> v1.0</div>
    <h1>打造你的 <span class="gradient">专属个人主页</span></h1>
    <p><?=h($siteDesc)?> — 无需编程，简单几步即可拥有一个集链接、文字、图片、视频于一体的个人主页。</p>
    <div class="hero-actions">
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-rocket"></i> 免费创建主页</a>
        <a href="./user/index.php" class="hero-btn hero-btn-secondary"><i class="fas fa-sign-in-alt"></i> 登录</a>
      <?php endif; ?>
    </div>
  </section>

  <!-- 功能介绍 -->
  <section class="features" id="features">
    <div class="section-title">
      <h2>✨ 功能介绍</h2>
      <p>丰富多样的模块，打造独一无二的个人主页</p>
    </div>
    <div class="feature-grid">
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(99,102,241,0.15);color:#818cf8"><i class="fas fa-link"></i></div>
        <h3>🔗 链接模块</h3>
        <p>添加网页链接，支持自定义图标、卡片颜色、边框样式，还可设置访问密码保护隐私。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(34,197,94,0.15);color:#4ade80"><i class="fas fa-font"></i></div>
        <h3>📝 文字模块</h3>
        <p>展示纯文字内容，支持居中显示，点击弹出大图预览，适合展示公告、说明等信息。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(251,146,60,0.15);color:#fb923c"><i class="fas fa-image"></i></div>
        <h3>🖼️ 图片模块</h3>
        <p>在主页直接展示图片，点击可查看大图，适合作品展示、摄影集等场景。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(236,72,153,0.15);color:#f472b6"><i class="fas fa-images"></i></div>
        <h3>🖼️ 弹窗图模块</h3>
        <p>点击卡片弹出图片大图预览，适合展示海报、二维码、证书等需要放大的内容。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(168,85,247,0.15);color:#c084fc"><i class="fas fa-video"></i></div>
        <h3>🎬 视频模块</h3>
        <p>嵌入视频链接，弹窗播放，支持设置封面图和循环播放，适配移动端体验。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(14,165,233,0.15);color:#38bdf8"><i class="fas fa-palette"></i></div>
        <h3>🎨 个性化装扮</h3>
        <p>自定义背景（颜色/渐变/图片），切换卡片样式（Glass/Neumorphism/Minimal）、主题模式等。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(234,179,8,0.15);color:#eab308"><i class="fas fa-music"></i></div>
        <h3>🎵 背景音乐</h3>
        <p>添加自定义音乐链接，支持循环播放、自动播放、图标切换，让主页更有氛围感。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(239,68,68,0.15);color:#f87171"><i class="fas fa-share-alt"></i></div>
        <h3>🔗 社交链接</h3>
        <p>集成微信、QQ、Telegram、抖音、B站、小红书、微博、GitHub、邮箱等社交渠道。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(99,102,241,0.15);color:#818cf8"><i class="fas fa-chart-simple"></i></div>
        <h3>📊 数据统计</h3>
        <p>实时统计主页访问量和链接点击量，了解你的主页受欢迎程度。</p>
      </div>
    </div>
  </section>

  <!-- 使用步骤 -->
  <section class="steps" id="howto">
    <div class="section-title">
      <h2>🚀 三步创建你的主页</h2>
      <p>简单快速，即刻拥有</p>
    </div>
    <div class="step-grid">
      <div class="step-card">
        <div class="step-num">1</div>
        <h3>注册账号</h3>
        <p>填写用户名和密码，免费注册，无需手机号或邮箱验证。</p>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <h3>编辑主页</h3>
        <p>添加链接、文字、图片、视频等模块，自定义背景和样式。</p>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <h3>设置后缀</h3>
        <p>设置专属后缀，分享给朋友：你的域名/你的后缀</p>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <h3>分享出去</h3>
        <p>一键分享到社交平台，让更多人看到你的专属主页！</p>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta">
    <div class="cta-box">
      <h2>🌟 开始打造你的主页</h2>
      <p>免费创建，无需任何费用，即刻拥有你的专属空间</p>
      <?php if ($isLogin): ?>
        <a href="./user/dashboard.php" class="hero-btn hero-btn-primary"><i class="fas fa-user"></i> 进入控制台</a>
      <?php else: ?>
        <a href="./user/index.php" class="hero-btn hero-btn-primary"><i class="fas fa-rocket"></i> 免费创建</a>
      <?php endif; ?>
    </div>
  </section>

  <!-- 页脚 -->
  <footer class="footer">
    <p>&copy; <?=date('Y')?> <a href="<?=h(BASE_URL)?>" style="color:rgba(255,255,255,0.35);text-decoration:none"><?=h($siteName)?></a> | 每个人都有自己的专属主页</p>
  </footer>

</div>
</body>
</html>
