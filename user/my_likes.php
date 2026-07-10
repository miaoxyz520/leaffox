<?php
/**
 * 用户后台 - 我的点赞（谁赞了我 & 我赞了谁）
 * Enhanced: avatar, homepage link, time, bidirectional
 */
function _userAvatar($u) {
    $initial = h(mb_substr($u['nickname'] ?: $u['username'], 0, 1));
    if (!empty($u['avatar'])) {
        $src = h($u['avatar']);
        return '<img src="' . $src . '" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">';
    }
    $colors = ['from-pink-500 to-rose-600', 'from-rose-500 to-red-600', 'from-fuchsia-500 to-pink-600'];
    $idx = crc32($u['id'] ?? 0) % count($colors);
    return '<div class="w-9 h-9 rounded-full bg-gradient-to-br ' . $colors[$idx] . ' flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">' . $initial . '</div>';
}
function _userHomeUrl($u) {
    $suffix = $u['suffix'] ?? '';
    return $suffix ? (BASE_URL . '/' . rawurlencode($suffix)) : (BASE_URL . '/page/index.php?id=' . ((int)$u['id']));
}
function _userLink($u, $label) {
    $url = _userHomeUrl($u);
    return '<a href="' . $url . '" target="_blank" rel="noopener" class="hover:text-indigo-400 transition">' . h($label) . '</a>';
}

$tab = $_GET['lt'] ?? 'to_me';
$lpage = max(1, (int)($_GET['lp'] ?? 1));
$li = max(1, (int)($_GET['li'] ?? 1));
$lperPage = 15;

if ($tab === 'to_me') {
    // 谁赞了我
    $totalLikes = (int)$db->query("SELECT COUNT(*) FROM page_likes WHERE page_user_id=$uid")->fetchColumn();
    $llastPage = max(1, ceil($totalLikes / $lperPage));
    $lpage = min($lpage, $llastPage);
    $loffset = ($lpage - 1) * $lperPage;
    $likes = $db->query("
        SELECT l.*, u.username, u.nickname, u.avatar, u.suffix
        FROM page_likes l
        LEFT JOIN users u ON l.visitor_id = u.id
        WHERE l.page_user_id = $uid
        ORDER BY l.created_at DESC
        LIMIT $lperPage OFFSET $loffset
    ")->fetchAll();
    $list = $likes;
    $total = $totalLikes;
    $curPage = $lpage;
    $lastPage = $llastPage;
    $pageParam = 'lp';
    $emptyMsg = '还没有人给你点赞';
    $emptyIcon = 'fa-heart';
    $title = '谁赞了我';
    $subTitle = "给你主页点赞的人（共 {$total} 个）";
    $actionIcon = '<div class="text-pink-400"><i class="fas fa-heart"></i></div>';
    $type = 'liked_me';
} else {
    // 我赞了谁
    $totalLikes = (int)$db->query("SELECT COUNT(*) FROM page_likes WHERE visitor_id=$uid")->fetchColumn();
    $llastPage = max(1, ceil($totalLikes / $lperPage));
    $li = min($li, $llastPage);
    $loffset = ($li - 1) * $lperPage;
    $likes = $db->query("
        SELECT l.*, u.username, u.nickname, u.avatar, u.suffix
        FROM page_likes l
        LEFT JOIN users u ON l.page_user_id = u.id
        WHERE l.visitor_id = $uid
        ORDER BY l.created_at DESC
        LIMIT $lperPage OFFSET $loffset
    ")->fetchAll();
    $list = $likes;
    $total = $totalLikes;
    $curPage = $li;
    $lastPage = $llastPage;
    $pageParam = 'li';
    $emptyMsg = '你还没有给任何人点赞';
    $emptyIcon = 'fa-heart';
    $title = '我赞了谁';
    $subTitle = "你点赞过的主页（共 {$total} 个）";
    $actionIcon = '<div class="text-pink-400/60"><i class="fas fa-thumbs-up"></i></div>';
    $type = 'i_liked';
}
?>
<div class="mb-4 flex gap-2 border-b border-white/10 pb-3">
    <a href="?page=likes&lt=to_me" class="px-4 py-1.5 rounded-lg text-sm font-medium transition <?=$tab==='to_me'?'bg-indigo-500/30 text-white':'text-gray-400 hover:text-white hover:bg-white/5'?>"><i class="fas fa-users mr-1"></i>谁赞了我</a>
    <a href="?page=likes&lt=i_liked" class="px-4 py-1.5 rounded-lg text-sm font-medium transition <?=$tab==='i_liked'?'bg-indigo-500/30 text-white':'text-gray-400 hover:text-white hover:bg-white/5'?>"><i class="fas fa-thumbs-up mr-1"></i>我赞了谁</a>
</div>
<div class="mb-6">
    <h2 class="text-xl font-bold text-white mb-1"><?=$title?></h2>
    <p class="text-sm text-gray-400"><?=$subTitle?></p>
</div>
<?php if (empty($list)): ?>
<div class="bg-white/5 border border-white/10 rounded-xl p-8 text-center">
    <div class="text-4xl mb-4 text-gray-500"><i class="far <?=$emptyIcon?>"></i></div>
    <p class="text-gray-400"><?=$emptyMsg?></p>
</div>
<?php else: ?>
<div class="space-y-3">
<?php foreach ($list as $item):
    $u = ['id'=>$item['id']??0, 'nickname'=>$item['nickname'] ?? $item['username'] ?? '匿名', 'username'=>$item['username'] ?? '匿名', 'avatar'=>$item['avatar']??'', 'suffix'=>$item['suffix']??''];
    $displayName = $item['nickname'] ?? $item['username'] ?? '匿名' ?: $item['username'] ?? '匿名';
    $avatarHtml = _userAvatar($u);
    $homeUrl = _userHomeUrl($u);
    $time = date('Y-m-d H:i', strtotime($item['created_at'] ?? ''));
?>
    <div class="bg-white/5 border border-white/10 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <?=$avatarHtml?>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="<?=$homeUrl?>" target="_blank" rel="noopener" class="text-sm font-medium text-white hover:text-indigo-400 transition truncate"><?=h($displayName)?></a>
                    <a href="<?=$homeUrl?>" target="_blank" rel="noopener" class="text-xs text-gray-500 hover:text-indigo-400 transition truncate max-w-[180px] hidden sm:inline"><i class="fas fa-external-link-alt mr-0.5"></i><?=$homeUrl?></a>
                </div>
                <div class="text-xs text-gray-500 mt-0.5"><?=$time?></div>
            </div>
            <?=$actionIcon?>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php if ($lastPage > 1): ?>
<div class="flex justify-center gap-2 mt-6">
    <?php for ($i=1; $i<=$lastPage; $i++): ?>
    <a href="?page=likes&<?=$pageParam?>=<?=$i?>&lt=<?=$tab?>" class="px-4 py-2 rounded-xl text-sm transition <?=$i==$curPage?'bg-indigo-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>"><?=$i?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
