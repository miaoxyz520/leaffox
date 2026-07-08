<?php
/**
 * 管理员 - 评论管理（列表/审核/隐藏/删除）
 */

// ---- 检测表结构 ----
$tableOk = false;
$needsMigration = false;
try {
    $testCols = $db->query("SHOW COLUMNS FROM page_comments")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('page_user_id', $testCols)) {
        $tableOk = true;
    } else {
        $needsMigration = true;
    }
} catch (Exception $e) {
    // 表可能不存在
    $needsMigration = true;
}

// ---- 如果表结构不对，显示友好提示而不是崩溃 ----
if (!$tableOk):
?>
<div class="mb-6">
  <h2 class="text-xl font-bold mb-1" style="color:var(--admin-text-primary)">评论管理</h2>
  <p style="color:var(--admin-text-secondary);font-size:13px">管理全站所有评论</p>
</div>

<div style="padding:40px 20px;text-align:center;border-radius:12px;border:1px solid var(--admin-card-border);background:var(--admin-card-bg)">
  <div style="font-size:48px;margin-bottom:16px"><i class="fas fa-database" style="color:#f59e0b"></i></div>
  <h3 style="color:var(--admin-text);font-size:18px;margin-bottom:8px">评论表需要迁移</h3>
  <p style="color:var(--admin-text-secondary);font-size:14px;margin-bottom:20px;max-width:400px;margin-left:auto;margin-right:auto">
    检测到 page_comments 表为旧版本结构，需要升级才能使用评论管理功能。
  </p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="../migrate_tables.php" target="_blank"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border-radius:10px;background:rgba(99,102,241,0.2);color:#818cf8;text-decoration:none;font-weight:600;font-size:14px">
      <i class="fas fa-wrench"></i> 运行迁移工具
    </a>
    <span style="color:var(--admin-text-muted);font-size:13px;display:flex;align-items:center">运行后刷新本页即可</span>
  </div>
</div>
<?php
return; // 不再往下执行
endif;

// ---- 表结构正常，正常执行 ----
$page = max(1, (int)($_GET['p'] ?? 1));
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$perPage = 20;

// 构建查询条件
$where = "1=1"; $params = [];
if ($statusFilter !== '' && $statusFilter !== 'all') {
    $where .= " AND c.status = ?";
    $params[] = (int)$statusFilter;
}
if ($search) {
    $where .= " AND (c.content LIKE ? OR u.username LIKE ? OR u.nickname LIKE ? OR pu.username LIKE ? OR pu.nickname LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s, $s, $s]);
}

// 分页
$stmt = $db->prepare("SELECT COUNT(*) FROM page_comments c WHERE $where");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

// 列表
$stmt = $db->prepare("
    SELECT c.*,
           u.username, u.nickname as visitor_nickname, u.avatar,
           pu.username as page_username, pu.nickname as page_nickname
    FROM page_comments c
    LEFT JOIN users u ON c.visitor_id = u.id
    LEFT JOIN users pu ON c.page_user_id = pu.id
    WHERE $where
    ORDER BY c.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$comments = $stmt->fetchAll();

// 各状态统计
$totalAll    = (int)$db->query("SELECT COUNT(*) FROM page_comments")->fetchColumn();
$totalPending = (int)$db->query("SELECT COUNT(*) FROM page_comments WHERE status = 0")->fetchColumn();
$totalVisible = (int)$db->query("SELECT COUNT(*) FROM page_comments WHERE status = 1")->fetchColumn();
$totalHidden  = (int)$db->query("SELECT COUNT(*) FROM page_comments WHERE status = 2")->fetchColumn();

$statusLabels = [0 => '待审核', 1 => '已通过', 2 => '已隐藏'];
$statusColors = [0 => '#f59e0b', 1 => '#22c55e', 2 => '#ef4444'];
?>
<div class="mb-6">
  <h2 class="text-xl font-bold mb-1" style="color:var(--admin-text-primary)">评论管理</h2>
  <p style="color:var(--admin-text-secondary);font-size:13px">管理全站所有评论，支持审核、隐藏、删除</p>
</div>

<!-- 状态标签导航 -->
<div class="flex flex-wrap gap-2 mb-4">
  <a href="?page=comments" class="px-3 py-1.5 rounded-lg text-xs font-semibold <?= $statusFilter==='' ? 'bg-indigo-500/20 text-indigo-300' : '' ?>" style="color:var(--admin-nav-text);background:var(--admin-card-bg);border:1px solid var(--admin-card-border)">全部 (<?=$totalAll?>)</a>
  <a href="?page=comments&status=0" class="px-3 py-1.5 rounded-lg text-xs font-semibold <?= $statusFilter==='0' ? 'bg-yellow-500/20 text-yellow-300' : '' ?>" style="color:var(--admin-nav-text);background:var(--admin-card-bg);border:1px solid var(--admin-card-border)"><i class="fas fa-hourglass-half"></i> 待审核 (<?=$totalPending?>)</a>
  <a href="?page=comments&status=1" class="px-3 py-1.5 rounded-lg text-xs font-semibold <?= $statusFilter==='1' ? 'bg-green-500/20 text-green-300' : '' ?>" style="color:var(--admin-nav-text);background:var(--admin-card-bg);border:1px solid var(--admin-card-border)"><i class="fas fa-check-circle" style="color:#10b981"></i> 已通过 (<?=$totalVisible?>)</a>
  <a href="?page=comments&status=2" class="px-3 py-1.5 rounded-lg text-xs font-semibold <?= $statusFilter==='2' ? 'bg-red-500/20 text-red-300' : '' ?>" style="color:var(--admin-nav-text);background:var(--admin-card-bg);border:1px solid var(--admin-card-border)"><i class="fas fa-eye-slash"></i> 已隐藏 (<?=$totalHidden?>)</a>
</div>

<!-- 搜索 -->
<form method="get" class="flex gap-2 mb-4">
  <input type="hidden" name="page" value="comments">
  <?php if ($statusFilter !== ''): ?>
  <input type="hidden" name="status" value="<?=h($statusFilter)?>">
  <?php endif; ?>
  <input type="text" name="search" placeholder="搜索评论内容、用户名…" value="<?=h($search)?>"
    class="flex-1 px-3 py-2 rounded-lg text-sm outline-none"
    style="background:var(--admin-input-bg);border:1px solid var(--admin-input-border);color:var(--admin-input-text)">
  <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold" style="background:rgba(99,102,241,0.2);color:#818cf8;border:none;cursor:pointer">搜索</button>
  <?php if ($search): ?>
  <a href="?page=comments<?=$statusFilter!==''?'&status='.h($statusFilter):''?>" class="px-3 py-2 rounded-lg text-sm" style="color:var(--admin-text-muted);background:var(--admin-card-bg);border:1px solid var(--admin-card-border);text-decoration:none">清除</a>
  <?php endif; ?>
</form>

<!-- 表格 -->
<div style="overflow-x:auto;border-radius:12px;border:1px solid var(--admin-card-border);background:var(--admin-card-bg)">
<table style="width:100%;border-collapse:collapse;font-size:13px">
<thead>
  <tr style="border-bottom:1px solid var(--admin-card-border);background:var(--admin-card-hover)">
    <th style="padding:10px 12px;text-align:left;color:var(--admin-text-muted);font-weight:600;white-space:nowrap">ID</th>
    <th style="padding:10px 12px;text-align:left;color:var(--admin-text-muted);font-weight:600;white-space:nowrap">评论者</th>
    <th style="padding:10px 12px;text-align:left;color:var(--admin-text-muted);font-weight:600;white-space:nowrap">所属主页</th>
    <th style="padding:10px 12px;text-align:left;color:var(--admin-text-muted);font-weight:600;white-space:nowrap">评论内容</th>
    <th style="padding:10px 12px;text-align:left;color:var(--admin-text-muted);font-weight:600;white-space:nowrap">状态</th>
    <th style="padding:10px 12px;text-align:left;color:var(--admin-text-muted);font-weight:600;white-space:nowrap">时间</th>
    <th style="padding:10px 12px;text-align:center;color:var(--admin-text-muted);font-weight:600;white-space:nowrap">操作</th>
  </tr>
</thead>
<tbody>
<?php if (empty($comments)): ?>
  <tr><td colspan="7" style="padding:40px;text-align:center;color:var(--admin-text-muted)">暂无评论</td></tr>
<?php else: ?>
  <?php foreach ($comments as $c):
    $visitorName = $c['visitor_nickname'] ?: $c['username'] ?: '（已注销）';
    $pageName = $c['page_nickname'] ?: $c['page_username'] ?: '（已注销）';
    $statusColor = $statusColors[$c['status']] ?? '#94a3b8';
    $statusLabel = $statusLabels[$c['status']] ?? '未知';
  ?>
  <tr style="border-bottom:1px solid var(--admin-card-border);transition:background 0.2s" onmouseover="this.style.background='var(--admin-card-hover)'" onmouseout="this.style.background='transparent'">
    <td style="padding:10px 12px;color:var(--admin-text-muted);white-space:nowrap">#<?=$c['id']?></td>
    <td style="padding:10px 12px">
      <div style="display:flex;align-items:center;gap:8px">
        <?php if ($c['avatar']): ?>
          <img src="<?=h($c['avatar'])?>" style="width:26px;height:26px;border-radius:50%;object-fit:cover">
        <?php else: ?>
          <div style="width:26px;height:26px;border-radius:50%;background:rgba(99,102,241,0.2);display:flex;align-items:center;justify-content:center;font-size:11px;color:#818cf8"><i class="fas fa-user"></i></div>
        <?php endif; ?>
        <span style="color:var(--admin-text)"><?=h($visitorName)?></span>
      </div>
    </td>
    <td style="padding:10px 12px;color:var(--admin-text)"><?=h($pageName)?></td>
    <td style="padding:10px 12px;max-width:300px;word-break:break-word">
      <span style="color:var(--admin-text)"><?=h(mb_substr($c['content'],0,80))?><?=mb_strlen($c['content'])>80?'…':''?></span>
    </td>
    <td style="padding:10px 12px">
      <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;color:#fff;background:<?=$statusColor?>"><?=$statusLabel?></span>
    </td>
    <td style="padding:10px 12px;color:var(--admin-text-muted);white-space:nowrap;font-size:12px"><?=date('m-d H:i',strtotime($c['created_at']))?></td>
    <td style="padding:10px 12px;text-align:center">
      <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap">
        <?php if ($c['status'] !== 1): ?>
        <button onclick="setCommentStatus(<?=$c['id']?>,1)" class="cmt-btn approve" title="通过"><i class="fas fa-check-circle" style="color:#10b981"></i></button>
        <?php endif; ?>
        <?php if ($c['status'] !== 2): ?>
        <button onclick="setCommentStatus(<?=$c['id']?>,2)" class="cmt-btn hide" title="隐藏"><i class="fas fa-eye-slash"></i></button>
        <?php endif; ?>
        <?php if ($c['status'] !== 0): ?>
        <button onclick="setCommentStatus(<?=$c['id']?>,0)" class="cmt-btn pending" title="标记为待审核"><i class="fas fa-hourglass-half"></i></button>
        <?php endif; ?>
        <button onclick="deleteComment(<?=$c['id']?>)" class="cmt-btn delete" title="删除"><i class="fas fa-trash-alt"></i></button>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>

<!-- 分页 -->
<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap">
  <?php for ($i=1; $i<=$totalPages; $i++): ?>
  <a href="?page=comments&p=<?=$i?><?=$statusFilter!==''?'&status='.h($statusFilter):''?><?=$search?'&search='.urlencode($search):''?>"
    style="padding:6px 14px;border-radius:8px;font-size:13px;text-decoration:none;<?=$i==$page?'background:rgba(99,102,241,0.25);color:#818cf8;font-weight:700':'color:var(--admin-nav-text);background:var(--admin-card-bg);border:1px solid var(--admin-card-border)'?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<style>
.cmt-btn{
  width:30px;height:30px;border-radius:8px;border:1px solid var(--admin-card-border);
  background:var(--admin-card-bg);cursor:pointer;font-size:14px;
  display:inline-flex;align-items:center;justify-content:center;
  transition:all 0.2s;
}
.cmt-btn:hover{transform:scale(1.15);background:var(--admin-card-hover)}
.cmt-btn.approve:hover{border-color:#22c55e;background:rgba(34,197,94,0.15)}
.cmt-btn.hide:hover{border-color:#ef4444;background:rgba(239,68,68,0.15)}
.cmt-btn.delete:hover{border-color:#dc2626;background:rgba(220,38,38,0.2)}
.cmt-btn.pending:hover{border-color:#f59e0b;background:rgba(245,158,11,0.15)}
</style>

<script>
function setCommentStatus(id, status){
  if(!confirm(status===1?'通过此评论？':status===2?'隐藏此评论（前端不显示）？':'标记为待审核？')) return;
  fetch('../api/interaction_comment.php?action=admin_set_status', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'id='+id+'&status='+status
  }).then(r=>r.json()).then(d=>{
    if(d.success) location.reload();
    else alert(d.message);
  });
}
function deleteComment(id){
  if(!confirm('确定永久删除此评论？不可恢复！')) return;
  fetch('../api/interaction_comment.php?action=admin_delete', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'id='+id
  }).then(r=>r.json()).then(d=>{
    if(d.success) location.reload();
    else alert(d.message);
  });
}
</script>
