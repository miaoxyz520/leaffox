<?php
/**
 * 管理员 - 定时清理设置（宝塔面板）
 * 指导管理员配置定时任务自动清理过期游客账号
 */
require_once __DIR__ . '/../config.php';
requireAdmin();

// 获取脚本路径
$scriptPath = realpath(__DIR__ . '/../cron_cleanup_guests.php');
$siteRoot = realpath(__DIR__ . '/..');
// 优先用 PHP_BINARY，失败则用常见路径
$phpPath = defined('PHP_BINARY') ? PHP_BINARY : '/usr/bin/php';

// 检查日志
$logFile = $siteRoot . '/data/logs/guest_cleanup.log';
$logContent = '';
$lastRun = '暂无记录';
if (file_exists($logFile)) {
    $logLines = file($logFile);
    if (!empty($logLines)) {
        // 取最近5条
        $recent = array_slice($logLines, -5);
        $logContent = implode('', $recent);
        // 最后一条的时间
        $lastLine = end($logLines);
        if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $lastLine, $m)) {
            $lastRun = $m[1];
        }
    }
}

// 统计待清理
$pendingCount = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE is_guest = 1 AND guest_expires_at IS NOT NULL AND guest_expires_at <= NOW()");
    $pendingCount = (int)$stmt->fetchColumn();
} catch (Exception $e) {}
?>
<h1 class="text-2xl font-bold mb-2">定时清理设置</h1>
<p class="text-gray-500 mb-6">配置宝塔面板定时任务，自动清理过期游客账号</p>

<!-- 快速状态卡片 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="stat-card" style="border-left:3px solid #6366f1">
    <div class="stat-icon" style="background:rgba(99,102,241,0.15);color:#818cf8"><i class="fas fa-users-slash"></i></div>
    <div class="stat-value"><?=$pendingCount?></div>
    <div class="stat-label">待清理过期游客</div>
  </div>
  <div class="stat-card" style="border-left:3px solid #22c55e">
    <div class="stat-icon" style="background:rgba(34,197,94,0.15);color:#4ade80"><i class="fas fa-clock"></i></div>
    <div class="stat-value"><?=h($lastRun)?></div>
    <div class="stat-label">最近一次清理</div>
  </div>
  <div class="stat-card" style="border-left:3px solid #f59e0b">
    <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#fbbf24"><i class="fas fa-check-circle"></i></div>
    <div class="stat-value"><?=file_exists($logFile) ? '正常' : '未运行'?></div>
    <div class="stat-label">脚本状态</div>
  </div>
</div>

<!-- ===== 宝塔面板设置步骤 ===== -->
<div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-5 mb-6">
  <h3 class="text-white font-semibold flex items-center gap-2">
    <i class="fas fa-server text-emerald-400"></i> 宝塔面板 · 设置定时任务
  </h3>
  <p class="text-gray-400 text-sm">按照以下步骤在宝塔面板中设置定时任务，系统将自动清理过期的游客账号及其相关数据。</p>

  <!-- 步骤 1 -->
  <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-5">
    <div class="flex items-start gap-4">
      <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-sm flex-shrink-0">1</div>
      <div class="min-w-0">
        <h4 class="text-white text-sm font-medium mb-2">登录宝塔面板后台</h4>
        <p class="text-gray-400 text-xs leading-relaxed">在浏览器中打开你的宝塔面板地址，使用管理员账号登录。</p>
      </div>
    </div>
  </div>

  <!-- 步骤 2 -->
  <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-5">
    <div class="flex items-start gap-4">
      <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-sm flex-shrink-0">2</div>
      <div class="min-w-0">
        <h4 class="text-white text-sm font-medium mb-2">进入计划任务</h4>
        <p class="text-gray-400 text-xs leading-relaxed">在左侧菜单找到 <strong class="text-indigo-300">计划任务</strong>，点击进入。</p>
      </div>
    </div>
  </div>

  <!-- 步骤 3 -->
  <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-5">
    <div class="flex items-start gap-4">
      <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-sm flex-shrink-0">3</div>
      <div class="min-w-0">
        <h4 class="text-white text-sm font-medium mb-2">添加计划任务</h4>
        <p class="text-gray-400 text-xs leading-relaxed">点击右上角 <strong class="text-indigo-300">添加任务</strong> 按钮，按以下信息填写：</p>
      </div>
    </div>
  </div>

  <!-- 配置表单 -->
  <div class="bg-gray-800/40 border border-white/5 rounded-xl p-5 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">任务类型</label>
        <div class="bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white text-sm">Shell脚本</div>
      </div>
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">任务名称</label>
        <div class="bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white/70 text-sm">清理过期游客</div>
      </div>
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">执行周期</label>
        <div class="bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white text-sm">
          <span class="text-indigo-400 font-mono">N分钟</span> → 设置为 <strong class="text-emerald-400">5 分钟</strong>
        </div>
      </div>
      <div>
        <label class="block text-gray-300 text-xs font-medium mb-1.5">脚本内容 (Shell)</label>
        <div style="position:relative">
          <pre class="bg-black/40 border border-white/10 rounded-lg px-4 py-3 text-emerald-400 text-xs font-mono leading-relaxed overflow-x-auto whitespace-pre-wrap" id="cronCommand"><?=h($phpPath)?> <?=h($scriptPath)?></pre>
          <button onclick="copyToClipboard('cronCommand')" class="absolute top-2 right-2 bg-white/10 hover:bg-white/20 text-gray-300 text-xs px-2.5 py-1 rounded-lg transition flex items-center gap-1">
            <i class="fas fa-copy text-[10px]"></i> 复制
          </button>
        </div>
      </div>
    </div>

    <div class="bg-amber-500/5 border border-amber-500/15 rounded-lg p-3">
      <p class="text-amber-300 text-xs flex items-start gap-2">
        <i class="fas fa-lightbulb mt-0.5"></i>
        <span>可以直接点击右侧的 <strong class="text-amber-200">复制</strong> 按钮，然后在宝塔面板的"脚本内容"输入框中粘贴即可。</span>
      </p>
    </div>
  </div>

  <!-- 步骤 4 -->
  <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-5">
    <div class="flex items-start gap-4">
      <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-sm flex-shrink-0">4</div>
      <div class="min-w-0">
        <h4 class="text-white text-sm font-medium mb-2">保存并测试</h4>
        <p class="text-gray-400 text-xs leading-relaxed">点击 <strong class="text-indigo-300">确定</strong> 保存任务。可以在任务列表中点击"执行"按钮手动测试一次，查看日志确认是否正常运行。</p>
      </div>
    </div>
  </div>
</div>

<!-- ===== 脚本信息 ===== -->
<div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-4 mb-6">
  <h3 class="text-white font-semibold flex items-center gap-2">
    <i class="fas fa-file-code text-purple-400"></i> 脚本信息
  </h3>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
    <div>
      <span class="text-gray-500">脚本路径：</span>
      <code class="text-indigo-300 bg-white/5 px-2 py-0.5 rounded text-xs"><?=h($scriptPath)?></code>
    </div>
    <div>
      <span class="text-gray-500">PHP 路径：</span>
      <code class="text-indigo-300 bg-white/5 px-2 py-0.5 rounded text-xs"><?=h($phpPath)?></code>
    </div>
    <div>
      <span class="text-gray-500">日志文件：</span>
      <code class="text-indigo-300 bg-white/5 px-2 py-0.5 rounded text-xs"><?=h($siteRoot)?>/data/logs/guest_cleanup.log</code>
    </div>
    <div>
      <span class="text-gray-500">推荐周期：</span>
      <span class="text-emerald-400 font-medium">每 5 分钟</span>
    </div>
  </div>
</div>

<!-- ===== 清理说明 ===== -->
<div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-4 mb-6">
  <h3 class="text-white font-semibold flex items-center gap-2">
    <i class="fas fa-info-circle text-sky-400"></i> 清理内容说明
  </h3>
  <div class="space-y-2 text-gray-400 text-sm">
    <p>当定时任务运行时，会自动执行以下清理操作：</p>
    <ul class="list-disc list-inside space-y-1 text-xs ml-2">
      <li>删除已过期的游客账号（超出有效期的临时用户）</li>
      <li>删除该游客产生的所有统计数据</li>
      <li>删除该游客创建的所有链接卡片</li>
      <li>删除该游客的点赞、评论和收藏记录</li>
    </ul>
    <div class="bg-yellow-500/5 border border-yellow-500/15 rounded-lg p-3 mt-3">
      <p class="text-yellow-300 text-xs flex items-start gap-2">
        <i class="fas fa-exclamation-triangle mt-0.5"></i>
        <span><strong>注意：</strong>清理操作不可恢复。被删除的游客数据将永久丢失，请在设置前确认 <strong class="text-yellow-200">游客过期时间(guest_expires_at)</strong> 配置正确。</span>
      </p>
    </div>
  </div>
</div>

<!-- ===== 执行日志 ===== -->
<div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-4">
  <h3 class="text-white font-semibold flex items-center gap-2">
    <i class="fas fa-history text-cyan-400"></i> 近期执行日志
  </h3>
  <?php if ($logContent): ?>
  <pre class="bg-black/40 border border-white/5 rounded-xl px-4 py-3 text-emerald-400 text-xs font-mono leading-relaxed overflow-x-auto max-h-64 overflow-y-auto"><?=h($logContent)?></pre>
  <?php else: ?>
  <div class="text-center py-8">
    <i class="fas fa-inbox text-4xl text-gray-600 mb-3 block"></i>
    <p class="text-gray-500 text-sm">暂无执行记录</p>
    <p class="text-gray-600 text-xs mt-1">设置定时任务后，这里将显示清理日志</p>
  </div>
  <?php endif; ?>
  <div class="flex items-center gap-3">
    <a href="?page=cron&refresh=1" class="bg-white/10 hover:bg-white/15 text-gray-300 px-4 py-2 rounded-xl text-xs transition flex items-center gap-1.5">
      <i class="fas fa-sync-alt text-[10px]"></i> 刷新日志
    </a>
    <button onclick="document.getElementById('manualRunConfirm').style.display='flex'" class="bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 px-4 py-2 rounded-xl text-xs transition flex items-center gap-1.5">
      <i class="fas fa-play text-[10px]"></i> 手动执行一次
    </button>
  </div>
</div>

<!-- ===== 手动执行确认弹窗 ===== -->
<div id="manualRunConfirm" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
  <div style="background:var(--admin-card-bg,#1e293b);border:1px solid var(--admin-card-border);border-radius:16px;max-width:420px;width:90%;padding:24px;" onclick="event.stopPropagation()">
    <h3 class="text-white font-semibold mb-3">手动执行清理脚本</h3>
    <p class="text-gray-400 text-sm mb-4">确认要立即执行清理脚本吗？将删除所有已过期的游客账号，此操作不可撤销。</p>
    <form method="POST" action="?page=cron">
      <?php csrfField(); ?>
      <div class="flex gap-3 justify-end">
        <button type="button" onclick="document.getElementById('manualRunConfirm').style.display='none'" class="bg-white/10 hover:bg-white/15 text-gray-300 px-4 py-2 rounded-xl text-sm transition">取消</button>
        <button type="submit" name="run_cleanup" value="1" class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2 rounded-xl text-sm font-medium transition">确认执行</button>
      </div>
    </form>
  </div>
</div>

<?php
// ===== 手动执行处理 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['run_cleanup'])) {
    requireCsrfToken();
    if (function_exists('exec')) {
        $output = '';
        $exitCode = 0;
        exec(escapeshellcmd($phpPath) . ' ' . escapeshellarg($scriptPath) . ' 2>&1', $output, $exitCode);
        $msg = $exitCode === 0 ? '✓ 清理脚本执行成功' : '✗ 执行失败，请检查脚本路径';
    } else {
        // exec 被禁用时，尝试通过 HTTP 请求调用
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // 直接使用网站根目录的 cron_cleanup_guests.php
        $cronUrl = $scheme . '://' . $host . '/cron_cleanup_guests.php';
        $ch = curl_init($cronUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $msg = $httpCode === 200 ? '✓ 已通过 HTTP 触发清理脚本' : '✗ 触发失败（HTTP ' . $httpCode . '），请检查脚本可访问性';
    }
    echo '<script>
      alert("' . $msg . '");
      window.location.href = "?page=cron&refresh=1";
    </script>';
}
?>

<script>
function copyToClipboard(elementId) {
    const el = document.getElementById(elementId);
    const text = el.textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const btn = el.querySelector('button') || el.parentElement.querySelector('button');
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-[10px]"></i> 已复制';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        }
    }).catch(() => {
        // 降级方案
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('已复制到剪贴板');
    });
}
</script>
