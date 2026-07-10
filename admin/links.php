<?php
/**
 * 管理员 - 链接审核（含预览、一键跳转）
 */
$page = max(1, (int)($_GET['p'] ?? 1));
$filter = $_GET['filter'] ?? 'all';
$perPage = 20;

$where = '1=1'; $params = [];
if ($filter === 'violation') { $where .= " AND l.is_violation = 1"; }

$pagi = paginate($db, 'links l', $where, $params, $page, $perPage);
$links = $db->prepare("SELECT l.*, u.username, u.nickname FROM links l LEFT JOIN users u ON l.user_id = u.id WHERE $where ORDER BY l.id DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$links->execute($params);
$linkList = $links->fetchAll();

// 处理违规操作
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $lid = (int)($_POST['link_id'] ?? 0);
    if (!$lid) { $msg = '参数错误'; }
    else {
        if (($_POST['action'] ?? '') === 'mark_violation') {
            $db->prepare("UPDATE links SET is_violation = 1 WHERE id=?")->execute([$lid]);
            adminLog($db, '标记违规链接', 'link', $lid);
            $msg = '已标记为违规并下架';
        } elseif (($_POST['action'] ?? '') === 'unmark_violation') {
            $db->prepare("UPDATE links SET is_violation = 0 WHERE id=?")->execute([$lid]);
            adminLog($db, '取消违规标记', 'link', $lid);
            $msg = '已取消违规标记';
        } elseif (($_POST['action'] ?? '') === 'delete_link') {
            $db->prepare("DELETE FROM links WHERE id=?")->execute([$lid]);
            adminLog($db, '删除违规链接', 'link', $lid);
            $msg = '链接已删除';
        }
    }
}

$typeLabels = ['link'=>'<i class="fas fa-link"></i>链接','text'=>'<i class="fas fa-edit"></i>文字','image'=>'<i class="fas fa-image"></i>弹图','picture'=>'<i class="fas fa-image"></i>图片','video'=>'<i class="fas fa-video"></i>视频'];
?>
<h1 class="text-2xl font-bold mb-2">链接审核</h1>
<p class="text-gray-500 mb-6">查看和管理所有用户创建的模块 · 可预览内容及一键跳转</p>

<?php if (isset($msg)): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($msg)?></div>
<?php endif; ?>

<div class="flex gap-3 mb-6 flex-wrap">
  <a href="?page=links&filter=all" class="px-4 py-2 rounded-xl text-sm transition <?=$filter==='all'?'bg-indigo-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>">全部</a>
  <a href="?page=links&filter=violation" class="px-4 py-2 rounded-xl text-sm transition <?=$filter==='violation'?'bg-red-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>">违规标记</a>
</div>

<div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
<div class="table-responsive">
<table class="w-full text-sm">
<thead><tr class="border-b border-white/10 text-gray-400">
  <th class="text-left p-4">ID</th>
  <th class="text-left p-4">用户</th>
  <th class="text-left p-4">类型</th>
  <th class="text-left p-4">内容</th>
  <th class="text-left p-4">预览</th>
  <th class="text-left p-4">状态</th>
  <th class="text-left p-4">点击</th>
  <th class="text-left p-4">操作</th>
</tr></thead>
<tbody>
<?php if (empty($linkList)): ?>
<tr><td colspan="8" class="p-8 text-center text-gray-500">暂无数据</td></tr>
<?php else: ?>
<?php foreach ($linkList as $l): 
  $type = $l['type'] ?? 'link';
  $label = $typeLabels[$type] ?? $type;
  // 获取可预览的图片/视频 URL
  $previewUrl = '';
  if ($type === 'picture') $previewUrl = $l['icon'];
  elseif ($type === 'image') $previewUrl = $l['popup_img'];
  elseif ($type === 'video') $previewUrl = $l['video_file'];
  // 获取可跳转的 URL
  $visitUrl = '';
  if ($type === 'link') $visitUrl = $l['url'];
  elseif ($type === 'video') $visitUrl = $l['video_file'];
  elseif ($type === 'picture') $visitUrl = $l['icon'];
  elseif ($type === 'image') $visitUrl = $l['popup_img'];
  $hasPreview = !empty($previewUrl);
  $hasVisit = !empty($visitUrl);
?>
<tr class="border-b border-white/5 hover:bg-white/5 transition">
  <td class="p-4 text-gray-400 whitespace-nowrap">#<?=$l['id']?></td>
  <td class="p-4"><span class="text-indigo-400"><?=h($l['nickname']?:$l['username'])?></span><span class="text-gray-500 text-xs"> @<?=h($l['username'])?></span></td>
  <td class="p-4 whitespace-nowrap">
    <?php
    $tc = '';
    if ($type === 'link') $tc = 'text-blue-300 bg-blue-500/10';
    elseif ($type === 'text') $tc = 'text-green-300 bg-green-500/10';
    elseif ($type === 'video') $tc = 'text-purple-300 bg-purple-500/10';
    elseif ($type === 'picture') $tc = 'text-pink-300 bg-pink-500/10';
    elseif ($type === 'image') $tc = 'text-orange-300 bg-orange-500/10';
    ?>
    <span class="text-xs px-2 py-0.5 rounded-full <?=$tc?>"><?=$label?></span>
  </td>
  <td class="p-4 max-w-[220px]">
    <?php if ($type === 'text'): ?>
      <div class="text-white text-xs leading-relaxed max-h-[60px] overflow-y-auto whitespace-pre-wrap break-words"><?=h($l['title'])?></div>
    <?php elseif ($type === 'picture' && !empty($l['icon'])): ?>
      <div class="flex items-center gap-2">
        <img src="<?=h($l['icon'])?>" class="w-10 h-10 rounded-lg object-cover bg-white/5" onerror="this.style.display='none'" alt="">
        <span class="text-white text-xs truncate"><?=h($l['title'] ?: '图片')?></span>
      </div>
    <?php elseif ($type === 'image' && !empty($l['popup_img'])): ?>
      <div class="flex items-center gap-2">
        <img src="<?=h($l['popup_img'])?>" class="w-10 h-10 rounded-lg object-cover bg-white/5" onerror="this.style.display='none'" alt="">
        <span class="text-white text-xs truncate"><?=h($l['title'] ?: '弹图')?></span>
      </div>
    <?php elseif ($type === 'video'): ?>
      <div class="flex items-center gap-2">
        <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center text-lg"><i class="fas fa-video"></i></div>
        <span class="text-white text-xs truncate"><?=h($l['title'] ?: '视频')?></span>
      </div>
    <?php else: ?>
      <div class="flex items-center gap-2">
        <span class="text-lg"><?=h(mb_substr($l['icon']?:'<i class="fas fa-link"></i>',0,2))?></span>
        <span class="text-white text-xs truncate"><?=h($l['title'] ?: '(无标题)')?></span>
      </div>
    <?php endif; ?>
  </td>
  <td class="p-4 whitespace-nowrap">
    <?php if ($hasPreview): ?>
      <button onclick="previewItem('<?=h($previewUrl)?>','<?=$type==='video'?'video':'image'?>')" class="bg-indigo-500/10 text-indigo-300 px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-500/20 transition">
        <i class="fas fa-eye"></i> 预览
      </button>
    <?php else: ?>
      <span class="text-gray-600 text-xs">-</span>
    <?php endif; ?>
  </td>
  <td class="p-4 whitespace-nowrap">
    <?php if ($l['is_violation']): ?><span class="text-red-400 bg-red-500/10 px-2 py-0.5 rounded text-xs">违规</span><?php endif; ?>
    <?php if ($l['is_hidden'] && !$l['is_violation']): ?><span class="text-gray-500 bg-white/5 px-2 py-0.5 rounded text-xs">隐藏</span><?php endif; ?>
    <?php if (!$l['is_hidden'] && !$l['is_violation']): ?><span class="text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded text-xs">正常</span><?php endif; ?>
    <?php if(!empty($l['passcode'])):?><span class="text-yellow-400 bg-yellow-500/10 px-2 py-0.5 rounded text-xs"><i class="fas fa-lock"></i></span><?php endif;?>
  </td>
  <td class="p-4 text-gray-300 whitespace-nowrap"><?=$l['click_count']?></td>
  <td class="p-4 whitespace-nowrap">
    <div class="flex flex-col gap-1.5">
      <?php if ($hasVisit): ?>
      <a href="<?=h($visitUrl)?>" target="_blank" class="bg-indigo-500/10 text-indigo-300 px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-500/20 transition text-center whitespace-nowrap inline-flex items-center gap-1 justify-center">
        <i class="fas fa-external-link-alt"></i> 访问
      </a>
      <?php endif; ?>
      <div class="flex gap-1">
        <?php if (!$l['is_violation']): ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('标记此链接为违规并下架？')">
          <input type="hidden" name="action" value="mark_violation"><input type="hidden" name="link_id" value="<?=$l['id']?>">
          <button class="bg-red-500/10 text-red-300 px-3 py-1.5 rounded-lg text-xs hover:bg-red-500/20 transition whitespace-nowrap"><i class="fas fa-ban"></i> 下架</button>
        </form>
        <?php else: ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('取消违规标记？')">
          <input type="hidden" name="action" value="unmark_violation"><input type="hidden" name="link_id" value="<?=$l['id']?>">
          <button class="bg-emerald-500/10 text-emerald-300 px-3 py-1.5 rounded-lg text-xs hover:bg-emerald-500/20 transition whitespace-nowrap"><i class="fas fa-undo"></i> 恢复</button>
        </form>
        <?php endif; ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('⚠️ 确认删除此链接？')">
          <input type="hidden" name="action" value="delete_link"><input type="hidden" name="link_id" value="<?=$l['id']?>">
          <button class="bg-red-500/10 text-red-300 px-3 py-1.5 rounded-lg text-xs hover:bg-red-500/20 transition whitespace-nowrap"><i class="fas fa-trash"></i> 删除</button>
        </form>
      </div>
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
  <a href="?page=links&p=<?=$i?>&filter=<?=$filter?>" class="px-4 py-2 rounded-xl text-sm transition <?=$i==$pagi['page']?'bg-indigo-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<!-- 预览弹窗 -->
<div id="previewModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.8);backdrop-filter:blur(10px);align-items:center;justify-content:center;" onclick="if(event.target===this)closePreview()">
  <div style="max-width:90vw;max-height:90vh;position:relative;" onclick="event.stopPropagation()">
    <span onclick="closePreview()" style="position:absolute;top:-36px;right:0;color:rgba(255,255,255,0.6);cursor:pointer;font-size:24px;z-index:10;">✕</span>
    <div id="previewBox" style="display:flex;align-items:center;justify-content:center;"></div>
  </div>
</div>

<script>
function previewItem(url, type) {
  var box = document.getElementById('previewBox');
  if (type === 'video') {
    box.innerHTML = '<video src="' + url.replace(/'/g,"%27") + '" controls autoplay style="max-width:85vw;max-height:85vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.5);"></video>';
  } else {
    box.innerHTML = '<img src="' + url.replace(/'/g,"%27") + '" style="max-width:85vw;max-height:85vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.5);object-fit:contain;" onerror="this.outerHTML='<div style=\\'color:#999;text-align:center;padding:40px;\\'><div style=\\'font-size:48px;margin-bottom:16px;\\'><i class="fas fa-times-circle" style="color:#ef4444"></i></div><p>图片加载失败</p></div>'">';
  }
  document.getElementById('previewModal').style.display = 'flex';
}
function closePreview() {
  document.getElementById('previewModal').style.display = 'none';
  document.getElementById('previewBox').innerHTML = '';
}
</script>
