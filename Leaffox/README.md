# 🍃 Leaffox 多用户主页系统

> **一款轻量级、高颜值、多用户个人主页 / 链接聚合系统**  
> 支持：链接卡片 · 图文展示 · 视频播放 · 背景音乐 · 社交集成 · 数据统计 · 多用户独立管理

<p align="center">
  <a href="https://www.leaffox.cn" target="_blank">🌐 在线演示站</a> &nbsp;|&nbsp;
  <a href="https://qun.qq.com/universal-share/share?ac=1&authKey=knpzQ1X9BQwHvtKzcMgAcDt6hcXiwTgyCyAgy5Zbw0PY%2BATsOGar%2FC79hYfbAgDi&busi_data=eyJncm91cENvZGUiOiI2MjkwNTczOTAiLCJ0b2tlbiI6IkFRTFhwQUFqU0JndmVmQWJ0WXlHTk5ZSkhsRDduQUNaOU5XSnhHUTd5bzVZdmFvY2RkMk85dnlySW5IL0t5MzgiLCJ1aW4iOiIzNDEzMTk0MTUxIn0%3D&data=pa96s9SACrKhcnl4TAm8Xa_94sRUn7UaFz1--QaJ9KxG2Tx_ZBMnZSGOZjtUvwR70_PnfS8YJs1VSTlBmHMTFQ&svctype=4&tempid=h5_group_info" target="_blank">💬 官方 Q 群（629057390）</a>
</p>

---

## 📖 目录

- [✨ 功能亮点](#-功能亮点)
- [🖼️ 界面截图](#️-界面截图)
- [🚀 快速部署](#-快速部署)
  - [环境要求](#环境要求)
  - [Apache 部署](#方式一-apache推荐)
  - [Nginx 部署](#方式二-nginx)
  - [安装步骤](#安装步骤)
- [📁 目录结构](#-目录结构)
- [⚙️ 技术架构](#️-技术架构)
- [❓ 常见问题](#-常见问题)
- [📄 开源许可](#-开源许可)

---

## ✨ 功能亮点

### 🎨 个性化装扮（自由定制）
- **背景**：支持纯色、渐变色、自定义背景图片，主题模式支持深色 / 浅色 / 跟随系统自动切换
- **卡片样式**：Glass 毛玻璃风格 · Neumorphism 新拟态 · Minimal 极简风，3 种卡片样式一键切换
- **自定义主题色**：按钮颜色、文字颜色、卡片背景色均可独立设置，千人千面

### 🔗 链接卡片（4 种类型）
| 类型 | 说明 |
|------|------|
| **🔗 网页链接** | 点击跳转，支持追踪点击统计 |
| **📝 纯文字展示** | 仅展示文案，不可点击，适合公告/介绍 |
| **🖼️ 图片展示** | 显示缩略图，点击弹窗查看大图 |
| **🎬 视频播放** | 支持 B 站内嵌播放器，无需跳出页面 |

- **密码保护**：单个链接可设置访问密码，私密内容更安全
- **图标支持**：每个链接可自定义 Emoji/图标

### 🎵 背景音乐
- 自定义音乐文件链接，支持循环播放、自动播放
- 悬浮音乐控制按钮，可随时播放/暂停

### 📱 社交展示（11+ 平台）
微信、QQ、Telegram、抖音、B站、小红书、微博、GitHub、邮箱、手机号、自定义链接……点击弹出详情，一键复制/跳转

### 💰 打赏功能
- 支持微信收款码 / 支付宝收款码
- 打赏文案可自定义
- 点击「打赏」弹出二维码，扫码即付

### 📊 数据统计
- 每个用户独立统计：总访问量（PV）、总点击量
- 每个链接独立统计点击次数
- 管理员可查看全局统计数据

### 🛡️ 用户与权限体系
- **多用户独立管理**：每个用户拥有独立后台，管理自己的链接、装扮、社交信息
- **管理员后台**：用户管理（禁用/启用/删除）、举报管理、操作日志、系统设置
- **用户注册**：支持开放注册 / 关闭注册，支持邮箱验证（SMTP）
- **模拟登录**：管理员可模拟登录任意用户账号，协助排查问题

### 🧩 其他实用功能
- **公告系统**：管理员可发布全站公告，用户主页也可设置个人公告
- **举报系统**：访客可举报违规页面，管理员审核处理
- **密码保护页**：支持对单个链接设置独立访问密码
- **多数据库支持**：MySQL / SQLite 均可

---

## 🖼️ 界面截图

> 演示站内置了完整的示例内容（示例视频、示例音乐、收款码、头像、背景图），  
> **解压部署后即可看到完整效果，无需额外配置！**

👉 **[点击访问演示站 https://www.leaffox.cn](https://www.leaffox.cn)**

---

## 🚀 快速部署

### 环境要求

| 项目 | 要求 |
|------|------|
| **PHP** | 7.4+（推荐 8.0+） |
| **数据库** | MySQL 5.6+ **或** SQLite（免配置） |
| **PHP 扩展** | `PDO`、`pdo_mysql`（MySQL 必需）或 `pdo_sqlite`（SQLite 必需）、`gd`（图片处理）、`mbstring` |
| **Web 服务器** | Apache（推荐，支持 `.htaccess`）或 Nginx |

### 方式一：Apache（推荐）

1. **下载源码** 并解压到网站根目录（如 `/var/www/html/`）
2. **设置目录权限**
   ```bash
   chmod -R 755 /var/www/html/
   chmod -R 777 /var/www/html/uploads/
   ```
3. **启用 Apache mod_rewrite**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
4. **在浏览器访问** `http://你的域名/install.php`
5. **按照安装向导操作**
   - 选择数据库类型（MySQL / SQLite）
   - 填写数据库连接信息
   - 设置管理员账号密码
   - 点击「立即安装」
6. **安装完成后务必删除安装文件**（安全起见）
   ```bash
   rm -f /var/www/html/install.php
   ```
7. **访问管理后台**
   - 用户端：`http://你的域名/`
   - 管理后台：`http://你的域名/admin/`
   - 默认管理员账号：`admin` / 密码：`admin123`（安装时设置的密码）

### 方式二：Nginx

**Nginx 配置文件示例**（将 `server_name` 和 `root` 替换为你自己的域名和路径）：

```nginx
server {
    listen 80;
    server_name 你的域名;
    root /var/www/html;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

> ⚠️ **注意**：使用 Nginx 时，请确保 `.user.ini` 文件中已设置 `open_basedir`，否则可能出现访问限制。

### 安装步骤（通用）

1. 将源码上传到服务器网站根目录
2. 设置 `uploads/` 目录为可写权限（777）
3. 访问 `http://你的域名/install.php`
4. 按向导完成安装
5. **删除 `install.php`**
6. 访问 `http://你的域名/admin/` 进入管理后台

---

## 📁 目录结构

```
/
├── index.php              # 前台入口（自动路由到用户主页）
├── config.php             # 核心配置（数据库 · 公共函数 · 常量）
├── install.php            # 安装向导（安装后务必删除！）
├── install.sql            # MySQL 数据库结构
├── install_sqlite.sql     # SQLite 数据库结构
├── register.php           # 用户注册入口
├── .htaccess              # Apache 伪静态规则
├── .user.ini              # PHP 配置（上传限制等）
│
├── admin/                 # 管理员后台
│   ├── index.php          # 管理员登录
│   ├── dashboard.php      # 控制台总览
│   ├── users.php          # 用户管理（增删改查 · 禁用/启用）
│   ├── links.php          # 所有用户链接管理
│   ├── settings.php       # 系统设置（注册开关 · SMTP · 公告等）
│   ├── reports.php        # 举报管理
│   ├── logs.php           # 操作日志
│   ├── impersonate.php    # 模拟登录（管理员可进入任意用户后台）
│   └── logout.php         # 退出登录
│
├── user/                  # 用户后台
│   ├── index.php          # 用户登录/注册
│   ├── dashboard.php      # 用户控制台（数据概览）
│   ├── settings.php       # 主页设置（装扮 · 社交 · 音乐 · 打赏等）
│   ├── links.php          # 链接管理（增删改 · 排序 · 密码保护）
│   └── logout.php         # 退出登录
│
├── page/                  # 用户主页展示（前端模板）
│   ├── assets/            # 主页静态资源
│   └── index.php          # 个人主页渲染引擎
│
├── api/                   # API 接口
│   ├── record.php         # 访问/点击统计记录
│   └── upload.php         # 文件上传接口
│
├── uploads/               # 用户上传文件（头像 · 封面 · 收款码 · 背景图）
│
└── assets/                # 系统公共静态资源
    ├── css/
    ├── js/
    └── img/               # 系统图片资源（箭头图标等）
```

---

## ⚙️ 技术架构

| 层级 | 技术 |
|------|------|
| **前端** | 原生 HTML5 + CSS3 + JavaScript（零依赖，轻量快速） |
| **后端** | PHP 7.4+（纯原生，无框架，性能优异） |
| **数据库** | MySQL / SQLite（PDO 抽象层，切换无感） |
| **部署** | 解压即用，无需 Composer / npm / Node.js |

### 设计亮点
- **纯原生**：不使用任何重量级前端框架或后端框架，开箱即用
- **低资源占用**：SQLite 模式下甚至无需数据库服务，单文件即可运行
- **响应式设计**：完美适配手机、平板、电脑各种屏幕尺寸
- **CSS 动画**：精致的入场动画和过渡效果，用户体验流畅

---

## ❓ 常见问题

**Q：安装后访问页面空白/报错怎么办？**  
A：检查 PHP 是否安装了所需扩展（PDO、pdo_mysql/pdo_sqlite、gd、mbstring），检查 `uploads/` 目录权限是否为 777。

**Q：如何开启邮箱验证？**  
A：在管理后台 → 系统设置中配置 SMTP 信息，开启「邮箱验证」开关即可。

**Q：如何关闭用户注册？**  
A：管理后台 → 系统设置 → 关闭「允许注册」开关。

**Q：忘记管理员密码怎么办？**  
A：使用 SQLite 时删除 `data/` 目录下的数据库文件重新安装；使用 MySQL 时可通过 phpMyAdmin 修改 `admin` 用户的密码字段（MD5 加密）。

**Q：如何修改系统名称和 Logo？**  
A：管理后台 → 系统设置中可以修改站点名称、Logo、Favicon 等。

---

## 📄 开源许可

本项目仅供学习和个人使用。

---

<p align="center">
  <a href="https://www.leaffox.cn" target="_blank">🌐 在线演示站</a> &nbsp;|&nbsp;
  <a href="https://qun.qq.com/universal-share/share?ac=1&authKey=knpzQ1X9BQwHvtKzcMgAcDt6hcXiwTgyCyAgy5Zbw0PY%2BATsOGar%2FC79hYfbAgDi&busi_data=eyJncm91cENvZGUiOiI2MjkwNTczOTAiLCJ0b2tlbiI6IkFRTFhwQUFqU0JndmVmQWJ0WXlHTk5ZSkhsRDduQUNaOU5XSnhHUTd5bzVZdmFvY2RkMk85dnlySW5IL0t5MzgiLCJ1aW4iOiIzNDEzMTk0MTUxIn0%3D&data=pa96s9SACrKhcnl4TAm8Xa_94sRUn7UaFz1--QaJ9KxG2Tx_ZBMnZSGOZjtUvwR70_PnfS8YJs1VSTlBmHMTFQ&svctype=4&tempid=h5_group_info" target="_blank">💬 加入官方 Q 群（629057390）</a>
</p>
