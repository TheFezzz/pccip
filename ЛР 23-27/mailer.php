<?php
declare(strict_types=1);

/**
 * Отправка писем через SMTP Gmail без внешних библиотек.
 */
function send_gmail_smtp(
    string $toEmail,
    string $toName,
    string $subject,
    string $bodyText,
    ?string $bodyHtml = null
): bool {
    $configPath = __DIR__ . '/../config/mail.php';
    if (!is_file($configPath)) {
        return false;
    }

    /** @var array<string,mixed> $cfg */
    $cfg = require $configPath;

    $host      = (string)($cfg['host'] ?? 'smtp.gmail.com');
    $port      = (int)($cfg['port'] ?? 587);
    $username  = (string)($cfg['username'] ?? '');
    $password  = (string)($cfg['password'] ?? '');
    $fromEmail = (string)($cfg['from_email'] ?? $username);
    $fromName  = (string)($cfg['from_name'] ?? 'Site');

    if ($username === '' || $password === '' || $fromEmail === '') {
        return false;
    }

    $errno  = 0;
    $errstr = '';
    $socket = @stream_socket_client(
        "tcp://{$host}:{$port}",
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        return false;
    }

    $readLine = static function () use ($socket): string {
        $line = '';
        while (!feof($socket)) {
            $chunk = fgets($socket, 512);
            if ($chunk === false) {
                break;
            }
            $line .= $chunk;
            if (strlen($chunk) < 4 || isset($chunk[3]) && $chunk[3] === ' ') {
                break;
            }
        }
        return $line;
    };

    $writeLine = static function (string $data) use ($socket): void {
        fwrite($socket, $data . "\r\n");
    };

    $readLine(); // 220

    $writeLine('EHLO localhost');
    $readLine();

    $writeLine('STARTTLS');
    $line = $readLine();
    if (strpos($line, '220') !== 0) {
        fclose($socket);
        return false;
    }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return false;
    }

    $writeLine('EHLO localhost');
    $readLine();

    $writeLine('AUTH LOGIN');
    $readLine();
    $writeLine(base64_encode($username));
    $readLine();
    $writeLine(base64_encode($password));
    $authResponse = $readLine();
    if (strpos($authResponse, '235') !== 0) {
        fclose($socket);
        return false;
    }

    $writeLine('MAIL FROM: <' . $fromEmail . '>');
    $readLine();

    $writeLine('RCPT TO: <' . $toEmail . '>');
    $readLine();

    $writeLine('DATA');
    $readLine();

    if ($bodyHtml === null) {
        $bodyHtml = nl2br($bodyText, false);
    }

    $boundary   = 'b' . bin2hex(random_bytes(8));
    $subjectEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $fromNameEnc = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

    $headers   = [];
    $headers[] = 'From: ' . $fromNameEnc . ' <' . $fromEmail . '>';
    $headers[] = 'To: <' . $toEmail . '>';
    $headers[] = 'Subject: ' . $subjectEnc;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

    $message  = implode("\r\n", $headers) . "\r\n\r\n";
    $message .= '--' . $boundary . "\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= $bodyText . "\r\n\r\n";
    $message .= '--' . $boundary . "\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= $bodyHtml . "\r\n\r\n";
    $message .= '--' . $boundary . "--\r\n.";

    $writeLine($message);
    $readLine();
    $writeLine('QUIT');
    fclose($socket);

    return true;
}

/**
 * Удобная обёртка для письма с кодом подтверждения регистрации.
 */
function send_registration_code_email(string $toEmail, string $toName, string $code): bool
{
    $subject = 'Код подтверждения регистрации';

    $bodyText = "Здравствуйте, {$toName}!\n\n"
        . "Спасибо за регистрацию на сайте «Геноцид в Беларуси».\n"
        . "Ваш код подтверждения: {$code}\n\n"
        . "Никому не сообщайте этот код.\n";

    $bodyHtml = '<p>Здравствуйте, ' . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . '!</p>'
        . '<p>Спасибо за регистрацию на сайте «Геноцид в Беларуси».</p>'
        . '<p><strong>Ваш код подтверждения: '
        . '<span style="font-size:20px;letter-spacing:4px;">'
        . htmlspecialchars($code, ENT_QUOTES, 'UTF-8')
        . '</span></strong></p>'
        . '<p>Никому не сообщайте этот код.</p>';

    return send_gmail_smtp($toEmail, $toName, $subject, $bodyText, $bodyHtml);
}

/**
 * Уведомление автору: история одобрена.
 */
function send_story_approved_email(string $toEmail, string $toName, string $storyTitle): bool
{
    $subject = 'Ваша история одобрена!';

    $eName  = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $eTitle = htmlspecialchars($storyTitle, ENT_QUOTES, 'UTF-8');

    $bodyText = "Здравствуйте, {$toName}!\n\n"
        . "Ваша история «{$storyTitle}» была проверена модератором и опубликована на сайте «Геноцид в Беларуси».\n\n"
        . "Спасибо за ваш вклад!\n";

    $bodyHtml = "<p>Здравствуйте, {$eName}!</p>"
        . "<p>Ваша история <strong>«{$eTitle}»</strong> была проверена модератором и "
        . "<span style=\"color:#2e7d32;font-weight:bold;\">опубликована</span> на сайте «Геноцид в Беларуси».</p>"
        . "<p>Спасибо за ваш вклад!</p>";

    return send_gmail_smtp($toEmail, $toName, $subject, $bodyText, $bodyHtml);
}

/**
 * Уведомление автору: история отклонена с указанием причины.
 */
function send_story_rejected_email(string $toEmail, string $toName, string $storyTitle, string $reason): bool
{
    $subject = 'Ваша история отклонена';

    $eName   = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $eTitle  = htmlspecialchars($storyTitle, ENT_QUOTES, 'UTF-8');
    $eReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');

    $bodyText = "Здравствуйте, {$toName}!\n\n"
        . "К сожалению, ваша история «{$storyTitle}» была отклонена модератором.\n\n"
        . "Причина отклонения:\n{$reason}\n\n"
        . "Вы можете исправить замечания и отправить историю повторно.\n"
        . "С уважением, администрация сайта «Геноцид в Беларуси».\n";

    $bodyHtml = "<p>Здравствуйте, {$eName}!</p>"
        . "<p>К сожалению, ваша история <strong>«{$eTitle}»</strong> была "
        . "<span style=\"color:#c62828;font-weight:bold;\">отклонена</span> модератором.</p>"
        . "<div style=\"margin:12px 0;padding:12px 16px;background:#2c2c2c;border-left:4px solid #c62828;border-radius:6px;\">"
        . "<strong>Причина отклонения:</strong><br>" . nl2br($eReason) . "</div>"
        . "<p>Вы можете исправить замечания и отправить историю повторно.</p>"
        . "<p>С уважением, администрация сайта «Геноцид в Беларуси».</p>";

    return send_gmail_smtp($toEmail, $toName, $subject, $bodyText, $bodyHtml);
}

