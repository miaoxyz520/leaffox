<?php
/**
 * 用户后台 - 主框架（移动端优化版）
 */
require_once __DIR__ . '/../config.php';
requireUser();

$uid = (int)$_SESSION['user_id'];
$subPage = $_GET['page'] ?? 'dashboard';

// 获取当前用户信息
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) { session_destroy(); redirect('./index.php'); }

// 统计数据
$myLinks   = (int)$db->query("SELECT COUNT(*) FROM links WHERE user_id=$uid")->fetchColumn();
$myViews   = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$uid AND type='view'")->fetchColumn();
$myClicks  = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$uid AND type='click'")->fetchColumn();
$todayViews = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$uid AND type='view' AND DATE(created_at)='" . date('Y-m-d') . "'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<?php $sn = getSiteName($db ?? null); ?><title>我的后台 - <?=h($sn)?></title>
<script src="https://cdn.tailwindcss.com">
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js">
</script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
  background:#0f172a;
  color:#e2e8f0;
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"PingFang SC","Microsoft YaHei",sans-serif;
  display:flex;
  min-height:100vh;
  min-height:100dvh;
  -webkit-tap-highlight-color:transparent;
}

/* ========== 侧边栏 ========== */
.sidebar{
  width:220px;
  background:rgba(30,27,75,0.5);
  backdrop-filter:blur(12px);
  -webkit-backdrop-filter:blur(12px);
  border-right:1px solid rgba(255,255,255,0.06);
  display:flex;
  flex-direction:column;
  position:fixed;
  top:0;left:0;bottom:0;
  z-index:50;
  transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);
}
.sidebar-logo{padding:20px;border-bottom:1px solid rgba(255,255,255,0.06);min-width:0}
.sidebar-logo span{color:#fff;font-size:14px;font-weight:700;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

.nav-item{
  display:flex;
  align-items:center;
  gap:10px;
  padding:12px 14px;
  margin:2px 10px;
  border-radius:10px;
  color:rgba(255,255,255,0.5);
  cursor:pointer;
  transition:all 0.2s;
  text-decoration:none;
  font-size:13px;
  overflow:hidden;
  min-height:44px; /* 移动端触摸友好 */
}
.nav-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-item:hover{color:#fff;background:rgba(255,255,255,0.07)}
.nav-item.active{color:#fff;background:rgba(99,102,241,0.2);font-weight:600}
.nav-item i{width:18px;text-align:center;font-size:14px;flex-shrink:0}
.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* ========== 主内容区 ========== */
.main{
  margin-left:220px;
  flex:1;
  min-height:100vh;
  min-height:100dvh;
  padding:24px 28px;
  max-width:960px;
  width:100%;
}

/* ========== 统计卡片网格 ========== */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
  gap:14px;
  margin-bottom:24px;
}
.stat-card{
  background:rgba(255,255,255,0.04);
  border:1px solid rgba(255,255,255,0.08);
  border-radius:14px;
  padding:18px;
}
.stat-card .stat-value{font-size:24px;font-weight:800;color:#fff}
.stat-card .stat-label{font-size:12px;color:rgba(255,255,255,0.45);margin-top:2px}

/* ========== 通用卡片 ========== */
.card-base{
  background:rgba(255,255,255,0.04);
  border:1px solid rgba(255,255,255,0.08);
  border-radius:16px;
  padding:22px;
  margin-bottom:20px;
}
.card-base h3{
  font-size:14px;
  font-weight:700;
  color:#fff;
  margin-bottom:14px;
  display:flex;
  align-items:center;
  gap:8px;
}

/* ========== 按钮 ========== */
.btn-sm{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:10px 18px;  /* 增大触摸区域 */
  border-radius:10px;
  font-size:13px;
  font-weight:500;
  border:none;
  cursor:pointer;
  transition:all 0.2s;
  text-decoration:none;
  min-height:40px; /* 触摸友好 */
}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.btn-primary:hover{box-shadow:0 4px 15px rgba(99,102,241,0.3)}
.btn-ghost{background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.7)}
.btn-ghost:hover{background:rgba(255,255,255,0.1);color:#fff}

/* ========== 表单控件 ========== */
input,select,textarea{
  background:rgba(255,255,255,0.06);
  border:1px solid rgba(255,255,255,0.12);
  color:#fff;
  border-radius:10px;
  padding:12px 14px;  /* 移动端更大内边距 */
  width:100%;
  outline:none;
  transition:all 0.2s;
  font-size:14px;     /* 移动端14px防缩放 */
}
input:focus,select:focus,textarea:focus{border-color:#818cf8;box-shadow:0 0 0 3px rgba(129,140,248,0.15)}
::placeholder{color:rgba(255,255,255,0.3)}

/* ========== 侧边栏遮罩 ========== */
.sidebar-overlay{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.5);
  z-index:49;
  opacity:0;
  pointer-events:none;
  transition:opacity 0.3s;
}
.sidebar-overlay.show{opacity:1;pointer-events:auto}

/* ========== 全选复制 ========== */
.select-all{-webkit-user-select:all;user-select:all}

/* ========== 移动端适配 ========== */
@media(max-width:768px){
  .sidebar{transform:translateX(-100%)}
  .sidebar.open{transform:translateX(0)}
  .main{
    margin-left:0;
    padding:16px 14px;  /* 更合适的移动端内边距 */
  }
  .stats-grid{
    grid-template-columns:repeat(2,1fr);
    gap:10px;
  }
  .stat-card{padding:14px}
  .stat-card .stat-value{font-size:20px}
  .stat-card .stat-label{font-size:11px}
  .card-base{padding:16px;border-radius:14px;margin-bottom:16px}
  .card-base h3{font-size:13px;margin-bottom:10px}
  .btn-sm{
    padding:10px 14px;
    font-size:12px;
    min-height:38px;
  }
}

/* 超小屏幕 (≤400px) */
@media(max-width:400px){
  .main{padding:12px 10px}
  .stats-grid{grid-template-columns:1fr 1fr;gap:8px}
  .stat-card{padding:12px}
  .stat-card .stat-value{font-size:18px}
  .card-base{padding:14px;border-radius:12px}
}

/* Toast 提示动画 */
.toast-anim{
  animation:slideDown 0.3s ease;
}
@keyframes slideDown{
  from{opacity:0;transform:translate(-50%,-20px) scale(0.9)}
  to{opacity:1;transform:translate(-50%,0) scale(1)}
}
</style>
</head>
<body>

<!-- 侧边栏遮罩 -->
<div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- 移动端汉堡按钮（固定在左上角） -->
<button onclick="toggleSidebar()" class="fixed top-3 left-3 z-[60] w-10 h-10 bg-white/10 backdrop-blur rounded-xl flex items-center justify-center text-white md:hidden hover:bg-white/20 transition" aria-label="菜单">
  <i class="fas fa-bars text-base"></i>
</button>

<!-- 侧边栏 -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo flex items-center gap-3">
    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">L</div>
    <div class="min-w-0 flex-1"><span class="truncate">我的后台</span><div class="text-xs text-gray-500 truncate"><?=h(getSiteName($db))?></div></div>
  </div>
  <nav class="flex-1 py-3">
    <a href="?page=dashboard" class="nav-item <?=$subPage=='dashboard'?'active':''?>" onclick="closeSidebar()"><i class="fas fa-home"></i>控制台</a>
    <a href="?page=profile" class="nav-item <?=$subPage=='profile'?'active':''?>" onclick="closeSidebar()"><i class="fas fa-user"></i>个人设置</a>
    <a href="?page=links" class="nav-item <?=$subPage=='links'?'active':''?>" onclick="closeSidebar()"><i class="fas fa-link"></i>链接管理</a>
    <a href="?page=stats" class="nav-item <?=$subPage=='stats'?'active':''?>" onclick="closeSidebar()"><i class="fas fa-chart-bar"></i>数据统计</a>
    <a href="?page=settings" class="nav-item <?=$subPage=='settings'?'active':''?>" onclick="closeSidebar()"><i class="fas fa-cog"></i>主页设置</a>
  </nav>
  <div class="p-4 border-t border-white/10">
    <div class="flex items-center gap-3 mb-3 px-2">
      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex-shrink-0 flex items-center justify-center text-white text-xs font-bold"><?=h(mb_substr($user['nickname']?:$user['username'],0,1))?></div>
      <div class="min-w-0 flex-1"><div class="text-sm text-white truncate"><?=h($user['nickname']?:$user['username'])?></div><div class="text-xs text-gray-500">ID: <?=$uid?></div></div>
    </div>
    <a href="./logout.php" class="nav-item text-red-400 hover:text-red-300" onclick="closeSidebar()"><i class="fas fa-sign-out-alt"></i>退出登录</a>
    <div class="mt-3 px-2 pt-3 border-t border-white/5 text-[11px] text-gray-600">
      v1.0 · Developed by shadow
    </div>
  </div>
</div>

<!-- 主内容区 -->
<div class="main">

<?php if (!empty($_SESSION['impersonated_by_admin'])): ?>
<div class="bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3 mb-4 md:px-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
  <div class="flex items-center gap-3 text-amber-200 text-sm">
    <i class="fas fa-user-shield text-amber-400 flex-shrink-0"></i>
    <span>管理员 <strong><?=h($_SESSION['impersonate_admin_name'] ?? '')?></strong> 正在以 <strong><?=h($_SESSION['user_name'])?></strong> 的身份查看后台</span>
  </div>
  <a href="../admin/dashboard.php" class="self-start md:self-auto text-xs bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 px-4 py-2 rounded-lg transition flex items-center gap-2 whitespace-nowrap">
    <i class="fas fa-arrow-left"></i> 返回管理后台
  </a>
</div>
<?php endif; ?>

<?php if ($subPage === 'dashboard'): ?>
  <!-- 页面标题+预览按钮（移动端纵向排列） -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
    <div>
      <h1 class="text-xl md:text-2xl font-bold">👋 欢迎回来</h1>
      <p class="text-gray-500 text-sm mt-0.5"><?=h($user['nickname']?:$user['username'])?>，这是你的主页数据概览</p>
    </div>
    <a href="<?=BASE_URL?>/page/index.php?id=<?=$uid?>" target="_blank" class="btn-sm btn-primary self-start">
      <i class="fas fa-external-link-alt"></i> 预览主页
    </a>
  </div>

  <!-- 🏠 主页链接卡片 -->
  <?php
    $homeSuffix = $user['suffix'] ?? '';
    $homeUrl = $homeSuffix ? (BASE_URL . '/' . $homeSuffix) : (BASE_URL . '/page/index.php?id=' . $uid);
  ?>
  <div class="card-base !border-indigo-500/30 mb-5 md:mb-6 overflow-hidden">
    <div class="flex items-center gap-3 mb-3 md:mb-4">
      <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex-shrink-0 flex items-center justify-center text-white text-base md:text-lg shadow-lg shadow-indigo-500/20">🔗</div>
      <div>
        <h3 class="text-white font-bold text-sm md:text-base">你的专属主页链接</h3>
        <p class="text-gray-500 text-xs mt-0.5">复制链接分享到朋友圈、抖音、微博</p>
      </div>
    </div>

    <!-- 链接行（移动端：后缀+URL竖排，复制按钮独立一行） -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 bg-white/5 border border-white/10 rounded-xl p-3 md:px-4 md:py-3 hover:border-indigo-500/40 transition">
      <div class="flex items-center gap-2 flex-1 min-w-0">
        <?php if ($homeSuffix): ?>
          <span class="flex-shrink-0 bg-indigo-500/20 text-indigo-300 text-xs font-bold px-2.5 py-1 rounded-lg whitespace-nowrap">📎 <?=h($homeSuffix)?></span>
        <?php endif; ?>
        <span class="flex-1 text-white font-mono text-xs md:text-sm truncate select-all leading-relaxed" id="homepage-url"><?=h($homeUrl)?></span>
      </div>
      <button onclick="copyHomeUrl()" class="flex-shrink-0 btn-sm btn-primary text-xs !py-2.5 sm:!py-2 !px-4 justify-center" id="copy-btn">
        <i class="fas fa-copy"></i> <span>复制链接</span>
      </button>
    </div>

    <!-- 复制成功 Toast -->
    <div id="copy-toast" class="hidden fixed top-5 left-1/2 -translate-x-1/2 z-[999] bg-emerald-500/90 text-white px-5 py-3 rounded-xl text-sm font-medium shadow-xl backdrop-blur toast-anim">✅ 已复制到剪贴板！</div>

    <!-- QR码区域（带头像） -->
    <div class="mt-3 md:mt-4 pt-3 md:pt-4 border-t border-white/5">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i class="fas fa-qrcode text-indigo-400 text-sm"></i>
          <span class="text-white text-sm font-medium">访问二维码</span>
          <span class="text-xs text-gray-500 hidden sm:inline">扫一扫直达主页</span>
        </div>
        <button onclick="toggleQrcode()" class="text-xs text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 py-1.5 px-2 rounded-lg hover:bg-white/5" id="qrcode-toggle-btn">
          <i class="fas fa-chevron-down"></i> <span>展开</span>
        </button>
      </div>
      <div id="qrcode-section" class="hidden mt-3 flex justify-center">
        <div class="relative inline-block">
          <div id="qrcode-canvas" class="rounded-2xl overflow-hidden bg-white p-3" style="width:170px;height:170px;"></div>
          <?php if (!empty($user['avatar'])): ?>
          <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-9 h-9 rounded-lg overflow-hidden border-2 border-white shadow-lg bg-white">
              <img src="<?=BASE_URL.'/'.$user['avatar']?>" class="w-full h-full object-cover" alt="">
            </div>
          </div>
          <?php else: ?>
          <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-9 h-9 rounded-lg overflow-hidden border-2 border-white shadow-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
              <?=h(mb_substr($user['nickname']?:$user['username'],0,1))?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- 统计卡片 -->
  <div class="stats-grid">
    <div class="stat-card border-l-[3px] border-indigo-500">
      <div class="stat-value"><?=$myViews?></div>
      <div class="stat-label">总访问量 · 今日 <strong class="text-white/70"><?=$todayViews?></strong></div>
    </div>
    <div class="stat-card border-l-[3px] border-emerald-500">
      <div class="stat-value"><?=$myClicks?></div>
      <div class="stat-label">总点击量</div>
    </div>
    <div class="stat-card border-l-[3px] border-amber-500">
      <div class="stat-value"><?=$myLinks?></div>
      <div class="stat-label">链接/模块总数</div>
    </div>
    <div class="stat-card border-l-[3px] border-rose-500">
      <div class="stat-value"><?=$myClicks > 0 ? round($myClicks/max($myViews,1)*100,1) : 0?>%</div>
      <div class="stat-label">点击率</div>
    </div>
  </div>

  <!-- 快捷操作 -->
  <div class="card-base">
    <h3><i class="fas fa-link text-indigo-400"></i> 快捷操作</h3>
    <div class="grid grid-cols-2 md:flex md:flex-wrap gap-2 md:gap-3">
      <a href="?page=links" class="btn-sm btn-primary justify-center md:justify-start"><i class="fas fa-plus"></i> 管理链接</a>
      <a href="?page=profile" class="btn-sm btn-ghost justify-center md:justify-start"><i class="fas fa-user-edit"></i> 编辑资料</a>
      <a href="?page=settings" class="btn-sm btn-ghost justify-center md:justify-start"><i class="fas fa-palette"></i> 主页风格</a>
      <a href="<?=BASE_URL?>/page/index.php?id=<?=$uid?>" target="_blank" class="btn-sm btn-ghost justify-center md:justify-start"><i class="fas fa-eye"></i> 查看主页</a>
    </div>
  </div>

  <!-- 我的信息 -->
  <div class="card-base">
    <h3><i class="fas fa-info-circle text-indigo-400"></i> 我的信息</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
      <div class="flex items-center gap-2">
        <span class="text-gray-500 flex-shrink-0">用户名：</span>
        <span class="text-white truncate"><?=h($user['username'])?></span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-gray-500 flex-shrink-0">昵称：</span>
        <span class="text-white truncate"><?=h($user['nickname']?:'未设置')?></span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-gray-500 flex-shrink-0">注册时间：</span>
        <span class="text-white truncate"><?=$user['created_at']?></span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-gray-500 flex-shrink-0">账号状态：</span>
        <span class="text-emerald-400">✅ 正常</span>
      </div>
    </div>
  </div>

<?php elseif ($subPage === 'profile'): ?>
  <?php include __DIR__ . '/profile.php'; ?>
<?php elseif ($subPage === 'links'): ?>
  <?php include __DIR__ . '/links.php'; ?>
<?php elseif ($subPage === 'stats'): ?>
  <?php include __DIR__ . '/stats.php'; ?>
<?php elseif ($subPage === 'settings'): ?>
  <?php include __DIR__ . '/settings.php'; ?>
<?php endif; ?>

</div>

<script>
// ====== 侧边栏控制 ======
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebar(){
  document.getElementById('sidebar')?.classList.remove('open');
  document.getElementById('sidebarOverlay')?.classList.remove('show');
}

// Escape 键关闭侧边栏
document.addEventListener('keydown', function(e){
  if(e.key === 'Escape') closeSidebar();
});

// 点击侧边栏外部时关闭（移动端）
document.addEventListener('click', function(e){
  var sidebar = document.getElementById('sidebar');
  var hamburger = document.querySelector('button[onclick*="toggleSidebar"]');
  if(window.innerWidth < 768 && sidebar && sidebar.classList.contains('open')){
    if(!sidebar.contains(e.target) && !hamburger?.contains(e.target)){
      closeSidebar();
    }
  }
});

// ====== 一键复制 ======
function copyHomeUrl() {
  const url = document.getElementById('homepage-url').textContent.trim();
  const btn = document.getElementById('copy-btn');
  const toast = document.getElementById('copy-toast');
  
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(() => {
      showCopyToast(btn, toast);
    }).catch(() => fallbackCopy(url, btn, toast));
  } else {
    fallbackCopy(url, btn, toast);
  }
}

function fallbackCopy(text, btn, toast) {
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'fixed';
  ta.style.opacity = '0';
  ta.style.left = '-999px';
  document.body.appendChild(ta);
  ta.select();
  try {
    document.execCommand('copy');
    showCopyToast(btn, toast);
  } catch(e) {
    // 静默失败
  }
  document.body.removeChild(ta);
}

function showCopyToast(btn, toast) {
  btn.innerHTML = '<i class="fas fa-check"></i> 已复制';
  btn.style.background = '#10b981';
  toast.classList.remove('hidden');
  setTimeout(() => {
    btn.innerHTML = '<i class="fas fa-copy"></i> <span>复制链接</span>';
    btn.style.background = '';
    toast.classList.add('hidden');
  }, 2000);
}

// ====== QR码展开/收起 ======
function toggleQrcode() {
  const section = document.getElementById('qrcode-section');
  const btn = document.getElementById('qrcode-toggle-btn');
  const isHidden = section.classList.contains('hidden');
  if (isHidden) {
    section.classList.remove('hidden');
    section.style.display = 'flex';
    btn.innerHTML = '<i class="fas fa-chevron-up"></i> <span>收起</span>';
    if (!section.dataset.generated) {
      setTimeout(generateQrcode, 100);
      section.dataset.generated = '1';
    }
  } else {
    section.classList.add('hidden');
    btn.innerHTML = '<i class="fas fa-chevron-down"></i> <span>展开</span>';
  }
}

// ====== 生成二维码 ======
function generateQrcode() {
  const url = document.getElementById('homepage-url').textContent.trim();
  const container = document.getElementById('qrcode-canvas');
  container.innerHTML = '';
  try {
    new QRCode(container, {
      text: url,
      width: 145,
      height: 145,
      colorDark: '#1e293b',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.H
    });
  } catch(e) {
    container.innerHTML = '<div class="text-gray-400 text-xs text-center p-4">二维码生成失败</div>';
  }
}
</script>
</body>
</html>
