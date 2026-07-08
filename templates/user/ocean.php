<?php
/**
 * Leaffox 用户主页模版 - Ocean（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/ocean.php
 * 风格：海洋蓝调，渐变蓝青色系，波浪元素
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
/* === Ocean 海洋蓝调 === */
@import url("https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap");

body::before{
  content:"";
  position:fixed;inset:0;
  background:linear-gradient(135deg,#0c2340 0%,#0f4c81 30%,#1a759f 60%,#4ecdc4 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(ellipse 120% 60% at 20% 90%, rgba(78,205,196,0.15) 0%, transparent 70%),
    radial-gradient(ellipse 80% 50% at 80% 10%, rgba(15,76,129,0.2) 0%, transparent 70%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Quicksand",-apple-system,BlinkMacSystemFont,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}
.profile-name{
  color:#e2f5ff!important;
  text-shadow:0 2px 12px rgba(15,76,129,0.3)!important;
  font-weight:700!important;
}
.profile-bio{
  color:rgba(226,245,255,0.75)!important;
}
.avatar-wrap{
  border:3px solid rgba(78,205,196,0.4)!important;
  box-shadow:0 4px 24px rgba(78,205,196,0.15)!important;
}
.avatar-wrap img{
  border-radius:50%!important;
}
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.06)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(255,255,255,0.08)!important;
  border-radius:16px!important;
  box-shadow:0 8px 32px rgba(0,0,0,0.08)!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.10)!important;
  border-color:rgba(78,205,196,0.2)!important;
  transform:translateY(-3px)!important;
  box-shadow:0 12px 40px rgba(78,205,196,0.1)!important;
}
.card-title{
  color:#e2f5ff!important;
  font-weight:600!important;
}
.card-sub{
  color:rgba(226,245,255,0.55)!important;
}
.card-icon{
  background:rgba(78,205,196,0.12)!important;
  color:#4ecdc4!important;
}
.card-arrow{
  color:rgba(78,205,196,0.5)!important;
}
.top-link-bar{
  background:rgba(255,255,255,0.06)!important;
  border:1px solid rgba(255,255,255,0.08)!important;
  backdrop-filter:blur(16px)!important;
  color:#e2f5ff!important;
  border-radius:12px!important;
}
.top-link-bar .link-text{
  color:rgba(226,245,255,0.7)!important;
}
.announcement-box{
  background:rgba(255,255,255,0.04)!important;
  border:1px solid rgba(78,205,196,0.15)!important;
  border-radius:12px!important;
  color:rgba(226,245,255,0.75)!important;
}
.social-item{
  background:rgba(255,255,255,0.06)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  color:rgba(226,245,255,0.6)!important;
  border-radius:12px!important;
}
.social-item:hover{
  background:rgba(78,205,196,0.1)!important;
  border-color:rgba(78,205,196,0.2)!important;
  color:#4ecdc4!important;
}
.stats-bar{
  color:rgba(226,245,255,0.5)!important;
}
.stats-bar span{
  color:#e2f5ff!important;
}
.footer-text{
  color:rgba(226,245,255,0.35)!important;
}
.footer-text a{
  color:rgba(78,205,196,0.6)!important;
}
.free-make-btn{
  background:rgba(78,205,196,0.15)!important;
  border:1px solid rgba(78,205,196,0.2)!important;
  color:#4ecdc4!important;
  border-radius:12px!important;
}
.free-make-btn:hover{
  background:rgba(78,205,196,0.2)!important;
}
.music-player-btn{
  background:rgba(78,205,196,0.12)!important;
  border:1px solid rgba(78,205,196,0.15)!important;
  color:#4ecdc4!important;
}
.music-player-btn:hover{
  background:rgba(78,205,196,0.2)!important;
}
.text-block{
  background:rgba(255,255,255,0.04)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  border-radius:12px!important;
  color:rgba(226,245,255,0.7)!important;
}
.picture-block{
  border-radius:12px!important;
  border:1px solid rgba(255,255,255,0.06)!important;
}
.tipping-btn{
  background:rgba(78,205,196,0.12)!important;
  border:1px solid rgba(78,205,196,0.15)!important;
  color:#4ecdc4!important;
  border-radius:12px!important;
}
.tipping-btn:hover{
  background:rgba(78,205,196,0.2)!important;
}
.card-lock{
  background:rgba(78,205,196,0.1)!important;
  color:#4ecdc4!important;
  border-radius:8px!important;
}
.card-tag{
  background:rgba(78,205,196,0.08)!important;
  color:#4ecdc4!important;
  border-radius:6px!important;
  font-size:10px!important;
}
.modal-box,#passModal{
  background:rgba(12,35,64,0.95)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(78,205,196,0.15)!important;
  border-radius:20px!important;
}
#passInput{
  background:rgba(255,255,255,0.06)!important;
  border:1px solid rgba(78,205,196,0.2)!important;
  color:#e2f5ff!important;
  border-radius:10px!important;
}
.report-box{
  background:rgba(12,35,64,0.95)!important;
  border:1px solid rgba(78,205,196,0.1)!important;
  border-radius:20px!important;
}
.report-type-btn{
  background:rgba(255,255,255,0.04)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  color:rgba(226,245,255,0.6)!important;
  border-radius:10px!important;
}
.report-type-btn:hover{
  background:rgba(78,205,196,0.08)!important;
  border-color:rgba(78,205,196,0.2)!important;
  color:#4ecdc4!important;
}
'
];
