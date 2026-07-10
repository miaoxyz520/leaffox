<?php
/**
 * Leaffox 用户主页模版 - Forest（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/forest.php
 * 风格：森林绿意，自然清新，暖木/苔绿色调
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
/* === Forest 森林绿意 === */
body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(160deg, #1a2e1f 0%, #2d4a33 30%, #4a6741 60%, #6b8c5c 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(ellipse 100% 40% at 50% 100%, rgba(107,140,92,0.12) 0%, transparent 80%),
    radial-gradient(ellipse 60% 40% at 30% 20%, rgba(74,103,65,0.1) 0%, transparent 70%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Inter","DM Serif Display",-apple-system,BlinkMacSystemFont,serif!important;
}
.page-wrap{
  background:transparent!important;
}
.profile-name{
  color:#eaf5e6!important;
  font-family:"DM Serif Display",serif!important;
  font-weight:400!important;
  font-style:italic!important;
  text-shadow:0 2px 8px rgba(0,0,0,0.15)!important;
}
.profile-bio{
  color:rgba(234,245,230,0.65)!important;
}
.avatar-wrap{
  border:2px solid rgba(107,140,92,0.3)!important;
}
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.05)!important;
  backdrop-filter:blur(12px)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  border-radius:12px!important;
  box-shadow:0 4px 20px rgba(0,0,0,0.06)!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.08)!important;
  border-color:rgba(107,140,92,0.15)!important;
  transform:translateY(-2px)!important;
}
.card-title{
  color:#eaf5e6!important;
  font-weight:600!important;
}
.card-sub{
  color:rgba(234,245,230,0.5)!important;
  font-size:12px!important;
}
.card-icon{
  background:rgba(107,140,92,0.12)!important;
  color:#8cb382!important;
  border-radius:50%!important;
}
.card-arrow{
  color:rgba(107,140,92,0.4)!important;
}
.top-link-bar{
  background:rgba(255,255,255,0.05)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  backdrop-filter:blur(16px)!important;
  border-radius:50px!important;
  color:#eaf5e6!important;
}
.top-link-bar .link-text{
  color:rgba(234,245,230,0.65)!important;
}
.social-item{
  background:rgba(255,255,255,0.04)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  color:rgba(234,245,230,0.55)!important;
  border-radius:50%!important;
  width:40px!important;
  height:40px!important;
}
.social-item:hover{
  background:rgba(107,140,92,0.1)!important;
  border-color:rgba(107,140,92,0.2)!important;
  color:#8cb382!important;
}
.footer-text{
  color:rgba(234,245,230,0.3)!important;
  font-size:11px!important;
}
.footer-text a{
  color:rgba(107,140,92,0.5)!important;
}
.stats-bar{
  color:rgba(234,245,230,0.4)!important;
}
.stats-bar span{
  color:#eaf5e6!important;
}
.announcement-box{
  background:rgba(255,255,255,0.03)!important;
  border:1px solid rgba(107,140,92,0.1)!important;
  border-radius:10px!important;
  color:rgba(234,245,230,0.7)!important;
}
.free-make-btn{
  background:rgba(107,140,92,0.12)!important;
  border:1px solid rgba(107,140,92,0.15)!important;
  color:#8cb382!important;
  border-radius:50px!important;
}
.free-make-btn:hover{
  background:rgba(107,140,92,0.18)!important;
}
.music-player-btn{
  background:rgba(107,140,92,0.1)!important;
  border:1px solid rgba(107,140,92,0.15)!important;
  color:#8cb382!important;
  border-radius:50%!important;
}
.music-player-btn:hover{
  background:rgba(107,140,92,0.18)!important;
}
.text-block{
  background:rgba(255,255,255,0.03)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  border-radius:10px!important;
  color:rgba(234,245,230,0.65)!important;
}
.picture-block{
  border-radius:10px!important;
  border:1px solid rgba(255,255,255,0.06)!important;
}
.report-box,.modal-box,#passModal{
  background:rgba(26,46,31,0.95)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(107,140,92,0.12)!important;
  border-radius:12px!important;
}
#passInput{
  background:rgba(255,255,255,0.05)!important;
  border:1px solid rgba(107,140,92,0.2)!important;
  color:#eaf5e6!important;
  border-radius:12px!important;
}
'
];
