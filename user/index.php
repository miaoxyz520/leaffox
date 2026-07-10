<?php
/**
 * 普通用户 - 登录/注册页（支持邮箱注册、游客模式、模板切换）
 * v2.6 优化：CSRF防护 · Remember Me · 速率限制 · 统一验证
 */
require_once __DIR__ . '/../config.php';

if (!$db) { header("Location: ../install.php"); exit; }

if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_login'])) {
    redirect('./dashboard.php');
}

$settings = getSettings($db);
$error = '';
$regSuccess = '';
$showEmailField = ($settings['reg_email_verify'] ?? 0) || ($settings['user_email_login'] ?? 1);
$guestMode = !empty($settings['guest_mode']);
$loginTemplate = $settings['login_template'] ?? 'default';
$registerTemplate = $settings['register_template'] ?? 'default';

// 游客登录处理（AJAX）- 不需要CSRF（无状态创建）
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'guest_login') {
    // 增强安全：非AJAX请求直接重定向（防止JSON意外暴露）
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if (!$isAjax) {
        redirect('./dashboard.php');
        exit;
    }
    
    header('Content-Type: application/json; charset=utf-8');
    if (!$guestMode) {
        echo json_encode(['success' => false, 'message' => '游客模式已关闭']);
        exit;
    }
    $rateCheck = checkRateLimit('guest');
    if (!$rateCheck['allowed']) {
        echo json_encode(['success' => false, 'message' => $rateCheck['message']]);
        exit;
    }
    
    $guestId = 'guest_' . substr(uniqid(), -8) . mt_rand(100, 999);
    $guestUsername = '游客' . mt_rand(10000, 99999);
    $guestPassword = bin2hex(random_bytes(16));
    $hash = password_hash($guestPassword, PASSWORD_DEFAULT);
    $guestSuffix = 'g_' . substr(md5(uniqid()), 0, 10);
    
    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, nickname, suffix, is_guest, is_active, guest_expires_at) VALUES (?, ?, ?, ?, 1, 1, DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
        $stmt->execute([$guestUsername, $hash, $guestUsername, $guestSuffix]);
        $newId = (int)$db->lastInsertId();
        
        session_regenerate_id(true);
        $_SESSION['user_id']    = $newId;
        $_SESSION['user_name']  = $guestUsername;
        $_SESSION['user_login'] = true;
        $_SESSION['is_guest']   = true;
        regenerateSession();
        
        echo json_encode(['success' => true, 'message' => '游客登录成功', 'redirect' => './dashboard.php?guest=1']);
    } catch (Exception $e) {
        error_log("[Guest Login Error] " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '游客账号创建失败，请重试']);
    }
    exit;
}

// 登录/注册处理（需要CSRF验证）
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'] ?? '';
    
    // 发送注册验证码 - AJAX，需要CSRF
    if ($action === 'send_register_code') {
        header('Content-Type: application/json; charset=utf-8');
        requireCsrfToken();
        
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '请输入有效的邮箱地址']);
            exit;
        }
        $rateCheck = checkRateLimit('send_code');
        if (!$rateCheck['allowed']) {
            echo json_encode(['success' => false, 'message' => $rateCheck['message']]);
            exit;
        }
        
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => '该邮箱已被注册']);
            exit;
        }
        if (empty($settings['smtp_host']) || empty($settings['smtp_user']) || empty($settings['smtp_pass'])) {
            echo json_encode(['success' => false, 'message' => '管理员未配置邮件发送服务']);
            exit;
        }
        if (!empty($_SESSION['reg_email_expiry']) && $_SESSION['reg_email_expiry'] > time() - 55) {
            echo json_encode(['success' => false, 'message' => '发送过于频繁，请稍后再试']);
            exit;
        }
        
        $code = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = time() + 600;
        $_SESSION['reg_email_code'] = $code;
        $_SESSION['reg_email_addr'] = $email;
        $_SESSION['reg_email_expiry'] = $expires;
        
        require_once __DIR__ . '/../api/mail.php';
        $siteName = h($settings['site_name'] ?? 'Leaffox主页系统');
        $subject = "注册验证码 - {$siteName}";
        $body = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>注册验证码</title></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);">
  <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:22px;">📧 邮箱验证</h1>
  </div>
  <div style="padding:35px 30px;text-align:center;">
    <p style="color:#333;font-size:15px;line-height:1.7;">您好！</p>
    <p style="color:#333;font-size:15px;line-height:1.7;">您正在 <strong>{$siteName}</strong> 注册账号，验证码为：</p>
    <div style="margin:30px 0;padding:20px;background:#f3f4f6;border-radius:12px;font-size:36px;font-weight:bold;letter-spacing:8px;color:#6366f1;font-family:monospace;">{$code}</div>
    <p style="color:#999;font-size:13px;">验证码10分钟内有效，请尽快输入。</p>
    <p style="color:#999;font-size:13px;">如果您没有进行注册操作，请忽略此邮件。</p>
  </div>
  <div style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #eee;">
    <p style="color:#aaa;font-size:12px;margin:0;">{$siteName}</p>
  </div>
</div>
</body></html>
HTML;
        $mailResult = sendMail($email, $subject, $body);
        if ($mailResult['success']) {
            recordRateLimit('send_code');
            echo json_encode(['success' => true, 'message' => '验证码已发送']);
        } else {
            echo json_encode(['success' => false, 'message' => '邮件发送失败: ' . $mailResult['message']]);
        }
        exit;
    }
    
    // 登录处理
    if ($action === 'login') {
        requireCsrfToken();
        
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember'] ?? '');
        
        if (empty($login) || empty($password)) {
            $error = '请输入账号/邮箱和密码';
        } else {
            // 速率限制
            $rateCheck = checkRateLimit('login');
            if (!$rateCheck['allowed']) {
                $error = $rateCheck['message'];
            } else {
                if (filter_var($login, FILTER_VALIDATE_EMAIL) && ($settings['user_email_login'] ?? 1)) {
                    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$login]);
                } else {
                    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                    $stmt->execute([$login]);
                }
                $user = $stmt->fetch();
                
                if (!$user) {
                    $error = '账号不存在';
                    recordRateLimit('login');
                } elseif (!($user['is_active'] ?? 1)) {
                    $error = '账号已被封禁，请联系管理员';
                } elseif (password_verify($password, $user['password'] ?? '')) {
                    // 账号锁定检查
                    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
                        $error = '账号已被临时锁定，请稍后再试';
                    } elseif (($settings['reg_email_verify'] ?? 0) && !empty($user['email']) && !($user['email_verified'] ?? 0)) {
                        $error = '请先验证邮箱后再登录';
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['user_id']    = (int)$user['id'];
                        $_SESSION['user_name']  = $user['nickname'] ?? '' ?: $user['username'] ?? '';
                        $_SESSION['user_login'] = true;
                        if (!empty($user['is_guest'])) {
                            $_SESSION['is_guest'] = true;
                        }
                        regenerateSession();
                        
                        // 重置登录尝试
                        $db->prepare("UPDATE users SET last_ip = ?, last_login = ?, login_attempts = 0, locked_until = NULL WHERE id = ?")
                            ->execute([getClientIP(), date('Y-m-d H:i:s'), $user['id']]);
                        
                        // Remember Me (30天)
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expires = time() + 86400 * 30;
                            setcookie('remember_token', $token, [
                            'expires' => $expires,
                            'path' => '/',
                            'domain' => '',
                            'secure' => !empty($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                            // 单独设置SameSite（PHP 7.3+支持options数组）
                            // 存储到数据库（需要 remember_tokens 表，简化处理：存储到 session）
                            $_SESSION['remember_token'] = $token;
                            $_SESSION['remember_expiry'] = $expires;
                        }
                        
                        redirect('./dashboard.php');
                    }
                } else {
                    $error = '密码错误';
                    recordRateLimit('login');
                    
                    // 账号锁定逻辑：连续5次错误锁定15分钟
                    $stmt = $db->prepare("SELECT login_attempts FROM users WHERE username = ? OR email = ? LIMIT 1");
                    $stmt->execute([$login, $login]);
                    $attemptData = $stmt->fetch();
                    $attempts = ($attemptData['login_attempts'] ?? 0) + 1;
                    if ($attempts >= 5) {
                        $db->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE username = ? OR email = ?")
                            ->execute([$attempts, date('Y-m-d H:i:s', time() + 900), $login, $login]);
                        $error = '密码错误次数过多，账号已锁定15分钟';
                    } else {
                        $db->prepare("UPDATE users SET login_attempts = ? WHERE (username = ? OR email = ?) AND id > 0")
                            ->execute([$attempts, $login, $login]);
                        $error = "密码错误（剩余" . (5 - $attempts) . "次机会）";
                    }
                }
            }
        }
    } elseif ($action === 'register') {
        requireCsrfToken();
        
        if (empty($settings['reg_enabled'])) {
            $error = '系统暂未开放注册';
        } else {
            $username = trim($_POST['reg_username'] ?? '');
            $password = $_POST['reg_password'] ?? '';
            $confirm  = $_POST['reg_confirm'] ?? '';
            $email    = trim($_POST['reg_email'] ?? '');
            $suffix   = trim($_POST['reg_suffix'] ?? '');
            $regCode  = trim($_POST['reg_code'] ?? '');
            $codeSession = trim($_POST['reg_code_session'] ?? '');
            
            if (empty($username) || empty($password) || empty($email) || empty($suffix)) {
                $error = '请填写所有字段';
            } elseif (!validateUsername($username)) {
                $error = '用户名2-20位，支持中文、字母、数字、下划线';
            } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
                $error = '密码至少需要 ' . PASSWORD_MIN_LENGTH . ' 位';
            } elseif ($password !== $confirm) {
                $error = '两次密码不一致';
            } elseif (!validateEmail($email)) {
                $error = '邮箱格式不正确';
            } elseif (!validateSuffix($suffix)) {
                $error = '后缀只允许字母、数字、下划线和连字符';
            } else {
                $savedCode = $_SESSION['reg_email_code'] ?? '';
                $savedEmail = $_SESSION['reg_email_addr'] ?? '';
                $codeExpiry = $_SESSION['reg_email_expiry'] ?? 0;
                
                if (empty($regCode)) {
                    $error = '请输入邮箱验证码';
                } elseif ($email !== $savedEmail) {
                    $error = '邮箱地址与发送验证码时不一致，请重新发送';
                } elseif ($savedCode === '' || time() > $codeExpiry) {
                    $error = '验证码已过期，请重新发送';
                } elseif ((string)$regCode !== (string)$savedCode) {
                    $error = '验证码不正确';
                } else {
                    // 检查重复
                    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) { $error = '用户名已存在'; }
                    
                    if (empty($error)) {
                        $stmt = $db->prepare("SELECT id FROM users WHERE suffix = ?");
                        $stmt->execute([$suffix]);
                        if ($stmt->fetch()) { $error = '该后缀已被使用'; }
                    }
                    
                    if (empty($error)) {
                        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) { $error = '该邮箱已被注册'; }
                    }
                    
                    // 检查被封禁后缀
                    $banned = $settings['banned_suffixes'] ?? '';
                    if (!empty($banned)) {
                        $bannedList = explode("\n", str_replace("\r", "", $banned));
                        foreach ($bannedList as $b) {
                            $b = trim($b);
                            if ($b && stripos($suffix, $b) !== false) {
                                $error = '该后缀不可用，请更换';
                                break;
                            }
                        }
                    }
                    
                    if (empty($error)) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $isVerified = ($settings['reg_email_verify'] ?? 0) ? 0 : 1;
                        $stmt = $db->prepare("INSERT INTO users (username, password, email, email_verified, nickname, suffix, is_active, is_guest, last_ip) VALUES (?, ?, ?, ?, ?, ?, 1, 0, ?)");
                        $stmt->execute([$username, $hash, $email, $isVerified, $username, $suffix, getClientIP()]);
                        
                        // 清理验证码Session
                        unset($_SESSION['reg_email_code'], $_SESSION['reg_email_addr'], $_SESSION['reg_email_expiry']);
                        
                        $regSuccess = '注册成功，请登录';
                    }
                }
            }
        }
    }
}

// 加载登录模板
$loginTplFile = __DIR__ . '/../templates/login/' . $loginTemplate . '.php';
$registerTplFile = __DIR__ . '/../templates/login/' . $registerTemplate . '.php';
$useTpl = file_exists($loginTplFile) ? $loginTplFile : __DIR__ . '/../templates/login/default.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>用户登录 - <?=h(getSiteName($db))?></title>
<link rel="stylesheet" href="../assets/css/tailwind.css">
<link rel="stylesheet" href="../assets/css/fontawesome.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
</style>
</head>
<body>
<div class="auth-container" style="width:100%;display:flex;align-items:center;justify-content:center">
<?php require $useTpl; ?>
</div>

<script>
const BASE_URL = '<?=BASE_URL?>';
const CSRF_TOKEN = '<?=getCsrfToken()?>';

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('form[id$=Form]').forEach(f => f.style.display = 'none');
    
    if (tab === 'login') {
        document.querySelector('.tab-btn').classList.add('active');
        document.getElementById('loginForm').style.display = '';
        document.getElementById('formTitle').textContent = '用户登录';
    } else if (tab === 'register') {
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
        document.getElementById('registerForm').style.display = '';
        document.getElementById('formTitle').textContent = '创建账号';
    }
}

function guestLogin(evt) {
    evt = evt || window.event;
    const btn = evt.target;
    btn.disabled = true;
    btn.textContent = '创建中...';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=guest_login'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            window.location.href = d.redirect;
        } else {
            alert(d.message);
            btn.disabled = false;
            btn.textContent = '游客';
        }
    })
    .catch(() => {
        alert('网络错误，请重试');
        btn.disabled = false;
        btn.textContent = '游客';
    });
}

let regStep = 1;
function showRegStep2() {
    const email = document.getElementById('regEmail')?.value || '';
    if (!email) { alert('请先填写邮箱'); return; }
    
    const form = document.getElementById('registerForm');
    const formData = new FormData(form);
    formData.set('action', 'send_register_code');
    formData.set('_csrf_token', CSRF_TOKEN);
    
    const btn = document.querySelector('#regStep1 .btn-primary');
    btn.disabled = true; btn.textContent = '发送中...';
    
    fetch('', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('regStep1').style.display = 'none';
            document.getElementById('regStep2').style.display = '';
            document.getElementById('regEmailDisplay').textContent = email;
        } else {
            alert(d.message);
        }
        btn.disabled = false; btn.textContent = '发送验证码';
    })
    .catch(() => {
        alert('网络错误');
        btn.disabled = false; btn.textContent = '发送验证码';
    });
}

function resendCode() {
    showRegStep2();
}

// 为所有POST表单添加CSRF Token
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[method="POST"]').forEach(function(form) {
        // 确保每个POST表单都有CSRF token
        if (!form.querySelector('input[name="_csrf_token"]')) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_csrf_token';
            input.value = CSRF_TOKEN;
            form.appendChild(input);
        }
    });
});
</script>
</body>
</html>
