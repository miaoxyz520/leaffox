<?php
/**
 * Leaffox 用户主页模版 - Cyberpunk
 * ============================================
 * 风格：赛博朋克，霓虹紫蓝，故障效果，科技凌厉
 * ============================================
 */
return [
'css' => '
/* === Cyberpunk 赛博朋克 === */
body::before{
  content:"";position:fixed;inset:0;
  background:
    radial-gradient(ellipse 120% 80% at 10% 20%, #0a0015 0%, transparent 60%),
    radial-gradient(ellipse 100% 60% at 90% 80%, #0d0221 0%, transparent 60%),
    linear-gradient(135deg, #0a0015 0%, #12002e 30%, #0d001a 60%, #000000 100%)!important;
  z-index:-2!important;
}
body::after{
  content:"";position:fixed;inset:0;z-index:-1!important;
  background:
    repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,255,255,0.015) 2px, rgba(0,255,255,0.015) 4px),
    radial-gradient(circle at 20% 30%, rgba(255,0,255,0.06) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(0,255,255,0.06) 0%, transparent 50%);
  pointer-events:none;
}
body{
  background:transparent!important;
  font-family:"Inter","Orbitron",-apple-system,sans-serif!important;
}
.page-wrap{
  background:transparent!important;
}

/* 网格线条装饰 */
.page-wrap::before{
  content:"";position:fixed;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,rgba(255,0,255,0.3),rgba(0,255,255,0.3),transparent);
  z-index:999;pointer-events:none;
}

.avatar-wrap{
  border:2px solid rgba(255,0,255,0.3)!important;
  box-shadow:
    0 0 20px rgba(255,0,255,0.15),
    0 0 60px rgba(0,255,255,0.05),
    inset 0 0 20px rgba(255,0,255,0.05)!important;
  border-radius:50%!important;
}
.profile-name{
  color:#ffffff!important;
  font-family:"Orbitron",monospace!important;
  font-weight:900!important;
  font-size:22px!important;
  letter-spacing:4px!important;
  text-transform:uppercase!important;
  text-shadow:0 0 20px rgba(255,0,255,0.3),0 0 40px rgba(0,255,255,0.15)!important;
}
.profile-bio{
  color:rgba(0,255,255,0.5)!important;
  font-family:"Inter",sans-serif!important;
  letter-spacing:2px!important;
  font-size:12px!important;
  text-transform:uppercase!important;
}

.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,0,255,0.03)!important;
  backdrop-filter:blur(20px) hue-rotate(300deg)!important;
  border:1px solid rgba(0,255,255,0.08)!important;
  border-radius:4px!important;
  box-shadow:
    0 0 15px rgba(255,0,255,0.02),
    inset 0 0 15px rgba(0,255,255,0.02)!important;
  transition:all 0.3s ease!important;
  position:relative!important;
  overflow:hidden!important;
}
.card-glass::before,.card-neumorphism::before,.card-minimal::before{
  content:"";position:absolute;top:0;left:-100%;width:100%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(0,255,255,0.03),transparent);
  transition:left 0.6s ease!important;
}
.card-glass:hover::before,.card-neumorphism:hover::before,.card-minimal:hover::before{
  left:100%!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  border-color:rgba(255,0,255,0.2)!important;
  box-shadow:
    0 0 30px rgba(255,0,255,0.08),
    0 0 60px rgba(0,255,255,0.03),
    inset 0 0 30px rgba(255,0,255,0.03)!important;
  transform:translateY(-2px) scale(1.01)!important;
}

.card-title{
  color:#ffffff!important;
  font-weight:600!important;
  letter-spacing:1px!important;
}
.card-sub{
  color:rgba(0,255,255,0.3)!important;
  font-size:11px!important;
  text-transform:uppercase!important;
  letter-spacing:2px!important;
}
.card-icon{
  background:rgba(255,0,255,0.08)!important;
  color:#ff00ff!important;
  border-radius:4px!important;
  box-shadow:0 0 10px rgba(255,0,255,0.1)!important;
}
.card-arrow{
  color:rgba(0,255,255,0.3)!important;
}

.top-link-bar{
  background:rgba(255,0,255,0.03)!important;
  border:1px solid rgba(0,255,255,0.06)!important;
  backdrop-filter:blur(20px)!important;
  border-radius:4px!important;
  color:#ffffff!important;
}
.top-link-bar .link-text{
  color:rgba(0,255,255,0.4)!important;
  text-transform:uppercase!important;
  letter-spacing:1px!important;
  font-size:11px!important;
}

.social-item{
  background:rgba(255,0,255,0.03)!important;
  border:1px solid rgba(0,255,255,0.06)!important;
  color:rgba(0,255,255,0.3)!important;
  border-radius:4px!important;
  transition:all 0.3s!important;
}
.social-item:hover{
  background:rgba(255,0,255,0.08)!important;
  border-color:rgba(255,0,255,0.2)!important;
  color:#ff00ff!important;
  box-shadow:0 0 20px rgba(255,0,255,0.1)!important;
}

.stats-bar{
  color:rgba(255,255,255,0.15)!important;
  font-family:"Orbitron",monospace!important;
  font-size:10px!important;
  letter-spacing:2px!important;
}
.stats-bar span{
  color:rgba(0,255,255,0.4)!important;
}

.announcement-box{
  background:rgba(255,0,255,0.02)!important;
  border:1px solid rgba(0,255,255,0.06)!important;
  border-radius:4px!important;
  color:rgba(0,255,255,0.4)!important;
  font-size:12px!important;
  letter-spacing:1px!important;
}

.footer-text{
  color:rgba(255,255,255,0.1)!important;
  font-family:"Orbitron",monospace!important;
  font-size:10px!important;
  letter-spacing:2px!important;
}
.footer-text a{
  color:rgba(255,0,255,0.25)!important;
}
.free-make-btn{
  background:rgba(255,0,255,0.06)!important;
  border:1px solid rgba(255,0,255,0.15)!important;
  color:#ff00ff!important;
  border-radius:4px!important;
  font-family:"Orbitron",monospace!important;
  letter-spacing:2px!important;
  font-size:11px!important;
  text-transform:uppercase!important;
}
.free-make-btn:hover{
  background:rgba(255,0,255,0.12)!important;
  box-shadow:0 0 20px rgba(255,0,255,0.1)!important;
}
.music-player-btn{
  background:rgba(0,255,255,0.04)!important;
  border:1px solid rgba(0,255,255,0.1)!important;
  color:#00ffff!important;
  border-radius:4px!important;
}
.music-player-btn:hover{
  background:rgba(0,255,255,0.08)!important;
  box-shadow:0 0 20px rgba(0,255,255,0.05)!important;
}

.text-block{
  background:rgba(255,0,255,0.02)!important;
  border:1px solid rgba(0,255,255,0.06)!important;
  border-radius:4px!important;
  color:rgba(0,255,255,0.4)!important;
}
.picture-block{
  border-radius:4px!important;
  border:1px solid rgba(0,255,255,0.06)!important;
}
.tipping-btn{
  background:rgba(255,0,255,0.06)!important;
  border:1px solid rgba(255,0,255,0.15)!important;
  color:#ff00ff!important;
  border-radius:4px!important;
}
.tipping-btn:hover{
  background:rgba(255,0,255,0.12)!important;
}

.report-box,.modal-box,#passModal{
  background:rgba(10,0,21,0.97)!important;
  backdrop-filter:blur(30px) hue-rotate(300deg)!important;
  border:1px solid rgba(255,0,255,0.1)!important;
  border-radius:4px!important;
}
#passInput{
  background:rgba(0,255,255,0.03)!important;
  border:1px solid rgba(255,0,255,0.15)!important;
  color:#ffffff!important;
  border-radius:4px!important;
  font-family:"Orbitron",monospace!important;
}
'
];
