<?php
/**
 * 用户后台 - 我的收藏（谁收藏了我 & 我收藏了谁）
 * Enhanced: avatar, homepage link, time, bidirectional
 */
function _favAvatar($u) {
    $initial = h(mb_substr($u['nickname'] ?: $u['username'], 0, 1));
    if (!empty($u['avatar'])) {
        $src = h($u['avatar']);
        return '<img src="' . $src . '" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">';
    }
    $colors = ['from-yellow-500 to-amber-600', 'from-amber-500 to-orange-600', 'from-orange-500 to-yellow-600'];
    $idx = crc32($u['id'] ?? 0) % count($colors);
    return '<div class="w-9 h-9 rounded-full bg-gradient-to-br ' . $colors[$idx] . ' flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">' . $initial . '</div>';
}
function _favHomeUrl($u) {
    $suffix = $u['suffix'] ?? '';
    return $suffix ? (BASE_URL . '/' . rawurlencode($suffix)) : (BASE_URL . '/page/index.php?id=' . ((int)$u['id']));
}

$tab = $_GET['ft'] ?? 'to_me';
$fpage = max(1, (int)($_GET['fp'] ?? 1));
$fi = max(1, (int)($_GET['fi'] ?? 1));
$fperPage = 15;

if ($tab === 'to_me') {
    // 谁收藏了我
    $total = (int)$db->query("SELECT COUNT(*) FROM page_favorites WHERE page_user_id=$uid")->fetchColumn();
    $lastPage = max(1, ceil($total / $fperPage));
    $fpage = min($fpage, $lastPage);
    $offset = ($fpage - 1) * $fperPage;
    $list = $db->query("
        SELECT f.*, u.username, u.nickname, u.avatar, u.suffix
        FROM page_favorites f
        LEFT JOIN users u ON f.visitor_id = u.id
        WHERE f.page_user_id = $uid
        ORDER BY f.created_at DESC
        LIMIT $fperPage OFFSET $offset
    ")->fetchAll();
    $curPage = $fpage;
    $pageParam = 'fp';
    $tabVal = 'to_me';
    $emptyMsg = '还没有人收藏你的主页';
    $emptyIcon = 'fa-star';
    $title = '谁收藏了我';
    $subTitle = "收藏你主页的人（共 {$total} 个）";
    $actionIcon = '<div class="text-yellow-400"><i class="fas fa-star"></i></div>';
} else {
    // 我收藏了谁
    $total = (int)$db->query("SELECT COUNT(*) FROM page_favorites WHERE visitor_id=$uid")->fetchColumn();
    $lastPage = max(1, ceil($total / $fperPage));
    $fi = min($fi, $lastPage);
    $offset = ($fi - 1) * $fperPage;
    $list = $db->query("
        SELECT f.*, u.username, u.nickname, u.avatar, u.suffix
        FROM page_favorites f
        LEFT JOIN users u ON f.page_user_id = u.id
        WHERE f.visitor_id = $uid
        ORDER BY f.created_at DESC
        LIMIT $fperPage OFFSET $offset
    ")->fetchAll();
    $curPage = $fi;
    $pageParam = 'fi';
    $tabVal = 'i_faved';
    $emptyMsg = '你还没有收藏任何人的主页';
    $emptyIcon = 'fa-star';
    $title = '我收藏了谁';
    $subTitle = "你收藏过的主页（共 {$total} 个）";
    $actionIcon = '<div class="text-yellow-400/60"><i class="fas fa-bookmark"></i></div>';
}
?>
<div class="mb-4 flex gap-2 border-b border-white/10 pb-3">
    <a href="?page=favorites&ft=to_me" class="px-4 py-1.5 rounded-lg text-sm font-medium transition <?=$tab==='to_me'?'bg-indigo-500/30 text-white':'text-gray-400 hover:text-white hover:bg-white/5'?>"><i class="fas fa-users mr-1"></i>谁收藏了我</a>
    <a href="?page=favorites&ft=i_faved" class="px-4 py-1.5 rounded-lg text-sm font-medium transition <?=$tab==='i_faved'?'bg-indigo-500/30 text-white':'text-gray-400 hover:text-white hover:bg-white/5'?>"><i class="fas fa-bookmark mr-1"></i>我收藏了谁</a>
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
    $avatarHtml = _favAvatar($u);
    $homeUrl = _favHomeUrl($u);
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
    <a href="?page=favorites&<?=$pageParam?>=<?=$i?>&ft=<?=$tabVal?>" class="px-4 py-2 rounded-xl text-sm transition <?=$i==$curPage?'bg-indigo-500/30 text-white':'bg-white/5 text-gray-400 hover:bg-white/10'?>"><?=$i?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
