<?php
/**
 * Leaffox 用户主页模版 - Default（内置）
 * ============================================
 * 模版类型：user（用户专属页）
 * 文件路径：templates/user/default.php
 * 变量说明：
 *   $templateCssData    (array)  模版返回数据
 *     ['css']  (string)  CSS 样式代码（用 !important 覆盖默认）
 * 
 * 可用 CSS 选择器参考：
 *   全局背景：     body, body::before, .page-wrap
 *   头像：         .avatar-wrap, .avatar-wrap img, .no-avatar
 *   名称/简介：    .profile-name, .profile-bio
 *   顶部栏：       .top-link-bar, .top-link-bar .link-text, .bar-btn
 *   公告：         .announcement-box
 *   链接卡片：     .card-glass, .card-neumorphism, .card-minimal
 *                  .card-icon, .card-title, .card-sub, .card-arrow
 *                  .card-lock, .card-tag, .text-center
 *                  .card-glass.outline, .card-neumorphism.outline, .card-minimal.outline
 *   文字模块：     .text-block
 *   图片模块：     .picture-block
 *   社交：         .social-item, .social-wrap
 *   打赏：         .tipping-btn
 *   统计：         .stats-bar, .stats-bar span
 *   页脚：         .footer-text, .footer-text .has-powered, .footer-text a
 *   免费制作按钮： .free-make-btn, .free-make-wrap
 *   音乐播放器：   .music-player-btn
 *   弹窗：         .report-box, .modal-box, .modal-overlay
 *                  .share-modal-box, .share-modal-overlay
 *                  .report-type-btn, .report-reason
 *                  .report-cancel-btn, .report-submit-btn
 *   密码弹窗：     #passModal, #passInput, .modal-err
 * 返回值格式：
 *   return [ 'css' => 'CSS 代码' ];
 *   所有 CSS 规则必须使用 !important 覆盖默认
 * ============================================
 */
// 此模版不输出任何 CSS，完全使用 page/index.php 中的默认样式
return [];
