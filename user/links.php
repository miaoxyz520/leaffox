<?php
/**
 * 用户 - 链接/模块管理（支持全部5种模块类型）
 * link/text/image/picture/video
 */
$uid = (int)$_SESSION['user_id'];
$msg = '';

// 新增/编辑
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $type       = in_array($_POST['type'] ?? 'link', ['link','text','image','picture','video']) ? $_POST['type'] ?? '' : 'link';
        $title      = safeSubstr(trim($_POST['title'] ?? ''), 100);
        $url        = safeSubstr(trim($_POST['url'] ?? ''), 500);
        $icon       = safeSubstr(trim($_POST['icon'] ?? '<i class="fas fa-link"></i>'), 100);
        $cardColor  = safeSubstr(trim($_POST['card_color'] ?? 'rgba(255,255,255,0.08)'), 20);
        $textColor  = safeSubstr(trim($_POST['text_color'] ?? '#ffffff'), 20);
        $sortOrder  = (int)($_POST['sort_order'] ?? 50);
        $outline    = (int)(!empty($_POST['outline'] ?? ''));
        $passcode   = safeSubstr(trim($_POST['passcode'] ?? ''), 10);
        $popupImg   = safeSubstr(trim($_POST['popup_img'] ?? ''), 500);
        $textCenter = (int)(!empty($_POST['text_center'] ?? ''));
        $videoFile  = safeSubstr(trim($_POST['video_file'] ?? ''), 500);
        $videoSource= in_array($_POST['video_source'] ?? 'file', ['file','bilibili','douyin','kuaishou']) ? $_POST['video_source'] ?? '' : 'file';
        $videoLoop  = (int)(!empty($_POST['video_loop'] ?? ''));
        $videoPoster= safeSubstr(trim($_POST['video_poster'] ?? ''), 500);
        $videoExtUrl= safeSubstr(trim($_POST['video_external_url'] ?? ''), 500);
        
        // 根据type确定实际存储的url
        if ($type === 'video' && $videoSource !== 'file' && !empty($videoExtUrl)) {
            $url = $videoExtUrl; // 视频外链用 video_external_url
        }
        
        if ($type === 'link' && empty($title)) { $msg = '请输入链接标题'; }
        elseif ($type === 'link' && empty($url)) { $msg = '请输入跳转地址'; }
        elseif ($type === 'text' && empty($title)) { $msg = '请输入文字内容'; }
        elseif ($type === 'image' && empty($popupImg)) { $msg = '请上传弹窗图片'; }
        elseif ($type === 'picture' && empty($icon)) { $msg = '请上传图片或输入图片URL'; }
        elseif ($type === 'video' && $videoSource === 'file' && empty($videoFile)) { $msg = '请输入视频地址'; }
        elseif ($type === 'video' && $videoSource !== 'file' && empty($videoExtUrl) && empty($url)) { $msg = '请输入视频链接'; }
        else {
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO links (user_id,type,title,url,icon,card_color,text_color,sort_order,outline,passcode,popup_img,text_center,video_file,video_source,video_loop,video_poster) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$uid,$type,$title,$url,$icon,$cardColor,$textColor,$sortOrder,$outline,$passcode,$popupImg,$textCenter,$videoFile,$videoSource,$videoLoop,$videoPoster]);
                $msg = '模块已添加';
            } else {
                $lid = (int)($_POST['link_id'] ?? 0);
                $stmt = $db->prepare("UPDATE links SET type=?,title=?,url=?,icon=?,card_color=?,text_color=?,sort_order=?,outline=?,passcode=?,popup_img=?,text_center=?,video_file=?,video_source=?,video_loop=?,video_poster=? WHERE id=? AND user_id=?");
                $stmt->execute([$type,$title,$url,$icon,$cardColor,$textColor,$sortOrder,$outline,$passcode,$popupImg,$textCenter,$videoFile,$videoSource,$videoLoop,$videoPoster,$lid,$uid]);
                $msg = '模块已更新';
            }
        }
    } elseif ($action === 'toggle_hide') {
        $lid = (int)($_POST['link_id'] ?? 0);
        $db->prepare("UPDATE links SET is_hidden = CASE WHEN is_hidden=1 THEN 0 ELSE 1 END WHERE id=? AND user_id=?")->execute([$lid, $uid]);
        $msg = '状态已切换';
    } elseif ($action === 'delete') {
        $lid = (int)($_POST['link_id'] ?? 0);
        $db->prepare("DELETE FROM links WHERE id=? AND user_id=?")->execute([$lid, $uid]);
        $msg = '模块已删除';
    }
}

// 获取所有
$links = $db->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY sort_order ASC, id DESC");
$links->execute([$uid]);
$linkList = $links->fetchAll();

$typeLabels = ['link'=>'<i class="fas fa-link"></i> 链接','text'=>'<i class="fas fa-edit"></i> 文字','image'=>'<i class="fas fa-image"></i> 弹图','picture'=>'<i class="fas fa-image"></i> 图片','video'=>'<i class="fas fa-video"></i> 视频'];
$typeBadges = ['link'=>'bg-blue-500/20 text-blue-300','text'=>'bg-green-500/20 text-green-300','image'=>'bg-orange-500/20 text-orange-300','picture'=>'bg-pink-500/20 text-pink-300','video'=>'bg-purple-500/20 text-purple-300'];
?>
<link rel="stylesheet" href="../assets/css/fontawesome.min.css">
<h1 class="text-xl font-bold mb-2"><i class="fas fa-cube"></i> 模块管理</h1>
<p class="text-gray-500 text-sm mb-6">添加不同类型的模块到你的主页（链接/文字/图片/弹窗图/视频）</p>

<?php if ($msg): ?>
<div class="bg-<?=strpos($msg,'已')!==false?'emerald':'red'?>-500/10 border border-<?=strpos($msg,'已')!==false?'emerald':'red'?>-500/30 text-<?=strpos($msg,'已')!==false?'emerald':'red'?>-300 px-4 py-3 rounded-xl mb-4 text-sm"><?=h($msg)?></div>
<?php endif; ?>

<div class="flex gap-2 mb-5 flex-wrap">
  <button onclick="openModal('add','link')" class="btn-sm btn-primary"><i class="fas fa-plus"></i> <i class="fas fa-link"></i> 链接</button>
  <button onclick="openModal('add','text')" class="btn-sm btn-ghost"><i class="fas fa-plus"></i> <i class="fas fa-edit"></i> 文字</button>
  <button onclick="openModal('add','image')" class="btn-sm btn-ghost"><i class="fas fa-plus"></i> <i class="fas fa-image"></i> 弹图</button>
  <button onclick="openModal('add','picture')" class="btn-sm btn-ghost"><i class="fas fa-plus"></i> <i class="fas fa-image"></i> 图片</button>
  <button onclick="openModal('add','video')" class="btn-sm btn-ghost"><i class="fas fa-plus"></i> <i class="fas fa-video"></i> 视频</button>
</div>

<?php if (empty($linkList)): ?>
<div class="card-base text-center py-12">
  <div class="text-4xl mb-3"><i class="fas fa-cube"></i></div>
  <p class="text-gray-500">还没有添加任何模块</p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 gap-3">
  <?php foreach ($linkList as $l): ?>
  <div class="card-base flex items-center gap-3 py-3 px-4 <?=$l['is_hidden']?'opacity-50':''?>">
    <span class="text-xl"><?=h(mb_substr($l['icon'],0,2))?></span>
    <div class="flex-1 min-w-0">
      <div class="text-white font-medium text-sm truncate flex items-center gap-2">
        <?=h($l['title'] ?: '(无标题)')?>
        <span class="text-xs px-2 py-0.5 rounded-full <?=$typeBadges[$l['type']]?>"><?=$l['type']?></span>
        <?php if(!empty($l['passcode'])):?><span class="text-xs text-yellow-400"><i class="fas fa-lock"></i></span><?php endif;?>
      </div>
      <div class="text-gray-500 text-xs truncate mt-0.5">
        排序: <?=$l['sort_order']?> · 点击: <?=$l['click_count']?> · <?=$l['is_hidden']?'已隐藏':'显示中'?>
        <?=$l['is_violation']?'· <span class="text-red-400">违规下架</span>':''?>
      </div>
    </div>
    <div class="flex gap-1.5">
      <button onclick="editLink(<?=$l['id']?>,'<?=$l['type']?>','<?=h(addslashes($l['title']))?>','<?=h(addslashes($l['url']))?>','<?=h(addslashes($l['icon']))?>','<?=h(addslashes($l['card_color']))?>','<?=h(addslashes($l['text_color']))?>',<?=$l['sort_order']?>,<?=$l['outline']?>,'<?=h(addslashes($l['passcode']))?>','<?=h(addslashes($l['popup_img']))?>',<?=$l['text_center']?>,'<?=h(addslashes($l['video_file']))?>','<?=h(addslashes($l['video_source']))?>',<?=$l['video_loop']?>,'<?=h(addslashes($l['video_poster']))?>')" class="btn-sm btn-ghost text-xs px-2"><i class="fas fa-edit"></i></button>
      <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="toggle_hide"><input type="hidden" name="link_id" value="<?=$l['id']?>">
        <button type="submit" class="btn-sm btn-ghost text-xs px-2"><i class="fas fa-<?=$l['is_hidden']?'eye':'eye-slash'?>"></i></button>
      </form>
      <form method="POST" style="display:inline" onsubmit="return confirm('确认删除？')">
        <input type="hidden" name="action" value="delete"><input type="hidden" name="link_id" value="<?=$l['id']?>">
        <button type="submit" class="btn-sm btn-ghost text-xs px-2 text-red-400"><i class="fas fa-trash"></i></button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- 弹窗 -->
<div id="modalOverlay" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeModal()">
  <div class="modal-box sm" onclick="event.stopPropagation()">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-white font-semibold text-lg" id="modalTitle">添加模块</h3>
      <span onclick="closeModal()" class="cursor-pointer text-gray-500 hover:text-white text-xl"><i class="fas fa-times"></i></span>
    </div>
    <form method="POST" id="modalForm" enctype="multipart/form-data">
      <input type="hidden" name="action" id="formAction" value="add">
      <input type="hidden" name="link_id" id="formId" value="0">
      <input type="hidden" name="type" id="formType" value="link">
      
      <div id="fieldCommon" class="space-y-3">
        <!-- 通用：标题 -->
        <div id="titleField">
          <label class="block text-gray-300 text-xs font-medium mb-1.5">标题</label>
          <input type="text" name="title" id="formTitle" placeholder="模块标题" required>
        </div>
        
        <!-- 通用：图标（适用于 link/image/video） -->
        <div id="iconField">
          <label class="block text-gray-300 text-xs font-medium mb-1.5">图标（Emoji或图片URL）</label>
          <input type="text" name="icon" id="formIcon" placeholder="<i class="fas fa-link"></i>">
        </div>
      </div>

      <!-- 链接专用 -->
      <div id="fieldLink" class="space-y-3 hidden">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">跳转地址</label><input type="text" name="url" id="formUrl" placeholder="https://"></div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">访问密码（留空=无需密码）</label><input type="text" name="passcode" id="formPasscode" placeholder="留空不设密码" maxlength="10"></div>
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300 mt-2">
          <input type="checkbox" name="outline" id="formOutline" value="1" class="accent-indigo-500 w-4 h-4"> 空心按钮样式
        </label>
      </div>

      <!-- 弹图(image)专用 -->
      <div id="fieldImage" class="space-y-3 hidden">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">弹窗图片URL</label><input type="text" name="popup_img" id="formPopupImg" placeholder="点击后展示的图片URL"></div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">或上传图片</label>
          <div class="flex gap-2">
            <input type="file" accept="image/*" id="imageUpload" onchange="uploadFile(this, 'image', 'formPopupImg')" class="block w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 cursor-pointer">
          </div>
          <div id="imageUploadProgress" class="text-xs text-indigo-400 hidden mt-1"><i class="fas fa-spinner fa-spin"></i> 上传中...</div>
        </div>
      </div>

      <!-- 文字(text)专用 -->
      <div id="fieldText" class="space-y-3 hidden">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">文字内容</label><textarea name="title" id="formTextContent" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-indigo-500/50 resize-none" placeholder="输入文字内容..."></textarea></div>
        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300">
          <input type="checkbox" name="text_center" id="formTextCenter" value="1" class="accent-indigo-500 w-4 h-4"> 居中显示
        </label>
      </div>

      <!-- 图片(picture)专用 -->
      <div id="fieldPicture" class="space-y-3 hidden">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">图片URL</label><input type="text" name="icon" id="formPictureUrl" placeholder="https://example.com/image.jpg"></div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">或上传图片</label>
          <div class="flex gap-2">
            <input type="file" accept="image/*" id="pictureUpload" onchange="uploadFile(this, 'image', 'formPictureUrl')" class="block w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 cursor-pointer">
          </div>
          <div id="pictureUploadProgress" class="text-xs text-indigo-400 hidden mt-1"><i class="fas fa-spinner fa-spin"></i> 上传中...</div>
        </div>
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">图片描述（Alt）</label><input type="text" name="title" id="formPictureAlt" placeholder="描述文字"></div>
      </div>

      <!-- 视频(video)专用 -->
      <div id="fieldVideo" class="space-y-3 hidden">
        <div><label class="block text-gray-300 text-xs font-medium mb-1.5">视频来源</label>
          <select name="video_source" id="formVideoSource" onchange="toggleVideoSource()" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white outline-none focus:border-indigo-500/50">
            <option value="file"><i class="fas fa-folder-open"></i> 上传/直链</option>
            <option value="bilibili"><i class="fas fa-tv"></i> B站</option>
            <option value="douyin"><i class="fas fa-music"></i> 抖音</option>
            <option value="kuaishou"><i class="fas fa-mobile-alt"></i> 快手</option>
          </select>
        </div>
        <!-- 直链模式 -->
        <div id="vsrcFile">
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">视频文件URL</label><input type="text" name="video_file" id="formVideoUrl" placeholder="https://example.com/video.mp4"></div>
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">或上传视频</label>
            <div class="flex gap-2">
              <input type="file" accept="video/*" id="videoUpload" onchange="uploadFile(this, 'video', 'formVideoUrl')" class="block w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 cursor-pointer">
            </div>
            <div id="videoUploadProgress" class="text-xs text-indigo-400 hidden mt-1"><i class="fas fa-spinner fa-spin"></i> 上传中...</div>
          </div>
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">视频封面URL</label><input type="text" name="video_poster" id="formVideoPoster" placeholder="封面图片URL（可选）"></div>
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">或上传封面</label>
            <div class="flex gap-2">
              <input type="file" accept="image/*" id="posterUpload" onchange="uploadFile(this, 'image', 'formVideoPoster')" class="block w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 cursor-pointer">
            </div>
            <div id="posterUploadProgress" class="text-xs text-indigo-400 hidden mt-1"><i class="fas fa-spinner fa-spin"></i> 上传中...</div>
          </div>
          <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-300">
            <input type="checkbox" name="video_loop" id="formVideoLoop" value="1" class="accent-indigo-500 w-4 h-4"> 循环播放
          </label>
        </div>
        <!-- 外部平台模式 -->
        <div id="vsrcExternal" class="hidden">
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">视频链接</label><input type="text" name="video_external_url" id="formVideoExternalUrl" placeholder="粘贴视频分享链接，如 https://www.bilibili.com/video/BV1xx..."></div>
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">视频封面URL（可选）</label><input type="text" name="video_poster" id="formVideoExternalPoster" placeholder="封面图片URL"></div>
          <p id="vsrcHint" class="text-xs text-gray-500 mt-1"><i class="fas fa-lightbulb"></i> 粘贴完整视频链接后，访问者点击将跳转至对应平台观看</p>
        </div>
      </div>

      <!-- 通用设置 -->
      <div class="mt-4 pt-4 border-t border-white/10">
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">卡片颜色</label><input type="text" name="card_color" id="formCardColor" placeholder="rgba(255,255,255,0.08)"></div>
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">文字颜色</label><input type="text" name="text_color" id="formTextColor" placeholder="#ffffff"></div>
          <div><label class="block text-gray-300 text-xs font-medium mb-1.5">排序（越小越靠前）</label><input type="number" name="sort_order" id="formSortOrder" value="50" min="0" max="999"></div>
        </div>
      </div>

      <button type="submit" class="btn-sm btn-primary w-full mt-4 py-3"><i class="fas fa-save"></i> 保存模块</button>
    </form>
  </div>
</div>

<style>
.modal-overlay{position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.6);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;animation:fadeIn 0.25s ease}
.modal-box.sm{background:rgba(20,20,35,0.96);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:28px;max-width:440px;width:92%;max-height:90vh;overflow-y:auto;animation:slideUp 0.3s ease}

/* ===== 美化输入框 ===== */
.modal-box input[type="text"],
.modal-box input[type="number"],
.modal-box input[type="password"] {
  width:100%;
  padding:12px 16px;
  border-radius:12px;
  border:1.5px solid rgba(255,255,255,0.1);
  background:rgba(255,255,255,0.05);
  color:#fff;
  font-size:14px;
  outline:none;
  transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  box-shadow:inset 0 2px 4px rgba(0,0,0,0.1);
}
.modal-box input[type="text"]:hover,
.modal-box input[type="number"]:hover {
  border-color:rgba(255,255,255,0.2);
  background:rgba(255,255,255,0.07);
}
.modal-box input[type="text"]:focus,
.modal-box input[type="number"]:focus,
.modal-box input[type="password"]:focus {
  border-color:rgba(99,102,241,0.6);
  background:rgba(99,102,241,0.06);
  box-shadow:0 0 0 3px rgba(99,102,241,0.15),inset 0 2px 4px rgba(0,0,0,0.1);
}
.modal-box input[type="text"]::placeholder,
.modal-box input[type="number"]::placeholder,
.modal-box textarea::placeholder { color:rgba(255,255,255,0.25); }

/* 美化 textarea */
.modal-box textarea {
  width:100%;
  padding:12px 16px;
  border-radius:12px;
  border:1.5px solid rgba(255,255,255,0.1);
  background:rgba(255,255,255,0.05);
  color:#fff;
  font-size:14px;
  outline:none;
  transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  font-family:inherit;
}
.modal-box textarea:hover { border-color:rgba(255,255,255,0.2); background:rgba(255,255,255,0.07); }
.modal-box textarea:focus { border-color:rgba(99,102,241,0.6); background:rgba(99,102,241,0.06); box-shadow:0 0 0 3px rgba(99,102,241,0.15); }

/* 美化 checkbox */
.modal-box input[type="checkbox"] { 
  width:18px;height:18px;border-radius:5px;
  accent-color:#6366f1;
  cursor:pointer;
  transition:transform 0.2s;
}
.modal-box input[type="checkbox"]:hover { transform:scale(1.15); }
.modal-box input[type="checkbox"]:active { transform:scale(0.95); }

/* 美化 type=file */
.modal-box input[type="file"] {
  width:100%;
  font-size:12px;
  color:rgba(255,255,255,0.5);
  cursor:pointer;
  transition:opacity 0.2s;
}
.modal-box input[type="file"]::file-selector-button {
  padding:8px 16px;
  border-radius:10px;
  border:none;
  font-size:12px;
  font-weight:600;
  background:linear-gradient(135deg,rgba(99,102,241,0.3),rgba(168,85,247,0.3));
  color:#a5b4fc;
  cursor:pointer;
  transition:all 0.3s;
  margin-right:12px;
}
.modal-box input[type="file"]::file-selector-button:hover {
  background:linear-gradient(135deg,rgba(99,102,241,0.5),rgba(168,85,247,0.5));
  color:#fff;
  transform:translateY(-1px);
  box-shadow:0 4px 12px rgba(99,102,241,0.2);
}

/* 美化提交按钮 */
.modal-box button[type="submit"] {
  width:100%;
  padding:14px;
  border-radius:14px;
  background:linear-gradient(135deg,#6366f1,#a78bfa);
  color:#fff;
  border:none;
  font-size:15px;
  font-weight:700;
  cursor:pointer;
  transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  letter-spacing:0.5px;
  position:relative;
  overflow:hidden;
}
.modal-box button[type="submit"]:hover {
  transform:translateY(-2px);
  box-shadow:0 8px 25px rgba(99,102,241,0.35);
}
.modal-box button[type="submit"]:active { transform:translateY(0) scale(0.98); }
.modal-box button[type="submit"]::after {
  content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,0.15),transparent);
  transition:left 0.5s;
}
.modal-box button[type="submit"]:hover::after { left:100%; }

/* label 美化 */
.modal-box label {
  display:block;
  font-size:12px;
  font-weight:600;
  color:rgba(255,255,255,0.6);
  margin-bottom:6px;
  letter-spacing:0.3px;
  transition:color 0.3s;
}
.modal-box label:hover { color:rgba(255,255,255,0.8); }

/* checkbox 行 */
.modal-box .flex.items-center.gap-2 { margin-top:4px; }
.modal-box .flex.items-center.gap-2 label { 
  display:inline; 
  font-weight:400;
  color:rgba(255,255,255,0.55);
  cursor:pointer;
}

/* 动画 */
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(30px) scale(0.97)}to{opacity:1;transform:translateY(0) scale(1)}}

/* 进度提示美化 */
#pictureUploadProgress, #videoUploadProgress, #imageUploadProgress, #posterUploadProgress {
  display:none;
  padding:8px 12px;
  border-radius:10px;
  background:rgba(99,102,241,0.1);
  border:1px solid rgba(99,102,241,0.2);
  color:#a5b4fc;
  font-size:12px;
  margin-top:6px;
}
</style>

<script>
function openModal(action, type){
  document.getElementById('formAction').value = action;
  document.getElementById('formType').value = type;
  document.getElementById('modalTitle').textContent = (action === 'add' ? '添加' : '编辑') + ' ' + getTypeLabel(type);
  if(action === 'add'){
    document.getElementById('formId').value = 0;
    // 重置视频来源为默认
    var vs = document.getElementById('formVideoSource');
    if(vs) vs.value = 'file';
  }
  showTypeFields(type);
  toggleVideoSource();
  document.getElementById('modalOverlay').style.display = 'flex';
}
function closeModal(){ document.getElementById('modalOverlay').style.display = 'none'; }

// 视频来源切换
function toggleVideoSource(){
  var vs = document.getElementById('formVideoSource');
  if(!vs) return;
  var isFile = vs.value === 'file';
  document.getElementById('vsrcFile').classList.toggle('hidden', !isFile);
  document.getElementById('vsrcExternal').classList.toggle('hidden', isFile);
  // 更新提示文字
  var hint = document.getElementById('vsrcHint');
  if(hint){
    var labels = {'bilibili':'<i class="fas fa-tv"></i> 跳转B站观看，支持iframe内嵌播放','douyin':'<i class="fas fa-music"></i> 跳转抖音App或网页观看','kuaishou':'<i class="fas fa-mobile-alt"></i> 跳转快手App或网页观看'};
    hint.textContent = '<i class="fas fa-lightbulb"></i> ' + (labels[vs.value] || '粘贴完整视频链接后，访问者点击将跳转至对应平台观看');
  }
}

// 文件上传（图片/视频）
function uploadFile(input, type, targetInputId){
  var file = input.files[0];
  if(!file) return;
  var progressId = (type === 'video' ? 'videoUploadProgress' : 'pictureUploadProgress');
  var progress = document.getElementById(progressId);
  progress.classList.remove('hidden');
  
  var formData = new FormData();
  formData.append('file', file);
  formData.append('type', type);
  
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '<?=BASE_URL?>/api/upload.php', true);
  xhr.onload = function(){
    progress.classList.add('hidden');
    if(xhr.status === 200){
      try {
        var r = JSON.parse(xhr.responseText);
        if(r.success){
          document.getElementById(targetInputId).value = r.url;
        } else {
          alert('上传失败：' + r.msg);
        }
      } catch(e){
        alert('上传返回异常');
      }
    } else {
      alert('上传失败（' + xhr.status + '）');
    }
  };
  xhr.onerror = function(){
    progress.classList.add('hidden');
    alert('网络错误，上传失败');
  };
  xhr.send(formData);
}

function showTypeFields(type){
  // 先禁用所有同名隐藏字段（防止覆盖提交值）
  document.getElementById('formTitle').disabled = true;
  document.getElementById('formTextContent').disabled = true;
  document.getElementById('formPictureAlt').disabled = true;
  document.getElementById('formIcon').disabled = true;
  document.getElementById('formPictureUrl').disabled = true;
  
  // 隐藏所有专用字段
  ['fieldLink','fieldImage','fieldText','fieldPicture','fieldVideo'].forEach(function(id){
    document.getElementById(id).classList.add('hidden');
  });
  document.getElementById('iconField').classList.remove('hidden');
  document.getElementById('titleField').classList.remove('hidden');
  document.getElementById('formTitle').required = true;
  document.getElementById('formTitle').placeholder = '模块标题';
  document.getElementById('formTitle').disabled = false;
  document.getElementById('formIcon').disabled = false;
  
  // 移除所有类型专用字段的 required（避免隐藏字段影响表单提交）
  document.getElementById('formUrl').required = false;
  document.getElementById('formVideoUrl').required = false;
  document.getElementById('formPopupImg').required = false;
  
  if(type === 'link'){
    document.getElementById('fieldLink').classList.remove('hidden');
    document.getElementById('formUrl').required = true;
  } else if(type === 'image'){
    document.getElementById('fieldImage').classList.remove('hidden');
  } else if(type === 'text'){
    document.getElementById('fieldText').classList.remove('hidden');
    document.getElementById('iconField').classList.add('hidden');
    document.getElementById('titleField').classList.add('hidden');
    document.getElementById('formTitle').required = false;
    document.getElementById('formTitle').disabled = true;
    document.getElementById('formIcon').disabled = true;
    document.getElementById('formTextContent').disabled = false;
  } else if(type === 'picture'){
    document.getElementById('fieldPicture').classList.remove('hidden');
    document.getElementById('iconField').classList.add('hidden');
    document.getElementById('titleField').classList.add('hidden');
    document.getElementById('formTitle').required = false;
    document.getElementById('formTitle').disabled = true;
    document.getElementById('formIcon').disabled = true;
    document.getElementById('formPictureUrl').disabled = false;
    document.getElementById('formPictureAlt').disabled = false;
  } else if(type === 'video'){
    document.getElementById('fieldVideo').classList.remove('hidden');
    document.getElementById('formTitle').placeholder = '视频标题';
  }
}

function getTypeLabel(t){ return {'link':'链接','text':'文字','image':'弹图','picture':'图片','video':'视频'}[t]||t; }

function editLink(id,type,title,url,icon,cardColor,textColor,sortOrder,outline,passcode,popupImg,textCenter,videoFile,videoSource,videoLoop,videoPoster){
  openModal('edit', type);
  document.getElementById('formId').value = id;
  document.getElementById('formTitle').value = title;
  document.getElementById('formUrl').value = url;
  document.getElementById('formIcon').value = icon;
  document.getElementById('formCardColor').value = cardColor;
  document.getElementById('formTextColor').value = textColor;
  document.getElementById('formSortOrder').value = sortOrder;
  document.getElementById('formOutline').checked = outline == 1;
  document.getElementById('formPasscode').value = passcode || '';
  document.getElementById('formPopupImg').value = popupImg || '';
  document.getElementById('formTextCenter').checked = textCenter == 1;
  document.getElementById('formVideoUrl').value = videoFile || '';
  document.getElementById('formVideoPoster').value = videoPoster || '';
  document.getElementById('formVideoLoop').checked = videoLoop == 1;
  var vs = document.getElementById('formVideoSource');
  if(vs && videoSource) vs.value = videoSource;
  // 外部平台模式时，将url填入外部视频链接输入框
  var extUrl = document.getElementById('formVideoExternalUrl');
  if(extUrl) {
    if(videoSource && videoSource !== 'file') {
      extUrl.value = url || '';
    } else {
      extUrl.value = '';
    }
  }
  var extPoster = document.getElementById('formVideoExternalPoster');
  if(extPoster && videoSource && videoSource !== 'file') extPoster.value = videoPoster || '';
  toggleVideoSource();
  if(type === 'text'){
    document.getElementById('formTextContent').value = title;
  }
  if(type === 'picture'){
    document.getElementById('formPictureUrl').value = icon;
    document.getElementById('formPictureAlt').value = title;
  }
}
</script>
