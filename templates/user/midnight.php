<?php
/**
 * Leaffox 用户主页模版 - Midnight
 * ============================================
 * 风格：深蓝午夜，靛蓝/藏青，沉稳静谧，深海静谧
 * ============================================
 */
return [
'css' => '
/* === Midnight 深蓝午夜 === */
@import url("https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;600&display=swap");

body::before{
  content:"";position:fixed;inset:0;
  background:
    radial-gradient(ellipse 100% 50% at 50% 0%, #0a1628 0%, transparent 60%),
    radial-gradient(ellipse 80% 50% at 50% 100%, #0d1f3c 0%, transparent 60%),
    linear-gradient(180deg, #060e1a 0%, #0a1628 30%, #0c1a30 60%, #060e1a 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(circle at 15% 25%, rgba(50,100,180,0.03) 0%, transparent 40%),
    radial-gradient(circle at 85% 75%, rgba(50,100,180,0.02) 0%, transparent 40%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Outfit","Inter",-apple-system,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}

.avatar-wrap{
  border:2px solid rgba(255,255,255,0.04)!important;
  box-shadow:0 4px 30px rgba(0,0,0,0.2)!important;
}
.profile-name{
  color:#d0d8e8!important;
  font-family:"Outfit",sans-serif!important;
  font-weight:600!important;
  font-size:24px!important;
  letter-spacing:2px!important;
}
.profile-bio{
  color:rgba(160,180,210,0.35)!important;
  font-weight:300!important;
  font-size:13px!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.02)!important;
  backdrop-filter:blur(16px)!important;
  border:1px solid rgba(255,255,255,0.03)!important;
  border-radius:12px!important;
  box-shadow:0 2px 20px rgba(0,0,0,0.08)!important;
  transition:all 0.3s ease!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.03)!important;
  border-color:rgba(80,140,220,0.04)!important;
  transform:translateY(-3px)!important;
  box-shadow:0 8px 30px rgba(80,140,220,0.02)!important;
}

.card-title{
  color:#d0d8e8!important;
  font-weight:600!important;
}
.card-sub{
  color:rgba(160,180,210,0.25)!important;
  font-weight:300!important;
}
.card-icon{
  background:rgba(80,140,220,0.04)!important;
  color:#508cdc!important;
  border-radius:10px!important;
}
.card-arrow{
  color:rgba(80,140,220,0.12)!important;
}

.top-link-bar{
  background:rgba(255,255,255,0.02)!important;
  border:1px solid rgba(255,255,255,0.03)!important;
  backdrop-filter:blur(16px)!important;
  border-radius:10px!important;
  color:#d0d8e8!important;
}
.top-link-bar .link-text{
  color:rgba(160,180,210,0.25)!important;
}

.social-item{
  background:rgba(255,255,255,0.01)!important;
  border:1px solid rgba(255,255,255,0.03)!important;
  color:rgba(160,180,210,0.2)!important;
  border-radius:10px!important;
  transition:all 0.3s!important;
}
.social-item:hover{
  background:rgba(80,140,220,0.03)!important;
  border-color:rgba(80,140,220,0.06)!important;
  color:#508cdc!important;
}

.stats-bar{
  color:rgba(160,180,210,0.08)!important;
}
.stats-bar span{
  color:rgba(160,180,210,0.2)!important;
}

.announcement-box{
  background:rgba(255,255,255,0.01)!important;
  border:1px solid rgba(80,140,220,0.03)!important;
  border-radius:10px!important;
  color:rgba(160,180,210,0.25)!important;
}

.footer-text{
  color:rgba(160,180,210,0.08)!important;
}
.footer-text a{
  color:rgba(80,140,220,0.12)!important;
}
.free-make-btn{
  background:rgba(80,140,220,0.03)!important;
  border:1px solid rgba(80,140,220,0.06)!important;
  color:#508cdc!important;
  border-radius:10px!important;
  font-weight:400!important;
}
.free-make-btn:hover{
  background:rgba(80,140,220,0.05)!important;
}
.music-player-btn{
  background:rgba(80,140,220,0.02)!important;
  border:1px solid rgba(80,140,220,0.05)!important;
  color:#508cdc!important;
  border-radius:50%!important;
}

.text-block{
  background:rgba(255,255,255,0.01)!important;
  border:1px solid rgba(255,255,255,0.03)!important;
  border-radius:10px!important;
  color:rgba(160,180,210,0.25)!important;
}
.picture-block{
  border-radius:10px!important;
  border:1px solid rgba(255,255,255,0.03)!important;
}
.tipping-btn{
  background:rgba(80,140,220,0.03)!important;
  border:1px solid rgba(80,140,220,0.06)!important;
  color:#508cdc!important;
  border-radius:10px!important;
}
.tipping-btn:hover{
  background:rgba(80,140,220,0.05)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(6,14,26,0.98)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(80,140,220,0.03)!important;
  border-radius:12px!important;
}
#passInput{
  background:rgba(255,255,255,0.02)!important;
  border:1px solid rgba(80,140,220,0.06)!important;
  color:#d0d8e8!important;
  border-radius:8px!important;
}
'
];
