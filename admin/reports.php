<?php
/**
 * 管理员 - 举报管理
 */

// ---- 处理标记操作 ----
$markMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($_POST['action'] === 'resolve' && $id) {
        $db->prepare("UPDATE reports SET status = 1 WHERE id = ?")->execute([$id]);
        adminLog($db, '处理举报 #'.$id, 'report', $id);
        $markMsg = '<i class="fas fa-check-circle" style="color:#10b981"></i> 举报 #'.$id.' 已标记为已处理';
    }
    if ($_POST['action'] === 'unresolve' && $id) {
        $db->prepare("UPDATE reports SET status = 0 WHERE id = ?")->execute([$id]);
        adminLog($db, '重开举报 #'.$id, 'report', $id);
        $markMsg = '<i class="fas fa-sync-alt"></i> 举报 #'.$id.' 已重开';
    }
    if ($_POST['action'] === 'delete' && $id) {
        $db->prepare("DELETE FROM reports WHERE id = ?")->execute([$id]);
        adminLog($db, '删除举报 #'.$id, 'report', $id);
        $markMsg = '<i class="fas fa-trash-alt"></i> 举报 #'.$id.' 已删除';
    }
}

// ---- 筛选参数 ----
$filter = $_GET['filter'] ?? 'all';
$where = ''; $params = [];
if ($filter === 'pending') { $where = 'WHERE r.status = 0'; }
elseif ($filter === 'resolved') { $where = 'WHERE r.status = 1'; }

$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$total = (int)$db->query("SELECT COUNT(*) FROM reports r $where")->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// 举报类型映射
$reportTypes = [
    'violation' => '违规内容',
    'spam' => '垃圾营销',
    'copyright' => '侵权投诉',
    'pornographic' => '色情低俗',
    'fraud' => '欺诈信息',
    'other' => '其他',
];

$reports = $db->query("
    SELECT r.*, u.username, u.nickname, u.avatar
    FROM reports r
    LEFT JOIN users u ON u.id = r.user_id
    $where
    ORDER BY r.created_at DESC
    LIMIT $perPage OFFSET $offset
")->fetchAll();
?>
<h1 class="text-2xl font-bold mb-1">举报管理</h1>
<p class="text-gray-500 mb-6">查看用户主页被举报的记录</p>

<?php if ($markMsg): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($markMsg)?></div>
<?php endif; ?>

<!-- 筛选标签 -->
<div class="flex items-center gap-2 mb-5 flex-wrap">
  <a href="?page=reports&filter=all" class="px-4 py-1.5 rounded-full text-sm transition <?=$filter==='all'?'bg-indigo-500/30 text-indigo-300 border border-indigo-500/40':'bg-white/5 text-gray-400 border border-white/10 hover:text-white'?>">全部 (<?=$total?>)</a>
  <?php
  $pendingCount = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 0")->fetchColumn();
  $resolvedCount = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 1")->fetchColumn();
  ?>
  <a href="?page=reports&filter=pending" class="px-4 py-1.5 rounded-full text-sm transition <?=$filter==='pending'?'bg-amber-500/30 text-amber-300 border border-amber-500/40':'bg-white/5 text-gray-400 border border-white/10 hover:text-white'?>">未处理 (<?=$pendingCount?>)</a>
  <a href="?page=reports&filter=resolved" class="px-4 py-1.5 rounded-full text-sm transition <?=$filter==='resolved'?'bg-emerald-500/30 text-emerald-300 border border-emerald-500/40':'bg-white/5 text-gray-400 border border-white/10 hover:text-white'?>">已处理 (<?=$resolvedCount?>)</a>
</div>

<?php if (empty($reports)): ?>
<div class="bg-white/5 border border-white/10 rounded-2xl p-10 text-center">
  <p class="text-gray-500">暂无举报记录 <i class="fas fa-celebration"></i></p>
</div>
<?php else: ?>
<div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-gray-500 text-left border-b border-white/10">
        <th class="pb-3 font-medium">ID</th>
        <th class="pb-3 font-medium">被举报用户</th>
        <th class="pb-3 font-medium">举报类型</th>
        <th class="pb-3 font-medium">原因说明</th>
        <th class="pb-3 font-medium">举报者IP</th>
        <th class="pb-3 font-medium">时间</th>
        <th class="pb-3 font-medium">状态</th>
        <th class="pb-3 font-medium">操作</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reports as $r): ?>
      <tr class="border-b border-white/5 hover:bg-white/[0.02]">
        <td class="py-3 text-gray-400">#<?=$r['id']?></td>
        <td class="py-3">
          <div class="flex items-center gap-2">
            <?php if ($r['avatar']): ?>
              <img src="<?=h($r['avatar'])?>" class="w-7 h-7 rounded-full object-cover bg-white/10">
            <?php else: ?>
              <div class="w-7 h-7 rounded-full bg-indigo-500/20 flex items-center justify-center text-xs text-indigo-300"><?=mb_substr(h($r['nickname']?:$r['username']),0,1)?></div>
            <?php endif; ?>
            <div>
              <span class="text-white"><?=h($r['nickname']?:$r['username'])?></span>
              <?php if ($r['username']): ?>
              <span class="text-gray-500 text-xs ml-1">@<?=h($r['username'])?></span>
              <?php endif; ?>
            </div>
          </div>
        </td>
        <td class="py-3">
          <?php
          $typeLabel = $reportTypes[$r['type']] ?? $r['type'];
          $typeColors = [
            'violation' => 'text-red-400 bg-red-500/10',
            'spam' => 'text-orange-400 bg-orange-500/10',
            'copyright' => 'text-blue-400 bg-blue-500/10',
            'pornographic' => 'text-pink-400 bg-pink-500/10',
            'fraud' => 'text-red-400 bg-red-500/10',
            'other' => 'text-gray-400 bg-white/10',
          ];
          $colorClass = $typeColors[$r['type']] ?? 'text-gray-400 bg-white/10';
          ?>
          <span class="px-2 py-0.5 rounded-full text-xs <?=$colorClass?>"><?=h($typeLabel)?></span>
        </td>
        <td class="py-3 text-gray-400 max-w-[200px] truncate"><?=h($r['reason'] ?? '-')?></td>
        <td class="py-3 text-gray-500 text-xs font-mono"><?=h($r['reporter_ip'])?></td>
        <td class="py-3 text-gray-400 text-xs whitespace-nowrap"><?=$r['created_at']?></td>
        <td class="py-3">
          <?php if ($r['status']): ?>
            <span class="text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full text-xs">已处理</span>
          <?php else: ?>
            <span class="text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full text-xs">未处理</span>
          <?php endif; ?>
        </td>
        <td class="py-3">
          <form method="POST" class="flex items-center gap-1" onsubmit="return confirm('确认操作？')">
            <input type="hidden" name="id" value="<?=$r['id']?>">
            <?php if ($r['status']): ?>
              <button type="submit" name="action" value="unresolve" class="px-2 py-1 text-xs rounded-lg bg-white/5 text-gray-400 hover:text-amber-300 hover:bg-amber-500/10 transition" title="标记为未处理"><i class="fas fa-repeat"></i> 重开</button>
            <?php else: ?>
              <button type="submit" name="action" value="resolve" class="px-2 py-1 text-xs rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition" title="标记为已处理"><i class="fas fa-check-circle" style="color:#10b981"></i> 处理</button>
            <?php endif; ?>
            <button type="submit" name="action" value="delete" class="px-2 py-1 text-xs rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition" title="删除"><i class="fas fa-trash-alt"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- 分页 -->
<?php if ($totalPages > 1): ?>
<div class="flex items-center justify-center gap-2 mt-6">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=reports&filter=<?=h($filter)?>&p=<?=$i?>" class="px-3 py-1.5 rounded-lg text-sm transition <?=$i===$page?'bg-indigo-500/30 text-indigo-300':'bg-white/5 text-gray-400 hover:text-white'?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
