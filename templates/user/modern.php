<?php
/**
 * Leaffox 用户主页模版 - Modern（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/modern.php
 * 风格：清爽明亮，白/灰色调，柔和大圆角，温和阴影
 * 
 * 可用变量：参见 templates/user/default.php 头部注释
 * 返回值：['css' => 'CSS 覆盖样式（需 !important）']
 * ============================================
 */
return [
'body_bg' => '#f0f2f5',
'body_color' => '#1e293b',
'card_bg' => 'rgba(255,255,255,0.92)',
'card_shadow' => '0 2px 12px rgba(0,0,0,0.06)',
'card_border' => '1px solid rgba(0,0,0,0.04)',
'card_hover_shadow' => '0 8px 30px rgba(0,0,0,0.1)',
'text_primary' => '#0f172a',
'text_secondary' => '#64748b',
'text_muted' => '#94a3b8',
'accent' => '#6366f1',
'radius_sm' => '12px',
'radius_md' => '16px',
'radius_lg' => '24px',
'overlay' => 'rgba(0,0,0,0.02)',
'css' => '
body{background:#f0f2f5!important;color:#1e293b!important}
body::before{display:none!important}
.page-wrap{padding-top:40px!important}
.avatar-wrap{border-color:rgba(0,0,0,0.08)!important;box-shadow:0 4px 20px rgba(0,0,0,0.06)!important}
.avatar-wrap:hover{border-color:#6366f1!important;box-shadow:0 8px 30px rgba(99,102,241,0.15)!important}
.profile-name{color:#0f172a!important}
.profile-name:hover{-webkit-text-fill-color:#6366f1!important}
.profile-bio{color:#64748b!important}
.top-link-bar{background:rgba(255,255,255,0.85)!important;border-color:rgba(0,0,0,0.06)!important;backdrop-filter:blur(20px)!important}
.top-link-bar .link-text{color:#64748b!important}
.top-link-bar .bar-btn{background:rgba(99,102,241,0.08)!important;color:#6366f1!important}
.top-link-bar .bar-btn:hover{background:rgba(99,102,241,0.15)!important}
.announcement-box{background:rgba(255,255,255,0.85)!important;border-color:rgba(0,0,0,0.06)!important;color:#475569!important}
.announcement-box:hover{border-color:rgba(99,102,241,0.2)!important}
/* 卡片样式覆盖 */
.card-glass,.card-neumorphism,.card-minimal{
  background:rgba(255,255,255,0.92)!important;
  backdrop-filter:blur(20px)!important;
  border:1px solid rgba(0,0,0,0.04)!important;
  box-shadow:0 2px 12px rgba(0,0,0,0.06)!important;
  border-radius:10px!important;
}
.card-glass:hover,.card-neumorphism:hover,.card-minimal:hover{
  box-shadow:0 8px 30px rgba(0,0,0,0.1)!important;
  border-color:rgba(99,102,241,0.2)!important;
  background:rgba(255,255,255,0.96)!important;
}
.card-glass.outline,.card-neumorphism.outline,.card-minimal.outline{
  background:transparent!important;
  border:2px solid rgba(0,0,0,0.08)!important;
}
.card-glass.outline:hover,.card-neumorphism.outline:hover,.card-minimal.outline:hover{
  border-color:#6366f1!important;
  background:rgba(99,102,241,0.03)!important;
}
.card-neumorphism{box-shadow:4px 4px 10px rgba(0,0,0,0.04),-4px -4px 10px rgba(255,255,255,0.8)!important}
.card-neumorphism:hover{box-shadow:2px 2px 6px rgba(0,0,0,0.06),-2px -2px 6px rgba(255,255,255,0.9)!important}
.card-minimal{border-bottom:1px solid rgba(0,0,0,0.06)!important;border-radius:0!important}
.card-minimal::before{background:linear-gradient(180deg,#6366f1,#818cf8)!important}
.card-title{color:#0f172a!important}
.card-sub{color:#94a3b8!important}
.card-arrow{color:#cbd5e1!important}
.card-glass:hover .card-arrow,.card-neumorphism:hover .card-arrow,.card-minimal:hover .card-arrow{color:#6366f1!important}
.text-block{color:#64748b!important;border-radius:12px!important}
.text-block:hover{background:rgba(0,0,0,0.02)!important;border-color:rgba(0,0,0,0.06)!important}
.stats-bar{background:rgba(255,255,255,0.7)!important;border-color:rgba(0,0,0,0.04)!important}
.stats-bar span{color:#94a3b8!important}
.stats-bar span:hover{color:#64748b!important}
.social-item{background:rgba(0,0,0,0.03)!important;border-color:rgba(0,0,0,0.06)!important;color:rgba(0,0,0,0.4)!important}
.social-item:hover{background:rgba(99,102,241,0.08)!important;border-color:rgba(99,102,241,0.2)!important;color:#6366f1!important;box-shadow:0 4px 15px rgba(99,102,241,0.12)!important}
.tipping-btn{background:rgba(244,63,94,0.06)!important;border-color:rgba(244,63,94,0.12)!important;color:#64748b!important}
.tipping-btn:hover{background:rgba(244,63,94,0.12)!important;color:#e11d48!important;border-color:rgba(244,63,94,0.25)!important}
.footer-text{color:#cbd5e1!important}
.footer-text .has-powered{border-color:rgba(0,0,0,0.04)!important}
.footer-text a{color:#94a3b8!important}
.footer-text a:hover{color:#6366f1!important}
.free-make-btn{background:linear-gradient(135deg,#6366f1,#818cf8)!important;box-shadow:0 4px 20px rgba(99,102,241,0.25)!important}
/* 弹窗适配 */
.report-box,.modal-box{background:#fff!important;border-color:rgba(0,0,0,0.06)!important}
.report-box h3,.modal-box h3,.share-modal-box h3{color:#0f172a!important}
.report-box .sub,.modal-box p,.share-modal-box .sub{color:#94a3b8!important}
.report-type-btn{color:#64748b!important;background:rgba(0,0,0,0.02)!important;border-color:rgba(0,0,0,0.06)!important}
.report-type-btn:hover{background:rgba(99,102,241,0.06)!important;border-color:rgba(99,102,241,0.15)!important;color:#6366f1!important}
.report-type-btn.selected{background:rgba(99,102,241,0.1)!important;border-color:#6366f1!important;color:#6366f1!important}
.report-reason{background:rgba(0,0,0,0.02)!important;border-color:rgba(0,0,0,0.06)!important;color:#1e293b!important}
.report-reason::placeholder{color:#cbd5e1!important}
.report-cancel-btn{background:rgba(0,0,0,0.03)!important;color:#94a3b8!important}
.report-cancel-btn:hover{background:rgba(0,0,0,0.06)!important;color:#64748b!important}
.modal-box input[type=password]{background:rgba(0,0,0,0.02)!important;border-color:rgba(0,0,0,0.1)!important;color:#0f172a!important}
.modal-box input[type=password]:focus{border-color:#6366f1!important;background:rgba(99,102,241,0.02)!important}
.share-modal-box{background:rgba(255,255,255,0.98)!important;border-color:rgba(0,0,0,0.06)!important}
.share-modal-box .share-url-row{background:rgba(0,0,0,0.02)!important;border-color:rgba(0,0,0,0.06)!important}
.share-modal-box .share-url-row .url-text{color:#64748b!important}
.close-share-btn{background:rgba(0,0,0,0.03)!important;color:#94a3b8!important}
.close-share-btn:hover{background:rgba(0,0,0,0.06)!important;color:#64748b!important}
#socialModalContent{color:#0f172a!important}
'
];
