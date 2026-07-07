<?php
/**
 * Leaffox 邮件发送函数（纯 PHP + SSL/TLS Socket，无需 Composer）
 * 支持 SMTP SSL/STARTTLS
 */

/**
 * 发送邮件
 * @param string $to      收件人
 * @param string $subject 主题
 * @param string $body    HTML 正文
 * @return array ['success' => bool, 'message' => string]
 */
function sendMail($to, $subject, $body) {
    // 从数据库获取 SMTP 配置
    global $db;
    if (!$db) {
        return ['success' => false, 'message' => '数据库未连接'];
    }
    $settings = getSettings($db);
    $host     = $settings['smtp_host'] ?? '';
    $port     = (int)($settings['smtp_port'] ?? 465);
    $user     = $settings['smtp_user'] ?? '';
    $pass     = $settings['smtp_pass'] ?? '';
    $encrypt  = $settings['smtp_encrypt'] ?? 'ssl';
    $fromName = $settings['smtp_from_name'] ?: $settings['site_name'] ?: 'Leaffox主页系统';

    if (empty($host) || empty($user) || empty($pass)) {
        return ['success' => false, 'message' => 'SMTP 未配置'];
    }

    // 使用 SSL 连接
    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    $errno = 0;
    $errstr = '';
    $prefix = ($encrypt === 'ssl') ? 'ssl://' : '';
    $socket = @stream_socket_client(
        $prefix . $host . ':' . $port,
        $errno, $errstr, 15,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        return ['success' => false, 'message' => "连接 SMTP 失败: $errstr ($errno)"];
    }

    $response = fgets($socket, 512);

    // STARTTLS
    if ($encrypt === 'tls') {
        fwrite($socket, "EHLO leaffox\r\n");
        while ($line = fgets($socket, 512)) {
            if (substr($line, 3, 1) === ' ') break;
        }
        fwrite($socket, "STARTTLS\r\n");
        $resp = fgets($socket, 512);
        if (substr($resp, 0, 3) !== '220') {
            fclose($socket);
            return ['success' => false, 'message' => 'STARTTLS 失败: ' . trim($resp)];
        }
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        // 重新 EHLO
        fwrite($socket, "EHLO leaffox\r\n");
        while ($line = fgets($socket, 512)) {
            if (substr($line, 3, 1) === ' ') break;
        }
    } else {
        fwrite($socket, "EHLO leaffox\r\n");
        while ($line = fgets($socket, 512)) {
            if (substr($line, 3, 1) === ' ') break;
        }
    }

    // 登录
    fwrite($socket, "AUTH LOGIN\r\n");
    $resp = fgets($socket, 512);
    if (substr($resp, 0, 3) !== '334') {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP 登录失败（AUTH）: ' . trim($resp)];
    }

    fwrite($socket, base64_encode($user) . "\r\n");
    $resp = fgets($socket, 512);
    if (substr($resp, 0, 3) !== '334') {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP 用户名验证失败: ' . trim($resp)];
    }

    fwrite($socket, base64_encode($pass) . "\r\n");
    $resp = fgets($socket, 512);
    if (substr($resp, 0, 3) !== '235') {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP 密码验证失败: ' . trim($resp)];
    }

    // 发件人
    fwrite($socket, "MAIL FROM:<{$user}>\r\n");
    $resp = fgets($socket, 512);
    if (substr($resp, 0, 3) !== '250') {
        fclose($socket);
        return ['success' => false, 'message' => 'MAIL FROM 失败: ' . trim($resp)];
    }

    // 收件人
    fwrite($socket, "RCPT TO:<{$to}>\r\n");
    $resp = fgets($socket, 512);
    if (substr($resp, 0, 3) !== '250') {
        fclose($socket);
        return ['success' => false, 'message' => 'RCPT TO 失败: ' . trim($resp)];
    }

    // DATA
    fwrite($socket, "DATA\r\n");
    $resp = fgets($socket, 512);
    if (substr($resp, 0, 3) !== '354') {
        fclose($socket);
        return ['success' => false, 'message' => 'DATA 命令失败: ' . trim($resp)];
    }

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$user}>\r\n";
    $headers .= "To: <{$to}>\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "X-Mailer: Leaffox-Mailer/1.0\r\n";

    fwrite($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
    $resp = fgets($socket, 512);

    // QUIT
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    if (substr($resp, 0, 3) === '250') {
        return ['success' => true, 'message' => '发送成功'];
    }
    return ['success' => false, 'message' => '邮件发送失败: ' . trim($resp)];
}
