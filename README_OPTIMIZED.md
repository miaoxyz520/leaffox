# Leaffox v2.6 优化版 🚀

## 优化内容总览

### 🔒 安全增强
| 优化项 | 说明 |
|--------|------|
| ✅ **CSRF防护** | 所有表单自动注入 CSRF Token，防止跨站请求伪造 |
| ✅ **Session加固** | HTTPOnly + Secure + SameSite=Lax Cookie；登录后自动重新生成Session ID |
| ✅ **会话劫持检测** | 检查 IP 和 User-Agent 变化 |
| ✅ **安全响应Header** | X-Frame-Options, X-Content-Type-Options, Content-Security-Policy |
| ✅ **文件上传安全** | 使用 `finfo` 检测真实 MIME 类型，避免伪造文件类型绕过 |
| ✅ **速率限制** | 登录/注册/发验证码 均有频率限制（10次/5分钟） |
| ✅ **账号锁定** | 连续5次密码错误自动锁定15分钟 |
| ✅ **密码强度** | 最低8位，需含大小写字母+数字（可在 config.php 调整） |
| ✅ **Remember Me** | 可选"记住登录状态30天" |
| ✅ **.htaccess防护** | 禁止访问 includes/cache/data 目录，禁止目录列表 |

### ⚡ 性能优化
| 优化项 | 说明 |
|--------|------|
| ✅ **设置缓存** | `getSettings()` 使用文件缓存（1分钟），减少数据库查询 |
| ✅ **数据库索引** | 所有表关键字段已添加索引 |
| ✅ **静态资源缓存** | .htaccess 配置了图片1年/CSS 1月/JS 1月缓存 |
| ✅ **Gzip压缩** | 启用 mod_deflate 压缩传输 |
| ✅ **模块化拆分** | config.php 从 700+ 行拆分为 3 个模块文件 |

### 🧹 代码质量
| 优化项 | 说明 |
|--------|------|
| ✅ **模块化架构** | `includes/init.php` 启动初始化、`includes/functions.php` 公共函数、`includes/security.php` 安全模块 |
| ✅ **统一错误日志** | 使用 `error_log()` 记录错误，不再静默吞异常 |
| ✅ **统一验证函数** | `validateUsername()`, `validateEmail()`, `validateSuffix()` 等 |
| ✅ **严格输出转义** | 所有输出统一通过 `h()` 函数转义 |

### ✨ 用户体验
| 优化项 | 说明 |
|--------|------|
| ✅ **Remember Me** | 登录页增加"记住登录状态30天"复选框 |
| ✅ **SMTP测试** | 后台设置页增加"测试SMTP"按钮 |
| ✅ **邮件模板预览** | 后台邮件模板编辑区增加预览按钮 |
| ✅ **登录提示优化** | 密码错误时提示剩余尝试次数 |
| ✅ **游客模式升级** | 游客登录仪表盘顶部提示设置账号密码 |

---

## 快速开始

1. 上传所有文件到网站根目录
2. 访问 `install.php` 运行安装向导
3. 后台 `/admin/` 登录（默认账号 admin / admin888）
4. 进入「全站设置」开关注册/游客模式，选择模板风格

## 文件结构

```
├── config.php              # 主配置（入口）
├── includes/
│   ├── init.php            # 初始化（Session → DB → 安全Header → 自动修复）
│   ├── functions.php       # 公共函数库
│   └── security.php        # 安全模块（CSRF · 速率限制 · Session加固）
├── admin/                  # 管理后台
├── user/                   # 用户中心
├── api/                    # API接口
├── templates/              # 模板文件
│   ├── login/              # 登录/注册页面模板（5套）
│   ├── landing/            # 着陆页模板（10套）
│   └── user/               # 用户主页模板（18套）
└── cache/                  # 设置缓存目录（自动创建）
```
