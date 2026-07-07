<?php
/**
 * 管理员后台 - 主框架 + 数据看板
 */
require_once __DIR__ . '/../config.php';
requireAdmin();

$pageTitle = '控制台';
$subPage = $_GET['page'] ?? 'dashboard';

// 数据看板统计
$totalUsers  = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$totalLinks  = (int)$db->query("SELECT COUNT(*) FROM links")->fetchColumn();
$totalViews  = (int)$db->query("SELECT COUNT(*) FROM stats WHERE type='view'")->fetchColumn();
$totalClicks = (int)$db->query("SELECT COUNT(*) FROM stats WHERE type='click'")->fetchColumn();
$todayViews  = (int)$db->query("SELECT COUNT(*) FROM stats WHERE type='view' AND DATE(created_at) = '" . date('Y-m-d') . "'")->fetchColumn();
$newUsers7d  = (int)$db->query("SELECT COUNT(*) FROM users WHERE created_at >= '" . date('Y-m-d H:i:s', strtotime('-7 days')) . "'")->fetchColumn();

// 热门用户排行 TOP5
$topUsers = $db->query("SELECT u.id, u.username, u.nickname, u.avatar,
    (SELECT COUNT(*) FROM stats s WHERE s.user_id = u.id) as total_stats
    FROM users u ORDER BY total_stats DESC LIMIT 5")->fetchAll();

// 近7天注册趋势
$regTrend = $db->query("SELECT DATE(created_at) as d, COUNT(*) as cnt FROM users 
    WHERE created_at >= '" . date('Y-m-d H:i:s', strtotime('-7 days')) . "' 
    GROUP BY DATE(created_at) ORDER BY d")->fetchAll();
$regLabels = []; $regData = [];
foreach ($regTrend as $r) { $regLabels[] = substr($r['d'],5); $regData[] = $r['cnt']; }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $sn = getSiteName($db ?? null); ?><title>管理后台 - <?=h($sn)?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0f172a;color:#e2e8f0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;min-height:100vh}
.sidebar{width:240px;background:rgba(30,27,75,0.6);backdrop-filter:blur(12px);border-right:1px solid rgba(255,255,255,0.06);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;transition:all 0.3s}
.sidebar-logo{padding:22px 20px;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;gap:12px;min-width:0}
.sidebar-logo .logo-text{min-width:0;flex:1}
.sidebar-logo .logo-text span{color:#fff;font-size:16px;font-weight:700;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sidebar-logo .logo-text small{color:rgba(255,255,255,0.35);font-size:10px;letter-spacing:2px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 16px;margin:2px 10px;border-radius:10px;color:rgba(255,255,255,0.55);cursor:pointer;transition:all 0.2s;text-decoration:none;font-size:14px;overflow:hidden}
.nav-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-item:hover{color:#fff;background:rgba(255,255,255,0.07)}
.nav-item.active{color:#fff;background:rgba(99,102,241,0.2);font-weight:600}
.nav-item i{width:20px;text-align:center;flex-shrink:0}
.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
/* ===== 侧边栏遮罩 ===== */
.sidebar-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:49;opacity:0;pointer-events:none;transition:opacity 0.3s}
.sidebar-overlay.show{opacity:1;pointer-events:auto}
/* ===== 主区域 ===== */
.main{margin-left:240px;flex:1;min-height:100vh;padding:24px 32px;max-width:1200px}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:28px}
.stat-card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:22px;transition:all 0.2s}
.stat-card:hover{background:rgba(255,255,255,0.06);transform:translateY(-2px)}
.stat-card .stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px}
.stat-card .stat-value{font-size:26px;font-weight:800;color:#fff;margin-bottom:4px}
.stat-card .stat-label{font-size:13px;color:rgba(255,255,255,0.5)}
.chart-card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:22px;margin-bottom:20px}
.chart-card h3{font-size:15px;font-weight:700;color:#fff;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.chart-row{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:28px}
/* ===== 表格横向滚动（移动端） ===== */
.table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:thin}
.table-responsive::-webkit-scrollbar{height:4px}
.table-responsive::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.15);border-radius:4px}
/* ===== 汉堡按钮 ===== */
.hamburger{display:none;position:fixed;top:12px;left:12px;z-index:55;width:40px;height:40px;border-radius:10px;background:rgba(30,27,75,0.8);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.08);color:#fff;font-size:18px;cursor:pointer;align-items:center;justify-content:center;transition:all 0.2s}
.hamburger:hover{background:rgba(99,102,241,0.3)}
/* ===== 移动端友好搜索栏 ===== */
.search-bar{display:flex;gap:12px;flex-wrap:nowrap}
.mb-6{/* keep */}

@media(max-width:900px){
  .sidebar{transform:translateX(-110%);box-shadow:none}
  .sidebar.open{transform:translateX(0);box-shadow:4px 0 30px rgba(0,0,0,0.4)}
  .main{margin-left:0;padding:16px;padding-top:60px}
  .chart-row{grid-template-columns:1fr}
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .hamburger{display:flex}
  /* 表格行间更大间距方便触控 */
  .table-responsive table td,.table-responsive table th{padding:10px 8px!important;white-space:nowrap}
  /* 搜索栏垂直堆叠 */
  .search-bar{flex-wrap:wrap}
  .search-bar input{flex:1 1 100%!important;min-width:0}
}
@media(max-width:480px){
  .stats-grid{grid-template-columns:1fr}
  .stat-card{padding:16px}
  .stat-card .stat-value{font-size:22px}
  .main{padding:12px;padding-top:56px}
  .chart-card{padding:14px}
}
</style>
</head>
<body>

<!-- ===== 移动端菜单按钮 ===== -->
<button class="hamburger" id="menuToggle" onclick="toggleSidebar()" aria-label="菜单">
  <i class="fas fa-bars"></i>
</button>

<!-- ===== 侧边栏遮罩 ===== -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ===== 侧边栏 ===== -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">L</div>
    <div class="logo-text"><span>管理后台</span><small><?=h(getSiteName($db))?></small></div>
  </div>
  <nav class="flex-1 py-4">
    <a href="?page=dashboard" class="nav-item <?=$subPage=='dashboard'?'active':''?>"><i class="fas fa-home"></i>控制台</a>
    <a href="?page=users" class="nav-item <?=$subPage=='users'?'active':''?>"><i class="fas fa-users"></i>用户管理</a>
    <a href="?page=links" class="nav-item <?=$subPage=='links'?'active':''?>"><i class="fas fa-link"></i>链接审核</a>
    <a href="?page=settings" class="nav-item <?=$subPage=='settings'?'active':''?>"><i class="fas fa-cog"></i>全站设置</a>
    <a href="?page=reports" class="nav-item <?=$subPage=='reports'?'active':''?>"><i class="fas fa-flag"></i>举报管理</a>
    <a href="?page=logs" class="nav-item <?=$subPage=='logs'?'active':''?>"><i class="fas fa-history"></i>操作日志</a>
    <a href="?page=password" class="nav-item <?=$subPage=='password'?'active':''?>"><i class="fas fa-key"></i>修改密码</a>
  </nav>
  <div class="p-4 border-t border-white/10">
    <div class="flex items-center gap-3 mb-3 px-2">
      <div class="w-8 h-8 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-sm"><?=h(mb_substr($_SESSION['admin_name'],0,1))?></div>
      <div class="min-w-0 flex-1"><div class="text-sm text-white font-medium truncate"><?=h($_SESSION['admin_name'])?></div><div class="text-xs text-gray-500">管理员</div></div>
    </div>
    <a href="./logout.php" class="nav-item text-red-400 hover:text-red-300"><i class="fas fa-sign-out-alt"></i>退出登录</a>
    <div class="mt-3 px-2 pt-3 border-t border-white/5 text-[11px] text-gray-600">
      v1.0 · Developed by shadow
    </div>
  </div>
</div>

<!-- ===== 主内容 ===== -->
<div class="main">

<?php if ($subPage === 'dashboard'): ?>
  <h1 class="text-2xl font-bold mb-2">控制台</h1>
  <p class="text-gray-500 mb-6">系统运行状态总览</p>

  <div class="stats-grid">
    <div class="stat-card" style="border-left:3px solid #6366f1">
      <div class="stat-icon" style="background:rgba(99,102,241,0.15);color:#818cf8"><i class="fas fa-users"></i></div>
      <div class="stat-value"><?=$totalUsers?></div>
      <div class="stat-label">注册用户 · 活跃 <?=$activeUsers?></div>
    </div>
    <div class="stat-card" style="border-left:3px solid #22c55e">
      <div class="stat-icon" style="background:rgba(34,197,94,0.15);color:#4ade80"><i class="fas fa-link"></i></div>
      <div class="stat-value"><?=$totalLinks?></div>
      <div class="stat-label">链接卡片总数</div>
    </div>
    <div class="stat-card" style="border-left:3px solid #f59e0b">
      <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#fbbf24"><i class="fas fa-eye"></i></div>
      <div class="stat-value"><?=number_format($totalViews)?></div>
      <div class="stat-label">总访问量 · 今日 <?=$todayViews?></div>
    </div>
    <div class="stat-card" style="border-left:3px solid #ec4899">
      <div class="stat-icon" style="background:rgba(236,72,153,0.15);color:#f472b6"><i class="fas fa-chart-line"></i></div>
      <div class="stat-value"><?=$newUsers7d?></div>
      <div class="stat-label">近7日新增用户</div>
    </div>
  </div>

  <div class="chart-row">
    <div class="chart-card">
      <h3><i class="fas fa-chart-bar text-indigo-400"></i> 近7日注册趋势</h3>
      <?php if (empty($regData)): ?>
        <p class="text-gray-500 text-sm text-center py-8">暂无数据</p>
      <?php else: ?>
      <div class="flex items-end gap-3 h-40 pt-4">
        <?php $maxReg = max($regData) ?: 1; ?>
        <?php foreach ($regData as $i=>$v): ?>
        <div class="flex-1 flex flex-col items-center justify-end h-full">
          <span class="text-xs text-gray-400 mb-1"><?=$v?></span>
          <div class="w-full max-w-[36px] rounded-t-lg bg-gradient-to-t from-indigo-500 to-purple-500 transition-all duration-300" style="height:<?=max(8,($v/$maxReg)*130)?>px"></div>
          <span class="text-xs text-gray-500 mt-2"><?=$regLabels[$i]?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="chart-card">
      <h3><i class="fas fa-trophy text-yellow-400"></i> 热门主页 TOP5</h3>
      <?php if (empty($topUsers)): ?>
        <p class="text-gray-500 text-sm text-center py-8">暂无用户</p>
      <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($topUsers as $i=>$u): ?>
        <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
          <span class="text-sm"><?=($i==0?'🥇':($i==1?'🥈':($i==2?'🥉':'#'.($i+1))))?></span>
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold"><?=h(mb_substr($u['nickname']?:$u['username'],0,1))?></div>
          <div class="flex-1 min-w-0">
            <div class="text-sm text-white truncate"><?=h($u['nickname']?:$u['username'])?></div>
            <div class="text-xs text-gray-500">@<?=h($u['username'])?></div>
          </div>
          <span class="text-indigo-400 text-sm font-bold"><?=$u['total_stats']?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

<?php elseif ($subPage === 'users'): ?>
  <?php include __DIR__ . '/users.php'; ?>
<?php elseif ($subPage === 'links'): ?>
  <?php include __DIR__ . '/links.php'; ?>
<?php elseif ($subPage === 'settings'): ?>
  <?php include __DIR__ . '/settings.php'; ?>
<?php elseif ($subPage === 'reports'): ?>
  <?php include __DIR__ . '/reports.php'; ?>
<?php elseif ($subPage === 'logs'): ?>
  <?php include __DIR__ . '/logs.php'; ?>
<?php elseif ($subPage === 'password'): ?>
  <?php include __DIR__ . '/password.php'; ?>
<?php endif; ?>

</div>

<script>
// 移动端侧边栏切换
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('show');
}
// 点击导航链接后自动关闭侧边栏
document.querySelectorAll('.sidebar nav a').forEach(function(a){
  a.addEventListener('click', function(){ 
    if(window.innerWidth <= 900) toggleSidebar(); 
  });
});
document.addEventListener('keydown', function(e){
  if(e.key === 'Escape') toggleSidebar();
});
// 侧边栏关闭后恢复图标
document.getElementById('sidebar').addEventListener('transitionend', function(){
  var icon = document.querySelector('#menuToggle i');
  if(icon) icon.className = this.classList.contains('open') ? 'fas fa-times' : 'fas fa-bars';
});
</script>
</body>
</html>
