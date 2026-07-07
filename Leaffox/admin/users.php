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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $uid = (int)($_POST['user_id'] ?? 0);
    if (!$uid) { $actionMsg = '参数错误'; }
    else {
        if ($_POST['action'] === 'toggle') {
            $stmt = $db->prepare("UPDATE users SET is_active = CASE WHEN is_active=1 THEN 0 ELSE 1 END WHERE id=?");
            $stmt->execute([$uid]);
            $newStatus = $db->query("SELECT is_active FROM users WHERE id=$uid")->fetchColumn();
            adminLog($db, $newStatus?'解封用户':'封禁用户', 'user', $uid, "用户ID:$uid → ".($newStatus?'正常':'封禁'));
            $actionMsg = '操作成功';
        } elseif ($_POST['action'] === 'reset_pwd') {
            $newPwd = substr(bin2hex(random_bytes(4)), 0, 8);
            $hash = password_hash($newPwd, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$hash, $uid]);
            adminLog($db, '重置用户密码', 'user', $uid, "密码重置为: $newPwd");
            $actionMsg = "密码已重置为: $newPwd";
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $db->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$uid]);
            adminLog($db, '删除用户', 'user', $uid, "删除用户ID:$uid");
            $actionMsg = '用户已删除及所有相关数据';
        }
    }
}
?>
<h1 class="text-2xl font-bold mb-2">用户管理</h1>
<p class="text-gray-500 mb-6">管理所有注册用户 · 可一键打开用户主页</p>

<?php if ($actionMsg): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($actionMsg)?></div>
<?php endif; ?>

<form method="GET" class="search-bar mb-6">
  <input type="hidden" name="page" value="users">
  <input type="text" name="search" value="<?=h($search)?>" placeholder="搜索用户名/昵称..." class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500/50">
  <button type="submit" class="bg-indigo-500/20 text-indigo-300 px-5 py-2.5 rounded-xl text-sm hover:bg-indigo-500/30 transition whitespace-nowrap">搜索</button>
  <?php if ($search): ?><a href="?page=users" class="bg-white/5 text-gray-400 px-4 py-2.5 rounded-xl text-sm hover:bg-white/10 transition whitespace-nowrap">清除</a><?php endif; ?>
</form>

<div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
<div class="table-responsive">
<table class="w-full text-sm">
<thead><tr class="border-b border-white/10 text-gray-400">
  <th class="text-left p-4">ID</th>
  <th class="text-left p-4">用户</th>
  <th class="text-left p-4">主页链接</th>
  <th class="text-left p-4">邮箱</th>
  <th class="text-left p-4">状态</th>
  <th class="text-left p-4">链接数</th>
  <th class="text-left p-4">注册时间</th>
  <th class="text-left p-4">操作</th>
</tr></thead>
<tbody>
<?php if (empty($userList)): ?>
<tr><td colspan="8" class="p-8 text-center text-gray-500">暂无数据</td></tr>
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
  <td class="p-4 text-gray-400 text-xs"><?=h($u['email']??'')?><?php if(!empty($u['email'])):?><?=($u['email_verified']??0)?' <span class="text-emerald-400">✓</span>':' <span class="text-yellow-400">待验证</span>'?><?php endif;?></td>
  <td class="p-4"><?=($u['is_active']??1)?'<span class="text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full text-xs">正常</span>':'<span class="text-red-400 bg-red-500/10 px-2.5 py-1 rounded-full text-xs">已封禁</span>'?></td>
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
