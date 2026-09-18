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
function notify_request(array $request): bool {
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

function render(string $view, array $data = [], bool $admin = false): void {
    extract($data);
    $title = $title ?? 'Crazy4Arcade';
    require __DIR__ . ($admin ? '/../admin/layout.php' : '/../public/layout.php');
}
