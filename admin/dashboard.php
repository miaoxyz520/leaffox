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
    'cron'     => 'system',
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
        '运维' => [['logs', 'fa-history', '操作日志'], ['templates', 'fa-paint-roller', '模版管理'], ['cron', 'fa-clock', '定时清理']],
    ]],
    'account' => ['icon' => 'fa-user-circle', 'label' => '账户', 'groups' => [
        '安全' => [['password', 'fa-key', '修改密码']],
    ]],
];
$currentCatItems = $catConfig[$currentCat]['items'] ?? [];


if ($db) {
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
} else {
    $totalUsers = $activeUsers = $totalLinks = $totalViews = $totalClicks = $todayViews = $newUsers7d = 0;
    $topUsers = $regTrend = []; $regLabels = []; $regData = [];
    $dbError = '数据库连接失败，请检查 config.php 中的数据库信息';
}
?>
<?php

// ---- 子页面 POST 预处理（必须在 HTML 输出之前执行） ----
// 防止 include 子页面时 header() 报 "headers already sent"
$earlyPostHandled = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($subPage) {
        case 'settings':
            // settings.php 的 POST 处理必须在输出前执行
            requireCsrfToken();
            $settings = getSettings($db);
            $fields = ['site_name', 'site_logo', 'site_desc', 'icp_record', 'announcement', 'powered_by_name', 'banned_suffixes'];
            $setParts = []; $params = [];
            foreach ($fields as $f) {
                $setParts[] = "$f = ?";
                $params[] = trim($_POST[$f] ?? '');
            }
            $setParts[] = "reg_enabled = ?";        $params[] = (int)(!empty($_POST['reg_enabled'] ?? ''));
            $setParts[] = "reg_invite = ?";         $params[] = (int)(!empty($_POST['reg_invite'] ?? ''));
            $setParts[] = "guest_mode = ?";         $params[] = (int)(!empty($_POST['guest_mode'] ?? ''));
            $setParts[] = "powered_by_enabled = ?"; $params[] = (int)(!empty($_POST['powered_by_enabled'] ?? ''));
            $setParts[] = "reg_email_verify = ?";   $params[] = (int)(!empty($_POST['reg_email_verify'] ?? ''));
            $setParts[] = "user_email_login = ?";   $params[] = (int)(!empty($_POST['user_email_login'] ?? ''));
            $setParts[] = "admin_email_login = ?";  $params[] = (int)(!empty($_POST['admin_email_login'] ?? ''));
            $setParts[] = "smtp_host = ?";          $params[] = trim($_POST['smtp_host'] ?? '');
            $setParts[] = "smtp_port = ?";          $params[] = (int)($_POST['smtp_port'] ?? 465);
            $setParts[] = "smtp_encrypt = ?";       $params[] = trim($_POST['smtp_encrypt'] ?? 'ssl');
            $setParts[] = "smtp_user = ?";          $params[] = trim($_POST['smtp_user'] ?? '');
            $smtpPass = $_POST['smtp_pass'] ?? '';
            if ($smtpPass !== '') {
                $setParts[] = "smtp_pass = ?";
                $params[] = $smtpPass;
            }
            $setParts[] = "smtp_from_name = ?";     $params[] = trim($_POST['smtp_from_name'] ?? '');
            $setParts[] = "site_template = ?";      $params[] = trim($_POST['site_template'] ?? 'default');
            $params[] = $settings['id'];
            try {
                $db->prepare("UPDATE settings SET " . implode(', ', $setParts) . " WHERE id = ?")->execute($params);
                $earlyPostMsg = '设置已保存';
                $settings = getSettings($db); // 刷新
            } catch (Exception $e) {
                $earlyPostMsg = '保存失败: ' . $e->getMessage();
            }
            $earlyPostHandled = true;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN" class="dark-mode">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $sn = getSiteName($db ?? null); ?><title>管理后台 - <?=h($sn)?></title>
<script>const CSRF_TOKEN = '<?=getCsrfToken()?>';</script>
<link rel="stylesheet" href="../assets/css/tailwind.css">
<link rel="stylesheet" href="../assets/css/fontawesome.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
/* ===== 昼夜模式 CSS 变量 ===== */
:root,.dark-mode{
  --admin-bg:#0f172a;
  --admin-text:#e2e8f0;
  --admin-text-primary:#fff;
  --admin-text-secondary:rgba(255,255,255,0.55);
  --admin-text-muted:#64748b;
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
  --admin-text-muted:#64748b;
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

/* ===== 侧边栏（手风琴菜单） ===== */
.sidebar{width:240px;background:var(--admin-sidebar-bg);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-right:1px solid var(--admin-sidebar-border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;transition:transform 0.3s,box-shadow 0.3s}
.sidebar-logo{padding:18px 16px;border-bottom:1px solid var(--admin-sidebar-border);display:flex;align-items:center;gap:10px}
.sidebar-logo .logo-text{min-width:0;flex:1}
.sidebar-logo .logo-text span{color:var(--admin-logo-text);font-size:15px;font-weight:700;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sidebar-logo .logo-text small{color:var(--admin-logo-sub);font-size:9px;letter-spacing:2px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* 手风琴 */
.acc-cat{border-bottom:1px solid var(--admin-sidebar-border)}
.acc-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;cursor:pointer;color:var(--admin-nav-text);transition:all 0.2s;user-select:none}
.acc-header:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.acc-header .acc-header-left{display:flex;align-items:center;gap:10px;min-width:0}
.acc-header .acc-header-left .acc-icon{width:20px;text-align:center;font-size:15px;flex-shrink:0}
.acc-header .acc-header-left span{font-size:14px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.acc-arrow{font-size:11px;opacity:0.5;transition:transform 0.25s ease;flex-shrink:0}
.acc-cat.open .acc-arrow{transform:rotate(180deg)}
.acc-body{display:none;padding:2px 0 6px}
.acc-cat.open .acc-body{display:block}
.acc-group-title{padding:10px 16px 4px 42px;font-size:10px;letter-spacing:1.5px;color:var(--admin-logo-sub);text-transform:uppercase;font-weight:600}

.nav-item{display:flex;align-items:center;gap:10px;padding:9px 16px 9px 42px;margin:1px 8px;border-radius:8px;color:var(--admin-nav-text);cursor:pointer;transition:all 0.2s;text-decoration:none;font-size:13px}
.nav-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-item:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.nav-item.active{color:var(--admin-nav-hover-text);background:rgba(99,102,241,0.2);font-weight:600}
.nav-item i{width:16px;text-align:center;font-size:13px;flex-shrink:0}
.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* 主题切换 */
.theme-toggle-sidebar{display:flex;align-items:center;justify-content:space-between;padding:9px 14px;margin:2px 0;border-radius:10px;color:var(--admin-nav-text);cursor:pointer;transition:all 0.2s;font-size:13px;user-select:none}
.theme-toggle-sidebar:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.theme-toggle-sidebar .tt-label{display:flex;align-items:center;gap:10px}
.theme-toggle-sidebar .tt-icon{width:18px;text-align:center;font-size:14px}
.theme-toggle-sidebar .tt-switch{width:36px;height:18px;border-radius:9px;background:var(--admin-card-border);position:relative;transition:background 0.3s;flex-shrink:0}
.theme-toggle-sidebar .tt-switch::after{content:'';position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:var(--admin-logo-text);transition:transform 0.3s}
.light-mode .theme-toggle-sidebar .tt-switch::after{transform:translateX(18px)}

/* 滚动条 */
.custom-scrollbar{scrollbar-width:thin}
.custom-scrollbar::-webkit-scrollbar{width:4px}
.custom-scrollbar::-webkit-scrollbar-thumb{background:var(--admin-scrollbar);border-radius:4px}

/* 侧边栏遮罩 */
.sidebar-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:var(--admin-overlay);z-index:49;opacity:0;pointer-events:none;transition:opacity 0.3s}
.sidebar-overlay.show{opacity:1;pointer-events:auto}

/* ===== 主区域 ===== */
.main{margin-left:240px;flex:1;min-height:100vh;padding:24px 32px;max-width:100%;min-width:0}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px}
.stat-card{background:var(--admin-card-bg);border:1px solid var(--admin-card-border);border-radius:16px;padding:22px;transition:all 0.2s}
.stat-card:hover{background:var(--admin-card-hover);transform:translateY(-2px)}
.stat-card .stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px}
.stat-card .stat-value{font-size:26px;font-weight:800;color:var(--admin-text-primary);margin-bottom:4px}
.stat-card .stat-label{font-size:13px;color:var(--admin-text-muted)}
.chart-card{background:var(--admin-card-bg);border:1px solid var(--admin-card-border);border-radius:16px;padding:22px;margin-bottom:20px}
.chart-card h3{font-size:15px;font-weight:700;color:var(--admin-text-primary);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.chart-row{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:28px}
.card-base{background:var(--admin-card-bg);border:1px solid var(--admin-card-border);border-radius:16px;padding:22px;margin-bottom:16px}

/* ===== 表格横向滚动 ===== */
.table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:thin}
.table-responsive::-webkit-scrollbar{height:4px}
.table-responsive::-webkit-scrollbar-thumb{background:var(--admin-scrollbar);border-radius:4px}

/* ===== 汉堡按钮 ===== */
.hamburger{display:none;position:fixed;top:12px;left:12px;z-index:999;width:40px;height:40px;border-radius:10px;background:var(--admin-hamburger-bg);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid var(--admin-hamburger-border);color:var(--admin-nav-text);font-size:18px;cursor:pointer;align-items:center;justify-content:center;transition:all 0.2s}
.hamburger:hover{background:rgba(99,102,241,0.3);color:var(--admin-nav-hover-text)}

/* ===== 搜索栏 ===== */
.search-bar{display:flex;gap:12px;flex-wrap:nowrap}

/* ===== Tailwind 色调覆盖（适配昼夜模式） ===== */
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
.main table,.main .table-responsive{background:transparent!important}
.main .bg-gradient-to-br{background:linear-gradient(135deg,#6366f1,#7c3aed)!important}
.sidebar .text-gray-500{color:var(--admin-logo-sub)!important}
.sidebar .text-white{color:var(--admin-logo-text)!important}
.sidebar .border-white\/5,.sidebar .border-white\/10{border-color:var(--admin-sidebar-border)!important}
.sidebar .text-gray-500.text-\[11px\]{color:var(--admin-logo-sub)!important}
.sidebar .nav-item.text-red-400{color:#f87171!important}
.sidebar .nav-item.text-red-400:hover{color:#fca5a5!important}
.hamburger{color:var(--admin-nav-text)!important}

/* ===== 输入框（浅色模式适配） ===== */
input[type="text"],input[type="search"],input[type="email"],input[type="password"]{background:var(--admin-input-bg)!important;color:var(--admin-input-text)!important;border-color:var(--admin-input-border)!important}
input[type="text"]::placeholder,input[type="search"]::placeholder{color:var(--admin-text-muted)!important}

/* ===== 响应式 - 移动端适配 ===== */
@media(max-width:1024px){
.card-base{padding:16px}

  .sidebar{transform:translateX(-110%);box-shadow:none;width:260px}
  .sidebar.open{transform:translateX(0);box-shadow:4px 0 30px rgba(0,0,0,0.4)}
  .main{margin-left:0;padding:20px;padding-top:60px;max-width:100%}
  .chart-row{grid-template-columns:1fr}
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .hamburger{display:flex}
  .table-responsive table td,.table-responsive table th{padding:10px 8px!important;white-space:nowrap}
  .search-bar{flex-wrap:wrap}
  .search-bar input{flex:1 1 100%!important;min-width:0}
}
@media(max-width:900px){
  .sidebar{width:260px}
  .stats-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:640px){
  .stats-grid{grid-template-columns:1fr}
  .chart-card{padding:16px}
  .main{padding:14px;padding-top:56px}
}

</style>

</head>
<body>

<!-- ===== 移动端菜单按钮 ===== -->
<button class="hamburger" id="menuToggle" onclick="toggleSidebar()" aria-label="菜单">
  <i class="fas fa-bars"></i>
</button>
<div style="height:0;clear:both"></div>

<!-- ===== 侧边栏（手风琴菜单） ===== -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">L</div>
    <div class="logo-text"><span>管理后台</span><small><?=h(getSiteName($db))?></small></div>
  </div>
  <nav class="flex-1 py-2 overflow-y-auto custom-scrollbar">

<?php $firstCat = true; foreach ($catConfig as $catKey => $catVal):
    $isActive = ($currentCat === $catKey);
    $expanded = $isActive ? 'true' : 'false';
    $openClass = $isActive ? ' open' : '';
?>
    <div class="acc-cat<?=$openClass?>">
      <div class="acc-header" onclick="toggleAccordion(this)" data-cat="<?=$catKey?>">
        <div class="acc-header-left">
          <i class="fas <?=$catVal['icon']?> acc-icon"></i>
          <span><?=h($catVal['label'])?></span>
        </div>
        <i class="fas fa-chevron-down acc-arrow"></i>
      </div>
      <div class="acc-body"<?php if ($isActive): ?> style="display:block"<?php endif; ?>>
<?php foreach ($catVal['groups'] as $groupLabel => $groupItems): ?>
        <div class="acc-group-title"><?=h($groupLabel)?></div>
<?php foreach ($groupItems as $item):
    $pageKey = $item[0]; $icon = $item[1]; $label = $item[2];
    $isPageActive = ($subPage === $pageKey);
?>
        <a href="?page=<?=$pageKey?>" class="nav-item<?=$isPageActive?' active':''?>" onclick="closeSidebarMobile()">
          <i class="fas <?=$icon?>"></i>
          <span><?=h($label)?></span>
        </a>
<?php endforeach; ?>
<?php endforeach; ?>
      </div>
    </div>
<?php endforeach; ?>

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
    <a href="./logout.php" class="nav-item text-red-400 hover:text-red-300 mt-2" onclick="closeSidebarMobile()">
      <i class="fas fa-sign-out-alt"></i><span>退出登录</span>
    </a>
    <div class="mt-3 px-2 pt-3 border-t border-white/5 text-[11px] text-gray-500">v1.0 · Developed by shadow</div>
  </div>
</div>

<!-- ===== 侧边栏遮罩 ===== -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

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

  <?php
  // ---- 快速工具：缓存管理 ----
  $cacheDir = __DIR__ . '/../cache';
  $cacheFiles = is_dir($cacheDir) ? glob($cacheDir . '/*.cache.php') : [];
  $cacheSize = 0;
  foreach ($cacheFiles as $f) $cacheSize += filesize($f);
  $cacheCleared = false;
  if (isset($_GET['clear_cache']) && $_GET['clear_cache'] === '1') {
      foreach ($cacheFiles as $f) @unlink($f);
      $cacheCleared = true;
  }
  ?>
  <div class="row" style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    <div class="stat-card" style="border-left:3px solid #8b5cf6;flex:1;min-width:200px;">
      <h3 style="font-size:14px;color:var(--admin-text-muted);margin:0 0 12px 0;"><i class="fas fa-database"></i> 系统缓存</h3>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <span style="font-size:13px;color:var(--admin-text-secondary);">
          缓存文件: <?=count($cacheFiles)?> 个 · 大小: <?=round($cacheSize/1024,1)?>KB
        </span>
        <?php if ($cacheCleared): ?>
          <span style="color:#22c55e;font-size:13px;">✅ 缓存已清除</span>
        <?php else: ?>
          <a href="?clear_cache=1" onclick="return confirm('确定要清除全部缓存吗？')"
             style="padding:5px 14px;border-radius:8px;background:rgba(139,92,246,0.15);color:#a78bfa;font-size:12px;text-decoration:none;">
            <i class="fas fa-trash-alt"></i> 清除缓存
          </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #f59e0b;flex:1;min-width:200px;">
      <h3 style="font-size:14px;color:var(--admin-text-muted);margin:0 0 12px 0;"><i class="fas fa-tasks"></i> 系统维护</h3>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <a href="./users.php?filter=guest" style="padding:5px 14px;border-radius:8px;background:rgba(245,158,11,0.15);color:#f59e0b;font-size:12px;text-decoration:none;">
          <i class="fas fa-user-clock"></i> 管理游客
        </a>
        <a href="./logs.php" style="padding:5px 14px;border-radius:8px;background:rgba(34,197,94,0.15);color:#22c55e;font-size:12px;text-decoration:none;">
          <i class="fas fa-history"></i> 操作日志
        </a>
        <span style="font-size:12px;color:var(--admin-text-muted);">
          最后清理: <?=date('m-d H:i', @filemtime(__DIR__.'/../data/logs/cleanup.log')?:time())?>
        </span>
      </div>
    </div>
  </div>

  <div class="chart-row">
    <div class="chart-card">
      <h3><i class="fas fa-chart-bar text-indigo-400"></i> 近7日注册趋势</h3>
      <?php if (empty($regData)): ?>
        <p class="text-gray-500 text-sm text-center py-8">暂无数据</p>
      <?php else: ?>
      <div class="flex items-end h-44 px-1" style="gap:2px">
        <?php $maxReg = max($regData) ?: 1; ?>
        <?php foreach ($regData as $i=>$v): ?>
        <div class="flex-1 flex flex-col items-center h-full relative" style="padding-top:20px;padding-bottom:24px">
          <span class="text-xs text-gray-400" style="margin-bottom:2px"><?=$v?></span>
          <div class="flex-1 w-full flex flex-col justify-end items-center">
            <div class="rounded-t-md" style="width:75%;max-width:32px;min-width:12px;background:linear-gradient(to top,#6366f1,#a855f7);height:<?=max(4,($v/$maxReg)*120)?>px"></div>
          </div>
          <span style="font-size:10px;color:#6b7280;position:absolute;bottom:2px;white-space:nowrap"><?=$regLabels[$i]?></span>
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
<?php elseif ($subPage === 'cron'): ?>
  <?php include __DIR__ . '/cron.php'; ?>
<?php endif; ?>

</div>

<script>
// 移动端侧边栏切换
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebarMobile(){
  if(window.innerWidth <= 900) toggleSidebar();
}
// 手风琴切换
function toggleAccordion(el){
  var cat = el.closest('.acc-cat');
  var body = cat.querySelector('.acc-body');
  if(!body) return;
  var isOpen = cat.classList.contains('open');
  if(isOpen){
    cat.classList.remove('open');
    body.style.display = 'none';
  } else {
    cat.classList.add('open');
    body.style.display = 'block';
  }
}
// 点击导航链接后自动关闭侧边栏（移动端）
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.sidebar nav a').forEach(function(a){
    a.addEventListener('click', function(){ 
      if(window.innerWidth <= 900) toggleSidebar(); 
    });
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
