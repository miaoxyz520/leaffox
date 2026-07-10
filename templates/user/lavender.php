<?php
/**
 * Leaffox 用户主页模版 - Lavender
 * ============================================
 * 风格：薰衣草，淡紫/粉紫，浪漫柔和，田园清新
 * ============================================
 */
return [
'css' => '
/* === Lavender 薰衣草 === */
body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(160deg, #f5f0ff 0%, #e8dff5 20%, #dcc8f0 40%, #e8dff5 60%, #f0e8f8 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(circle at 30% 20%, rgba(255,255,255,0.5) 0%, transparent 40%),
    radial-gradient(circle at 70% 80%, rgba(180,140,220,0.06) 0%, transparent 50%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Jost","Inter",-apple-system,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}

.avatar-wrap{
  border:3px solid rgba(255,255,255,0.7)!important;
  box-shadow:0 6px 30px rgba(180,140,220,0.08)!important;
}
.profile-name{
  color:#3a2a5a!important;
  font-family:"Jost",sans-serif!important;
  font-weight:600!important;
  font-size:26px!important;
  letter-spacing:2px!important;
}
.profile-bio{
  color:rgba(58,42,90,0.4)!important;
  font-weight:300!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.45)!important;
  backdrop-filter:blur(8px)!important;
  border:1px solid rgba(255,255,255,0.55)!important;
  border-radius:12px!important;
  box-shadow:0 4px 20px rgba(180,140,220,0.03)!important;
  transition:all 0.3s ease!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.6)!important;
  border-color:rgba(180,140,220,0.1)!important;
  transform:translateY(-4px) scale(1.01)!important;
  box-shadow:0 12px 40px rgba(180,140,220,0.06)!important;
}

.card-title{
  color:#3a2a5a!important;
  font-weight:600!important;
}
.card-sub{
  color:rgba(58,42,90,0.3)!important;
  font-weight:300!important;
}
.card-icon{
  background:rgba(180,140,220,0.1)!important;
  color:#b48cdc!important;
  border-radius:8px!important;
}
.card-arrow{
  color:rgba(180,140,220,0.2)!important;
}

.top-link-bar{
  background:rgba(255,255,255,0.4)!important;
  border:1px solid rgba(255,255,255,0.45)!important;
  backdrop-filter:blur(8px)!important;
  border-radius:8px!important;
  color:#3a2a5a!important;
}
.top-link-bar .link-text{
  color:rgba(58,42,90,0.35)!important;
  font-weight:300!important;
}

.social-item{
  background:rgba(255,255,255,0.25)!important;
  border:1px solid rgba(255,255,255,0.35)!important;
  color:rgba(58,42,90,0.3)!important;
  border-radius:8px!important;
  transition:all 0.3s!important;
}
.social-item:hover{
  background:rgba(255,255,255,0.5)!important;
  border-color:rgba(180,140,220,0.15)!important;
  color:#b48cdc!important;
  transform:translateY(-2px)!important;
}

.stats-bar{
  color:rgba(58,42,90,0.15)!important;
}
.stats-bar span{
  color:rgba(58,42,90,0.35)!important;
}

.announcement-box{
  background:rgba(255,255,255,0.25)!important;
  border:1px solid rgba(180,140,220,0.08)!important;
  border-radius:8px!important;
  color:rgba(58,42,90,0.4)!important;
}

.footer-text{
  color:rgba(58,42,90,0.15)!important;
}
.footer-text a{
  color:rgba(180,140,220,0.3)!important;
}
.free-make-btn{
  background:rgba(180,140,220,0.08)!important;
  border:1px solid rgba(180,140,220,0.12)!important;
  color:#b48cdc!important;
  border-radius:8px!important;
  font-weight:600!important;
}
.free-make-btn:hover{
  background:rgba(180,140,220,0.14)!important;
}
.music-player-btn{
  background:rgba(180,140,220,0.06)!important;
  border:1px solid rgba(180,140,220,0.1)!important;
  color:#b48cdc!important;
  border-radius:50%!important;
}

.text-block{
  background:rgba(255,255,255,0.2)!important;
  border:1px solid rgba(255,255,255,0.35)!important;
  border-radius:8px!important;
  color:rgba(58,42,90,0.35)!important;
}
.picture-block{
  border-radius:8px!important;
  border:1px solid rgba(255,255,255,0.35)!important;
}
.tipping-btn{
  background:rgba(180,140,220,0.08)!important;
  border:1px solid rgba(180,140,220,0.12)!important;
  color:#b48cdc!important;
  border-radius:8px!important;
}
.tipping-btn:hover{
  background:rgba(180,140,220,0.14)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(245,240,255,0.98)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(180,140,220,0.08)!important;
  border-radius:12px!important;
}
#passInput{
  background:rgba(255,255,255,0.5)!important;
  border:1px solid rgba(180,140,220,0.12)!important;
  color:#3a2a5a!important;
  border-radius:12px!important;
}
'
];
