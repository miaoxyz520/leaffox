<?php
/**
 * Leaffox 用户主页模版 - Astronaut
 * ============================================
 * 风格：太空宇航员，深邃星空，深邃蓝紫，宇宙探索
 * ============================================
 */
return [
'css' => '
/* === Astronaut 太空宇航员 === */
@import url("https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@300;400;600&display=swap");

body::before{
  content:"";position:fixed;inset:0;
  background:
    radial-gradient(ellipse 120% 80% at 50% 20%, #0b0e2a 0%, transparent 60%),
    radial-gradient(ellipse 100% 60% at 50% 80%, #1a1040 0%, transparent 60%),
    radial-gradient(ellipse 80% 40% at 30% 60%, #0d1b3e 0%, transparent 50%),
    linear-gradient(180deg, #050816 0%, #0b0e2a 30%, #0d1b3e 60%, #050816 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background-image:
    radial-gradient(1px 1px at 10% 20%, rgba(255,255,255,0.6) 0%, transparent 100%),
    radial-gradient(1px 1px at 25% 45%, rgba(255,255,255,0.4) 0%, transparent 100%),
    radial-gradient(1.5px 1.5px at 40% 15%, rgba(255,255,255,0.8) 0%, transparent 100%),
    radial-gradient(1px 1px at 55% 60%, rgba(255,255,255,0.3) 0%, transparent 100%),
    radial-gradient(1.5px 1.5px at 70% 25%, rgba(255,255,255,0.5) 0%, transparent 100%),
    radial-gradient(1px 1px at 85% 50%, rgba(255,255,255,0.4) 0%, transparent 100%),
    radial-gradient(2px 2px at 15% 75%, rgba(255,255,255,0.6) 0%, transparent 100%),
    radial-gradient(1px 1px at 35% 85%, rgba(255,255,255,0.3) 0%, transparent 100%),
    radial-gradient(1.5px 1.5px at 60% 40%, rgba(255,255,255,0.5) 0%, transparent 100%),
    radial-gradient(1px 1px at 90% 80%, rgba(255,255,255,0.3) 0%, transparent 100%),
    radial-gradient(1px 1px at 50% 90%, rgba(255,255,255,0.2) 0%, transparent 100%),
    radial-gradient(1px 1px at 5% 50%, rgba(255,255,255,0.4) 0%, transparent 100%),
    radial-gradient(1.5px 1.5px at 75% 70%, rgba(255,255,255,0.3) 0%, transparent 100%),
    radial-gradient(1px 1px at 45% 30%, rgba(255,255,255,0.5) 0%, transparent 100%),
    radial-gradient(1px 1px at 65% 10%, rgba(255,255,255,0.3) 0%, transparent 100%),
    radial-gradient(2px 2px at 95% 35%, rgba(255,255,255,0.4) 0%, transparent 100%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Space Grotesk","Inter",-apple-system,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}

.avatar-wrap{
  border:2px solid rgba(255,255,255,0.08)!important;
  box-shadow:
    0 0 40px rgba(100,140,255,0.08),
    inset 0 0 30px rgba(100,140,255,0.03)!important;
  border-radius:50%!important;
}
.profile-name{
  color:#e8eeff!important;
  font-family:"Space Grotesk",sans-serif!important;
  font-weight:700!important;
  font-size:24px!important;
  letter-spacing:2px!important;
}
.profile-bio{
  color:rgba(200,215,255,0.4)!important;
  font-weight:300!important;
  letter-spacing:1px!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.03)!important;
  backdrop-filter:blur(16px)!important;
  border:1px solid rgba(255,255,255,0.04)!important;
  border-radius:16px!important;
  box-shadow:0 4px 30px rgba(0,0,0,0.1)!important;
  transition:all 0.4s ease!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.05)!important;
  border-color:rgba(100,140,255,0.08)!important;
  transform:translateY(-3px) scale(1.01)!important;
  box-shadow:0 8px 40px rgba(100,140,255,0.05)!important;
}

.card-title{
  color:#e8eeff!important;
  font-weight:600!important;
}
.card-sub{
  color:rgba(200,215,255,0.3)!important;
  font-weight:300!important;
}
.card-icon{
  background:rgba(100,140,255,0.08)!important;
  color:#648cff!important;
  border-radius:12px!important;
}
.card-arrow{
  color:rgba(100,140,255,0.2)!important;
}

.top-link-bar{
  background:rgba(255,255,255,0.03)!important;
  border:1px solid rgba(255,255,255,0.04)!important;
  backdrop-filter:blur(16px)!important;
  border-radius:12px!important;
  color:#e8eeff!important;
}
.top-link-bar .link-text{
  color:rgba(200,215,255,0.35)!important;
}

.social-item{
  background:rgba(255,255,255,0.02)!important;
  border:1px solid rgba(255,255,255,0.04)!important;
  color:rgba(200,215,255,0.3)!important;
  border-radius:12px!important;
  transition:all 0.3s!important;
}
.social-item:hover{
  background:rgba(100,140,255,0.06)!important;
  border-color:rgba(100,140,255,0.12)!important;
  color:#648cff!important;
  transform:translateY(-2px)!important;
}

.stats-bar{
  color:rgba(200,215,255,0.12)!important;
  font-weight:300!important;
}
.stats-bar span{
  color:rgba(200,215,255,0.4)!important;
}

.announcement-box{
  background:rgba(255,255,255,0.02)!important;
  border:1px solid rgba(100,140,255,0.04)!important;
  border-radius:12px!important;
  color:rgba(200,215,255,0.35)!important;
}

.footer-text{
  color:rgba(200,215,255,0.1)!important;
}
.footer-text a{
  color:rgba(100,140,255,0.2)!important;
}
.free-make-btn{
  background:rgba(100,140,255,0.06)!important;
  border:1px solid rgba(100,140,255,0.1)!important;
  color:#648cff!important;
  border-radius:12px!important;
  font-weight:600!important;
}
.free-make-btn:hover{
  background:rgba(100,140,255,0.1)!important;
}
.music-player-btn{
  background:rgba(100,140,255,0.04)!important;
  border:1px solid rgba(100,140,255,0.08)!important;
  color:#648cff!important;
  border-radius:50%!important;
}

.text-block{
  background:rgba(255,255,255,0.02)!important;
  border:1px solid rgba(255,255,255,0.04)!important;
  border-radius:12px!important;
  color:rgba(200,215,255,0.35)!important;
}
.picture-block{
  border-radius:12px!important;
  border:1px solid rgba(255,255,255,0.04)!important;
}
.tipping-btn{
  background:rgba(100,140,255,0.06)!important;
  border:1px solid rgba(100,140,255,0.1)!important;
  color:#648cff!important;
  border-radius:12px!important;
}
.tipping-btn:hover{
  background:rgba(100,140,255,0.1)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(5,8,22,0.97)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(100,140,255,0.06)!important;
  border-radius:16px!important;
}
#passInput{
  background:rgba(255,255,255,0.03)!important;
  border:1px solid rgba(100,140,255,0.1)!important;
  color:#e8eeff!important;
  border-radius:10px!important;
}
'
];
