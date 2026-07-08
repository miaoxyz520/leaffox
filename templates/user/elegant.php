<?php
/**
 * Leaffox 用户主页模版 - Elegant（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/elegant.php
 * 风格：优雅简约，暖色调，圆润卡片，精致细节
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'css' => '
@import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700;900&family=Inter:wght@300;400;500;600;700&display=swap");

body{
  font-family:"Playfair Display","Noto Serif SC",Georgia,serif!important;
  background:linear-gradient(180deg,#1a1a2e 0%,#16213e 40%,#0f3460 100%)!important;
}
body::before{
  background:linear-gradient(180deg,rgba(0,0,0,0.5) 0%,rgba(0,0,0,0.1) 30%,rgba(0,0,0,0.4) 100%)!important;
}

/* ---- 装饰性顶部分隔线 ---- */
.page-wrap::before{
  content:"";display:block;width:60px;height:2px;
  background:linear-gradient(90deg,#d4a373,#faedcd);
  margin:0 auto 20px;border-radius:2px;animation:fadeUp 0.6s ease both;
}

/* ---- 头像 - 金边圆框 ---- */
.avatar-wrap{
  width:110px!important;height:110px!important;
  border-radius:50%!important;
  border:3px solid rgba(212,163,115,0.4)!important;
  box-shadow:0 0 0 6px rgba(212,163,115,0.08),0 8px 32px rgba(0,0,0,0.2)!important;
  background:rgba(255,255,255,0.04)!important;
}
.avatar-wrap:hover{
  border-color:rgba(212,163,115,0.7)!important;
  box-shadow:0 0 0 8px rgba(212,163,115,0.12),0 12px 40px rgba(0,0,0,0.25)!important;
  transform:scale(1.05)!important;
}

/* ---- 名称 - 衬线字体 ---- */
.profile-name{
  font-family:"Playfair Display","Noto Serif SC",Georgia,serif!important;
  font-size:28px!important;font-weight:700!important;
  color:#faedcd!important;letter-spacing:2px!important;
}
.profile-name:hover{-webkit-text-fill-color:#d4a373!important}

/* ---- 简介 ---- */
.profile-bio{
  font-family:"Inter","Noto Sans SC",sans-serif!important;
  font-size:14px!important;font-weight:300!important;
  color:rgba(250,237,205,0.6)!important;letter-spacing:0.5px!important;
}

/* ---- 顶部栏 ---- */
.top-link-bar{
  background:rgba(212,163,115,0.06)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(212,163,115,0.1)!important;
  border-radius:30px!important;
  padding:8px 16px!important;
}
.top-link-bar .link-text{color:rgba(250,237,205,0.5)!important}
.top-link-bar .bar-btn{background:rgba(212,163,115,0.08)!important;color:#d4a373!important;border-radius:20px!important}
.top-link-bar .bar-btn:hover{background:rgba(212,163,115,0.15)!important}

/* ---- 公告 ---- */
.announcement-box{
  background:rgba(212,163,115,0.04)!important;
  backdrop-filter:blur(16px)!important;
  border:1px solid rgba(212,163,115,0.08)!important;
  border-radius:16px!important;
  color:rgba(250,237,205,0.8)!important;
  font-family:"Inter","Noto Sans SC",sans-serif!important;
  font-size:13px!important;
}
.announcement-box:hover{background:rgba(212,163,115,0.08)!important;border-color:rgba(212,163,115,0.15)!important}

/* ---- 卡片 - 金边圆角 ---- */
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(250,237,205,0.04)!important;
  backdrop-filter:blur(20px)!important;
  -webkit-backdrop-filter:blur(20px)!important;
  border:1px solid rgba(212,163,115,0.1)!important;
  border-radius:30px!important;
  padding:18px 24px!important;
  transition:all 0.6s cubic-bezier(0.34,1.56,0.64,1)!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  background:rgba(250,237,205,0.08)!important;
  border-color:rgba(212,163,115,0.25)!important;
  transform:translateY(-3px)!important;
  box-shadow:0 8px 30px rgba(0,0,0,0.15),0 0 30px rgba(212,163,115,0.05)!important;
}
.card-glass:active,.card-neumorphism:active,.card-minimal:active{transform:scale(0.97)!important}
.card-glass.outline,.card-neumorphism.outline,.card-minimal.outline{
  background:transparent!important;
  border:1.5px solid rgba(212,163,115,0.15)!important;
}
.card-glass.outline:hover,.card-neumorphism.outline:hover,.card-minimal.outline:hover{
  background:rgba(212,163,115,0.03)!important;
  border-color:rgba(212,163,115,0.3)!important;
}
.card-neumorphism{box-shadow:5px 5px 12px rgba(0,0,0,0.15),-5px -5px 12px rgba(250,237,205,0.02)!important}
.card-neumorphism:hover{box-shadow:2px 2px 6px rgba(0,0,0,0.2),-2px -2px 6px rgba(250,237,205,0.03)!important}
.card-minimal{border-radius:20px!important}
.card-minimal::before{background:linear-gradient(180deg,#d4a373,#faedcd)!important;width:2px!important;border-radius:0 2px 2px 0!important}

/* 图标 */
.card-icon{font-size:28px!important;transition:transform 0.6s cubic-bezier(0.34,1.56,0.64,1)!important}
.card-glass:hover .card-icon,.card-neumorphism:hover .card-icon,.card-minimal:hover .card-icon{transform:scale(1.2) rotate(-8deg)!important}

.card-title{
  font-family:"Playfair Display","Noto Serif SC",Georgia,serif!important;
  font-size:16px!important;font-weight:600!important;color:#faedcd!important;letter-spacing:0.5px!important;
}
.card-sub{
  font-family:"Inter","Noto Sans SC",sans-serif!important;
  font-size:11px!important;color:rgba(212,163,115,0.4)!important;
}
.card-arrow{color:rgba(212,163,115,0.2)!important}
.card-glass:hover .card-arrow,.card-neumorphism:hover .card-arrow,.card-minimal:hover .card-arrow{
  transform:translateX(8px) scale(1.2)!important;color:#d4a373!important
}

/* ---- 文字模块 ---- */
.text-block{
  font-family:"Inter","Noto Sans SC",sans-serif!important;
  background:rgba(250,237,205,0.03)!important;
  backdrop-filter:blur(12px)!important;
  border-radius:20px!important;
  color:rgba(250,237,205,0.7)!important;
  padding:18px 22px!important;
}
.text-block:hover{background:rgba(250,237,205,0.06)!important;border-color:rgba(212,163,115,0.1)!important}

/* ---- 社交 ---- */
.social-item{
  width:46px!important;height:46px!important;border-radius:50%!important;
  background:rgba(212,163,115,0.04)!important;
  border:1px solid rgba(212,163,115,0.08)!important;
}
.social-item:hover{
  background:rgba(212,163,115,0.1)!important;
  border-color:rgba(212,163,115,0.2)!important;
  box-shadow:0 4px 20px rgba(212,163,115,0.08)!important;
}

/* ---- 打赏 ---- */
.tipping-btn{
  border-radius:30px!important;
  background:rgba(212,163,115,0.06)!important;
  border:1px solid rgba(212,163,115,0.12)!important;
  color:#d4a373!important;
}
.tipping-btn:hover{background:rgba(212,163,115,0.12)!important;border-color:rgba(212,163,115,0.25)!important;color:#faedcd!important}

/* ---- 统计 ---- */
.stats-bar{
  background:rgba(212,163,115,0.03)!important;
  backdrop-filter:blur(12px)!important;
  border:1px solid rgba(212,163,115,0.04)!important;
  border-radius:30px!important;
}
.stats-bar span{color:rgba(212,163,115,0.35)!important}
.stats-bar span:hover{color:rgba(212,163,115,0.6)!important}

/* ---- 页脚 ---- */
.footer-text{
  font-family:"Inter","Noto Sans SC",sans-serif!important;
  color:rgba(212,163,115,0.2)!important
}
.footer-text .has-powered{border-color:rgba(212,163,115,0.04)!important}
.footer-text a{color:rgba(212,163,115,0.25)!important}
.footer-text a:hover{color:rgba(212,163,115,0.5)!important}

/* ---- 免费制作 ---- */
.free-make-btn{
  background:linear-gradient(135deg,#d4a373,#faedcd)!important;
  color:#1a1a2e!important;
  box-shadow:0 4px 20px rgba(212,163,115,0.2)!important;
  font-family:"Inter","Noto Sans SC",sans-serif!important;
  border-radius:50px!important;
}

/* ---- 音乐播放器 ---- */
.music-player-btn{
  background:rgba(212,163,115,0.08)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(212,163,115,0.1)!important;
}

/* ---- 弹窗适配 ---- */
.report-box,.modal-box{background:rgba(26,26,46,0.96)!important;backdrop-filter:blur(30px)!important;border-color:rgba(212,163,115,0.06)!important}
.share-modal-box{background:rgba(26,26,46,0.96)!important;backdrop-filter:blur(30px)!important;border-color:rgba(212,163,115,0.06)!important}
'
];
