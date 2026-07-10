<?php
/**
 * Leaffox 用户主页模版 - Nord（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/nord.php
 * 风格：Nord 北欧极简，冰雪/极光色调，冷静克制
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
/* === Nord 北欧极简冷色 === */
body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(160deg, #2e3440 0%, #3b4252 30%, #434c5e 60%, #4c566a 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(ellipse 100% 50% at 50% 0%, rgba(143,188,187,0.06) 0%, transparent 70%),
    radial-gradient(ellipse 60% 40% at 30% 100%, rgba(136,192,208,0.05) 0%, transparent 70%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Inter",-apple-system,BlinkMacSystemFont,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}
.profile-name{
  color:#eceff4!important;
  font-weight:700!important;
  letter-spacing:-0.3px!important;
}
.profile-bio{
  color:rgba(236,239,244,0.55)!important;
}
.avatar-wrap{
  border:2px solid rgba(143,188,187,0.2)!important;
}
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(67,76,94,0.25)!important;
  border:1px solid rgba(76,86,106,0.15)!important;
  border-radius:12px!important;
  box-shadow:0 2px 12px rgba(0,0,0,0.08)!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(67,76,94,0.35)!important;
  border-color:rgba(143,188,187,0.15)!important;
  transform:translateY(-2px)!important;
}
.card-title{
  color:#eceff4!important;
  font-weight:600!important;
  font-size:15px!important;
}
.card-sub{
  color:rgba(236,239,244,0.45)!important;
  font-size:12px!important;
}
.card-icon{
  background:rgba(136,192,208,0.08)!important;
  color:#88c0d0!important;
  border-radius:8px!important;
}
.card-arrow{
  color:rgba(143,188,187,0.35)!important;
}
.top-link-bar{
  background:rgba(67,76,94,0.2)!important;
  border:1px solid rgba(76,86,106,0.12)!important;
  border-radius:10px!important;
  color:#eceff4!important;
}
.top-link-bar .link-text{
  color:rgba(236,239,244,0.6)!important;
}
.social-item{
  background:rgba(67,76,94,0.15)!important;
  border:1px solid rgba(76,86,106,0.1)!important;
  color:rgba(236,239,244,0.45)!important;
  border-radius:10px!important;
}
.social-item:hover{
  background:rgba(143,188,187,0.08)!important;
  border-color:rgba(143,188,187,0.15)!important;
  color:#8fbcbb!important;
}
.stats-bar{
  color:rgba(236,239,244,0.3)!important;
}
.stats-bar span{
  color:#eceff4!important;
}
.announcement-box{
  background:rgba(67,76,94,0.12)!important;
  border:1px solid rgba(143,188,187,0.08)!important;
  border-radius:10px!important;
  color:rgba(236,239,244,0.65)!important;
}
.footer-text{
  color:rgba(236,239,244,0.25)!important;
  font-size:11px!important;
}
.footer-text a{
  color:rgba(136,192,208,0.4)!important;
}
.free-make-btn{
  background:rgba(143,188,187,0.08)!important;
  border:1px solid rgba(143,188,187,0.12)!important;
  color:#8fbcbb!important;
  border-radius:8px!important;
}
.free-make-btn:hover{
  background:rgba(143,188,187,0.12)!important;
}
.music-player-btn{
  background:rgba(180,142,173,0.08)!important;
  border:1px solid rgba(180,142,173,0.12)!important;
  color:#b48ead!important;
  border-radius:8px!important;
}
.music-player-btn:hover{
  background:rgba(180,142,173,0.12)!important;
}
.text-block{
  background:rgba(67,76,94,0.12)!important;
  border:1px solid rgba(76,86,106,0.1)!important;
  border-radius:10px!important;
  color:rgba(236,239,244,0.6)!important;
}
.picture-block{
  border-radius:10px!important;
  border:1px solid rgba(76,86,106,0.1)!important;
}
.tipping-btn{
  background:rgba(163,190,140,0.08)!important;
  border:1px solid rgba(163,190,140,0.12)!important;
  color:#a3be8c!important;
  border-radius:8px!important;
}
.tipping-btn:hover{
  background:rgba(163,190,140,0.12)!important;
}
.card-lock{
  background:rgba(191,97,106,0.1)!important;
  color:#bf616a!important;
  border-radius:6px!important;
}
.card-tag{
  background:rgba(136,192,208,0.06)!important;
  color:#88c0d0!important;
  border-radius:4px!important;
}
.report-box,.modal-box,#passModal{
  background:rgba(46,52,64,0.96)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(76,86,106,0.12)!important;
  border-radius:8px!important;
}
#passInput{
  background:rgba(67,76,94,0.15)!important;
  border:1px solid rgba(143,188,187,0.15)!important;
  color:#eceff4!important;
  border-radius:8px!important;
}
'
];
