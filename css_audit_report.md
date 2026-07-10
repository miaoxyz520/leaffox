# CSS + 显示问题深度审计报告

> 生成时间: 2026-07-09 12:45
> 审计范围: 用户控制台 & 管理员控制台

---

## 🔴 P0 - 严重问题

### 1. admin/dashboard.php CSS 完全重复定义

**问题:** 同一个 `<style>` 块内，整个侧边栏/导航/主题切换样式被写了**两遍**。

| 选择器 | 第1次定义 (L55-94, 被覆盖) | 第2次定义 (L163-200, 实际生效) |
|--------|:-------------------------:|:---------------------------:|
| `.sidebar-logo padding` | 22px 20px | 18px 16px |
| `.sidebar-logo gap` | 12px | 10px |
| `.nav-item padding` | 11px 16px | 9px 16px 9px 42px |
| `.nav-item gap` | 12px | 10px |
| `.nav-item font-size` | 14px | 13px |
| `.nav-item i width` | 20px | 16px |
| `tt-switch width/height` | 38px / 20px | 36px / 18px |
| `tt-icon width` | 20px | 18px |
| `.sidebar transition` | all 0.3s (性能差) | transform 0.3s,box-shadow 0.3s |

**影响:** 第1次定义完全无效（死代码），但维护时极易改错。

**修复:** 删除 L55-94 的重复样式，只保留手风琴版。同时确保媒体查询一致。

### 2. `.main` 最大宽度丢失

第1次: `margin-left:240px; flex:1; min-height:100vh; padding:24px 32px; max-width:1200px`
第2次: `margin-left:240px` ← 只覆盖了这一行

`max-width:1200px` 和 `padding:24px 32px` 仍从第1次继承（刚好没问题）。

---

## 🟡 P1 - 中等问题

### 3. 用户控制台汉堡按钮依赖 Tailwind CDN

**用户控制台:** 使用 Tailwind 内联类 `fixed top-3 left-3 z-[60] md:hidden ...`
**管理员控制台:** 有完整的 `.hamburger{display:none} → @media{display:flex}` CSS

**风险:** Tailwind CDN 加载慢/失败时，用户控制台移动端汉堡按钮消失。

### 4. 子页面 PHP 动态类名边界条件

多个子页面有类似代码:
```php
class="<?=$tab==='active'?'bg-indigo-500/30':''?>"
class="<?=$emptyIcon?>"
```
变量未定义或为 null 时 → `class=""` 或样式缺失。

### 5. admin/users.php 三栏布局与 dashboard 不一致

| 页面 | 侧边栏结构 | main margin-left |
|------|-----------|:----------------:|
| admin/dashboard.php | 单侧边栏 240px | 240px |
| admin/users.php | 主栏 72px + 次级栏 200px | 272px (72+200) |

---

## 🟠 P2 - 显示/样式问题

### 6. 浅色模式颜色对比度不足

```
浅色背景: #f1f5f9
--admin-text-muted: #94a3b8  → 对比度 ~3.2:1 (WCAG AA 要求 4.5:1 ❌)
```

### 7. `.mb-6` 空规则
```css
.mb-6{ /* keep */ }
```
无用空选择器，建议删除。

### 8. 6 个未使用的 @keyframes
`cardFloat`、`dotBounce`、`glowPulse`、`pulse`、`ripple`、`shimmerBorder`

### 9. 9 个未使用的 CSS 变量
`--bg-btn`、`--bg-report`、`--icon-color`、`--icon-hover`、`--loader-bg`、`--user-card-hover`、`--user-input-bg`、`--user-input-border`、`--user-overlay`

---

## ✅ 已确认正常的部分

- ✅ 所有文件大括号平衡
- ✅ 所有引用的 CSS 变量都有定义
- ✅ 所有引用的 @keyframes 都有定义
- ✅ 用户控制台移动端 768px 响应式正常（margin-left:0）
- ✅ 管理员控制台移动端 900px 响应式正常
- ✅ Font Awesome 6.5 CDN 已引入
- ✅ Tailwind CSS CDN 已引入

---

## 📋 修复优先级

| 优先级 | 文件 | 问题 | 建议 |
|:------:|------|------|------|
| **P0** | `admin/dashboard.php` | CSS 重复定义 | 删除第1套(原始)，保留第2套(手风琴版) |
| **P1** | `user/dashboard.php` | 汉堡按钮无独立CSS | 添加 `.hamburger` 样式+媒体查询 |
| **P1** | `admin/dashboard.php` | 浅色模式对比度 | 改 `#94a3b8` → `#64748b` |
| **P2** | `admin/dashboard.php` | 清理死代码 | 删 `.mb-6`, 6个动画, 9个变量 |
| **P2** | `user/dashboard.php` | 清理死代码 | 删未用变量 |
