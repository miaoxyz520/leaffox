<?php
/**
 * 用户后台 - 我的评论（谁评论了我 & 我评论了谁）
 * Enhanced: avatar, homepage link, comment content, time, bidirectional
 */
function _comAvatar($u) {
    $initial = h(mb_substr($u['nickname'] ?: $u['username'], 0, 1));
    if (!empty($u['avatar'])) {
        $src = h($u['avatar']);
        return '<img src="' . $src . '" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">';
    }
    $colors = ['from-indigo-500 to-purple-600', 'from-purple-500 to-violet-600', 'from-violet-500 to-indigo-600'];
    $idx = crc32($u['id'] ?? 0) % count($colors);
    return '<div class="w-9 h-9 rounded-full bg-gradient-to-br ' . $colors[$idx] . ' flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">' . $initial . '</div>';
}
function _comHomeUrl($u) {
    $suffix = $u['suffix'] ?? '';
    return $suffix ? (BASE_URL . '/' . rawurlencode($suffix)) : (BASE_URL . '/page/index.php?id=' . ((int)$u['id']));
}
function _statusBadge($status) {
    if ($status == 0) return '<span class="text-xs px-2 py-0.5 rounded-full bg-yellow-500/10 text-yellow-400"><i class="fas fa-clock mr-0.5"></i>待审核</span>';
    if ($status == 1) return '<span class="text-xs px-2 py-0.5 rounded-full bg-green-500/10 text-green-400"><i class="fas fa-check-circle mr-0.5"></i>已公开</span>';
    if ($status == 2) return '<span class="text-xs px-2 py-0.5 rounded-full bg-red-500/10 text-red-400"><i class="fas fa-eye-slash mr-0.5"></i>已隐藏</span>';
    return '';
}

$tab = $_GET['ct'] ?? 'to_me';
$cpage = max(1, (int)($_GET['cp'] ?? 1));
$ci = max(1, (int)($_GET['ci'] ?? 1));
$cperPage = 15;

if ($tab === 'to_me') {
    // 谁评论了我
    $total = (int)$db->query("SELECT COUNT(*) FROM page_comments WHERE page_user_id=$uid")->fetchColumn();
    $lastPage = max(1, ceil($total / $cperPage));
    $cpage = min($cpage, $lastPage);
    $offset = ($cpage - 1) * $cperPage;
    $list = $db->query("
        SELECT c.*, u.username, u.nickname, u.avatar, u.suffix
        FROM page_comments c
        LEFT JOIN users u ON c.visitor_id = u.id
        WHERE c.page_user_id = $uid
        ORDER BY c.created_at DESC
        LIMIT $cperPage OFFSET $offset
    ")->fetchAll();
    $curPage = $cpage;
    $pageParam = 'cp';
    $tabVal = 'to_me';
    $title = '谁评论了我';
    $subTitle = "给你主页留言的人（共 {$total} 条）";
    $emptyMsg = '还没有人给你留言';
    $emptyIcon = 'fa-comment-dots';
    $role = 'target';
} else {
    // 我评论了谁
    $total = (int)$db->query("SELECT COUNT(*) FROM page_comments WHERE visitor_id=$uid")->fetchColumn();
    $lastPage = max(1, ceil($total / $cperPage));
    $ci = min($ci, $lastPage);
    $offset = ($ci - 1) * $cperPage;
    $list = $db->query("
        SELECT c.*, u.username, u.nickname, u.avatar, u.suffix
        FROM page_comments c
        LEFT JOIN users u ON c.page_user_id = u.id
        WHERE c.visitor_id = $uid
        ORDER BY c.created_at DESC
        LIMIT $cperPage OFFSET $offset
    ")->fetchAll();
    $curPage = $ci;
    $pageParam = 'ci';
    $tabVal = 'i_commented';
    $title = '我评论了谁';
    $subTitle = "你留言过的主页（共 {$total} 条）";
    $emptyMsg = '你还没有给任何人留言';
    $emptyIcon = 'fa-comment-dots';
    $role = 'actor';
}
?>
<div class="mb-4 flex gap-2 border-b border-white/10 pb-3">
    <a href="?page=comments&ct=to_me" class="px-4 py-1.5 rounded-lg text-sm font-medium transition <?=$tab==='to_me'?'bg-indigo-500/30 text-white':'text-gray-400 hover:text-white hover:bg-white/5'?>"><i class="fas fa-users mr-1"></i>谁评论了我</a>
    <a href="?page=comments&ct=i_commented" class="px-4 py-1.5 rounded-lg text-sm font-medium transition <?=$tab==='i_commented'?'bg-indigo-500/30 text-white':'text-gray-400 hover:text-white hover:bg-white/5'?>"><i class="fas fa-pen mr-1"></i>我评论了谁</a>
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
    $avatarHtml = _comAvatar($u);
    $homeUrl = _comHomeUrl($u);
    $time = date('Y-m-d H:i', strtotime($item['created_at'] ?? ''));
?>
    <div class="bg-white/5 border border-white/10 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <?=$avatarHtml?>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <a href="<?=$homeUrl?>" target="_blank" rel="noopener" class="text-sm font-medium text-white hover:text-indigo-400 transition truncate"><?=h($displayName)?></a>
                    <span class="text-xs text-gray-500 whitespace-nowrap"><?=$time?></span>
                </div>
                <div class="mt-2 text-sm text-gray-300 leading-relaxed bg-white/5 rounded-lg p-3 border border-white/5">
                    <i class="fas fa-quote-left text-gray-500 mr-1 text-xs"></i><?=h($item['content'] ?? '')?>
                </div>
                <div class="mt-2 flex items-center gap-2 flex-wrap">
                    <?=_statusBadge($item['status'] ?? 0)?>
                    <a href="<?=$homeUrl?>" target="_blank" rel="noopener" class="text-xs text-gray-500 hover:text-indigo-400 transition"><i class="fas fa-external-link-alt mr-0.5"></i><?=$homeUrl?></a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php if ($lastPage > 1): ?>
<div class="flex justify-center gap-2 mt-6">
    <?php for ($i=1; $i<=$lastPage; $i++): ?>
    <a href="?page=comments&<?=$pageParam?>=<?=$i?>&ct=<?=$tabVal?>" class="px-4 py-2 rounded-xl text-sm transition <?=$i==$curPage?'bg-indigo-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>"><?=$i?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
