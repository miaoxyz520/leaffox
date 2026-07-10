<?php
/**
 * 管理员 - 用户管理（独立页面 + 双侧栏 + 子分组）
 * 支持独立访问（admin/users.php）或从 dashboard.php 引用
 */

// ---- 独立/引用模式检测 ----
$isStandalone = !isset($subPage);
if ($isStandalone) {
    require_once __DIR__ . '/../config.php';
    session_start();
    if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
    $stmt = $db->prepare("SELECT role FROM admin WHERE id=?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    if (!$admin || $admin['role'] !== 'super') { http_response_code(403); echo '无权限'; exit; }
    $subPage = 'users';
    // ---- 分类 + 子分组配置（双侧栏） ----
    $pageToCat = [
        'dashboard'=>'overview','users'=>'users','links'=>'content',
        'comments'=>'content','reports'=>'content','settings'=>'system',
        'logs'=>'system','templates'=>'system','password'=>'account',
    ];
    $currentCat = $pageToCat[$subPage] ?? 'overview';
    $catConfig = [
        'overview'=>['icon'=>'fa-chart-pie','label'=>'概览','groups'=>[
            '概览' => [['dashboard','fa-home','控制台']],
        ]],
        'content'=>['icon'=>'fa-layer-group','label'=>'内容管理','groups'=>[
            '链接' => [['links','fa-link','链接审核']],
            '互动' => [['comments','fa-comments','评论管理'],['reports','fa-flag','举报管理']],
        ]],
        'users'=>['icon'=>'fa-users','label'=>'用户管理','groups'=>[
            '帐号' => [['users','fa-user','用户管理']],
        ]],
        'system'=>['icon'=>'fa-cog','label'=>'系统设置','groups'=>[
            '配置' => [['settings','fa-sliders-h','全站设置']],
            '运维' => [['logs','fa-history','操作日志'],['templates','fa-paint-roller','模版管理']],
        ]],
        'account'=>['icon'=>'fa-user-circle','label'=>'账户','groups'=>[
            '安全' => [['password','fa-key','修改密码']],
        ]],
    ];
    $currentCatGroups = $catConfig[$currentCat]['groups'] ?? [];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $sn = getSiteName($db ?? null); ?><title>用户管理 - <?=h($sn)?></title>
<link rel="stylesheet" href="../assets/css/tailwind.css">
<link rel="stylesheet" href="../assets/css/fontawesome.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root,.dark-mode{
  --admin-bg:#0f172a;--admin-text:#e2e8f0;--admin-text-primary:#fff;
  --admin-text-secondary:rgba(255,255,255,0.55);--admin-text-muted:rgba(255,255,255,0.5);
  --admin-text-gray:rgba(255,255,255,0.35);
  --admin-sidebar-bg:rgba(30,27,75,0.6);--admin-sidebar-border:rgba(255,255,255,0.06);
  --admin-card-bg:rgba(255,255,255,0.04);--admin-card-border:rgba(255,255,255,0.08);
  --admin-card-hover:rgba(255,255,255,0.06);--admin-scrollbar:rgba(255,255,255,0.15);
  --admin-hamburger-bg:rgba(30,27,75,0.8);--admin-hamburger-border:rgba(255,255,255,0.08);
  --admin-nav-text:rgba(255,255,255,0.55);
  --admin-nav-hover-bg:rgba(255,255,255,0.07);--admin-nav-hover-text:#fff;
  --admin-logo-text:#fff;--admin-logo-sub:rgba(255,255,255,0.35);
  --admin-input-bg:rgba(255,255,255,0.06);--admin-input-border:rgba(255,255,255,0.12);
  --admin-input-text:var(--admin-text);--admin-overlay:rgba(0,0,0,0.5);
}
.light-mode{
  --admin-bg:#f1f5f9;--admin-text:#334155;--admin-text-primary:#0f172a;
  --admin-text-secondary:#64748b;--admin-text-muted:#94a3b8;--admin-text-gray:#a0aec0;
  --admin-sidebar-bg:rgba(255,255,255,0.85);--admin-sidebar-border:rgba(0,0,0,0.08);
  --admin-card-bg:rgba(255,255,255,0.8);--admin-card-border:rgba(0,0,0,0.08);
  --admin-card-hover:rgba(255,255,255,0.95);--admin-scrollbar:rgba(0,0,0,0.12);
  --admin-hamburger-bg:rgba(255,255,255,0.9);--admin-hamburger-border:rgba(0,0,0,0.08);
  --admin-nav-text:#64748b;
  --admin-nav-hover-bg:rgba(0,0,0,0.05);--admin-nav-hover-text:#0f172a;
  --admin-logo-text:#0f172a;--admin-logo-sub:#94a3b8;
  --admin-input-bg:#fff;--admin-input-border:#d1d9e6;--admin-input-text:#1e293b;
  --admin-overlay:rgba(0,0,0,0.3);
}
body{background:var(--admin-bg);color:var(--admin-text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;min-height:100vh;transition:background 0.3s,color 0.3s}
.primary-sidebar{width:72px;background:var(--admin-sidebar-bg);backdrop-filter:blur(12px);border-right:1px solid var(--admin-sidebar-border);display:flex;flex-direction:column;align-items:center;position:fixed;top:0;left:0;bottom:0;z-index:51;padding:16px 0;gap:4px}
.primary-sidebar .ps-logo{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px;margin-bottom:16px;flex-shrink:0}
.p-nav-item{width:52px;height:52px;border-radius:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;color:var(--admin-nav-text);cursor:pointer;text-decoration:none;transition:all 0.2s;font-size:10px;flex-shrink:0}
.p-nav-item i{font-size:20px}.p-nav-item .p-label{font-size:9px;letter-spacing:0.5px;opacity:0.7}
.p-nav-item:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.p-nav-item.active{color:#818cf8;background:rgba(99,102,241,0.2)}
.p-nav-spacer{flex:1}.p-nav-bottom{padding-top:8px;border-top:1px solid var(--admin-sidebar-border);width:100%;display:flex;flex-direction:column;align-items:center;gap:4px}
.sidebar{width:200px;background:var(--admin-sidebar-bg);backdrop-filter:blur(12px);border-right:1px solid var(--admin-sidebar-border);display:flex;flex-direction:column;position:fixed;top:0;left:72px;bottom:0;z-index:50;transition:all 0.3s}
.sidebar-logo{padding:22px 20px;border-bottom:1px solid var(--admin-sidebar-border);display:flex;align-items:center;gap:12px;min-width:0}
.sidebar-logo .logo-text{min-width:0;flex:1}
.sidebar-logo .logo-text span{color:var(--admin-logo-text);font-size:16px;font-weight:700;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sidebar-logo .logo-text small{color:var(--admin-logo-sub);font-size:10px;letter-spacing:2px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sidebar .nav-section-title{padding:18px 16px 6px;font-size:10px;letter-spacing:2px;color:var(--admin-logo-sub);text-transform:uppercase;font-weight:600}
.sidebar .nav-subgroup-title{padding:10px 16px 2px;font-size:9px;letter-spacing:1.5px;color:var(--admin-text-gray);text-transform:uppercase;font-weight:500}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 16px;margin:2px 10px;border-radius:10px;color:var(--admin-nav-text);cursor:pointer;transition:all 0.2s;text-decoration:none;font-size:14px;overflow:hidden}
.nav-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-item:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.nav-item.active{color:var(--admin-nav-hover-text);background:rgba(99,102,241,0.2);font-weight:600}
.nav-item i{width:20px;text-align:center;flex-shrink:0}
.theme-toggle-sidebar{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;margin:2px 10px 6px;border-radius:10px;color:var(--admin-nav-text);cursor:pointer;transition:all 0.2s;font-size:13px;user-select:none}
.theme-toggle-sidebar:hover{color:var(--admin-nav-hover-text);background:var(--admin-nav-hover-bg)}
.theme-toggle-sidebar .tt-label{display:flex;align-items:center;gap:10px}
.theme-toggle-sidebar .tt-icon{width:20px;text-align:center;font-size:15px}
.theme-toggle-sidebar .tt-switch{width:38px;height:20px;border-radius:10px;background:var(--admin-card-border);position:relative;transition:background 0.3s;flex-shrink:0}
.theme-toggle-sidebar .tt-switch::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:var(--admin-logo-text);transition:transform 0.3s}
.light-mode .theme-toggle-sidebar .tt-switch::after{transform:translateX(18px)}
.hamburger{display:none;position:fixed;top:12px;left:12px;z-index:999;width:40px;height:40px;border-radius:10px;background:var(--admin-hamburger-bg);backdrop-filter:blur(8px);border:1px solid var(--admin-hamburger-border);color:var(--admin-nav-text);font-size:18px;cursor:pointer;align-items:center;justify-content:center;transition:all 0.2s}
.hamburger:hover{background:rgba(99,102,241,0.3);color:var(--admin-nav-hover-text)}
.sidebar-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:var(--admin-overlay);z-index:49;opacity:0;pointer-events:none;transition:opacity 0.3s}
.sidebar-overlay.show{opacity:1;pointer-events:auto}
.main{margin-left:272px;flex:1;min-height:100vh;padding:24px 32px;max-width:100%;min-width:0}
.search-bar{display:flex;gap:12px;flex-wrap:nowrap}.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:thin}
.table-responsive::-webkit-scrollbar{height:4px}
.table-responsive::-webkit-scrollbar-thumb{background:var(--admin-scrollbar);border-radius:4px}
.card-base{background:var(--admin-card-bg);border:1px solid var(--admin-card-border);border-radius:16px;padding:22px;margin-bottom:16px}
.text-white{color:var(--admin-text-primary)!important}
.text-gray-500{color:var(--admin-text-muted)!important}.text-gray-400{color:var(--admin-text-secondary)!important}
.bg-white\/5{background:var(--admin-card-bg)!important}
.border-white\/10{border-color:var(--admin-sidebar-border)!important}
.hover\:bg-white\/5:hover{background:var(--admin-card-hover)!important}
input[type="text"],input[type="search"]{background:var(--admin-input-bg)!important;color:var(--admin-input-text)!important;border-color:var(--admin-input-border)!important}
input::placeholder{color:var(--admin-text-muted)!important}
@media(max-width:900px){
  .primary-sidebar{display:none}
  .sidebar{left:0;transform:translateX(-110%)}
  .sidebar.open{transform:translateX(0);box-shadow:4px 0 30px rgba(0,0,0,0.4)}
  .main{margin-left:0;padding:16px;padding-top:60px}
  .hamburger{display:flex}
  .search-bar{flex-wrap:wrap}
  .search-bar input{flex:1 1 100%!important;min-width:0}
  .table-responsive table td,.table-responsive table th{padding:10px 8px!important;white-space:nowrap}
}
@media(max-width:480px){
  .main{padding:12px;padding-top:56px}
}
</style>
</head>
<body>
<button class="hamburger" id="menuToggle" onclick="toggleSidebar()" aria-label="菜单"><i class="fas fa-bars"></i></button>
<?php
$catOrder = ['overview','content','users','system','account'];
$firstPages = ['overview'=>'dashboard','content'=>'links','users'=>'users','system'=>'settings','account'=>'password'];
?>
<div class="primary-sidebar">
  <div class="ps-logo">L</div>
<?php foreach ($catOrder as $ck):
  $first = $firstPages[$ck];
  $label = $catConfig[$ck]['label'] ?? '';
  $icon = $catConfig[$ck]['icon'] ?? 'fa-circle';
?>
    <a href="?page=<?=$first?>" class="p-nav-item <?=$currentCat==$ck?'active':''?>" title="<?=h($label)?>">
      <i class="fas <?=$icon?>"></i><span class="p-label"><?=h($label)?></span>
    </a>
<?php endforeach; ?>
  <div class="p-nav-spacer"></div>
  <div class="p-nav-bottom">
    <a href="./logout.php" class="p-nav-item" title="退出登录" style="color:#ef4444">
      <i class="fas fa-sign-out-alt"></i><span class="p-label">退出</span>
    </a>
  </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
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
      <i class="fas <?=$icon?>"></i><span><?=h($label)?></span>
    </a>
<?php endforeach; endforeach; ?>
  </nav>
  <div class="p-4 border-t border-white/10">
    <div class="flex items-center gap-3 mb-3 px-2">
      <div class="w-8 h-8 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-sm"><?=h(mb_substr($_SESSION['admin_name'],0,1))?></div>
      <div class="min-w-0 flex-1"><div class="text-sm text-white font-medium truncate"><?=h($_SESSION['admin_name'])?></div><div class="text-xs text-gray-500">管理员</div></div>
    </div>
    <div class="theme-toggle-sidebar" onclick="toggleAdminTheme()">
      <span class="tt-label"><span class="tt-icon" id="adminThemeIcon"><i class="fas fa-moon"></i></span>黑夜模式</span>
      <span class="tt-switch"></span>
    </div>
    <div class="mt-3 px-2 pt-3 border-t border-white/5 text-[11px] text-gray-500">v1.0 · Developed by shadow</div>
  </div>
</div>
<div class="main">
<?php } // end standalone wrapper
?>

<?php
/**
 * 管理员 - 用户管理（含主页链接一键打开）
 */
$page = max(1, (int)($_GET['p'] ?? 1));
$search = trim($_GET['search'] ?? '');
$perPage = 15;

$where = '1=1'; $params = [];
if ($search) { $where .= " AND (username LIKE ? OR nickname LIKE ?)"; $params = ["%$search%", "%$search%"]; }

$pagi = paginate($db, 'users', $where, $params, $page, $perPage);
$users = $db->prepare("SELECT * FROM users WHERE $where ORDER BY id DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$users->execute($params);
$userList = $users->fetchAll();

// 处理操作
$actionMsg = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    // ★ 新增用户不需要 user_id，优先处理，避免被 uid 校验拦截 ★
    if (($_POST['action'] ?? '') === 'add_user') {
            $newUsername = trim($_POST['username'] ?? '');
            $newPassword = $_POST['password'] ?? '';
            $newNickname = trim($_POST['nickname'] ?? '');
            $newEmail    = trim($_POST['email'] ?? '');
            
            if (empty($newUsername) || empty($newPassword)) {
                $actionMsg = '用户名和密码不能为空';
            } elseif (strlen($newUsername) < 3 || strlen($newUsername) > 20) {
                $actionMsg = '用户名长度需3-20个字符';
            } elseif (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u', $newUsername)) {
                $actionMsg = '用户名只能包含字母、数字、下划线或中文';
            } else {
                // 检查用户名是否已存在
                $check = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
                $check->execute([$newUsername]);
                if ($check->fetch()) {
                    $actionMsg = '用户名已存在';
                } else {
                    // 检查邮箱是否已存在
                    if ($newEmail) {
                        $checkEmail = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                        $checkEmail->execute([$newEmail]);
                        if ($checkEmail->fetch()) {
                            $actionMsg = '邮箱已被使用';
                            $addError = true;
                        }
                    }
                    if (empty($addError)) {
                        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO users (username, password, nickname, email, email_verified, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
                        $stmt->execute([$newUsername, $hash, $newNickname ?: $newUsername, $newEmail ?: '', $newEmail ? 0 : 0]);
                        $newUid = $db->lastInsertId();
                        adminLog($db, '新增用户', 'user', (int)$newUid, "用户名: $newUsername, 昵称: $newNickname");
                        $actionMsg = "用户 <strong>$newUsername</strong> 创建成功！";
                    }
                }
            }
    }
    // ★ 其他操作需要校验 user_id ★
    else {
        $uid = (int)($_POST['user_id'] ?? 0);
        if (!$uid) { $actionMsg = '参数错误'; }
        else {
            if (in_array($_POST['action'] ?? '', ['toggle_likes','toggle_comments','toggle_favorites'])) {
                $field = ($_POST['action'] ?? '') === 'toggle_likes' ? 'enable_likes' : (($_POST['action'] ?? '') === 'toggle_comments' ? 'enable_comments' : 'enable_favorites');
                $stmt = $db->prepare("UPDATE users SET $field = CASE WHEN $field=1 THEN 0 ELSE 1 END WHERE id=?");
                $stmt->execute([$uid]);
                $newVal = $db->query("SELECT $field FROM users WHERE id=$uid")->fetchColumn();
                $label = ['enable_likes'=>'点赞','enable_comments'=>'评论','enable_favorites'=>'收藏'][$field];
                adminLog($db, "切换互动:$label", 'user', $uid, "用户ID:$uid → ".($newVal?'开启':'关闭'));
                $actionMsg = "$label 已".($newVal?'开启':'关闭');
            } elseif (($_POST['action'] ?? '') === 'toggle_audit') {
                $stmt = $db->prepare("UPDATE users SET comment_audit_enabled = CASE WHEN comment_audit_enabled=1 THEN 0 ELSE 1 END WHERE id=?");
                $stmt->execute([$uid]);
                $newVal = $db->query("SELECT comment_audit_enabled FROM users WHERE id=$uid")->fetchColumn();
                adminLog($db, '切换评论审核', 'user', $uid, "用户ID:$uid → ".($newVal?'开启':'关闭'));
                $actionMsg = '评论审核已'.($newVal?'开启':'关闭');
            } elseif (($_POST['action'] ?? '') === 'toggle') {
                $stmt = $db->prepare("UPDATE users SET is_active = CASE WHEN is_active=1 THEN 0 ELSE 1 END WHERE id=?");
                $stmt->execute([$uid]);
                $newStatus = $db->query("SELECT is_active FROM users WHERE id=$uid")->fetchColumn();
                adminLog($db, $newStatus?'解封用户':'封禁用户', 'user', $uid, "用户ID:$uid → ".($newStatus?'正常':'封禁'));
                $actionMsg = '操作成功';
            } elseif (($_POST['action'] ?? '') === 'reset_pwd') {
                $newPwd = substr(bin2hex(random_bytes(4)), 0, 8);
                $hash = password_hash($newPwd, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->execute([$hash, $uid]);
                adminLog($db, '重置用户密码', 'user', $uid, "密码重置为: $newPwd");
                $actionMsg = "密码已重置为: $newPwd";
            } elseif (($_POST['action'] ?? '') === 'delete') {
                $stmt = $db->prepare("DELETE FROM users WHERE id=?");
                $stmt->execute([$uid]);
                adminLog($db, '删除用户', 'user', $uid, "删除用户ID:$uid");
                $actionMsg = '用户已删除及所有相关数据';
            }
        }
    }
}
?>

<h1 class="text-2xl font-bold mb-2">用户管理</h1>
<p class="text-gray-500 mb-6">管理所有注册用户 · 可一键打开用户主页</p>

<?php if ($actionMsg): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($actionMsg)?></div>
<?php endif; ?>

<div class="flex items-center gap-3 mb-6 flex-wrap">
<form method="GET" class="search-bar flex-1 min-w-[200px]">
  <input type="hidden" name="page" value="users">
  <input type="text" name="search" value="<?=h($search)?>" placeholder="搜索用户名/昵称..." class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500/50">
  <button type="submit" class="bg-indigo-500/20 text-indigo-300 px-5 py-2.5 rounded-xl text-sm hover:bg-indigo-500/30 transition whitespace-nowrap">搜索</button>
  <?php if ($search): ?><a href="?page=users" class="bg-white/5 text-gray-400 px-4 py-2.5 rounded-xl text-sm hover:bg-white/10 transition whitespace-nowrap">清除</a><?php endif; ?>
</form>
<button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="bg-emerald-500/20 text-emerald-300 px-5 py-2.5 rounded-xl text-sm hover:bg-emerald-500/30 transition whitespace-nowrap flex items-center gap-2"><i class="fas fa-plus"></i> 新增用户</button>
</div>

<!-- 新增用户弹窗 -->
<div id="addUserModal" class="hidden fixed inset-0 z-[999] flex items-center justify-center" style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px)">
  <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 w-full max-w-md mx-4 shadow-2xl">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-lg font-bold text-white"><i class="fas fa-user-plus text-emerald-400 mr-2"></i>新增用户</h3>
      <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-gray-400 hover:text-white text-xl leading-none">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_user">
      <div class="mb-4">
        <label class="block text-gray-300 text-sm font-medium mb-1.5">用户名 <span class="text-red-400">*</span></label>
        <input type="text" name="username" required minlength="3" maxlength="20" placeholder="3-20个字符" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500/50">
      </div>
      <div class="mb-4">
        <label class="block text-gray-300 text-sm font-medium mb-1.5">密码 <span class="text-red-400">*</span></label>
        <input type="text" name="password" required minlength="6" placeholder="至少6位" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500/50">
      </div>
      <div class="mb-4">
        <label class="block text-gray-300 text-sm font-medium mb-1.5">昵称</label>
        <input type="text" name="nickname" placeholder="默认同用户名" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500/50">
      </div>
      <div class="mb-5">
        <label class="block text-gray-300 text-sm font-medium mb-1.5">邮箱</label>
        <input type="email" name="email" placeholder="可选" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500/50">
      </div>
      <button type="submit" class="w-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-5 py-2.5 rounded-xl text-sm hover:bg-emerald-500/30 transition font-medium">确认创建</button>
    </form>
  </div>
</div>

<div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
<div class="table-responsive">
<table class="w-full text-sm">
<thead><tr class="border-b border-white/10 text-gray-400">
  <th class="text-left p-4">ID</th>
  <th class="text-left p-4">用户</th>
  <th class="text-left p-4">主页链接</th>
  <th class="text-left p-4">邮箱</th>
  <th class="text-left p-4">状态</th>
  <th class="text-left p-4">互动</th>
  <th class="text-left p-4">链接数</th>
  <th class="text-left p-4">注册时间</th>
  <th class="text-left p-4">操作</th>
</tr></thead>
<tbody>
<?php if (empty($userList)): ?>
<tr><td colspan="9" class="p-8 text-center text-gray-500">暂无数据</td></tr>
<?php else: ?>
<?php foreach ($userList as $u): 
  $linkCount = (int)$db->query("SELECT COUNT(*) FROM links WHERE user_id={$u['id']}")->fetchColumn();
  $suffix = $u['suffix'] ?? '';
  $homeUrl = $suffix ? BASE_URL . '/' . urlencode($suffix) : '#';
?>
<tr class="border-b border-white/5 hover:bg-white/5 transition">
  <td class="p-4 text-gray-400 whitespace-nowrap">#<?=$u['id']?></td>
  <td class="p-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold"><?=h(mb_substr($u['nickname']?:$u['username'],0,1))?></div>
      <div><div class="text-white font-medium"><?=h($u['nickname']?:'未设置昵称')?></div><div class="text-gray-500 text-xs">@<?=h($u['username'])?></div></div>
    </div>
  </td>
  <td class="p-4">
    <?php if ($suffix): ?>
      <div class="flex items-center gap-2">
        <a href="<?=h($homeUrl)?>" target="_blank" class="text-indigo-400 hover:text-indigo-300 text-xs underline truncate max-w-[150px] block" title="用户主页"><?=h(BASE_URL)?>/<?=h($suffix)?></a>
        <a href="<?=h($homeUrl)?>" target="_blank" class="bg-indigo-500/15 text-indigo-300 hover:bg-indigo-500/25 w-7 h-7 rounded-lg flex items-center justify-center text-xs transition flex-shrink-0" title="打开主页"><i class="fas fa-external-link-alt"></i></a>
      </div>
    <?php else: ?>
      <span class="text-gray-500 text-xs">未设置</span>
    <?php endif; ?>
  </td>
  <td class="p-4 text-gray-400 text-xs"><?=h($u['email']??'')?><?php if(!empty($u['email'])):?><?=($u['email_verified']??0)?' <span class="text-emerald-400"><i class="fas fa-check" style="color:#10b981"></i></span>':' <span class="text-yellow-400">待验证</span>'?><?php endif;?></td>
  <td class="p-4"><?=($u['is_active']??1)?'<span class="text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full text-xs">正常</span>':'<span class="text-red-400 bg-red-500/10 px-2.5 py-1 rounded-full text-xs">已封禁</span>'?></td>
  <td class="p-4">
    <div class="flex gap-1">
      <?php
      $interactItems = [
        'likes' => ['field'=>'enable_likes','icon'=>'赞','on_color'=>'text-red-300 bg-red-500/15','off_color'=>'text-gray-500 bg-white/5'],
        'comments' => ['field'=>'enable_comments','icon'=>'评','on_color'=>'text-blue-300 bg-blue-500/15','off_color'=>'text-gray-500 bg-white/5'],
        'favorites' => ['field'=>'enable_favorites','icon'=>'藏','on_color'=>'text-yellow-300 bg-yellow-500/15','off_color'=>'text-gray-500 bg-white/5'],
      ];
      foreach ($interactItems as $key => $item):
        $enabled = $u[$item['field']] ?? 1;
      ?>
      <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="toggle_<?=$key?>">
        <input type="hidden" name="user_id" value="<?=$u['id']?>">
        <button type="submit" class="px-2 py-1 rounded-lg text-xs transition <?=$enabled?$item['on_color'] ?? '#10b981':$item['off_color'] ?? '#6b7280'?>" title="<?=$enabled?'点击关闭':'点击开启'?> <?=$key=='likes'?'点赞':($key=='comments'?'评论':'收藏')?>"><?=$item['icon'] ?? ''?></button>
      </form>
      <?php endforeach; ?>
      <!-- 审核开关 -->
      <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="toggle_audit">
        <input type="hidden" name="user_id" value="<?=$u['id']?>">
        <?php $auditOn = $u['comment_audit_enabled'] ?? 0; ?>
        <button type="submit" class="px-2 py-1 rounded-lg text-xs transition <?=$auditOn?'text-purple-300 bg-purple-500/15':'text-gray-500 bg-white/5'?>" title="<?=$auditOn?'点击关闭':'点击开启'?>评论审核">审</button>
      </form>
    </div>
  </td>
  <td class="p-4 text-gray-300"><?=$linkCount?></td>
  <td class="p-4 text-gray-400 text-xs whitespace-nowrap"><?=$u['created_at']?></td>
  <td class="p-4">
    <div class="flex gap-2 flex-wrap">
      <form method="POST" style="display:inline" onsubmit="return confirm('确认<?=($u['is_active']??1)?'封禁':'解封'?>此用户？')">
        <input type="hidden" name="action" value="toggle">
        <input type="hidden" name="user_id" value="<?=$u['id']?>">
        <button type="submit" class="bg-<?=($u['is_active']??1)?'yellow':'emerald'?>-500/10 text-<?=($u['is_active']??1)?'yellow':'emerald'?>-300 px-3 py-1.5 rounded-lg text-xs hover:bg-<?=($u['is_active']??1)?'yellow':'emerald'?>-500/20 transition"><?=($u['is_active']??1)?'封禁':'解封'?></button>
      </form>
      <a href="impersonate.php?id=<?=$u['id']?>" class="inline-block bg-indigo-500/10 text-indigo-300 px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-500/20 transition" title="以该用户身份登录其后台"><i class="fas fa-user-shield"></i> 一键登录</a>
      <form method="POST" style="display:inline" onsubmit="return confirm('确认重置此用户密码？')">
        <input type="hidden" name="action" value="reset_pwd">
        <input type="hidden" name="user_id" value="<?=$u['id']?>">
        <button type="submit" class="bg-blue-500/10 text-blue-300 px-3 py-1.5 rounded-lg text-xs hover:bg-blue-500/20 transition">重置密码</button>
      </form>
      <form method="POST" style="display:inline" onsubmit="return confirm('⚠️ 确认删除此用户及所有数据？此操作不可恢复！')">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="user_id" value="<?=$u['id']?>">
        <button type="submit" class="bg-red-500/10 text-red-300 px-3 py-1.5 rounded-lg text-xs hover:bg-red-500/20 transition">删除</button>
      </form>
    </div>
  </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<?php if ($pagi['lastPage'] > 1): ?>
<div class="flex justify-center gap-2 mt-6">
  <?php for ($i=1; $i<=$pagi['lastPage']; $i++): ?>
  <a href="?page=users&p=<?=$i?><?=$search?'&search='.urlencode($search):''?>" class="px-4 py-2 rounded-xl text-sm transition <?=$i==$pagi['page']?'bg-indigo-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>


<?php if ($isStandalone): ?>
</div><!-- .main -->
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('show')}
document.querySelectorAll('.sidebar nav a').forEach(function(a){a.addEventListener('click',function(){if(window.innerWidth<=900)toggleSidebar()})});
(function(){var t=localStorage.getItem('admin_theme');if(t==='light')document.body.classList.remove('dark-mode'),document.body.classList.add('light-mode');})();
function toggleAdminTheme(){var b=document.body;b.classList.toggle('light-mode');b.classList.toggle('dark-mode');localStorage.setItem('admin_theme',b.classList.contains('light-mode')?'light':'dark');var i=document.getElementById('adminThemeIcon');i.innerHTML=b.classList.contains('light-mode')?'<i class="fas fa-sun"></i>':'<i class="fas fa-moon"></i>';}
</script>
</body>
</html>
<?php endif; ?>