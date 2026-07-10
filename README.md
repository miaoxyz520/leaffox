# 🍃 Leaffox 多用户主页系统

> **一款轻量级、高颜值、多用户个人主页 / 链接聚合系统**  
> 支持：链接卡片 · 图文展示 · 视频播放 · 背景音乐 · 社交集成 · 数据统计 · 多用户独立管理 · 互动点评

<p align="center">
  <a href="https://www.leaffox.cn" target="_blank"><strong>🌐 在线演示站</strong></a> &nbsp;·&nbsp;
  <a href="https://github.com/miaoxyz520/leaffox" target="_blank"><strong>📦 GitHub 开源地址</strong></a> &nbsp;·&nbsp;
  <a href="https://qun.qq.com/universal-share/share?ac=1&authKey=knpzQ1X9BQwHvtKzcMgAcDt6hcXiwTgyCyAgy5Zbw0PY%2BATsOGar%2FC79hYfbAgDi&busi_data=eyJncm91cENvZGUiOiI2MjkwNTczOTAiLCJ0b2tlbiI6IkFRTFhwQUFqU0JndmVmQWJ0WXlHTk5ZSkhsRDduQUNaOU5XSnhHUTd5bzVZdmFvY2RkMk85dnlySW5IL0t5MzgiLCJ1aW4iOiIzNDEzMTk0MTUxIn0%3D&data=pa96s9SACrKhcnl4TAm8Xa_94sRUn7UaFz1--QaJ9KxG2Tx_ZBMnZSGOZjtUvwR70_PnfS8YJs1VSTlBmHMTFQ&svctype=4&tempid=h5_group_info" target="_blank"><strong>💬 官方 Q 群：629057390</strong></a>
</p>

<p align="center">
  <a href="https://www.leaffox.cn">
    <img src="https://img.shields.io/badge/%E6%BC%94%E7%A4%BA%E7%AB%99-leaffox.cn-6366f1?style=flat-square" alt="演示站">
  </a>
  <a href="https://github.com/miaoxyz520/leaffox">
    <img src="https://img.shields.io/badge/GitHub-miaoxyz520%2Fleaffox-181717?style=flat-square&logo=github" alt="GitHub">
  </a>
  <a href="#-环境要求">
    <img src="https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php" alt="PHP">
  </a>
  <a href="#-环境要求">
    <img src="https://img.shields.io/badge/DB-MySQL%20%7C%20SQLite-4479A1?style=flat-square&logo=mysql" alt="DB">
  </a>
</p>

---

## 📖 目录

- [📖 目录](#-目录)
- [✨ 功能亮点](#-功能亮点)
- [🖼️ 界面截图](#️-界面截图)
- [🚀 快速部署](#-快速部署)
  - [环境要求](#环境要求)
  - [Apache 部署（推荐）](#方式一-apache推荐)
  - [Nginx 部署](#方式二-nginx)
  - [Docker 部署](#方式三-docker)
  - [安装步骤（通用）](#安装步骤通用)
- [📖 系统介绍](#-系统介绍)
- [📁 目录结构](#-目录结构)
- [⚙️ 技术架构](#️-技术架构)
- [🎯 使用教程](#-使用教程)
  - [一、管理员操作](#一管理员操作)
  - [二、用户操作](#二用户操作)
- [❓ 常见问题（FAQ）](#-常见问题faq)
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
| **🎬 视频播放** | 支持 B 站内嵌播放器 / 直链视频播放 |

- **密码保护**：单个链接可设置访问密码，私密内容更安全
- **图标支持**：每个链接可自定义 Emoji/图标

### 🎵 背景音乐
- 自定义音乐文件链接，支持循环播放、自动播放
- 悬浮音乐控制按钮，可随时播放/暂停

### 📱 社交展示（11+ 平台）
微信、QQ、Telegram、抖音、B站、小红书、微博、GitHub、邮箱、手机号、自定义链接……  
点击弹出详情，一键复制/跳转

### 💰 打赏功能
- 支持微信收款码 / 支付宝收款码
- 打赏文案可自定义
- 点击「打赏」弹出二维码，扫码即付

### 💬 互动系统（新）
- **点赞**：给喜欢的主页点个赞，支持双向查看（谁赞了我 / 我赞了谁）
- **评论**：留言互动，支持审核机制，显示评论内容、时间、状态
- **收藏**：收藏感兴趣的主页，支持双向查看（谁收藏了我 / 我收藏了谁）
- **完整记录**：每条互动记录包含用户头像、昵称、主页链接、操作时间

### 📊 数据统计
- 每个用户独立统计：总访问量（PV）、每个链接独立点击统计
- 管理员可查看全局统计数据、活跃度分析

### 🛡️ 用户与权限体系
- **多用户独立管理**：每个用户拥有独立后台，管理自己的链接、装扮、社交信息
- **管理员后台**：用户管理（禁用/启用/删除）、举报管理、操作日志、系统设置
- **用户注册**：支持开放注册 / 关闭注册，支持邮箱验证（SMTP）
- **模拟登录**：管理员可模拟登录任意用户账号，协助排查问题

### 🧩 其他实用功能
- **公告系统**：管理员可发布全站公告，用户主页也可设置个人公告
- **举报系统**：访客可举报违规页面，管理员审核处理
- **密码保护页**：支持对单个链接 / 整个主页设置独立访问密码
- **微信/QQ/抖音内置浏览器提示**：自动检测并提示「在浏览器打开」
- **多数据库支持**：MySQL / SQLite 均可，切换无感

---

## 🖼️ 界面截图

> 演示站内置了完整的示例内容（示例视频、示例音乐、收款码、头像、背景图），  
> **解压部署后即可看到完整效果，无需额外配置！**

👉 **[点击访问演示站 https://www.leaffox.cn](https://www.leaffox.cn)**  
👉 **[GitHub 仓库 https://github.com/miaoxyz520/leaffox](https://github.com/miaoxyz520/leaffox)**

---

## 🚀 快速部署

### 环境要求

| 项目 | 要求 | 说明 |
|------|------|------|
| **PHP** | 7.4+（推荐 8.0+） | 不支持 PHP 5.x，推荐 PHP 8.0~8.4 |
| **数据库** | MySQL 5.6+ **或** SQLite | SQLite 无需额外数据库服务，开箱即用 |
| **必要扩展** | PDO、pdo_mysql（MySQL 必需）或 pdo_sqlite（SQLite 必需） | 用于数据库操作 |
| **推荐扩展** | gd（图片处理）、mbstring（字符串处理） | 缺少可能导致部分功能异常 |
| **Web 服务器** | Apache（推荐，支持 .htaccess）或 Nginx | 详见下方部署方式 |
| **操作系统** | Linux / Windows / macOS 均可 | 只要有 PHP 环境即可运行 |

> 💡 **新手推荐**：使用 **Apache + SQLite** 组合，无需安装配置 MySQL 数据库，上传解压即可运行。

---

### 📥 第一步：下载源码

从以下渠道获取最新版本源码：

| 渠道 | 地址 |
|------|------|
| 🌐 **GitHub Releases** | [https://github.com/miaoxyz520/leaffox/releases](https://github.com/miaoxyz520/leaffox/releases) |
| 🗜️ **直接下载 ZIP** | [https://github.com/miaoxyz520/leaffox/archive/refs/heads/main.zip](https://github.com/miaoxyz520/leaffox/archive/refs/heads/main.zip) |

下载后得到一个压缩包（如 `leaffox.zip` 或 `leaffox.tar.gz`），解压备用。

---

### 📤 第二步：上传到服务器

#### 选项 A：已有服务器 / 云主机（Linux）

```bash
# 1. 通过 SSH 登录服务器
ssh root@你的服务器IP

# 2. 进入网站根目录（以 Apache 默认目录为例）
cd /var/www/html/

# 3. 下载源码（方式一：wget 直接下载）
wget https://github.com/miaoxyz520/leaffox/archive/refs/heads/main.zip
unzip main.zip
mv leaffox-main/* .
mv leaffox-main/.* . 2>/dev/null  # 移动隐藏文件
rm -rf leaffox-main main.zip

# 或方式二：本地上传后解压
# 先用 FTP/SCP 把 zip 上传到服务器，然后：
# unzip leaffox.zip -d /var/www/html/
```

#### 选项 B：Windows 本地环境（XAMPP / PHPStudy）

1. 安装 [XAMPP](https://www.apachefriends.org/) 或 PHPStudy
2. 将解压后的源码文件夹复制到网站目录：
   - XAMPP 默认：`C:\xampp\htdocs\leaffox\`
   - PHPStudy 默认：`C:\phpstudy_pro\WWW\leaffox\`
3. 启动 Apache 服务

> ⚠️ 如果是本地测试，访问地址为 `http://localhost/leaffox/`，后续步骤中的 `http://你的域名/` 请对应替换。

---

### 🔧 第三步：设置目录权限

#### Linux 服务器

```bash
# 进入项目目录
cd /var/www/html/

# 设置目录权限
chmod -R 755 .
chmod -R 777 uploads/

# 如果使用 SQLite，还需要给 data 目录写权限（安装时会自动创建）
chmod -R 777 data/ 2>/dev/null || true
```

#### Windows 服务器

Windows 下通常不需要单独设置权限，但如果遇到上传失败的问题：
- 右键 `uploads` 文件夹 → 属性 → 安全 → 编辑 → 给 `Everyone` 添加「完全控制」权限

---

### 🌐 第四步：配置 Web 服务器

---

#### 方式一：Apache（推荐，最简单）

Apache 是最推荐的部署方式，因为开箱支持 `.htaccess` 伪静态规则。

**如果使用 XAMPP / PHPStudy / 宝塔面板 / 军哥 LNMP：**
- Apache 通常已自动启用 `mod_rewrite`，无需额外配置
- 直接将源码放到网站目录即可

**如果使用纯净的 Apache（如 Ubuntu/Debian）：**

```bash
# 1. 启用 rewrite 模块
sudo a2enmod rewrite

# 2. 重启 Apache
sudo systemctl restart apache2
```

**检查 Apache 是否已启用 mod_rewrite：**

```bash
# 方法一：查看已启用的模块列表
apache2ctl -M | grep rewrite

# 方法二：如果看到输出 "rewrite_module (shared)" 表示已启用
```

**Apache 虚拟主机配置参考**（`/etc/apache2/sites-available/leaffox.conf`）：

```apache
<VirtualHost *:80>
    ServerName 你的域名
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

启用站点：
```bash
sudo a2ensite leaffox.conf
sudo systemctl reload apache2
```

---

#### 方式二：Nginx

Nginx 需要手动配置伪静态规则，不支持 `.htaccess`。

**完整 Nginx 虚拟主机配置**（`/etc/nginx/sites-available/leaffox`）：

```nginx
server {
    listen 80;
    server_name 你的域名;
    root /var/www/html;
    index index.php;

    # 字符编码
    charset utf-8;

    # 伪静态 - 将请求路由到 index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP 解析
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        # 如果不知道 PHP-FPM 的 sock 位置，试试：
        # fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态资源缓存（提升加载速度）
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问敏感文件
    location ~ /\. {
        deny all;
    }
    location ~ (install\.php|\.sql|\.md)$ {
        deny all;
    }
}
```

启用站点：
```bash
# 创建软链接
sudo ln -s /etc/nginx/sites-available/leaffox /etc/nginx/sites-enabled/

# 测试配置是否正确
sudo nginx -t

# 重新加载 Nginx
sudo systemctl reload nginx
```

**常见问题：Nginx 下访问链接出现 404**
- 确认 `try_files` 规则已正确配置
- 确认 `root` 路径指向项目根目录
- 确认 PHP-FPM 服务正在运行：`systemctl status php8.2-fpm`

---

#### 方式三：宝塔面板（新手推荐）

如果你使用宝塔面板，安装更简单：

1. **创建站点**：宝塔面板 → 网站 → 添加站点 → 输入域名 → 创建
2. **上传源码**：进入站点根目录，将源码上传并解压
3. **设置伪静态**：
   - 站点设置 → 伪静态 → 选择 `laravel5` 或 `thinkphp`（效果相同）
   - 或者选择「自定义」并粘贴 Nginx 伪静态规则
4. **设置权限**：站点设置 → 权限 → 设置为 755，所有者 www
5. **访问安装**：浏览器打开域名安装即可

---

#### 方式四：Docker

适合不想在宿主机安装 PHP 环境的用户：

```bash
# 1. 创建数据目录
mkdir -p /data/leaffox

# 2. 下载源码到数据目录
cd /data/leaffox
wget https://github.com/miaoxyz520/leaffox/archive/refs/heads/main.zip
unzip main.zip
mv leaffox-main/* .
mv leaffox-main/.* . 2>/dev/null
rm -rf leaffox-main main.zip

# 3. 启动 Docker 容器
docker run -d \
  --name leaffox \
  -p 80:80 \
  -v /data/leaffox:/var/www/html \
  php:8.2-apache

# 4. 进入容器启用 rewrite
docker exec leaffox a2enmod rewrite

# 5. 重启容器
docker restart leaffox

# 6. 访问 http://localhost/install.php 安装
```

---

### 🧙 第五步：运行安装向导

无论使用哪种服务器，安装流程完全一致：

#### 第 1 步：访问安装页面

在浏览器中打开：**`http://你的域名/install.php`**

> 例如：`http://localhost/leaffox/install.php`（本地测试）  
> 或 `https://www.leaffox.cn/install.php`（正式域名）

如果看到安装向导界面，说明环境配置正确！

> ❌ **如果页面空白或 500 报错**，请参考下方的 [常见问题](#-常见问题faq) 章节排查。

#### 第 2 步：选择数据库

安装向导第一步让你选择数据库类型：

- **SQLite**（推荐新手）✅
  - 无需安装 MySQL，零配置
  - 适合个人或小规模使用（几十个用户以内）
  - 数据文件存放在 `data/` 目录下

- **MySQL**（推荐生产环境）
  - 需要提前创建好数据库
  - 适合大规模使用，并发性能更强
  - 需要填写数据库主机、数据库名、用户名、密码

> 💡 **如何创建 MySQL 数据库？**  
> 如果你有 phpMyAdmin：登录 → 新建数据库 → 输入名称（如 `leaffox`）→ 选择 `utf8mb4_general_ci` → 创建  
> 如果使用命令行：`CREATE DATABASE leaffox CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;`

#### 第 3 步：填写数据库信息

**选择 SQLite 时：**
- 只需确认 `data/` 目录可写即可，无需额外信息
- 点击「下一步」

**选择 MySQL 时：**
| 字段 | 说明 | 示例 |
|------|------|------|
| 数据库主机 | 通常为 `localhost` 或 `127.0.0.1` | `localhost` |
| 数据库端口 | 默认 3306 | `3306` |
| 数据库名 | 提前创建好的数据库名称 | `leaffox` |
| 用户名 | 有权限访问该数据库的用户 | `root` |
| 密码 | 对应用户的密码 | `your_password` |

> ⚠️ **如果提示数据库连接失败**：请检查数据库服务是否已启动，用户名密码是否正确。

#### 第 4 步：设置管理员账号

| 字段 | 说明 | 示例 |
|------|------|------|
| 管理员账号 | 登录管理后台的用户名 | `admin` |
| 管理员密码 | 建议设置 **12 位以上** 复杂密码 | `Leaffox@2024!` |
| 管理员邮箱 | 用于密码找回等（可选） | `admin@example.com` |

> ⚠️ **安全提醒**：请勿使用 `admin` / `admin123` 等弱密码！建议包含大小写字母、数字和特殊符号。

#### 第 5 步：完成安装

点击「立即安装」按钮，系统会自动：
1. ✅ 创建数据库表结构
2. ✅ 写入配置文件 `config.php`
3. ✅ 创建管理员账号
4. ✅ 创建默认数据目录

安装成功后，页面会显示 **「安装成功！」** 提示。

---

### 🧹 第六步：删除安装文件

**这是非常重要的一步！** 不删除 `install.php` 可能导致他人重新安装覆盖你的数据。

```bash
# Linux 服务器
rm -f /var/www/html/install.php

# 或如果使用宝塔面板：直接在文件管理中删除 install.php
```

---

### 🎉 第七步：开始使用

安装完成后，访问以下地址：

| 页面 | 地址 | 说明 |
|------|------|------|
| **🏠 用户主页** | `http://你的域名/` | 查看已注册用户的主页，未注册时显示登录链接 |
| **🔐 管理后台** | `http://你的域名/admin/` | 使用安装时设置的管理员账号登录 |
| **👤 用户注册** | `http://你的域名/user/` | 新用户注册和登录入口 |
| **🔧 用户后台** | `http://你的域名/user/`（登录后） | 用户管理自己的主页 |

---

### 📋 安装检查清单

部署完成后，逐项确认以下内容：

- [ ] `install.php` 已删除
- [ ] `uploads/` 目录可写（777）
- [ ] Apache mod_rewrite 已启用 或 Nginx 伪静态已配置
- [ ] 管理后台可正常登录
- [ ] 可正常注册新用户
- [ ] 用户后台可正常访问
- [ ] 用户主页正常显示（含链接卡片、音乐播放器等）
- [ ] 文件上传功能正常（头像、封面、收款码）
- [ ] 互动功能正常（点赞、评论、收藏）

---

## 📖 系统介绍

### 什么是 Leaffox？

Leaffox 是一款**轻量级、高颜值、多用户个人主页 / 链接聚合系统**。  
它让每个用户都能拥有一个属于自己的个性化主页，展示自己的链接、作品、社交账号、个人信息等。

### 适用场景

| 场景 | 说明 |
|------|------|
| **个人主页** | 替代 Linktree / Bio.link，打造专属个人名片 |
| **创作者名片** | 聚合你的作品、视频、社交平台、打赏入口 |
| **团队/组织介绍** | 多成员各自拥有独立页面，统一平台管理 |
| **作品集展示** | 以图文/视频形式展示个人作品 |
| **付费内容入口** | 设置密码保护链接，私密分发内容 |

### 核心设计理念

- **零门槛**：无需 Composer / npm / Node.js，解压即用
- **原生高性能**：纯原生 PHP 实现，无框架依赖，页面加载极快
- **高颜值**：毛玻璃、新拟态、极简风三种卡片风格，深色的高级感设计
- **全平台适配**：手机、平板、电脑完美响应式布局
- **数据自主**：支持 MySQL 和 SQLite，数据掌握在自己手中

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
│   ├── dashboard.php      # 控制台总览 · 数据统计
│   ├── users.php          # 用户管理（增删改查 · 禁用/启用）
│   ├── links.php          # 所有用户链接管理
│   ├── settings.php       # 系统设置（注册开关 · SMTP · 公告等）
│   ├── reports.php        # 举报管理
│   ├── logs.php           # 操作日志
│   ├── impersonate.php    # 模拟登录
│   └── logout.php         # 退出登录
│
├── user/                  # 用户后台
│   ├── index.php          # 用户登录/注册
│   ├── dashboard.php      # 用户控制台 · 我的评论 · 我的点赞 · 我的收藏
│   ├── settings.php       # 主页设置（装扮 · 社交 · 音乐 · 打赏 · 互动开关等）
│   ├── links.php          # 链接管理（增删改 · 排序 · 密码保护）
│   ├── my_comments.php    # 评论记录（谁评论了我 / 我评论了谁）
│   ├── my_likes.php       # 点赞记录（谁赞了我 / 我赞了谁）
│   ├── my_favorites.php   # 收藏记录（谁收藏了我 / 我收藏了谁）
│   └── logout.php         # 退出登录
│
├── page/                  # 用户主页展示（前端模板）
│   ├── assets/            # 主页静态资源（CSS · JS · 字体）
│   └── index.php          # 个人主页渲染引擎（链接卡片 · 音乐 · 社交 · 互动）
│
├── api/                   # API 接口
│   ├── record.php         # 访问/点击统计记录
│   ├── upload.php         # 文件上传接口
│   ├── interaction_*.php  # 互动接口（评论 · 点赞 · 收藏 · 状态查询）
│   ├── report.php         # 举报提交
│   ├── mail.php           # 邮件发送
│   └── reset_password.php # 密码重置
│
├── uploads/               # 用户上传文件（头像 · 封面 · 收款码 · 背景图 · 音乐）
│
└── assets/                # 系统公共静态资源
    ├── css/               # 样式文件
    ├── js/                # 脚本文件
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
- **低资源占用**：SQLite 模式下无需数据库服务，单文件即可运行
- **响应式设计**：完美适配手机、平板、电脑各种屏幕尺寸
- **CSS 动画**：精致的入场动画和过渡效果，用户体验流畅
- **BEM 命名**：CSS 类名规范清晰，易于二次开发

---

## 🎯 使用教程

### 一、管理员操作

#### 1. 登录管理后台
访问 `http://你的域名/admin/`，使用安装时设置的管理员账号登录

#### 2. 用户管理
- **查看用户**：进入「用户管理」查看所有注册用户
- **添加用户**：点击「添加用户」手动创建账号
- **编辑/禁用/删除**：每个用户可独立操作
- **模拟登录**：点击「模拟登录」直接进入该用户后台，协助排查问题

#### 3. 系统设置
- **站点名称/Logo/Favicon**：自定义品牌信息
- **注册开关**：开启/关闭用户公开注册
- **邮箱验证**：配置 SMTP 后开启
- **全站公告**：显示在所有用户主页顶部
- **打赏说明**：自定义打赏弹窗文案

#### 4. 举报管理
- 查看被举报的主页
- 审核后删除违规内容或禁用用户

### 二、用户操作

#### 1. 注册与登录
- 访问 `http://你的域名/user/` 注册新账号
- 登录后进入用户控制台

#### 2. 个人设置
| 功能 | 说明 |
|------|------|
| **个人资料** | 设置昵称、简介、头像（支持上传）、个性后缀（短链接路由） |
| **背景装扮** | 纯色 / 渐变色 / 背景图，三种主题模式 |
| **卡片样式** | Glass 毛玻璃 / Neumorphism 新拟态 / Minimal 极简 |
| **按钮样式** | 自定义按钮背景色、文字色、边框色、箭头开关 |
| **社交渠道** | 添加微信/QQ/Telegram/B站/抖音/小红书/微博/GitHub/邮箱/手机号等 |
| **音乐** | 添加背景音乐链接，设置循环/自动播放 |
| **打赏** | 上传微信/支付宝收款码，设置打赏文案 |
| **互动开关** | 独立控制点赞 / 评论 / 收藏功能的开启与关闭 |
| **内置浏览器提示** | 微信/QQ/抖音/微博内访问时提示「在浏览器打开」 |

#### 3. 链接管理
- **添加链接**：支持网页链接、纯文字、图片、视频 4 种类型
- **排序**：拖拽排序 / 手动置顶
- **密码保护**：单个链接可设置独立访问密码
- **点击统计**：每个链接独立记录点击次数

#### 4. 互动记录
在用户后台「我的评论」「我的点赞」「我的收藏」中：
- **谁评论/点赞/收藏了我** — 查看访客互动记录
- **我评论/点赞/收藏了谁** — 查看你主动产生的互动记录
- 每条记录显示：用户头像、昵称、主页链接、操作时间、评论内容

#### 5. 数据统计
- 查看自己的主页总访问量、链接总点击量

---

## ❓ 常见问题（FAQ）

### 🔧 安装相关

**Q：安装后访问页面空白 / 500 报错怎么办？**  
A：检查以下项目：
1. PHP 版本是否满足 7.4+？终端运行 `php -v` 查看
2. 是否安装了必要扩展？`php -m | grep -E "pdo|mbstring|gd"`
3. `uploads/` 目录是否可写？`ls -la uploads/` 检查权限（应为 777）
4. 查看 PHP 错误日志：`tail -f /var/log/apache2/error.log` 或 Nginx 错误日志
5. SQLite 模式下检查 `data/` 目录是否可写

**Q：安装时数据库连接失败？**  
A：使用 MySQL 时请确认：
- 数据库名、用户名、密码是否正确
- MySQL 服务是否已启动
- 是否允许远程连接（如果数据库和 Web 不在同一台机器）
- 建议先在 phpMyAdmin 中创建好空数据库再填写

**Q：安装完成后务必删除 install.php 吗？**  
A：**强烈建议删除**。install.php 如果被恶意访问，可能导致数据库被重置。执行：
```bash
rm -f /var/www/html/install.php
```

**Q：如何从 MySQL 切换到 SQLite？**  
A：目前不支持直接切换。如需切换，请重新运行 `install.php` 选择 SQLite 重新安装。

### 🌐 部署相关

**Q：如何绑定域名和开启 HTTPS？**  
A：
1. 在域名 DNS 解析中指向服务器 IP
2. 使用 Nginx/Apache 配置 `server_name`
3. 推荐使用 [Certbot](https://certbot.eff.org/) 免费申请 Let's Encrypt SSL 证书开启 HTTPS

**Q：Nginx 下访问链接出现 404？**  
A：Nginx 需要正确配置伪静态规则。参考上方的 [Nginx 配置示例](#方式二-nginx)，确保 `try_files` 规则正确。

**Q：后台页面样式错乱 / CSS 加载不出来？**  
A：检查浏览器控制台是否有 404 错误。通常是因为：
- 伪静态规则未正确配置
- 站点根路径设置不正确
- 文件权限不足

### 👤 用户相关

**Q：用户忘记密码怎么办？**  
A：
- **管理员操作**：在管理后台 → 用户管理 → 点击「编辑」→ 重置密码
- **用户自助**：如果开启了邮箱验证，可在登录页点击「忘记密码」
- **数据库操作**：SQLite 模式直接删除 `data/` 目录下的数据库文件重新安装；MySQL 模式通过 phpMyAdmin 修改对应用户的 password 字段

**Q：如何关闭用户注册？**  
A：管理后台 → 系统设置 → 将「允许注册」开关关闭即可。关闭后注册页面自动跳转到登录页。

**Q：如何修改用户的个性后缀？**  
A：用户登录后在个人设置中自行修改，或管理员在用户管理中直接编辑。

### 🎨 功能相关

**Q：如何添加背景音乐？**  
A：在用户后台 → 主页设置 → 音乐设置中填写音乐文件直链 URL（支持 mp3/wav/ogg 格式），开启循环/自动播放开关即可。

**Q：如何添加 B 站视频？**  
A：在链接管理中添加「视频」类型链接，填写 B 站视频的 BV 号或嵌入链接即可自动加载播放器。

**Q：如何设置链接密码？**  
A：在链接管理中编辑链接，开启「密码保护」开关，设置密码。访问者点击该链接时需要输入密码才能跳转。

**Q：打赏收款码怎么设置？**  
A：用户后台 → 主页设置 → 打赏设置中上传微信和支付宝收款码图片，可自定义打赏按钮文案。

**Q：互动功能如何开启/关闭？**  
A：用户后台 → 主页设置 → 互动设置中可独立控制点赞 / 评论 / 收藏功能的开关。

**Q：为什么我设置了头像但不显示？**  
A：请确保：
1. 头像图片上传成功（建议 200x200 以上，jpg/png 格式）
2. 图片链接可正常访问（浏览器直接打开头像链接测试）
3. 可能是 CDN 缓存，尝试 Ctrl+F5 强制刷新

### ⚡ 性能相关

**Q：系统运行慢怎么办？**  
A：
1. 使用 SQLite 模式（无需网络连接数据库）
2. 启用 PHP OPcache 加速（php.ini 中开启 `opcache.enable=1`）
3. 使用 CDN 加速静态资源
4. 压缩图片大小，避免大图上传

**Q：SQLite 和 MySQL 哪个好？**  
A：
- **SQLite**：适合小规模使用（数十个用户），无需安装维护数据库，部署简单
- **MySQL**：适合大规模使用（数百个以上用户），并发性能更强，支持在线备份

**Q：PHP 8.x 兼容吗？**  
A：完全兼容 PHP 7.4 ~ 8.4，推荐使用 PHP 8.0+ 以获得更好性能。

### 🐛 错误处理

**Q：出现 "SQLSTATE[HY000] [14] unable to open database file"**  
A：SQLite 模式下 `data/` 目录没有写入权限，执行：
```bash
chmod -R 777 /var/www/html/data/
```

**Q：上传图片提示 "文件上传失败"**  
A：检查：
1. `uploads/` 目录权限（应为 777）
2. PHP 上传大小限制（在 `.user.ini` 或 `php.ini` 中调整 `upload_max_filesize` 和 `post_max_size`）
3. 文件格式是否在允许列表中

**Q：登录后提示 "Session 过期" 或自动退出**  
A：
1. 检查 PHP Session 存储目录权限
2. 确认服务器时间正确
3. 检查 `php.ini` 中的 `session.gc_maxlifetime` 设置

**Q：页面显示 "open_basedir restriction in effect"**  
A：在 `.user.ini` 或 `php.ini` 中配置 `open_basedir`，将项目目录加入允许列表：
```ini
open_basedir = /var/www/html/:/tmp/
```

### 💬 其他

**Q：如何自定义页面标题和描述？**  
A：管理后台 → 系统设置中可修改站点名称、描述、关键词等 SEO 信息。

**Q：系统有 API 接口吗？**  
A：目前内置了统计记录、文件上传、互动系统等 API，暂未开放外部调用接口。

**Q：如何更新到最新版本？**  
A：
1. 备份数据库和 `uploads/` 目录
2. 下载最新源码覆盖除 `config.php` 外的所有文件
3. 如数据库结构有变化，重新运行 `install.php`（或手动执行新的 SQL 迁移）
4. 若使用了 SQLite，注意备份 `data/` 目录

**Q：我想二次开发，从哪里开始？**  
A：项目结构清晰，建议从以下文件入手：
- `page/index.php` — 前端主页渲染逻辑
- `user/dashboard.php` — 用户后台控制台
- `admin/` — 管理员后台各模块
- `config.php` — 核心数据库和工具函数

---

## 📄 开源许可

本项目仅供学习和个人使用。  
请勿用于违法用途，保留原作者版权信息。

---

<p align="center">
  <a href="https://www.leaffox.cn" target="_blank">🌐 在线演示站</a> &nbsp;·&nbsp;
  <a href="https://github.com/miaoxyz520/leaffox" target="_blank">📦 GitHub 开源地址</a> &nbsp;·&nbsp;
  <a href="https://qun.qq.com/universal-share/share?ac=1&authKey=knpzQ1X9BQwHvtKzcMgAcDt6hcXiwTgyCyAgy5Zbw0PY%2BATsOGar%2FC79hYfbAgDi&busi_data=eyJncm91cENvZGUiOiI2MjkwNTczOTAiLCJ0b2tlbiI6IkFRTFhwQUFqU0JndmVmQWJ0WXlHTk5ZSkhsRDduQUNaOU5XSnhHUTd5bzVZdmFvY2RkMk85dnlySW5IL0t5MzgiLCJ1aW4iOiIzNDEzMTk0MTUxIn0%3D&data=pa96s9SACrKhcnl4TAm8Xa_94sRUn7UaFz1--QaJ9KxG2Tx_ZBMnZSGOZjtUvwR70_PnfS8YJs1VSTlBmHMTFQ&svctype=4&tempid=h5_group_info" target="_blank">💬 官方 Q 群：629057390</a>
</p>
