<?php
/**
 * Leaffox 用户主页模版 - Zen
 * ============================================
 * 风格：禅意极简，米白/灰色，大量留白，侘寂美学
 * ============================================
 */
return [
'css' => '
/* === Zen 禅意极简 === */
@import url("https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;700&family=Inter:wght@300;400;600&display=swap");

body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(170deg, #f5f0e8 0%, #e8e0d0 50%, #f0ebe3 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(ellipse 60% 40% at 50% 10%, rgba(255,255,255,0.5) 0%, transparent 60%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Noto Serif SC","Inter",Georgia,serif!important;
}
.page-wrap{
  background:transparent!important;
  max-width:640px!important;
}

.avatar-wrap{
  border-radius:2px!important;
  border:1px solid rgba(0,0,0,0.06)!important;
  box-shadow:none!important;
  filter:sepia(0.15) saturate(0.8)!important;
}
.profile-name{
  color:#2c2c2c!important;
  font-family:"Noto Serif SC",serif!important;
  font-weight:400!important;
  font-size:24px!important;
  letter-spacing:6px!important;
}
.profile-bio{
  color:rgba(44,44,44,0.4)!important;
  font-weight:300!important;
  font-size:13px!important;
  letter-spacing:2px!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.3)!important;
  backdrop-filter:blur(4px)!important;
  border:1px solid rgba(0,0,0,0.04)!important;
  border-radius:2px!important;
  box-shadow:none!important;
  transition:all 0.4s ease!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.5)!important;
  border-color:rgba(0,0,0,0.06)!important;
  transform:translateY(-2px)!important;
  box-shadow:0 4px 20px rgba(0,0,0,0.02)!important;
}

.card-title{
  color:#2c2c2c!important;
  font-family:"Noto Serif SC",serif!important;
  font-weight:400!important;
  font-size:15px!important;
  letter-spacing:2px!important;
}
.card-sub{
  color:rgba(44,44,44,0.3)!important;
  font-weight:300!important;
  font-size:11px!important;
}
.card-icon{
  background:rgba(44,44,44,0.04)!important;
  color:#2c2c2c!important;
  border-radius:2px!important;
  font-size:18px!important;
}
.card-arrow{
  color:rgba(44,44,44,0.1)!important;
}

.top-link-bar{
  background:rgba(255,255,255,0.2)!important;
  border:1px solid rgba(0,0,0,0.04)!important;
  backdrop-filter:blur(4px)!important;
  border-radius:2px!important;
  color:#2c2c2c!important;
  letter-spacing:2px!important;
  font-size:12px!important;
}
.top-link-bar .link-text{
  color:rgba(44,44,44,0.35)!important;
  font-weight:300!important;
}

.social-item{
  background:rgba(255,255,255,0.2)!important;
  border:1px solid rgba(0,0,0,0.04)!important;
  color:rgba(44,44,44,0.3)!important;
  border-radius:2px!important;
  transition:all 0.4s!important;
}
.social-item:hover{
  background:rgba(44,44,44,0.04)!important;
  border-color:rgba(44,44,44,0.1)!important;
  color:#2c2c2c!important;
}

.stats-bar{
  color:rgba(44,44,44,0.15)!important;
  font-weight:300!important;
  letter-spacing:2px!important;
  font-size:11px!important;
}
.stats-bar span{
  color:#2c2c2c!important;
}

.announcement-box{
  background:rgba(255,255,255,0.15)!important;
  border:1px solid rgba(0,0,0,0.03)!important;
  border-radius:2px!important;
  color:rgba(44,44,44,0.45)!important;
  font-family:"Noto Serif SC",serif!important;
  font-size:13px!important;
  line-height:2!important;
}

.footer-text{
  color:rgba(44,44,44,0.15)!important;
  font-weight:300!important;
  letter-spacing:2px!important;
}
.footer-text a{
  color:rgba(44,44,44,0.25)!important;
}
.free-make-btn{
  background:rgba(44,44,44,0.03)!important;
  border:1px solid rgba(44,44,44,0.08)!important;
  color:#2c2c2c!important;
  border-radius:2px!important;
  font-family:"Noto Serif SC",serif!important;
  font-weight:400!important;
  letter-spacing:2px!important;
}
.free-make-btn:hover{
  background:rgba(44,44,44,0.06)!important;
}
.music-player-btn{
  background:rgba(44,44,44,0.03)!important;
  border:1px solid rgba(44,44,44,0.08)!important;
  color:#2c2c2c!important;
  border-radius:2px!important;
}

.text-block{
  background:rgba(255,255,255,0.15)!important;
  border:1px solid rgba(0,0,0,0.03)!important;
  border-radius:2px!important;
  color:rgba(44,44,44,0.4)!important;
  font-family:"Noto Serif SC",serif!important;
}
.picture-block{
  border-radius:2px!important;
  border:1px solid rgba(0,0,0,0.04)!important;
}
.tipping-btn{
  background:rgba(44,44,44,0.03)!important;
  border:1px solid rgba(44,44,44,0.08)!important;
  color:#2c2c2c!important;
  border-radius:2px!important;
  letter-spacing:2px!important;
}
.tipping-btn:hover{
  background:rgba(44,44,44,0.06)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(245,240,232,0.98)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(0,0,0,0.04)!important;
  border-radius:2px!important;
}
#passInput{
  background:rgba(255,255,255,0.4)!important;
  border:1px solid rgba(0,0,0,0.08)!important;
  color:#2c2c2c!important;
  border-radius:2px!important;
  font-family:"Noto Serif SC",serif!important;
}
'
];
