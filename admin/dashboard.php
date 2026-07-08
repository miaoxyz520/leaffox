<?php
/**
 * 管理员后台 - 主框架 + 数据看板
 */
require_once __DIR__ . '/../config.php';
requireAdmin();

$pageTitle = '控制台';
$subPage = $_GET['page'] ?? 'dashboard';
// ---- 双侧栏分类映射 ----
$pageToCat = [
    'dashboard' => 'overview',
    'users'    => 'users',
    'links'    => 'content',
    'comments' => 'content',
    'reports'  => 'content',
    'settings' => 'system',
    'logs'     => 'system',
    'templates'=> 'system',
    'password' => 'account',
];
$currentCat = $pageToCat[$subPage] ?? 'overview';
$catConfig = [
    'overview' => ['icon' => 'fa-chart-pie', 'label' => '概览', 'groups' => [
        '概览' => [['dashboard', 'fa-home', '控制台']],
    ]],
    'content' => ['icon' => 'fa-layer-group', 'label' => '内容管理', 'groups' => [
        '链接' => [['links', 'fa-link', '链接审核']],
        '互动' => [['comments', 'fa-comments', '评论管理'], ['reports', 'fa-flag', '举报管理']],
    ]],
    'users' => ['icon' => 'fa-users', 'label' => '用户管理', 'groups' => [
        '帐号' => [['users', 'fa-user', '用户管理']],
    ]],
    'system' => ['icon' => 'fa-cog', 'label' => '系统设置', 'groups' => [
        '配置' => [['settings', 'fa-sliders-h', '全站设置']],
        '运维' => [['logs', 'fa-history', '操作日志'], ['templates', 'fa-paint-roller', '模版管理']],
    ]],
    'account' => ['icon' => 'fa-user-circle', 'label' => '账户', 'groups' => [
        '安全' => [['password', 'fa-key', '修改密码']],
    ]],
];
$currentCatItems = $catConfig[$currentCat]['items'] ?? [];


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
/* ===== 昼夜模式 CSS 变量 ===== */
:root,.dark-mode{
  --admin-bg:#0f172a;
  --admin-text:#e2e8f0;
  --admin-text-primary:#fff;
  --admin-text-secondary:rgba(255,255,255,0.55);
  --admin-text-muted:rgba(255,255,255,0.5);
  --admin-text-gray:rgba(255,255,255,0.35);
  --admin-sidebar-bg:rgba(30,27,75,0.6);
  --admin-sidebar-border:rgba(255,255,255,0.06);
  --admin-card-bg:rgba(255,255,255,0.04);
  --admin-card-border:rgba(255,255,255,0.08);
  --admin-card-hover:rgba(255,255,255,0.06);
  --admin-scrollbar:rgba(255,255,255,0.15);
  --admin-hamburger-bg:rgba(30,27,75,0.8);
  --admin-hamburger-border:rgba(255,255,255,0.08);
  --admin-nav-text:rgba(255,255,255,0.55);
  --admin-nav-hover-bg:rgba(255,255,255,0.07);
  --admin-nav-hover-text:#fff;
  --admin-logo-text:#fff;
  --admin-logo-sub:rgba(255,255,255,0.35);
  --admin-input-bg:rgba(255,255,255,0.06);
  --admin-input-border:rgba(255,255,255,0.12);
  --admin-input-text:var(--admin-text);
  --admin-overlay:rgba(0,0,0,0.5);
}
.light-mode{
  --admin-bg:#f1f5f9;
  --admin-text:#334155;
  --admin-text-primary:#0f172a;
  --admin-text-secondary:#64748b;
  --admin-text-muted:#94a3b8;
  --admin-text-gray:#a0aec0;
  --admin-sidebar-bg:rgba(255,255,255,0.85);
  --admin-sidebar-border:rgba(0,0,0,0.08);
  --admin-card-bg:rgba(255,255,255,0.8);
  --admin-card-border:rgba(0,0,0,0.08);
  --admin-card-hover:rgba(255,255,255,0.95);
  --admin-scrollbar:rgba(0,0,0,0.12);
  --admin-hamburger-bg:rgba(255,255,255,0.9);
  --admin-hamburger-border:rgba(0,0,0,0.08);
  --admin-nav-text:#64748b;
  --admin-nav-hover-bg:rgba(0,0,0,0.05);
  --admin-nav-hover-text:#0f172a;
  --admin-logo-text:#0f172a;
  --admin-logo-sub:#94a3b8;
  --admin-input-bg:#fff;
  --admin-input-border:#d1d9e6;
  --admin-input-text:#1e293b;
  --admin-overlay:rgba(0,0,0,0.3);
}
body{background:var(--admin-bg);color:var(--admin-text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;min-height:100vh;transition:background 0.3s,color 0.3s}
.sidebar{width:240px;background:var(--admin-sidebar-bg);backdrop-filter:blur(12px);border-right:1px solid var(--admin-sidebar-border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;transition:all 0.3s}
.sidebar-logo{padding:22px 20px;border-bottom:1px solid var(--admin-sidebar-border);display:flex;align-items:center;gap:12px;min-width:0}
.sidebar-logo .logo-text{min-width:0;flex:1}
.sidebar-logo .logo-text span{color:var(--admin-logo-text);font-size:16px;font-weight:700;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sidebar-logo .logo-text small{color:var(--admin-logo-sub);font-size:10px;letter-spacing:2px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 16px;margin:2px 10px;border-radius:10px;color:var(--admin-nav-text);cursor:pointer;transition:all 0.2s;text-decoration:none;font-size:14px;overflow:hidden}
.nav-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-item:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.nav-item.active{color:var(--admin-nav-hover-text);background:rgba(99,102,241,0.2);font-weight:600}
.nav-item i{width:20px;text-align:center;flex-shrink:0}
.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
/* ===== 主题切换按钮（侧边栏底部） ===== */
.theme-toggle-sidebar{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;margin:2px 10px 6px;border-radius:10px;color:var(--admin-nav-text);cursor:pointer;transition:all 0.2s;font-size:13px;user-select:none}
.theme-toggle-sidebar:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.theme-toggle-sidebar .tt-label{display:flex;align-items:center;gap:10px}
.theme-toggle-sidebar .tt-icon{width:20px;text-align:center;font-size:15px}
.theme-toggle-sidebar .tt-switch{width:38px;height:20px;border-radius:10px;background:var(--admin-card-border);position:relative;transition:background 0.3s;flex-shrink:0}
.theme-toggle-sidebar .tt-switch::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:var(--admin-logo-text);transition:transform 0.3s}
.light-mode .theme-toggle-sidebar .tt-switch::after{transform:translateX(18px)}
/* ===== 侧边栏遮罩 ===== */
.sidebar-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:var(--admin-overlay);z-index:49;opacity:0;pointer-events:none;transition:opacity 0.3s}
.sidebar-overlay.show{opacity:1;pointer-events:auto}
/* ===== 主区域 ===== */
.main{margin-left:240px;flex:1;min-height:100vh;padding:24px 32px;max-width:1200px}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:28px}
.stat-card{background:var(--admin-card-bg);border:1px solid var(--admin-card-border);border-radius:16px;padding:22px;transition:all 0.2s}
.stat-card:hover{background:var(--admin-card-hover);transform:translateY(-2px)}
.stat-card .stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px}
.stat-card .stat-value{font-size:26px;font-weight:800;color:var(--admin-text-primary);margin-bottom:4px}
.stat-card .stat-label{font-size:13px;color:var(--admin-text-muted)}
.chart-card{background:var(--admin-card-bg);border:1px solid var(--admin-card-border);border-radius:16px;padding:22px;margin-bottom:20px}
.chart-card h3{font-size:15px;font-weight:700;color:var(--admin-text-primary);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.chart-row{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:28px}
/* ===== 表格横向滚动（移动端） ===== */
.table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:thin}
.table-responsive::-webkit-scrollbar{height:4px}
.table-responsive::-webkit-scrollbar-thumb{background:var(--admin-scrollbar);border-radius:4px}
/* ===== 汉堡按钮 ===== */
.hamburger{display:none;position:fixed;top:12px;left:12px;z-index:999;width:40px;height:40px;border-radius:10px;background:var(--admin-hamburger-bg);backdrop-filter:blur(8px);border:1px solid var(--admin-hamburger-border);color:var(--admin-nav-text);font-size:18px;cursor:pointer;align-items:center;justify-content:center;transition:all 0.2s}
.hamburger:hover{background:rgba(99,102,241,0.3);color:var(--admin-nav-hover-text)}
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
/* ===== 被包含页面 & 侧边栏的 Tailwind 色调覆盖（适配昼夜模式） ===== */
.text-white{color:var(--admin-text-primary)!important}
.text-gray-500{color:var(--admin-text-muted)!important}
.text-gray-400{color:var(--admin-text-secondary)!important}
.text-gray-300{color:var(--admin-text)!important}
.bg-white\/5{background:var(--admin-card-bg)!important}
.bg-white\/10{background:var(--admin-card-border)!important}
.border-white\/10{border-color:var(--admin-sidebar-border)!important}
.border-white\/5{border-color:var(--admin-sidebar-border)!important}
.hover\:bg-white\/5:hover{background:var(--admin-card-hover)!important}
.hover\:bg-white\/10:hover{background:var(--admin-card-hover)!important}
/* 表格 & 卡片特殊背景 */
.main table,.main .table-responsive{background:transparent!important}
.main .bg-gradient-to-br{background:linear-gradient(135deg,#6366f1,#7c3aed)!important}
/* 侧边栏附加覆盖 */
.sidebar .text-gray-500{color:var(--admin-logo-sub)!important}
.sidebar .text-white{color:var(--admin-logo-text)!important}
.sidebar .border-white\/5,.sidebar .border-white\/10{border-color:var(--admin-sidebar-border)!important}
/* 侧边栏 footer */
.sidebar .text-gray-500.text-\[11px\]{color:var(--admin-logo-sub)!important}
/* 侧边栏 hover 颜色 */
.sidebar .nav-item.text-red-400{color:#f87171!important}
.sidebar .nav-item.text-red-400:hover{color:#fca5a5!important}
/* 汉堡按钮默认色 */
.hamburger{color:var(--admin-nav-text)!important}
/* 输入框在浅色模式下 */
input[type="text"],input[type="search"],input[type="email"],input[type="password"]{
  background:var(--admin-input-bg)!important;
  color:var(--admin-input-text)!important;
  border-color:var(--admin-input-border)!important;
}
input[type="text"]::placeholder,input[type="search"]::placeholder{
  color:var(--admin-text-muted)!important;
}
</style>
/* ===== 双侧栏布局 ===== */
body{display:flex}
/* 一级侧边栏（最左侧图标栏） */
.primary-sidebar{
  width:72px;
  background:var(--admin-sidebar-bg);
  backdrop-filter:blur(12px);
  border-right:1px solid var(--admin-sidebar-border);
  display:flex;
  flex-direction:column;
  align-items:center;
  position:fixed;
  top:0;
  left:0;
  bottom:0;
  z-index:51;
  padding:16px 0;
  gap:4px;
}
.primary-sidebar .ps-logo{
  width:42px;height:42px;
  border-radius:12px;
  background:linear-gradient(135deg,#6366f1,#7c3aed);
  display:flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  font-weight:800;
  font-size:16px;
  margin-bottom:16px;
  flex-shrink:0;
}
.p-nav-item{
  width:52px;height:52px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:3px;
  color:var(--admin-nav-text);
  cursor:pointer;
  text-decoration:none;
  transition:all 0.2s;
  font-size:10px;
  flex-shrink:0;
}
.p-nav-item i{font-size:20px}
.p-nav-item .p-label{font-size:9px;letter-spacing:0.5px;opacity:0.7}
.p-nav-item:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.p-nav-item.active{color:#818cf8;background:rgba(99,102,241,0.2)}
.p-nav-spacer{flex:1}
.p-nav-bottom{padding-top:8px;border-top:1px solid var(--admin-sidebar-border);width:100%;display:flex;flex-direction:column;align-items:center;gap:4px}
/* 二级侧边栏 */
.sidebar{left:72px;width:200px}
.sidebar .nav-section-title{
  padding:18px 16px 6px;
  font-size:10px;
  letter-spacing:2px;
  color:var(--admin-logo-sub);
  text-transform:uppercase;
  font-weight:600;
}
/* 主内容偏移 */
.main{margin-left:272px}
/* 移动端适配 */
@media(max-width:900px){
  .primary-sidebar{display:none}
  .sidebar{left:0}
  .main{margin-left:0}
  .sidebar.open{transform:translateX(0)}
}

</head>
<body>

<!-- ===== 移动端菜单按钮 ===== -->
<button class="hamburger" id="menuToggle" onclick="toggleSidebar()" aria-label="菜单">
  <i class="fas fa-bars"></i>
</button>
<div style="height:0;clear:both"></div>

<!-- ===== 一级侧边栏（图标栏） ===== -->
<div class="primary-sidebar">
  <div class="ps-logo">L</div>
    <a href="?page=dashboard" class="p-nav-item <?=$currentCat=='overview'?'active':''?>" title="<?=$catConfig['overview']['label']?>">
      <i class="fas <?=$catConfig['overview']['icon']?>"></i>
      <span class="p-label"><?=$catConfig['overview']['label']?></span>
    </a>
    <a href="?page=links" class="p-nav-item <?=$currentCat=='content'?'active':''?>" title="<?=$catConfig['content']['label']?>">
      <i class="fas <?=$catConfig['content']['icon']?>"></i>
      <span class="p-label"><?=$catConfig['content']['label']?></span>
    </a>
    <a href="?page=users" class="p-nav-item <?=$currentCat=='users'?'active':''?>" title="<?=$catConfig['users']['label']?>">
      <i class="fas <?=$catConfig['users']['icon']?>"></i>
      <span class="p-label"><?=$catConfig['users']['label']?></span>
    </a>
    <a href="?page=settings" class="p-nav-item <?=$currentCat=='system'?'active':''?>" title="<?=$catConfig['system']['label']?>">
      <i class="fas <?=$catConfig['system']['icon']?>"></i>
      <span class="p-label"><?=$catConfig['system']['label']?></span>
    </a>
    <a href="?page=password" class="p-nav-item <?=$currentCat=='account'?'active':''?>" title="<?=$catConfig['account']['label']?>">
      <i class="fas <?=$catConfig['account']['icon']?>"></i>
      <span class="p-label"><?=$catConfig['account']['label']?></span>
    </a>
  <div class="p-nav-spacer"></div>
  <div class="p-nav-bottom">
    <a href="./logout.php" class="p-nav-item" title="退出登录" style="color:#ef4444">
      <i class="fas fa-sign-out-alt"></i>
      <span class="p-label">退出</span>
    </a>
  </div>
</div>

<!-- ===== 侧边栏遮罩 ===== -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ===== 二级侧边栏（分类子菜单） ===== -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">L</div>
    <div class="logo-text"><span><?=h($catConfig[$currentCat]['label'] ?? '管理后台')?></span><small><?=h(getSiteName($db))?></small></div>
  </div>
  <nav class="flex-1 py-4">
    <div class="nav-section-title"><?=h($catConfig[$currentCat]['label'] ?? '')?></div>
<?php
$groups = $catConfig[$currentCat]['groups'] ?? [];
foreach ($groups as $groupLabel => $groupItems):
?>
    <div class="nav-subgroup-title"><?=h($groupLabel)?></div>
<?php foreach ($groupItems as $item): $pageKey=$item[0]; $icon=$item[1]; $label=$item[2]; ?>
    <a href="?page=<?=$pageKey?>" class="nav-item <?=$subPage==$pageKey?'active':''?>">
      <i class="fas <?=$icon?>"></i>
      <span><?=h($label)?></span>
    </a>
<?php endforeach; endforeach; ?>
  </nav>
  <div class="p-4 border-t border-white/10">
    <div class="flex items-center gap-3 mb-3 px-2">
      <div class="w-8 h-8 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-sm"><?=h(mb_substr($_SESSION['admin_name'],0,1))?></div>
      <div class="min-w-0 flex-1"><div class="text-sm text-white font-medium truncate"><?=h($_SESSION['admin_name'])?></div><div class="text-xs text-gray-500">管理员</div></div>
    </div>
    <div class="theme-toggle-sidebar" onclick="toggleAdminTheme()">
      <span class="tt-label">
        <span class="tt-icon" id="adminThemeIcon"><i class="fas fa-moon"></i></span>
        黑夜模式
      </span>
      <span class="tt-switch"></span>
    </div>
    <div class="mt-3 px-2 pt-3 border-t border-white/5 text-[11px] text-gray-500">
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
          <span class="text-sm"><?=($i==0?'<i class="fas fa-medal" style="color:#fbbf24"></i>':($i==1?'<i class="fas fa-medal" style="color:#94a3b8"></i>':($i==2?'<i class="fas fa-medal" style="color:#d97706"></i>':'#'.($i+1))))?></span>
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
<?php elseif ($subPage === 'comments'): ?>
  <?php include __DIR__ . '/comments.php'; ?>
<?php elseif ($subPage === 'logs'): ?>
  <?php include __DIR__ . '/logs.php'; ?>
<?php elseif ($subPage === 'password'): ?>
  <?php include __DIR__ . '/password.php'; ?>
<?php elseif ($subPage === 'templates'): ?>
  <?php include __DIR__ . '/templates.php'; ?>
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
// ===== 管理员后台黑夜/白天模式切换 =====
(function(){
  var saved = localStorage.getItem('admin_theme');
  var html = document.documentElement;
  if(saved === 'light'){
    html.classList.remove('dark-mode');
    html.classList.add('light-mode');
    var icon = document.getElementById('adminThemeIcon');
    if(icon) icon.innerHTML = '<i class="fas fa-sun"></i>';
    var label = document.querySelector('.theme-toggle-sidebar .tt-label');
    if(label) label.childNodes[2].textContent = '白天模式';
  } else {
    html.classList.add('dark-mode');
  }
})();
function toggleAdminTheme(){
  var html = document.documentElement;
  var icon = document.getElementById('adminThemeIcon');
  if(html.classList.contains('light-mode')){
    html.classList.remove('light-mode');
    html.classList.add('dark-mode');
    localStorage.setItem('admin_theme', 'dark');
    if(icon) icon.innerHTML = '<i class="fas fa-moon"></i>';
    var label = document.querySelector('.theme-toggle-sidebar .tt-label');
    if(label) label.childNodes[2].textContent = '黑夜模式';
  } else {
    html.classList.remove('dark-mode');
    html.classList.add('light-mode');
    localStorage.setItem('admin_theme', 'light');
    if(icon) icon.innerHTML = '<i class="fas fa-sun"></i>';
    var label = document.querySelector('.theme-toggle-sidebar .tt-label');
    if(label) label.childNodes[2].textContent = '白天模式';
  }
}
// 侧边栏关闭后恢复图标
document.getElementById('sidebar').addEventListener('transitionend', function(){
  var icon = document.querySelector('#menuToggle i');
  if(icon) icon.className = this.classList.contains('open') ? 'fas fa-times' : 'fas fa-bars';
});
</script>
</body>
</html>
