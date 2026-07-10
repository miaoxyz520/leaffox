<?php
/**
 * 管理员 - 修改密码
 */
$msg = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $oldPwd = $_POST['old_password'] ?? '';
    $newPwd = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($oldPwd) || empty($newPwd) || empty($confirm)) {
        $msg = '请填写所有字段';
    } elseif ($newPwd !== $confirm) {
        $msg = '两次密码不一致';
    } elseif (strlen($newPwd) < 6) {
        $msg = '新密码至少6位';
    } else {
        $stmt = $db->prepare("SELECT password FROM admin WHERE id=?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            $msg = '管理员不存在';
        } elseif (!password_verify($oldPwd, $admin['password'])) {
            $msg = '原密码错误';
        } else {
            $hash = password_hash($newPwd, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admin SET password=? WHERE id=?");
            $stmt->execute([$hash, $_SESSION['admin_id']]);
            adminLog($db, '管理员修改密码', 'admin', $_SESSION['admin_id']);
            $msg = 'success';
        }
    }
}
?>
<h1 class="text-2xl font-bold mb-2">修改密码</h1>
<p class="text-gray-500 mb-6">修改当前管理员登录密码</p>

<?php if ($msg === 'success'): ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm"><i class="fas fa-check-circle" style="color:#10b981"></i> 密码修改成功，下次登录请使用新密码</div>
<?php elseif ($msg): ?>
<div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($msg)?></div>
<?php endif; ?>

<form method="POST" class="max-w-md space-y-5">
  <div>
    <label class="block text-gray-300 text-sm font-medium mb-2">当前密码</label>
    <input type="password" name="old_password" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
  </div>
  <div>
    <label class="block text-gray-300 text-sm font-medium mb-2">新密码</label>
    <input type="password" name="new_password" required minlength="6" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
  </div>
  <div>
    <label class="block text-gray-300 text-sm font-medium mb-2">确认新密码</label>
    <input type="password" name="confirm_password" required minlength="6" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50">
  </div>
  <button type="submit" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-3 rounded-xl font-medium hover:shadow-lg hover:shadow-indigo-500/25 transition">修改密码</button>
</form>
