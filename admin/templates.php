<?php
/**
 * 管理后台 - 模版管理
 * 支持上传系统主页模版 / 用户专属页模版，内置变量注释说明
 */
requireAdmin();

$pageTitle = '模版管理';
$msg = '';
$err = '';

// ===== 内置模版白名单（不可删除） =====
$builtInLanding = ['default'];
$builtInUser = ['default', 'modern', 'vibrant', 'elegant', 'neon'];

// ===== 扫描模版目录 =====
function scanTemplates($dir) {
    $files = [];
    if (!is_dir($dir)) return $files;
    foreach (glob($dir . '/*.php') as $f) {
        $files[] = basename($f, '.php');
    }
    sort($files);
    return $files;
}

$userTemplatesDir = __DIR__ . '/../templates/user';
$landingTemplatesDir = __DIR__ . '/../templates/landing';

// ===== 处理上传 =====
$uploadTarget = $_GET['target'] ?? 'user'; // user | landing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadTargetPost = $_POST['upload_target'] ?? 'user';
    $targetDir = $uploadTargetPost === 'landing' ? $landingTemplatesDir : $userTemplatesDir;
    
    if (!empty($_FILES['template_file']['name'])) {
        $file = $_FILES['template_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $err = '上传失败，错误码: ' . $file['error'];
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'php') {
                $err = '仅支持 .php 格式的模版文件';
            } else {
                $newName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
                if (empty($newName)) $newName = 'custom_' . time();
                $dest = $targetDir . '/' . $newName . '.php';
                if (file_exists($dest)) {
                    $err = '模版 "' . h($newName) . '" 已存在，请先删除旧模版再上传';
                } else {
                    $content = file_get_contents($file['tmp_name']);
                    // 简单校验是否为合法PHP
                    $check = @eval('return true; ' . $content);
                    if ($check === false && trim($content) !== '<?php') {
                        // 尝试用 php -l 检查
                        $tmpf = tempnam(sys_get_temp_dir(), 'tpl');
                        file_put_contents($tmpf, $content);
                        $out = shell_exec("php -l " . escapeshellarg($tmpf) . " 2>&1");
                        @unlink($tmpf);
                        if (strpos($out, 'No syntax errors') === false) {
                            $err = 'PHP 语法错误：' . h(trim($out));
                        }
                    }
                    if (empty($err)) {
                        file_put_contents($dest, $content);
                        $msg = '<i class="fas fa-check-circle" style="color:#10b981"></i> 模版 "' . h($newName) . '" 上传成功！';
                        adminLog($db, '上传模版', 'system', 0, "模版: {$newName} (类型: {$uploadTargetPost})");
                    }
                }
            }
        }
    } elseif (!empty($_POST['delete_template'])) {
        $del = $_POST['delete_template'];
        $delType = $_POST['delete_type'] ?? 'user';
        $delDir = $delType === 'landing' ? $landingTemplatesDir : $userTemplatesDir;
        $builtIn = $delType === 'landing' ? $builtInLanding : $builtInUser;
        
        if (in_array($del, $builtIn)) {
            $err = '内置模版不可删除';
        } else {
            $delFile = $delDir . '/' . $del . '.php';
            if (file_exists($delFile)) {
                @unlink($delFile);
                $msg = '<i class="fas fa-trash-alt"></i> 模版 "' . h($del) . '" 已删除';
                adminLog($db, '删除模版', 'system', 0, "模版: {$del} (类型: {$delType})");
            } else {
                $err = '模版文件不存在';
            }
        }
    }
}

// ===== 刷新列表 =====
$landingTemplates = scanTemplates($landingTemplatesDir);
$userTemplates = scanTemplates($userTemplatesDir);

// ===== 读取模版内容用于预览 =====
$previewTemplate = $_GET['preview'] ?? '';
$previewType = $_GET['preview_type'] ?? 'user';
$previewContent = '';
if ($previewTemplate) {
    $previewDir = $previewType === 'landing' ? $landingTemplatesDir : $userTemplatesDir;
    $previewFile = $previewDir . '/' . $previewTemplate . '.php';
    if (file_exists($previewFile)) {
        $previewContent = file_get_contents($previewFile);
    }
}

// ===== 模版变量文档 =====
$templateVarsDoc = [
    'landing' => [
        'name' => '系统主页（落地页）模版变量',
        'desc' => '系统主页模版文件位于 templates/landing/，返回带 CSS/HTML 的关联数组。模版中所有动态内容通过 PHP 变量输出。',
        'vars' => [
            'title' => ['string', '页面标题', '$title'],
            'subtitle' => ['string', '页面副标题', '$subtitle'],
            'features' => ['array', '功能介绍列表（每个元素有 icon/title/desc）', '$features'],
            'stats' => ['array', '统计数据（total_users, total_links, total_views）', '$stats'],
            'siteName' => ['string', '站点名称', '$siteName'],
            'bgStyle' => ['string', '背景样式 CSS', '$bgStyle'],
            'baseUrl' => ['string', '站点基础 URL', '$baseUrl'],
            'ctaText' => ['string', '按钮文字', '$ctaText'],
            'ctaLink' => ['string', '按钮链接', '$ctaLink'],
            'footerHtml' => ['string', '页脚 HTML', '$footerHtml'],
            'isLogin' => ['bool', '当前访问者是否已登录', '$isLogin'],
        ],
        'return' => '返回关联数组: [ "css" => "CSS 样式代码（可选）", "html" => "HTML 内容（可选，不传则使用默认布局）" ]',
    ],
    'user' => [
        'name' => '用户专属页模版变量',
        'desc' => '用户页模版文件位于 templates/user/，返回关联数组，其中 css 字段包含覆盖默认样式的 CSS。所有 !important 规则会覆盖 page/index.php 中的默认样式。PHP 变量已由 page/index.php 预计算好，模版中不应包含 PHP 逻辑。',
        'vars' => [
            'css' => ['string', 'CSS 覆盖样式（必须使用 !important）', "\$templateCssData['css']"],
            '备注' => ['text', '模版中可使用 @import 引入外部字体/CSS，但注意加载性能'],
            '设计原则' => ['text', '模版只覆盖外观（颜色、字体、圆角、阴影、动画），不改变 HTML 结构和 JS 功能'],
        ],
        'return' => '返回关联数组: [ "css" => "CSS 覆盖样式（字符串）" ]',
    ],
];
?>
<style>
.btn-upload{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:all 0.25s;background:linear-gradient(135deg,#6366f1,#a78bfa);color:#fff}
.btn-upload:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(99,102,241,0.35)}
.btn-upload:active{transform:scale(0.96)}
.btn-danger{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:500;border:none;cursor:pointer;transition:all 0.2s;background:rgba(239,68,68,0.12);color:#ef4444}
.btn-danger:hover{background:rgba(239,68,68,0.2)}.btn-danger:active{transform:scale(0.95)}
.btn-info{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:500;border:none;cursor:pointer;transition:all 0.2s;background:rgba(99,102,241,0.1);color:#818cf8}
.btn-info:hover{background:rgba(99,102,241,0.18)}.btn-info:active{transform:scale(0.95)}
.var-table{width:100%;border-collapse:collapse;font-size:13px}
.var-table th{text-align:left;padding:8px 12px;background:rgba(255,255,255,0.04);color:rgba(255,255,255,0.5);font-weight:500;font-size:11px;text-transform:uppercase;letter-spacing:0.5px}
.var-table td{padding:8px 12px;border-top:1px solid rgba(255,255,255,0.04);color:rgba(255,255,255,0.75);vertical-align:top}
.var-table .var-name{font-family:monospace;color:#a78bfa;font-size:12px}
.var-table .var-type{display:inline-block;padding:1px 8px;border-radius:4px;font-size:10px;font-weight:500;background:rgba(99,102,241,0.1);color:#818cf8}
.var-table .var-desc{color:rgba(255,255,255,0.5);font-size:12px}
.code-block{background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:16px;overflow-x:auto;font-family:monospace;font-size:12px;line-height:1.6;color:rgba(255,255,255,0.7);white-space:pre;max-height:500px;overflow-y:auto}
.template-card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:14px 18px;transition:all 0.25s;display:flex;align-items:center;justify-content:space-between}
.template-card:hover{background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1)}
.template-card .tpl-name{font-size:15px;font-weight:600;color:#e2e8f0}
.template-card .tpl-badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:500;margin-left:8px;vertical-align:middle}
.badge-builtin{background:rgba(99,102,241,0.12);color:#818cf8}
.badge-custom{background:rgba(16,185,129,0.12);color:#34d399}
.upload-zone{border:2px dashed rgba(255,255,255,0.1);border-radius:14px;padding:30px;text-align:center;transition:all 0.3s;cursor:pointer}
.upload-zone:hover{border-color:rgba(99,102,241,0.3);background:rgba(99,102,241,0.03)}
.upload-zone.dragover{border-color:#6366f1;background:rgba(99,102,241,0.06)}
.tab-btn{padding:8px 18px;border-radius:8px;font-size:13px;font-weight:500;border:none;cursor:pointer;transition:all 0.2s;background:rgba(255,255,255,0.04);color:rgba(255,255,255,0.5)}
.tab-btn.active{background:rgba(99,102,241,0.15);color:#818cf8;font-weight:600}
.tab-btn:hover:not(.active){background:rgba(255,255,255,0.08)}
</style>

<h1 class="text-2xl font-bold mb-2"><i class="fas fa-palette"></i> 模版管理</h1>
<p class="text-gray-500 mb-6">管理系统主页模版和用户专属页模版，上传自定义模版文件</p>

<?php if ($msg): ?><div class="alert-success mb-4 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm"><?=$msg?></div><?php endif; ?>
<?php if ($err): ?><div class="alert-error mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm"><?=$err?></div><?php endif; ?>

<!-- ===== Tab 切换 ===== -->
<div class="flex gap-3 mb-6">
  <button class="tab-btn <?=$uploadTarget==='user'?'active':''?>" onclick="location.href='?page=templates&target=user'"><i class="fas fa-user"></i> 用户专属页模版</button>
  <button class="tab-btn <?=$uploadTarget==='landing'?'active':''?>" onclick="location.href='?page=templates&target=landing'"><i class="fas fa-home"></i> 系统主页模版</button>
</div>

<?php if ($uploadTarget === 'user'): $tplList = $userTemplates; $builtIn = $builtInUser; $typeLabel = 'user'; $typeName = '用户专属页'; $targetDir = $userTemplatesDir; ?>
<?php else: $tplList = $landingTemplates; $builtIn = $builtInLanding; $typeLabel = 'landing'; $typeName = '系统主页'; $targetDir = $landingTemplatesDir; ?>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

  <!-- 左侧：模版列表 + 上传 -->
  <div class="xl:col-span-2">
    <div class="card-base mb-6">
      <h3 class="text-lg font-semibold mb-4"><i class="fas fa-folder-open"></i> 可用模版（<?=count($tplList)?>个）</h3>
      <div class="flex flex-col gap-3">
        <?php foreach ($tplList as $tpl): 
          $isBuiltIn = in_array($tpl, $builtIn);
        ?>
        <div class="template-card">
          <div>
            <span class="tpl-name"><?=h($tpl)?></span>
            <span class="tpl-badge <?=$isBuiltIn?'badge-builtin':'badge-custom'?>"><?=$isBuiltIn?'内置':'自定义'?></span>
          </div>
          <div class="flex gap-2">
            <a href="?page=templates&preview=<?=urlencode($tpl)?>&preview_type=<?=$typeLabel?>&target=<?=$typeLabel?>" class="btn-info"><i class="fas fa-code"></i> 查看</a>
            <?php if (!$isBuiltIn): ?>
            <form method="post" style="display:inline" onsubmit="return confirm('确认删除模版「<?=h($tpl)?>」？')">
              <input type="hidden" name="delete_template" value="<?=h($tpl)?>">
              <input type="hidden" name="delete_type" value="<?=$typeLabel?>">
              <button type="submit" class="btn-danger"><i class="fas fa-trash"></i> 删除</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 上传区域 -->
    <div class="card-base">
      <h3 class="text-lg font-semibold mb-2"><i class="fas fa-upload"></i> 上传新模版</h3>
      <p class="text-gray-500 text-xs mb-4">上传 .php 格式的模版文件，文件名仅支持字母、数字、下划线和横线</p>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload_target" value="<?=$typeLabel?>">
        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
          <div class="text-4xl mb-3"><i class="fas fa-file-alt"></i></div>
          <p class="text-gray-400 text-sm mb-1">点击或拖拽 PHP 文件到此处</p>
          <p class="text-gray-600 text-xs">支持 .php 格式，推荐遵循模版变量规范</p>
          <input type="file" id="fileInput" name="template_file" accept=".php" style="display:none" onchange="this.form.submit()">
        </div>
        <div class="mt-4 flex gap-3">
          <button type="button" class="btn-upload" onclick="document.getElementById('fileInput').click()"><i class="fas fa-upload"></i> 选择并上传</button>
          <button type="button" class="btn-info" onclick="showTemplateGuide()"><i class="fas fa-question-circle"></i> 查看开发指南</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 右侧：预览 / 变量文档 -->
  <div>
    <?php if ($previewTemplate && $previewContent): ?>
    <div class="card-base">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold"><i class="fas fa-edit"></i> <?=h($previewTemplate)?>.php</h3>
        <a href="?page=templates&target=<?=$typeLabel?>" class="text-gray-500 text-sm hover:text-gray-300"><i class="fas fa-times"></i> 关闭</a>
      </div>
      <div class="code-block"><?=h($previewContent)?></div>
    </div>
    <?php endif; ?>

    <!-- 变量文档 -->
    <div class="card-base mt-6">
      <h3 class="text-lg font-semibold mb-3"><i class="fas fa-book"></i> 模版变量参考</h3>
      <?php $doc = $templateVarsDoc[$typeLabel]; ?>
      <p class="text-gray-500 text-xs mb-4"><?=h($doc['desc'])?></p>
      
      <?php if ($typeLabel === 'user'): ?>
      <div class="bg-indigo-500/5 border border-indigo-500/10 rounded-xl p-4 mb-4 text-sm text-gray-400">
        <strong class="text-indigo-400"><i class="fas fa-lightbulb"></i> 设计原则：</strong><br>
        用户页模版只提供 CSS 覆盖样式，不改变 HTML 结构。<br>
        所有 CSS 规则需使用 <code class="text-indigo-300">!important</code> 来覆盖默认样式。<br>
        模版通过 <code class="text-indigo-300">\$templateCssData['css']</code> 注入到页面。
      </div>
      <?php endif; ?>

      <table class="var-table">
        <thead><tr><th style="width:30%">变量</th><th style="width:15%">类型</th><th>说明</th></tr></thead>
        <tbody>
          <?php foreach ($doc['vars'] as $varName => $info): 
            $type = $info[0] ?? '';
            $desc = $info[1] ?? '';
            $usage = $info[2] ?? '';
          ?>
          <tr>
            <td><code class="var-name"><?=h($varName)?></code></td>
            <td><span class="var-type"><?=h($type)?></span></td>
            <td><span class="var-desc"><?=h($desc)?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="mt-4 p-4 bg-white/5 rounded-xl text-xs text-gray-500">
        <strong class="text-gray-400">模版返回值：</strong><br>
        <code class="text-indigo-300"><?=h($doc['return'])?></code>
      </div>
    </div>
  </div>
</div>

<!-- 拖拽上传支持 -->
<script>
(function(){
  var zone = document.getElementById('uploadZone');
  if (!zone) return;
  zone.addEventListener('dragover', function(e){ e.preventDefault(); this.classList.add('dragover'); });
  zone.addEventListener('dragleave', function(){ this.classList.remove('dragover'); });
  zone.addEventListener('drop', function(e){
    e.preventDefault();
    this.classList.remove('dragover');
    var files = e.dataTransfer.files;
    if (files.length > 0) {
      document.getElementById('fileInput').files = files;
      document.getElementById('fileInput').closest('form').submit();
    }
  });
})();
</script>

<div id="templateGuideModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.65);backdrop-filter:blur(10px);display:none;align-items:center;justify-content:center" onclick="if(event.target===this)closeTemplateGuide()">
  <div style="background:rgba(20,20,40,0.96);border:1px solid rgba(255,255,255,0.06);border-radius:20px;padding:32px;max-width:700px;width:90%;max-height:85vh;overflow-y:auto" onclick="event.stopPropagation()">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="color:#fff;font-size:20px;font-weight:700"><i class="fas fa-book-open"></i> 模版开发指南</h3>
      <span onclick="closeTemplateGuide()" style="cursor:pointer;color:rgba(255,255,255,0.4);font-size:24px">&times;</span>
    </div>
    <div style="color:rgba(255,255,255,0.7);font-size:14px;line-height:1.8">
      <h4 style="color:#a78bfa;font-weight:600;margin-top:20px">系统主页模版</h4>
      <p>文件位置: <code style="background:rgba(0,0,0,0.2);padding:2px 8px;border-radius:4px">templates/landing/</code></p>
      <p>返回值: <code style="background:rgba(0,0,0,0.2);padding:2px 8px;border-radius:4px">['css' => '样式', 'html' => 'HTML']</code></p>
      
      <h4 style="color:#a78bfa;font-weight:600;margin-top:20px">用户专属页模版</h4>
      <p>文件位置: <code style="background:rgba(0,0,0,0.2);padding:2px 8px;border-radius:4px">templates/user/</code></p>
      <p>返回值: <code style="background:rgba(0,0,0,0.2);padding:2px 8px;border-radius:4px">['css' => 'CSS 覆盖样式']</code></p>
      <p><i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i>️ 所有 CSS 规则必须使用 <code style="background:rgba(0,0,0,0.2);padding:2px 8px;border-radius:4px;color:#34d399">!important</code></p>
      
      <h4 style="color:#a78bfa;font-weight:600;margin-top:20px">可用变量列表</h4>
      <p>见右侧「模版变量参考」面板。</p>
      
      <h4 style="color:#a78bfa;font-weight:600;margin-top:20px">示例：最简用户模版</h4>
      <pre style="background:rgba(0,0,0,0.2);border-radius:8px;padding:16px;font-size:12px;overflow-x:auto;margin-top:8px"><code style="color:#34d399">&lt;?php
/**
 * 我的自定义模版
 * 只修改背景颜色和卡片颜色
 */
return [
'css' =&gt; '
body{background:#ff6b6b!important}
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.15)!important;
  border-radius:20px!important;
}
'
];
?&gt;</code></pre>
      
      <h4 style="color:#a78fa2;font-weight:600;margin-top:20px">预览和调试</h4>
      <p>上传后，在「可用模版」中点击「查看」按钮可查看模版源码。用户可在个人设置中切换到自定义模版。</p>
    </div>
  </div>
</div>

<script>
function showTemplateGuide(){
  document.getElementById('templateGuideModal').style.display = 'flex';
}
function closeTemplateGuide(){
  document.getElementById('templateGuideModal').style.display = 'none';
}
</script>
