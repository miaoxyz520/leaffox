<?php
/**
 * Leaffox 用户主页模版 - Dracula（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/dracula.php
 * 风格：Dracula 暗紫高对比，深紫/粉色/青色搭配
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
/* === Dracula 暗紫高对比 === */
body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(135deg, #1a1b2f 0%, #282a36 30%, #44475a 70%, #6272a4 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(ellipse 80% 30% at 30% 0%, rgba(189,147,249,0.06) 0%, transparent 70%),
    radial-gradient(ellipse 60% 40% at 70% 80%, rgba(80,250,123,0.04) 0%, transparent 70%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Space Grotesk",-apple-system,BlinkMacSystemFont,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}
.profile-name{
  color:#f8f8f2!important;
  font-weight:700!important;
  letter-spacing:-0.5px!important;
}
.profile-bio{
  color:rgba(248,248,242,0.55)!important;
}
.avatar-wrap{
  border:2px solid rgba(189,147,249,0.3)!important;
}
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(68,71,90,0.3)!important;
  border:1px solid rgba(98,114,164,0.2)!important;
  border-radius:10px!important;
  box-shadow:0 4px 20px rgba(0,0,0,0.2)!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(68,71,90,0.4)!important;
  border-color:rgba(189,147,249,0.25)!important;
  transform:translateY(-2px)!important;
  box-shadow:0 8px 30px rgba(189,147,249,0.08)!important;
}
.card-title{
  color:#f8f8f2!important;
  font-weight:600!important;
}
.card-sub{
  color:rgba(248,248,242,0.45)!important;
}
.card-icon{
  background:rgba(80,250,123,0.08)!important;
  color:#50fa7b!important;
  border-radius:8px!important;
}
.card-arrow{
  color:rgba(189,147,249,0.4)!important;
}
.top-link-bar{
  background:rgba(68,71,90,0.25)!important;
  border:1px solid rgba(98,114,164,0.15)!important;
  border-radius:10px!important;
  color:#f8f8f2!important;
}
.top-link-bar .link-text{
  color:rgba(248,248,242,0.6)!important;
}
.social-item{
  background:rgba(68,71,90,0.2)!important;
  border:1px solid rgba(98,114,164,0.15)!important;
  color:rgba(248,248,242,0.5)!important;
  border-radius:10px!important;
}
.social-item:hover{
  background:rgba(189,147,249,0.12)!important;
  border-color:rgba(189,147,249,0.25)!important;
  color:#bd93f9!important;
}
.stats-bar{
  color:rgba(248,248,242,0.35)!important;
}
.stats-bar span{
  color:#f8f8f2!important;
}
.announcement-box{
  background:rgba(68,71,90,0.15)!important;
  border:1px solid rgba(255,184,108,0.12)!important;
  border-radius:10px!important;
  color:rgba(248,248,242,0.65)!important;
}
.footer-text{
  color:rgba(248,248,242,0.25)!important;
}
.footer-text a{
  color:rgba(255,121,198,0.5)!important;
}
.free-make-btn{
  background:rgba(80,250,123,0.1)!important;
  border:1px solid rgba(80,250,123,0.15)!important;
  color:#50fa7b!important;
  border-radius:10px!important;
  font-family:"Space Grotesk",monospace!important;
}
.free-make-btn:hover{
  background:rgba(80,250,123,0.15)!important;
}
.music-player-btn{
  background:rgba(255,121,198,0.1)!important;
  border:1px solid rgba(255,121,198,0.15)!important;
  color:#ff79c6!important;
  border-radius:10px!important;
}
.music-player-btn:hover{
  background:rgba(255,121,198,0.15)!important;
}
.text-block{
  background:rgba(68,71,90,0.15)!important;
  border:1px solid rgba(98,114,164,0.12)!important;
  border-radius:10px!important;
  color:rgba(248,248,242,0.6)!important;
}
.picture-block{
  border-radius:10px!important;
  border:1px solid rgba(98,114,164,0.12)!important;
}
.tipping-btn{
  background:rgba(255,184,108,0.1)!important;
  border:1px solid rgba(255,184,108,0.15)!important;
  color:#ffb86c!important;
  border-radius:10px!important;
}
.tipping-btn:hover{
  background:rgba(255,184,108,0.15)!important;
}
.card-lock{
  background:rgba(255,121,198,0.1)!important;
  color:#ff79c6!important;
  border-radius:6px!important;
}
.card-tag{
  background:rgba(80,250,123,0.08)!important;
  color:#50fa7b!important;
  border-radius:4px!important;
}
.report-box,.modal-box,#passModal{
  background:rgba(26,27,47,0.96)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(98,114,164,0.15)!important;
  border-radius:10px!important;
}
#passInput{
  background:rgba(68,71,90,0.2)!important;
  border:1px solid rgba(189,147,249,0.2)!important;
  color:#f8f8f2!important;
  border-radius:8px!important;
}
'
];
