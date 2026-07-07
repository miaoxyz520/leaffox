<?php
/**
 * 用户 - 数据统计
 */
$uid = (int)$_SESSION['user_id'];

// 近7天访问趋势
$views7d = $db->prepare("SELECT DATE(created_at) as d, COUNT(*) as cnt FROM stats WHERE user_id=? AND type='view' AND created_at >= ? GROUP BY DATE(created_at) ORDER BY d");
$views7d->execute([$uid, date('Y-m-d H:i:s', strtotime('-7 days'))]);
$viewsData = $views7d->fetchAll();
$vLabels = []; $vData = [];
for ($i=6; $i>=0; $i--) { $day = date('Y-m-d', strtotime("-$i days")); $vLabels[] = substr($day,5); $vData[$day] = 0; }
foreach ($viewsData as $r) { $vData[$r['d']] = (int)$r['cnt']; }
$vFinal = array_values($vData);

// 各链接点击排行
$topLinks = $db->prepare("SELECT l.id, l.title, l.icon, l.click_count, (SELECT COUNT(*) FROM stats s WHERE s.link_id=l.id AND s.type='click') as real_clicks FROM links l WHERE l.user_id=? ORDER BY real_clicks DESC LIMIT 10");
$topLinks->execute([$uid]);
$topLinksData = $topLinks->fetchAll();

// 总览数据
$totalViews  = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$uid AND type='view'")->fetchColumn();
$totalClicks = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$uid AND type='click'")->fetchColumn();
$todayViews  = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$uid AND type='view' AND DATE(created_at)='" . date('Y-m-d') . "'")->fetchColumn();
$todayClicks = (int)$db->query("SELECT COUNT(*) FROM stats WHERE user_id=$uid AND type='click' AND DATE(created_at)='" . date('Y-m-d') . "'")->fetchColumn();
?>
<h1 class="text-xl font-bold mb-2">数据统计</h1>
<p class="text-gray-500 text-sm mb-6">你的主页访问和链接点击数据</p>

<div class="stats-grid mb-6">
  <div class="stat-card"><div class="stat-value"><?=$totalViews?></div><div class="stat-label">总访问量</div></div>
  <div class="stat-card"><div class="stat-value"><?=$totalClicks?></div><div class="stat-label">总点击量</div></div>
  <div class="stat-card"><div class="stat-value"><?=$todayViews?></div><div class="stat-label">今日访问</div></div>
  <div class="stat-card"><div class="stat-value"><?=$todayClicks?></div><div class="stat-label">今日点击</div></div>
</div>

<div class="chart-row grid grid-cols-1 md:grid-cols-2 gap-5">
  <!-- 7天访问趋势 -->
  <div class="card-base">
    <h3><i class="fas fa-chart-line text-indigo-400"></i> 近7天访问趋势</h3>
    <?php $maxV = max($vFinal) ?: 1; ?>
    <div class="flex items-end gap-2 h-32 pt-4">
      <?php foreach ($vFinal as $i=>$v): ?>
      <div class="flex-1 flex flex-col items-center justify-end h-full">
        <span class="text-xs text-gray-400 mb-1"><?=$v?></span>
        <div class="w-full max-w-[32px] rounded-t-lg bg-gradient-to-t from-indigo-500 to-purple-500 transition-all" style="height:<?=max(6,($v/$maxV)*110)?>px"></div>
        <span class="text-xs text-gray-500 mt-2"><?=$vLabels[$i]?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 链接点击排行 -->
  <div class="card-base">
    <h3><i class="fas fa-trophy text-yellow-400"></i> 链接点击排行</h3>
    <?php if (empty($topLinksData)): ?>
      <p class="text-gray-500 text-sm text-center py-8">暂无数据，添加链接后自动统计</p>
    <?php else: ?>
    <div class="space-y-2">
      <?php foreach ($topLinksData as $i=>$l): ?>
      <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
        <span class="text-sm"><?=($i==0?'🥇':($i==1?'🥈':($i==2?'🥉':'#'.($i+1))))?></span>
        <span class="text-lg"><?=h($l['icon'])?></span>
        <span class="flex-1 text-sm text-white truncate"><?=h($l['title'])?></span>
        <span class="text-indigo-400 text-sm font-bold"><?=$l['real_clicks']?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
