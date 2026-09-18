<?php
declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Falta configurar app/config.php.');
}
$config = require $configFile;
date_default_timezone_set('Europe/Madrid');
session_start();

try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['db']['host'], $config['db']['port'], $config['db']['name'], $config['db']['charset']);
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('No se ha podido conectar con la base de datos.');
}

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Solicitud no válida.'); } }
function is_admin(): bool { return !empty($_SESSION['admin']); }
function require_admin(): void { if (!is_admin()) redirect('/admin/login.php'); }
function price(float|int|string $value): string { return number_format((float)$value, 0, ',', '.') . ' €'; }
function date_value(?string $value): string { return $value ?: date('Y-m-d'); }
function clean_game_counts(string $value): string { return trim((string)preg_replace('/\b(?:más de|hasta)?\s*\d+\s+juegos\b/i', 'juegos clásicos', $value)); }
function notification_email(): string { return $GLOBALS['config']['notification_email'] ?? 'thorzanocade@gmail.com'; }

function mail_config(): array {
    $mail = $GLOBALS['config']['mail'] ?? [];
    return [
        'host' => (string)($mail['host'] ?? 'smtp.ionos.es'),
        'port' => (int)($mail['port'] ?? 587),
        'encryption' => strtolower((string)($mail['encryption'] ?? 'starttls')),
        'username' => (string)($mail['username'] ?? ''),
        'password' => (string)($mail['password'] ?? ''),
        'from_email' => (string)($mail['from_email'] ?? ($mail['username'] ?? '')),
        'from_name' => (string)($mail['from_name'] ?? 'Crazy4Arcade'),
        'timeout' => max(5, (int)($mail['timeout'] ?? 15)),
    ];
}

function smtp_response($socket, int $expectedCode): string {
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') break;
    }
    if ((int)substr($response, 0, 3) !== $expectedCode) {
        throw new RuntimeException('SMTP response ' . trim($response));
    }
    return $response;
}

function smtp_command($socket, string $command, int $expectedCode): string {
    if (fwrite($socket, $command . "\r\n") === false) {
        throw new RuntimeException('No se pudo escribir en el servidor SMTP.');
    }
    return smtp_response($socket, $expectedCode);
}

function encoded_header(string $value): string { return '=?UTF-8?B?' . base64_encode($value) . '?='; }

function smtp_send(string $to, string $subject, string $body, ?string $replyTo = null): bool {
    $mail = mail_config();
    foreach ([$to, $mail['username'], $mail['from_email']] as $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) {
            error_log('Crazy4Arcade: configuración SMTP con una dirección no válida.');
            return false;
        }
    }
    if ($mail['password'] === '' || preg_match('/[\r\n]/', $mail['host'])) {
        error_log('Crazy4Arcade: falta configurar la contraseña SMTP o el servidor SMTP.');
        return false;
    }

    $socket = null;
    try {
        $transport = $mail['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
        $context = stream_context_create(['ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'peer_name' => $mail['host'],
        ]]);
        $socket = stream_socket_client(
            $transport . $mail['host'] . ':' . $mail['port'],
            $errno,
            $errstr,
            $mail['timeout'],
            STREAM_CLIENT_CONNECT,
            $context
        );
        if ($socket === false) throw new RuntimeException('No se pudo conectar con SMTP: ' . $errstr . ' (' . $errno . ')');
        stream_set_timeout($socket, $mail['timeout']);
        smtp_response($socket, 220);
        smtp_command($socket, 'EHLO crazy4arcade.com', 250);
        if ($mail['encryption'] === 'starttls') {
            smtp_command($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('No se pudo activar TLS en SMTP.');
            }
            smtp_command($socket, 'EHLO crazy4arcade.com', 250);
        }
        smtp_command($socket, 'AUTH LOGIN', 334);
        smtp_command($socket, base64_encode($mail['username']), 334);
        smtp_command($socket, base64_encode($mail['password']), 235);
        smtp_command($socket, 'MAIL FROM:<' . $mail['from_email'] . '>', 250);
        smtp_command($socket, 'RCPT TO:<' . $to . '>', 250);
        smtp_command($socket, 'DATA', 354);

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . encoded_header($mail['from_name']) . ' <' . $mail['from_email'] . '>',
            'To: <' . $to . '>',
            'Subject: ' . encoded_header($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL) && !preg_match('/[\r\n]/', $replyTo)) {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }
        $message = implode("\r\n", $headers) . "\r\n\r\n" . preg_replace('/\r?\n/', "\r\n", $body);
        $message = preg_replace('/\r\n\./', "\r\n..", $message) . "\r\n.\r\n";
        if (fwrite($socket, $message) === false) throw new RuntimeException('No se pudo enviar el contenido del correo.');
        smtp_response($socket, 250);
        fwrite($socket, "QUIT\r\n");
        return true;
    } catch (Throwable $e) {
        error_log('Crazy4Arcade SMTP: ' . $e->getMessage());
        return false;
    } finally {
        if (is_resource($socket)) fclose($socket);
    }
}
function notify_request_legacy(array $request): bool {
    $subject = 'Nueva solicitud de alquiler - ' . ($request['maquina_nombre'] ?? 'Crazy4Arcade');
    $body = "Se ha recibido una nueva solicitud en Crazy4Arcade.\n\n" .
        "Cliente: " . ($request['nombre_cliente'] ?? '') . "\n" .
        "Email: " . ($request['email_cliente'] ?? '') . "\n" .
        "Teléfono: " . ($request['telefono_cliente'] ?? '') . "\n" .
        "Máquina: " . ($request['maquina_nombre'] ?? '') . "\n" .
        "Fechas: " . ($request['fecha_inicio'] ?? '') . " a " . ($request['fecha_fin'] ?? '') . "\n" .
        "Notas: " . (($request['notas'] ?? '') ?: 'Sin notas') . "\n";
    $headers = "From: no-reply@crazy4arcade.com\r\nReply-To: " . ($request['email_cliente'] ?? '') . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    return function_exists('mail') && mail(notification_email(), '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}

function notify_request(array $request): bool {
    $subject = 'Nueva solicitud de alquiler - ' . ($request['maquina_nombre'] ?? 'Crazy4Arcade');
    $body = "Se ha recibido una nueva solicitud en Crazy4Arcade.\n\n" .
        "Cliente: " . ($request['nombre_cliente'] ?? '') . "\n" .
        "Email: " . ($request['email_cliente'] ?? '') . "\n" .
        "Teléfono: " . ($request['telefono_cliente'] ?? '') . "\n" .
        "Máquina: " . ($request['maquina_nombre'] ?? '') . "\n" .
        "Fechas: " . ($request['fecha_inicio'] ?? '') . " a " . ($request['fecha_fin'] ?? '') . "\n" .
        "Notas: " . (($request['notas'] ?? '') ?: 'Sin notas') . "\n";
    $adminSent = smtp_send(notification_email(), $subject, $body, $request['email_cliente'] ?? null);

    $customerBody = "Hola " . ($request['nombre_cliente'] ?? 'cliente') . ",\n\n" .
        "Hemos recibido tu solicitud de alquiler en Crazy4Arcade.\n\n" .
        "Máquina: " . ($request['maquina_nombre'] ?? '') . "\n" .
        "Fechas: " . ($request['fecha_inicio'] ?? '') . " a " . ($request['fecha_fin'] ?? '') . "\n\n" .
        "Te confirmaremos la disponibilidad y los detalles en menos de 24 horas.\n\n" .
        "Gracias,\nCrazy4Arcade";
    $customerSent = $request['email_cliente'] === notification_email()
        ? $adminSent
        : smtp_send($request['email_cliente'], 'Hemos recibido tu solicitud - Crazy4Arcade', $customerBody, notification_email());

    return $adminSent && $customerSent;
}

function render(string $view, array $data = [], bool $admin = false): void {
    extract($data);
    $title = $title ?? 'Crazy4Arcade';
    require __DIR__ . ($admin ? '/../admin/layout.php' : '/../public/layout.php');
}
