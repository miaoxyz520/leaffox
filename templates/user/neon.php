<?php
/**
 * Leaffox 用户主页模版 - Neon（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/neon.php
 * 风格：赛博霓虹，黑暗+霓虹光效，科技感
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
body{
  background:#0a0a0f!important;
  font-family:"JetBrains Mono","Courier New",monospace!important;
}
body::before{
  background:linear-gradient(180deg,rgba(0,0,0,0.6) 0%,rgba(0,0,0,0.1) 50%,rgba(0,0,0,0.6) 100%)!important;
}
/* 扫描线效果 */
body::after{
  content:""!important;display:block!important;
  position:fixed!important;top:0!important;left:0!important;
  width:100%!important;height:2px!important;
  background:linear-gradient(90deg,transparent,rgba(0,255,255,0.08),transparent)!important;
  animation:scanline 3s linear infinite!important;
  pointer-events:none!important;z-index:999!important;
}
@keyframes scanline{
  0%{top:0}
  100%{top:100%}
}

/* ---- 网格背景 ---- */
.page-wrap::after{
  content:""!important;display:block!important;
  position:fixed!important;inset:0!important;
  background-image:
    linear-gradient(rgba(0,255,255,0.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,255,255,0.03) 1px,transparent 1px)!important;
  background-size:50px 50px!important;
  pointer-events:none!important;z-index:-1!important;
}

/* ---- 头像 ---- */
.avatar-wrap{
  border:2px solid rgba(0,255,255,0.2)!important;
  box-shadow:0 0 20px rgba(0,255,255,0.05),0 0 60px rgba(0,255,255,0.02),inset 0 0 20px rgba(0,255,255,0.02)!important;
  border-radius:12px!important;
  transition:all 0.5s!important;
}
.avatar-wrap:hover{
  border-color:#00ffff!important;
  box-shadow:0 0 30px rgba(0,255,255,0.15),0 0 80px rgba(0,255,255,0.05)!important;
  transform:scale(1.05) rotate(0)!important;
}
.avatar-wrap img{border-radius:18px!important}

/* ---- 名称 ---- */
.profile-name{
  font-family:"Orbitron","JetBrains Mono",monospace!important;
  font-size:22px!important;font-weight:600!important;
  color:#00ffff!important;
  text-shadow:0 0 10px rgba(0,255,255,0.3),0 0 30px rgba(0,255,255,0.1)!important;
  letter-spacing:4px!important;
}
.profile-name:hover{-webkit-text-fill-color:#00ffff!important;text-shadow:0 0 20px rgba(0,255,255,0.5)!important}

/* ---- 简介 ---- */
.profile-bio{
  font-family:"JetBrains Mono",monospace!important;
  color:rgba(0,255,255,0.4)!important;
  font-size:13px!important;letter-spacing:1px!important;
}

/* ---- 顶部栏 ---- */
.top-link-bar{
  background:rgba(0,255,255,0.03)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(0,255,255,0.08)!important;
  border-radius:8px!important;
}
.top-link-bar .link-text{
  font-family:"JetBrains Mono",monospace!important;
  color:rgba(0,255,255,0.4)!important;
  font-size:11px!important;
}
.top-link-bar .bar-btn{background:rgba(0,255,255,0.06)!important;color:#00ffff!important;border-radius:6px!important}
.top-link-bar .bar-btn:hover{background:rgba(0,255,255,0.1)!important;box-shadow:0 0 15px rgba(0,255,255,0.05)!important}

/* ---- 公告 ---- */
.announcement-box{
  background:rgba(0,255,255,0.03)!important;
  backdrop-filter:blur(16px)!important;
  border:1px solid rgba(0,255,255,0.06)!important;
  border-radius:8px!important;
  color:rgba(0,255,255,0.6)!important;
  font-size:12px!important;
}
.announcement-box:hover{background:rgba(0,255,255,0.05)!important;border-color:rgba(0,255,255,0.12)!important}

/* ---- 卡片 ---- */
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(0,255,255,0.02)!important;
  backdrop-filter:blur(20px)!important;
  -webkit-backdrop-filter:blur(20px)!important;
  border:1px solid rgba(0,255,255,0.06)!important;
  border-radius:12px!important;
  padding:16px 20px!important;
  transition:all 0.4s!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(0,255,255,0.05)!important;
  border-color:rgba(0,255,255,0.2)!important;
  box-shadow:0 0 20px rgba(0,255,255,0.05),0 4px 20px rgba(0,0,0,0.2)!important;
  transform:translateY(-2px)!important;
}
.card-glass.outline,.card-neumorphism.outline,.card-minimal.outline{
  background:transparent!important;
  border:1px solid rgba(0,255,255,0.1)!important;
}
.card-glass.outline:hover,.card-neumorphism.outline:hover,.card-minimal.outline:hover{
  border-color:rgba(0,255,255,0.25)!important;
  box-shadow:0 0 20px rgba(0,255,255,0.06)!important;
}
.card-neumorphism{box-shadow:4px 4px 10px rgba(0,0,0,0.2),-4px -4px 10px rgba(0,255,255,0.02)!important}
.card-minimal{border-radius:8px!important}
.card-minimal::before{background:linear-gradient(180deg,#00ffff,#00ff88)!important;width:2px!important}

.card-icon{font-size:24px!important;filter:drop-shadow(0 0 8px rgba(0,255,255,0.2))!important}
.card-title{
  font-family:"Orbitron","JetBrains Mono",monospace!important;
  font-size:13px!important;font-weight:600!important;
  color:#00ffff!important;letter-spacing:1px!important;
  text-shadow:0 0 10px rgba(0,255,255,0.15)!important;
}
.card-sub{
  font-family:"JetBrains Mono",monospace!important;
  font-size:10px!important;color:rgba(0,255,255,0.3)!important;
}
.card-arrow{color:rgba(0,255,255,0.15)!important}
.card-glass:hover .card-arrow,.card-neumorphism:hover .card-arrow,.card-minimal:hover .card-arrow{
  transform:translateX(6px)!important;color:#00ffff!important
}
/* 文字模块 */
.text-block{
  background:rgba(0,255,255,0.02)!important;
  border:1px solid rgba(0,255,255,0.04)!important;
  border-radius:8px!important;
  color:rgba(0,255,255,0.5)!important;
  font-size:13px!important;
  font-family:"JetBrains Mono",monospace!important;
}
.text-block:hover{background:rgba(0,255,255,0.04)!important;border-color:rgba(0,255,255,0.08)!important}

/* 图片 */
.picture-block{border-radius:12px!important}
.picture-block img{border-radius:12px!important;border:1px solid rgba(0,255,255,0.06)!important}

/* 社交 */
.social-item{
  width:44px!important;height:44px!important;
  border-radius:10px!important;
  background:rgba(0,255,255,0.03)!important;
  border:1px solid rgba(0,255,255,0.06)!important;
}
.social-item:hover{
  background:rgba(0,255,255,0.08)!important;
  border-color:rgba(0,255,255,0.15)!important;
  box-shadow:0 0 20px rgba(0,255,255,0.06)!important;
}

/* 打赏 */
.tipping-btn{
  border-radius:8px!important;
  background:rgba(0,255,255,0.04)!important;
  border:1px solid rgba(0,255,255,0.08)!important;
  color:rgba(0,255,255,0.6)!important;
  font-family:"JetBrains Mono",monospace!important;
  font-size:13px!important;
}
.tipping-btn:hover{background:rgba(0,255,255,0.08)!important;border-color:rgba(0,255,255,0.15)!important;color:#00ffff!important}

/* 统计 */
.stats-bar{
  background:rgba(0,255,255,0.02)!important;
  backdrop-filter:blur(12px)!important;
  border:1px solid rgba(0,255,255,0.04)!important;
  border-radius:8px!important;
}
.stats-bar span{color:rgba(0,255,255,0.25)!important;font-size:11px!important}
.stats-bar span:hover{color:rgba(0,255,255,0.4)!important}

/* 页脚 */
.footer-text{color:rgba(0,255,255,0.15)!important;font-size:10px!important}
.footer-text .has-powered{border-color:rgba(0,255,255,0.03)!important}
.footer-text a{color:rgba(0,255,255,0.2)!important}
.footer-text a:hover{color:rgba(0,255,255,0.4)!important}
.footer-text span[onclick]{font-size:10px!important}

/* 免费制作 */
.free-make-btn{
  background:rgba(0,255,255,0.06)!important;
  border:1px solid rgba(0,255,255,0.15)!important;
  color:#00ffff!important;
  font-family:"JetBrains Mono",monospace!important;
  font-size:12px!important;
  box-shadow:0 0 20px rgba(0,255,255,0.05)!important;
  border-radius:8px!important;
}
.free-make-btn:hover{background:rgba(0,255,255,0.1)!important;box-shadow:0 0 30px rgba(0,255,255,0.1)!important}

/* 音乐 */
.music-player-btn{
  background:rgba(0,255,255,0.04)!important;
  border:1px solid rgba(0,255,255,0.08)!important;
}

/* 弹窗 */
.report-box,.modal-box{background:rgba(10,10,15,0.96)!important;backdrop-filter:blur(30px)!important;border-color:rgba(0,255,255,0.06)!important}
.report-box h3,.modal-box h3{color:#00ffff!important}
.report-type-btn{border-color:rgba(0,255,255,0.06)!important;color:rgba(0,255,255,0.5)!important}
.report-type-btn:hover{background:rgba(0,255,255,0.06)!important;border-color:rgba(0,255,255,0.12)!important;color:#00ffff!important}
.report-type-btn.selected{background:rgba(0,255,255,0.1)!important;border-color:#00ffff!important;color:#00ffff!important}
.share-modal-box{background:rgba(10,10,15,0.96)!important;border-color:rgba(0,255,255,0.06)!important}
'
];
