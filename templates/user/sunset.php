<?php
/**
 * Leaffox 用户主页模版 - Sunset（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/sunset.php
 * 风格：日落暖色，橙红/粉紫渐变，温暖治愈
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
/* === Sunset 日落暖色 === */
@import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;600;700&display=swap");

body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(135deg, #2d1b2e 0%, #5c2a3d 20%, #c2574d 50%, #f4a460 80%, #ffd8a8 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(ellipse 150% 30% at 50% 0%, rgba(244,164,96,0.15) 0%, transparent 80%),
    radial-gradient(ellipse 100% 50% at 80% 80%, rgba(194,87,77,0.08) 0%, transparent 70%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Nunito","Playfair Display",-apple-system,BlinkMacSystemFont,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}
.profile-name{
  color:#fff5ee!important;
  font-family:"Playfair Display",serif!important;
  font-weight:700!important;
  font-size:28px!important;
  text-shadow:0 2px 16px rgba(194,87,77,0.3)!important;
}
.profile-bio{
  color:rgba(255,245,238,0.7)!important;
}
.avatar-wrap{
  border:3px solid rgba(244,164,96,0.3)!important;
  box-shadow:0 4px 30px rgba(194,87,77,0.15)!important;
}
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.05)!important;
  backdrop-filter:blur(16px)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  border-radius:20px!important;
  box-shadow:0 4px 20px rgba(0,0,0,0.05)!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.08)!important;
  border-color:rgba(244,164,96,0.15)!important;
  transform:translateY(-3px)!important;
  box-shadow:0 8px 30px rgba(244,164,96,0.08)!important;
}
.card-title{
  color:#fff5ee!important;
  font-weight:700!important;
}
.card-sub{
  color:rgba(255,245,238,0.5)!important;
}
.card-icon{
  background:rgba(244,164,96,0.12)!important;
  color:#f4a460!important;
  border-radius:10px!important;
}
.card-arrow{
  color:rgba(244,164,96,0.4)!important;
}
.top-link-bar{
  background:rgba(255,255,255,0.05)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  backdrop-filter:blur(16px)!important;
  border-radius:12px!important;
  color:#fff5ee!important;
}
.top-link-bar .link-text{
  color:rgba(255,245,238,0.65)!important;
}
.social-item{
  background:rgba(255,255,255,0.04)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  color:rgba(255,245,238,0.55)!important;
  border-radius:12px!important;
}
.social-item:hover{
  background:rgba(244,164,96,0.1)!important;
  border-color:rgba(244,164,96,0.2)!important;
  color:#f4a460!important;
}
.stats-bar{
  color:rgba(255,245,238,0.4)!important;
}
.stats-bar span{
  color:#fff5ee!important;
}
.announcement-box{
  background:rgba(255,255,255,0.03)!important;
  border:1px solid rgba(244,164,96,0.1)!important;
  border-radius:12px!important;
  color:rgba(255,245,238,0.7)!important;
}
.footer-text{
  color:rgba(255,245,238,0.3)!important;
}
.footer-text a{
  color:rgba(244,164,96,0.5)!important;
}
.free-make-btn{
  background:rgba(244,164,96,0.12)!important;
  border:1px solid rgba(244,164,96,0.15)!important;
  color:#f4a460!important;
  border-radius:12px!important;
}
.free-make-btn:hover{
  background:rgba(244,164,96,0.18)!important;
}
.music-player-btn{
  background:rgba(244,164,96,0.1)!important;
  border:1px solid rgba(244,164,96,0.15)!important;
  color:#f4a460!important;
  border-radius:12px!important;
}
.music-player-btn:hover{
  background:rgba(244,164,96,0.18)!important;
}
.text-block{
  background:rgba(255,255,255,0.03)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  border-radius:12px!important;
  color:rgba(255,245,238,0.65)!important;
}
.picture-block{
  border-radius:12px!important;
  border:1px solid rgba(255,255,255,0.06)!important;
}
.tipping-btn{
  background:rgba(244,164,96,0.12)!important;
  border:1px solid rgba(244,164,96,0.15)!important;
  color:#f4a460!important;
  border-radius:12px!important;
}
.tipping-btn:hover{
  background:rgba(244,164,96,0.18)!important;
}
.report-box,.modal-box,#passModal{
  background:rgba(45,27,46,0.95)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(244,164,96,0.12)!important;
  border-radius:20px!important;
}
#passInput{
  background:rgba(255,255,255,0.05)!important;
  border:1px solid rgba(244,164,96,0.2)!important;
  color:#fff5ee!important;
  border-radius:10px!important;
}
'
];
