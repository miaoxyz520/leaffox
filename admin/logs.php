<?php
/**
 * 管理员 - 操作日志
 */
require_once __DIR__ . '/../config.php';
requireAdmin();

$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 30;

$pagi = paginate($db, 'admin_logs', '1=1', [], $page, $perPage);
$logs = $db->prepare("SELECT l.*, a.username as admin_name FROM admin_logs l LEFT JOIN admin a ON l.admin_id = a.id ORDER BY l.id DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$logs->execute();
$logList = $logs->fetchAll();
?>
<h1 class="text-2xl font-bold mb-2">操作日志</h1>
<p class="text-gray-500 mb-6">管理员操作记录</p>

<div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
<div class="table-responsive">
<table class="w-full text-sm">
<thead><tr class="border-b border-white/10 text-gray-400">
  <th class="text-left p-4">时间</th><th class="text-left p-4">管理员</th><th class="text-left p-4">操作</th><th class="text-left p-4">对象</th><th class="text-left p-4">详情</th><th class="text-left p-4">IP</th>
</tr></thead>
<tbody>
<?php if (empty($logList)): ?>
<tr><td colspan="6" class="p-8 text-center text-gray-500">暂无操作日志</td></tr>
<?php else: ?>
<?php foreach ($logList as $log): ?>
<tr class="border-b border-white/5 hover:bg-white/5 transition">
  <td class="p-4 text-gray-400 text-xs whitespace-nowrap"><?=$log['created_at']?></td>
  <td class="p-4 text-indigo-300"><?=h($log['admin_name']?:'未知')?></td>
  <td class="p-4 text-white"><?=h($log['action'])?></td>
  <td class="p-4 text-gray-400"><?=$log['target_type']?$log['target_type'].' #'.$log['target_id']:'-'?></td>
  <td class="p-4 text-gray-400 text-xs max-w-[200px] truncate"><?=h($log['detail']??'-')?></td>
  <td class="p-4 text-gray-500 text-xs"><?=$log['ip']?></td>
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
  <a href="?page=logs&p=<?=$i?>" class="px-4 py-2 rounded-xl text-sm transition <?=$i==$pagi['page']?'bg-indigo-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
