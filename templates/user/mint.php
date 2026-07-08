<?php
/**
 * Leaffox 用户主页模版 - Mint
 * ============================================
 * 风格：薄荷清爽，青绿/白色，夏日清凉，活力清新
 * ============================================
 */
return [
'css' => '
/* === Mint 薄荷清爽 === */
@import url("https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Inter:wght@300;400;600&display=swap");

body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(135deg, #e0f5e8 0%, #c8efe0 25%, #b0e8d8 50%, #c8efe0 75%, #e0f5e8 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(circle at 20% 20%, rgba(255,255,255,0.5) 0%, transparent 40%),
    radial-gradient(circle at 80% 80%, rgba(160,220,200,0.15) 0%, transparent 50%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Quicksand","Inter",-apple-system,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}

.avatar-wrap{
  border:3px solid rgba(255,255,255,0.7)!important;
  box-shadow:0 6px 30px rgba(80,180,150,0.1)!important;
}
.profile-name{
  color:#1a4a3a!important;
  font-family:"Quicksand",sans-serif!important;
  font-weight:700!important;
  font-size:26px!important;
  letter-spacing:1px!important;
}
.profile-bio{
  color:rgba(26,74,58,0.4)!important;
  font-weight:600!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.5)!important;
  backdrop-filter:blur(8px)!important;
  border:1px solid rgba(255,255,255,0.6)!important;
  border-radius:20px!important;
  box-shadow:0 4px 20px rgba(80,180,150,0.04)!important;
  transition:all 0.3s ease!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.7)!important;
  border-color:rgba(80,180,150,0.1)!important;
  transform:translateY(-4px) scale(1.01)!important;
  box-shadow:0 12px 40px rgba(80,180,150,0.08)!important;
}

.card-title{
  color:#1a4a3a!important;
  font-weight:700!important;
}
.card-sub{
  color:rgba(26,74,58,0.35)!important;
  font-weight:600!important;
}
.card-icon{
  background:rgba(80,180,150,0.12)!important;
  color:#50b496!important;
  border-radius:14px!important;
}
.card-arrow{
  color:rgba(80,180,150,0.2)!important;
}

.top-link-bar{
  background:rgba(255,255,255,0.45)!important;
  border:1px solid rgba(255,255,255,0.5)!important;
  backdrop-filter:blur(8px)!important;
  border-radius:14px!important;
  color:#1a4a3a!important;
}
.top-link-bar .link-text{
  color:rgba(26,74,58,0.4)!important;
  font-weight:600!important;
}

.social-item{
  background:rgba(255,255,255,0.3)!important;
  border:1px solid rgba(255,255,255,0.4)!important;
  color:rgba(26,74,58,0.35)!important;
  border-radius:14px!important;
  transition:all 0.3s!important;
}
.social-item:hover{
  background:rgba(255,255,255,0.55)!important;
  border-color:rgba(80,180,150,0.2)!important;
  color:#50b496!important;
  transform:translateY(-2px)!important;
}

.stats-bar{
  color:rgba(26,74,58,0.2)!important;
  font-weight:600!important;
}
.stats-bar span{
  color:#1a4a3a!important;
}

.announcement-box{
  background:rgba(255,255,255,0.3)!important;
  border:1px solid rgba(80,180,150,0.1)!important;
  border-radius:14px!important;
  color:rgba(26,74,58,0.45)!important;
}

.footer-text{
  color:rgba(26,74,58,0.2)!important;
}
.footer-text a{
  color:rgba(80,180,150,0.35)!important;
}
.free-make-btn{
  background:rgba(80,180,150,0.1)!important;
  border:1px solid rgba(80,180,150,0.15)!important;
  color:#50b496!important;
  border-radius:14px!important;
  font-weight:700!important;
}
.free-make-btn:hover{
  background:rgba(80,180,150,0.18)!important;
}
.music-player-btn{
  background:rgba(80,180,150,0.08)!important;
  border:1px solid rgba(80,180,150,0.12)!important;
  color:#50b496!important;
  border-radius:50%!important;
}

.text-block{
  background:rgba(255,255,255,0.25)!important;
  border:1px solid rgba(255,255,255,0.4)!important;
  border-radius:14px!important;
  color:rgba(26,74,58,0.4)!important;
}
.picture-block{
  border-radius:14px!important;
  border:1px solid rgba(255,255,255,0.4)!important;
}
.tipping-btn{
  background:rgba(80,180,150,0.1)!important;
  border:1px solid rgba(80,180,150,0.15)!important;
  color:#50b496!important;
  border-radius:14px!important;
  font-weight:700!important;
}
.tipping-btn:hover{
  background:rgba(80,180,150,0.18)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(224,245,232,0.98)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(80,180,150,0.1)!important;
  border-radius:20px!important;
}
#passInput{
  background:rgba(255,255,255,0.5)!important;
  border:1px solid rgba(80,180,150,0.15)!important;
  color:#1a4a3a!important;
  border-radius:12px!important;
  font-weight:600!important;
}
'
];
