<?php
/**
 * 用户 - 个人设置（修改资料、头像、密码、后缀）
 */
$uid = (int)$_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $nickname = safeSubstr(trim($_POST['nickname'] ?? ''), 32);
        $bio      = safeSubstr(trim($_POST['bio'] ?? ''), 200);
        $suffix   = safeSubstr(trim($_POST['suffix'] ?? ''), 32);
        
        // 后缀校验：只允许字母数字下划线连字符，且不与其他用户冲突
        if ($suffix && !preg_match('/^[a-zA-Z0-9_-]+$/', $suffix)) {
            $msg = '后缀只允许字母、数字、下划线和连字符';
        } else {
            // 检查是否在禁止后缀列表中
            if ($suffix) {
                $settings    = getSettings($db);
                $bannedRaw   = trim($settings['banned_suffixes'] ?? '');
                $bannedList  = $bannedRaw ? array_map('trim', explode("
", $bannedRaw)) : [];
                // 自动加入保留路径前缀
                $autoBanned  = ['page', 'user', 'admin', 'api'];
                $bannedList  = array_merge($bannedList, $autoBanned);
                $bannedList  = array_unique(array_filter($bannedList));
                if (in_array($suffix, $bannedList)) {
                    $msg = '该后缀已被系统保留，请换一个';
                }
            }
            if (empty($msg)) {
                // 检查后缀唯一性（排除自己）
                $stmt = $db->prepare("SELECT id FROM users WHERE suffix=? AND id!=? LIMIT 1");
                $stmt->execute([$suffix, $uid]);
                if ($suffix && $stmt->fetch()) {
                    $msg = '该后缀已被其他用户使用';
                } else {
                    $stmt = $db->prepare("UPDATE users SET nickname=?, bio=?, suffix=? WHERE id=?");
                    $stmt->execute([$nickname, $bio, $suffix, $uid]);
                    $_SESSION['user_name'] = $nickname ?: $user['username'];
                    $msg = '资料已更新 ✓';
                }
            }
        }
        
    } elseif ($action === 'change_password') {
        $oldPwd  = $_POST['old_password'] ?? '';
        $newPwd  = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($oldPwd) || empty($newPwd) || empty($confirm)) {
            $msg = '请填写所有密码字段';
        } elseif ($newPwd !== $confirm) {
            $msg = '两次密码不一致';
        } elseif (strlen($newPwd) < 6) {
            $msg = '新密码至少6位';
        } else {
            if (!password_verify($oldPwd, $user['password'])) {
                $msg = '原密码错误';
            } else {
                $hash = password_hash($newPwd, PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $uid]);
                $msg = '密码修改成功 ✓';
            }
        }
    } elseif ($action === 'upload_avatar') {
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $result = uploadImage($_FILES['avatar'], "avatar_{$uid}");
            if (isset($result['error'])) {
                $msg = $result['error'];
            } else {
                $stmt = $db->prepare("UPDATE users SET avatar=? WHERE id=?");
                $stmt->execute([$result['path'], $uid]);
                $msg = '头像已更新 ✓';
            }
        } else {
            $msg = '请选择图片';
        }
    }
    
    // 刷新用户信息
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
}
?>
<h1 class="text-xl font-bold mb-2">个人设置</h1>
<p class="text-gray-500 text-sm mb-6">管理你的个人资料和账号信息</p>

<?php if ($msg): ?>
<div class="bg-<?=strpos($msg,'✓')!==false?'emerald':'red'?>-500/10 border border-<?=strpos($msg,'✓')!==false?'emerald':'red'?>-500/30 text-<?=strpos($msg,'✓')!==false?'emerald':'red'?>-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($msg)?></div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <!-- 左侧：头像 -->
  <div class="card-base">
    <h3>我的头像</h3>
    <div class="text-center">
      <div class="w-28 h-28 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold overflow-hidden mb-4">
        <?php if ($user['avatar']): ?>
          <img src="<?=BASE_URL.'/'.$user['avatar']?>" class="w-full h-full object-cover">
        <?php else: ?>
          <?=h(mb_substr($user['nickname']?:$user['username'],0,1))?>
        <?php endif; ?>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_avatar">
        <label class="btn-sm btn-primary cursor-pointer"><i class="fas fa-upload"></i> 选择图片<input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()"></label>
        <p class="text-gray-500 text-xs mt-2">支持 JPG/PNG/GIF，最大2MB</p>
      </form>
    </div>
  </div>

  <!-- 右侧：资料 + 密码 -->
  <div class="md:col-span-2 space-y-5">
    <div class="card-base">
      <h3><i class="fas fa-user-edit text-indigo-400"></i> 基本资料</h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="update_profile">
        <div>
          <label class="block text-gray-300 text-xs font-medium mb-1.5">昵称</label>
          <input type="text" name="nickname" value="<?=h($user['nickname'])?>" maxlength="32" placeholder="设置你的显示昵称">
        </div>
        <div>
          <label class="block text-gray-300 text-xs font-medium mb-1.5">个人简介</label>
          <textarea name="bio" rows="3" maxlength="200" placeholder="写一句介绍你自己吧"><?=h($user['bio'])?></textarea>
        </div>
        <div>
          <label class="block text-gray-300 text-xs font-medium mb-1.5">个性后缀 <span class="text-gray-500 font-normal">(访问你主页的短链)</span></label>
          <div class="flex items-center gap-2">
            <span class="text-gray-500 text-sm whitespace-nowrap"><?=BASE_URL?>/</span>
            <input type="text" name="suffix" value="<?=h($user['suffix'])?>" maxlength="32" placeholder="mypage" pattern="[a-zA-Z0-9_-]+" title="只允许字母、数字、下划线和连字符" class="flex-1">
          </div>
          <p class="text-gray-500 text-xs mt-1">✨ 设置后可通过短链直接访问你的主页</p>
        </div>
        <button type="submit" class="btn-sm btn-primary">保存资料</button>
      </form>
    </div>

    <div class="card-base">
      <h3><i class="fas fa-key text-indigo-400"></i> 修改密码</h3>
      <form method="POST" class="space-y-4 max-w-sm">
        <input type="hidden" name="action" value="change_password">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">当前密码</label><input type="password" name="old_password" required></div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">新密码</label><input type="password" name="new_password" required minlength="6"></div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">确认新密码</label><input type="password" name="confirm_password" required minlength="6"></div>
        <button type="submit" class="btn-sm btn-primary">修改密码</button>
      </form>
    </div>
  </div>
</div>
