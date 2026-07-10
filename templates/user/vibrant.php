<?php
/**
 * Leaffox 用户主页模版 - Vibrant（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/vibrant.php
 * 风格：绚丽活力，动态渐变背景，彩虹文字，大圆角卡片
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
/* ---- 全局背景动态渐变 ---- */
body{
  background:linear-gradient(-45deg,#667eea,#764ba2,#f093fb,#f5576c,#4facfe)!important;
  background-size:400% 400%!important;
  animation:gradientShift 12s ease infinite!important;
}
@keyframes gradientShift{
  0%{background-position:0% 50%}
  50%{background-position:100% 50%}
  100%{background-position:0% 50%}
}
body::before{background:linear-gradient(180deg,rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.05) 40%,rgba(0,0,0,0.3) 100%)!important}

/* ---- 头像动画 ---- */
.avatar-wrap{
  border-color:rgba(255,255,255,0.3)!important;
  box-shadow:0 0 0 4px rgba(255,255,255,0.15),0 8px 32px rgba(0,0,0,0.2)!important;
  animation:float 3s ease-in-out infinite!important;
}
@keyframes float{
  0%,100%{transform:translateY(0)}
  50%{transform:translateY(-8px)}
}
.avatar-wrap:hover{animation:none!important;transform:scale(1.1) rotate(-5deg)!important}

/* ---- 名称彩虹渐变 ---- */
.profile-name{
  font-size:26px!important;
  background:linear-gradient(90deg,#f9a8d4,#c084fc,#818cf8,#38bdf8,#34d399)!important;
  background-size:300% auto!important;
  -webkit-background-clip:text!important;
  background-clip:text!important;
  -webkit-text-fill-color:transparent!important;
  animation:rainbow 4s linear infinite!important;
}
@keyframes rainbow{
  0%{background-position:0% center}
  100%{background-position:300% center}
}
.profile-name:hover{-webkit-text-fill-color:transparent!important}

/* ---- 简介 ---- */
.profile-bio{color:rgba(255,255,255,0.8)!important;font-size:15px!important}

/* ---- 顶部栏 ---- */
.top-link-bar{
  background:rgba(255,255,255,0.12)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(255,255,255,0.15)!important;
  border-radius:50px!important;
  padding:8px 18px!important;
}
.top-link-bar .link-text{color:rgba(255,255,255,0.7)!important}
.top-link-bar .bar-btn{background:rgba(255,255,255,0.1)!important;color:rgba(255,255,255,0.85)!important;border-radius:50px!important;padding:6px 16px!important}
.top-link-bar .bar-btn:hover{background:rgba(255,255,255,0.2)!important}

/* ---- 公告 ---- */
.announcement-box{
  background:rgba(255,255,255,0.1)!important;
  backdrop-filter:blur(16px)!important;
  border:1px solid rgba(255,255,255,0.12)!important;
  border-radius:12px!important;
  color:rgba(255,255,255,0.9)!important;
}
.announcement-box:hover{background:rgba(255,255,255,0.15)!important;border-color:rgba(255,255,255,0.2)!important}

/* ---- 卡片：圆润+渐变 ---- */
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.1)!important;
  backdrop-filter:blur(24px)!important;
  -webkit-backdrop-filter:blur(24px)!important;
  border:1px solid rgba(255,255,255,0.12)!important;
  border-radius:50px!important;
  padding:18px 24px!important;
  transition:all 0.5s cubic-bezier(0.34,1.56,0.64,1)!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(255,255,255,0.18)!important;
  border-color:rgba(255,255,255,0.3)!important;
  transform:translateY(-4px) scale(1.03)!important;
  box-shadow:0 12px 40px rgba(0,0,0,0.2),0 0 40px rgba(255,255,255,0.05)!important;
}
.card-glass:active,.card-neumorphism:active,.card-minimal:active{transform:scale(0.95)!important}
.card-glass.outline,.card-neumorphism.outline,.card-minimal.outline{
  background:transparent!important;
  border:2px solid rgba(255,255,255,0.2)!important;
}
.card-glass.outline:hover,.card-neumorphism.outline:hover,.card-minimal.outline:hover{
  background:rgba(255,255,255,0.05)!important;
  border-color:rgba(255,255,255,0.4)!important;
}
.card-neumorphism{box-shadow:6px 6px 16px rgba(0,0,0,0.15),-6px -6px 16px rgba(255,255,255,0.05)!important}
.card-neumorphism:hover{box-shadow:3px 3px 8px rgba(0,0,0,0.2),-3px -3px 8px rgba(255,255,255,0.08)!important}
.card-minimal{border-radius:12px!important}
.card-minimal::before{display:none!important}
.card-minimal:hover{padding-left:24px!important}
/* 图标放大效果 */
.card-icon{font-size:32px!important;transition:transform 0.5s cubic-bezier(0.34,1.56,0.64,1)!important}
.card-glass:hover .card-icon,.card-neumorphism:hover .card-icon,.card-minimal:hover .card-icon{transform:scale(1.3) rotate(-10deg)!important}
.card-title{font-size:16px!important;font-weight:700!important}
.card-arrow{font-size:16px!important}
.card-glass:hover .card-arrow{transform:translateX(8px) scale(1.3)!important;color:rgba(255,255,255,0.8)!important}

/* ---- 文字模块 ---- */
.text-block{
  background:rgba(255,255,255,0.08)!important;
  backdrop-filter:blur(12px)!important;
  border-radius:12px!important;
  color:rgba(255,255,255,0.85)!important;
  padding:20px 24px!important;
}
.text-block:hover{background:rgba(255,255,255,0.12)!important;border-color:rgba(255,255,255,0.15)!important}

/* ---- 图片模块 ---- */
.picture-block{border-radius:12px!important}
.picture-block img{border-radius:12px!important}
.picture-block:hover{transform:scale(1.05)!important;box-shadow:0 16px 48px rgba(0,0,0,0.3)!important}

/* ---- 社交 ---- */
.social-item{
  width:48px!important;height:48px!important;border-radius:50%!important;
  background:rgba(255,255,255,0.08)!important;
  border:1px solid rgba(255,255,255,0.1)!important;
  font-size:22px!important;
}
.social-item:hover{
  transform:translateY(-4px) scale(1.15)!important;
  background:rgba(255,255,255,0.15)!important;
  border-color:rgba(255,255,255,0.25)!important;
  box-shadow:0 8px 25px rgba(0,0,0,0.15)!important;
}

/* ---- 打赏 ---- */
.tipping-btn{
  border-radius:50px!important;
  padding:12px 28px!important;
  background:linear-gradient(135deg,rgba(244,63,94,0.15),rgba(244,63,94,0.08))!important;
  border:1px solid rgba(244,63,94,0.2)!important;
  font-size:15px!important;
}
.tipping-btn:hover{background:linear-gradient(135deg,rgba(244,63,94,0.25),rgba(244,63,94,0.15))!important;border-color:rgba(244,63,94,0.4)!important}

/* ---- 统计 ---- */
.stats-bar{
  background:rgba(255,255,255,0.06)!important;
  backdrop-filter:blur(12px)!important;
  border:1px solid rgba(255,255,255,0.06)!important;
  border-radius:50px!important;
  padding:10px 24px!important;
}
.stats-bar span{color:rgba(255,255,255,0.5)!important}

/* ---- 免费制作按钮 ---- */
.free-make-btn{
  background:linear-gradient(135deg,#f472b6,#a78bfa,#818cf8)!important;
  background-size:200% auto!important;
  animation:rainbow 3s linear infinite!important;
  border-radius:50px!important;
  box-shadow:0 4px 25px rgba(168,85,247,0.3)!important;
}
.free-make-btn:hover{transform:translateY(-3px) scale(1.05)!important;box-shadow:0 8px 35px rgba(168,85,247,0.4)!important}

/* ---- 音乐播放器 ---- */
.music-player-btn{
  width:54px!important;height:54px!important;
  background:rgba(255,255,255,0.12)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(255,255,255,0.15)!important;
}
.music-player-btn:hover{transform:scale(1.15)!important;background:rgba(255,255,255,0.2)!important}

/* ---- 页脚 ---- */
.footer-text{color:rgba(255,255,255,0.3)!important}
.footer-text .has-powered{border-color:rgba(255,255,255,0.06)!important}
.footer-text a{color:rgba(255,255,255,0.4)!important}
.footer-text a:hover{color:rgba(255,255,255,0.7)!important}

/* ---- 弹窗适配 ---- */
.report-box,.modal-box{background:rgba(20,20,40,0.95)!important;backdrop-filter:blur(30px)!important;border-color:rgba(255,255,255,0.08)!important}
.share-modal-box{background:rgba(20,20,40,0.96)!important;backdrop-filter:blur(30px)!important;border-color:rgba(255,255,255,0.08)!important}
'
];
