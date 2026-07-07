# 🌐 Leaffox 个人社交主页系统 v1.0

> 一款轻量级、高颜值的个人主页/链接聚合系统  
> 支持：链接卡片 · 图文展示 · 视频播放 · 背景音乐 · 社交集成 · 数据统计

---

## 🚀 快速部署

### 方式一：Apache（推荐）

1. **解压** 所有文件到网站根目录（如 `/var/www/html/`）
2. **访问** `http://你的域名/install.php` 开始安装
3. 选择 MySQL 或 SQLite，填写数据库信息
4. 安装完成 → **删除 `install.php`**（安全起见）
5. 默认管理员账号：`admin` / 密码：`admin123`

> 若需要启用 URL 重写（隐藏 index.php），请确保 Apache 已开启 `mod_rewrite`。

### 方式二：Nginx

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
        fastcgi_pass unix:/var/run/php/php8.x-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 📁 目录结构

```
/
├── index.php          # 前台入口（自动路由到用户主页）
├── config.php         # 核心配置（数据库 · 公共函数）
├── install.php        # 安装向导（安装后请删除）
├── install.sql        # MySQL 数据库结构
├── install_sqlite.sql # SQLite 数据库结构
├── register.php       # 用户注册
├── .htaccess          # Apache 伪静态规则
├── admin/             # 管理员后台
│   ├── index.php      # 管理员登录
│   ├── dashboard.php  # 控制台
│   ├── users.php      # 用户管理
│   ├── links.php      # 链接管理
│   ├── settings.php   # 系统设置
│   ├── reports.php    # 举报管理
│   ├── logs.php       # 操作日志
│   ├── impersonate.php# 模拟登录
│   └── logout.php     # 退出登录
├── user/              # 用户后台
│   ├── index.php      # 用户登录/注册
│   ├── dashboard.php  # 用户控制台
│   ├── settings.php   # 主页设置
│   ├── links.php      # 链接管理
│   └── logout.php     # 退出登录
├── page/              # 用户主页展示
├── api/               # 接口
├── uploads/           # 上传文件（头像、封面、收款码等）
└── assets/            # 静态资源
    └── img/           # 系统图片资源
```

---

## ✨ 功能特性

| 功能 | 说明 |
|------|------|
| 🎨 **自定义装扮** | 背景颜色/渐变/图片、卡片样式（Glass/Neumorphism/Minimal）、深色/浅色/自动主题 |
| 🔗 **链接卡片** | 支持网页链接、纯文字、图片展示、弹窗大图、视频播放，可设置密码保护 |
| 🎵 **背景音乐** | 自定义音乐链接，支持循环/自动播放 |
| 📱 **社交集成** | 微信、QQ、Telegram、抖音、B站、小红书、微博、GitHub、邮箱等 |
| 💰 **打赏功能** | 上传收款码，一键打赏 |
| 📊 **数据统计** | 访问量/点击量统计 |
| 📝 **全站公告** | 管理员可发布公告，用户主页可设置公告 |
| 🛡️ **举报系统** | 用户可举报违规内容 |
| 📧 **邮箱验证** | 支持 SMTP 邮箱验证登录 |

---

## 🔧 环境要求

- **PHP** 7.4+（推荐 8.0+）
- **MySQL** 5.6+ **或** SQLite（无需数据库时使用）
- **扩展需求**：`PDO`、`mysqli`（MySQL）、`gd`（图片处理）、`mbstring`
- **Web 服务器**：Apache（推荐）或 Nginx

---

## 📸 示例截图

> 系统自带演示内容：示例视频、示例音乐、收款码、头像、背景图  
> 解压即可看到效果，无需额外配置！

---

## ⚙️ 技术架构

- **前端**：原生 HTML5 + CSS3 + JavaScript
- **后端**：PHP 7.4+（无框架）
- **数据库**：MySQL / SQLite（PDO）
- **部署**：解压即用，无需 Composer

---

## 📄 开源许可

本项目仅供学习和个人使用。
