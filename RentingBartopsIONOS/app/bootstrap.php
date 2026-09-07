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

function render(string $view, array $data = [], bool $admin = false): void {
    extract($data);
    $title = $title ?? 'CrazyForMame Bartops Renting';
    require __DIR__ . ($admin ? '/../admin/layout.php' : '/../public/layout.php');
}
