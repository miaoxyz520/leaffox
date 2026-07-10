<?php
/**
 * Leaffox 用户主页模版 - Coral
 * ============================================
 * 风格：珊瑚海洋，温暖橙粉，清新自然，热带度假感
 * ============================================
 */
return [
'css' => '
/* === Coral 珊瑚海洋 === */
body::before{
  content:"";position:fixed;inset:0;
  background:linear-gradient(180deg, #ffecd2 0%, #fcb69f 30%, #ff9a9e 60%, #fad0c4 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    radial-gradient(circle at 20% 30%, rgba(255,255,255,0.3) 0%, transparent 40%),
    radial-gradient(circle at 80% 70%, rgba(255,154,158,0.08) 0%, transparent 50%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Nunito","Fredoka One",-apple-system,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}

.avatar-wrap{
  border:3px solid rgba(255,255,255,0.6)!important;
  box-shadow:0 8px 32px rgba(252,182,159,0.2)!important;
}
.profile-name{
  color:#5c3a3a!important;
  font-family:"Fredoka One",cursive!important;
  font-weight:400!important;
  font-size:28px!important;
  letter-spacing:1px!important;
}
.profile-bio{
  color:rgba(92,58,58,0.55)!important;
  font-weight:600!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.55)!important;
  backdrop-filter:blur(12px)!important;
  border:1px solid rgba(255,255,255,0.7)!important;
  border-radius:12px!important;
  box-shadow:
    0 4px 20px rgba(252,182,159,0.08),
    0 1px 3px rgba(0,0,0,0.02)!important;
  transition:all 0.3s ease!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.75)!important;
  border-color:rgba(255,255,255,0.9)!important;
  transform:translateY(-4px) scale(1.01)!important;
  box-shadow:0 12px 40px rgba(252,182,159,0.15)!important;
}

.card-title{
  color:#5c3a3a!important;
  font-weight:700!important;
  font-size:16px!important;
}
.card-sub{
  color:rgba(92,58,58,0.4)!important;
  font-weight:600!important;
}
.card-icon{
  background:rgba(255,154,158,0.2)!important;
  color:#ff9a9e!important;
  border-radius:8px!important;
}
.card-arrow{
  color:rgba(255,154,158,0.3)!important;
}

.top-link-bar{
  background:rgba(255,255,255,0.5)!important;
  border:1px solid rgba(255,255,255,0.6)!important;
  backdrop-filter:blur(12px)!important;
  border-radius:10px!important;
  color:#5c3a3a!important;
}
.top-link-bar .link-text{
  color:rgba(92,58,58,0.45)!important;
  font-weight:600!important;
}

.social-item{
  background:rgba(255,255,255,0.35)!important;
  border:1px solid rgba(255,255,255,0.5)!important;
  color:rgba(92,58,58,0.45)!important;
  border-radius:8px!important;
  transition:all 0.3s!important;
}
.social-item:hover{
  background:rgba(255,255,255,0.6)!important;
  border-color:rgba(255,154,158,0.3)!important;
  color:#ff9a9e!important;
  transform:translateY(-2px)!important;
}

.stats-bar{
  color:rgba(92,58,58,0.25)!important;
  font-weight:600!important;
}
.stats-bar span{
  color:#5c3a3a!important;
}

.announcement-box{
  background:rgba(255,255,255,0.35)!important;
  border:1px solid rgba(255,154,158,0.15)!important;
  border-radius:10px!important;
  color:rgba(92,58,58,0.55)!important;
}

.footer-text{
  color:rgba(92,58,58,0.25)!important;
}
.footer-text a{
  color:rgba(255,154,158,0.5)!important;
}
.free-make-btn{
  background:rgba(255,154,158,0.15)!important;
  border:1px solid rgba(255,154,158,0.2)!important;
  color:#ff9a9e!important;
  border-radius:10px!important;
  font-weight:700!important;
}
.free-make-btn:hover{
  background:rgba(255,154,158,0.25)!important;
  transform:translateY(-2px)!important;
}
.music-player-btn{
  background:rgba(255,154,158,0.12)!important;
  border:1px solid rgba(255,154,158,0.2)!important;
  color:#ff9a9e!important;
  border-radius:50%!important;
}
.music-player-btn:hover{
  background:rgba(255,154,158,0.2)!important;
}

.text-block{
  background:rgba(255,255,255,0.3)!important;
  border:1px solid rgba(255,255,255,0.5)!important;
  border-radius:10px!important;
  color:rgba(92,58,58,0.5)!important;
}
.picture-block{
  border-radius:10px!important;
  border:1px solid rgba(255,255,255,0.5)!important;
}
.tipping-btn{
  background:rgba(255,154,158,0.15)!important;
  border:1px solid rgba(255,154,158,0.2)!important;
  color:#ff9a9e!important;
  border-radius:10px!important;
  font-weight:700!important;
}
.tipping-btn:hover{
  background:rgba(255,154,158,0.25)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(255,236,210,0.97)!important;
  backdrop-filter:blur(24px)!important;
  border:1px solid rgba(255,154,158,0.15)!important;
  border-radius:12px!important;
}
#passInput{
  background:rgba(255,255,255,0.5)!important;
  border:2px solid rgba(255,154,158,0.2)!important;
  color:#5c3a3a!important;
  border-radius:12px!important;
  font-weight:600!important;
}
'
];
