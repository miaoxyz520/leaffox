<?php
/**
 * Leaffox 用户主页模版 - Crimson
 * ============================================
 * 风格：暗红玫瑰，深红/暗金，高级质感，奢华低调
 * ============================================
 */
return [
'css' => '
/* === Crimson 暗红玫瑰 === */
@import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;600&display=swap");

body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(160deg, #1a0a0a 0%, #2d0f0f 20%, #3d1212 40%, #2a0e0e 60%, #1a0808 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(ellipse 100% 40% at 50% 0%, rgba(139,30,30,0.06) 0%, transparent 60%),
    radial-gradient(ellipse 80% 30% at 30% 80%, rgba(180,140,60,0.03) 0%, transparent 50%),
    radial-gradient(ellipse 60% 40% at 80% 20%, rgba(139,30,30,0.04) 0%, transparent 50%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Inter","Playfair Display",Georgia,serif!important;
}
.page-wrap{
  background:transparent!important;
}

.avatar-wrap{
  border:2px solid rgba(180,140,60,0.2)!important;
  box-shadow:0 4px 30px rgba(139,30,30,0.15)!important;
  border-radius:50%!important;
}
.profile-name{
  color:#f0e0d0!important;
  font-family:"Playfair Display",serif!important;
  font-weight:700!important;
  font-size:26px!important;
  letter-spacing:2px!important;
}
.profile-bio{
  color:rgba(180,140,60,0.4)!important;
  font-weight:300!important;
  font-style:italic!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(139,30,30,0.04)!important;
  backdrop-filter:blur(12px)!important;
  border:1px solid rgba(180,140,60,0.06)!important;
  border-radius:16px!important;
  box-shadow:0 2px 20px rgba(0,0,0,0.08)!important;
  transition:all 0.4s ease!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(139,30,30,0.08)!important;
  border-color:rgba(180,140,60,0.12)!important;
  transform:translateY(-3px)!important;
  box-shadow:0 8px 35px rgba(139,30,30,0.08)!important;
}

.card-title{
  color:#f0e0d0!important;
  font-weight:600!important;
  letter-spacing:1px!important;
}
.card-sub{
  color:rgba(180,140,60,0.3)!important;
  font-weight:300!important;
  font-style:italic!important;
}
.card-icon{
  background:rgba(180,140,60,0.08)!important;
  color:#b48c3c!important;
  border-radius:10px!important;
}
.card-arrow{
  color:rgba(180,140,60,0.15)!important;
}

.top-link-bar{
  background:rgba(139,30,30,0.04)!important;
  border:1px solid rgba(180,140,60,0.06)!important;
  backdrop-filter:blur(12px)!important;
  border-radius:12px!important;
  color:#f0e0d0!important;
}
.top-link-bar .link-text{
  color:rgba(180,140,60,0.3)!important;
  font-weight:300!important;
}

.social-item{
  background:rgba(139,30,30,0.03)!important;
  border:1px solid rgba(180,140,60,0.04)!important;
  color:rgba(180,140,60,0.3)!important;
  border-radius:12px!important;
  transition:all 0.3s!important;
}
.social-item:hover{
  background:rgba(180,140,60,0.06)!important;
  border-color:rgba(180,140,60,0.12)!important;
  color:#b48c3c!important;
}

.stats-bar{
  color:rgba(180,140,60,0.1)!important;
}
.stats-bar span{
  color:rgba(180,140,60,0.3)!important;
}

.announcement-box{
  background:rgba(139,30,30,0.03)!important;
  border:1px solid rgba(180,140,60,0.04)!important;
  border-radius:12px!important;
  color:rgba(180,140,60,0.3)!important;
  font-style:italic!important;
}

.footer-text{
  color:rgba(180,140,60,0.1)!important;
}
.footer-text a{
  color:rgba(180,140,60,0.15)!important;
}
.free-make-btn{
  background:rgba(180,140,60,0.06)!important;
  border:1px solid rgba(180,140,60,0.1)!important;
  color:#b48c3c!important;
  border-radius:12px!important;
  font-family:"Playfair Display",serif!important;
}
.free-make-btn:hover{
  background:rgba(180,140,60,0.1)!important;
}
.music-player-btn{
  background:rgba(139,30,30,0.06)!important;
  border:1px solid rgba(180,140,60,0.08)!important;
  color:#b48c3c!important;
  border-radius:50%!important;
}

.text-block{
  background:rgba(139,30,30,0.03)!important;
  border:1px solid rgba(180,140,60,0.04)!important;
  border-radius:12px!important;
  color:rgba(180,140,60,0.3)!important;
  font-style:italic!important;
}
.picture-block{
  border-radius:12px!important;
  border:1px solid rgba(180,140,60,0.04)!important;
}
.tipping-btn{
  background:rgba(180,140,60,0.06)!important;
  border:1px solid rgba(180,140,60,0.1)!important;
  color:#b48c3c!important;
  border-radius:12px!important;
}
.tipping-btn:hover{
  background:rgba(180,140,60,0.1)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(20,8,8,0.97)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(180,140,60,0.06)!important;
  border-radius:16px!important;
}
#passInput{
  background:rgba(180,140,60,0.03)!important;
  border:1px solid rgba(180,140,60,0.1)!important;
  color:#f0e0d0!important;
  border-radius:10px!important;
}
'
];
