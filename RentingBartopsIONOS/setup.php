<?php
declare(strict_types=1);

$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = [
        'host' => trim($_POST['db_host'] ?? ''),
        'port' => (int)($_POST['db_port'] ?? 3306),
        'name' => trim($_POST['db_name'] ?? ''),
        'user' => trim($_POST['db_user'] ?? ''),
        'password' => $_POST['db_password'] ?? '',
        'charset' => 'utf8mb4',
    ];
    $mail = [
        'host' => 'smtp.ionos.es',
        'port' => 587,
        'encryption' => 'starttls',
        'username' => trim($_POST['mail_username'] ?? ''),
        'password' => $_POST['mail_password'] ?? '',
        'from_email' => trim($_POST['mail_from'] ?? ''),
        'from_name' => 'Crazy4Arcade',
        'timeout' => 15,
    ];
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPassword = $_POST['admin_password'] ?? '';

    try {
        if (!filter_var($mail['username'], FILTER_VALIDATE_EMAIL) || !filter_var($mail['from_email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Introduce una dirección SMTP y un remitente válidos.');
        }
        if ($mail['password'] === '') throw new RuntimeException('Introduce la contraseña del correo de IONOS.');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);
        $pdo = new PDO($dsn, $db['user'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
        $hasNotas = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitudes' AND COLUMN_NAME = 'notas'")->fetchColumn();
        if (!$hasNotas) $pdo->exec("ALTER TABLE solicitudes ADD COLUMN notas TEXT NULL AFTER telefono_cliente");

        if ((int)$pdo->query('SELECT COUNT(*) FROM maquinas')->fetchColumn() === 0) {
            $q = $pdo->prepare('INSERT INTO maquinas (nombre,descripcion,categoria,imagen_url,precio_base) VALUES (?,?,?,?,?)');
            foreach ([
                ['Bartop Retro Clásico', 'Gabinete bartop de madera con juegos clásicos y pantalla CRT.', 'Grande', '/img/placeholder.svg', 180],
                ['Bartop Pixel Art', 'Diseño artesanal con ilustraciones de píxel art pintadas a mano.', 'Grande', '/img/placeholder.svg', 200],
                ['Mini Bartop Retro', 'Versión compacta ideal para mesas y espacios pequeños.', 'Pequeña', '/img/placeholder.svg', 120],
                ['Bartop Neon City', 'Gabinete con iluminación LED neon y temática cyberpunk.', 'Grande', '/img/placeholder.svg', 220],
                ['Mini Bartop Madera Natural', 'Acabado en madera de haya vaporizada. Minimalista y elegante.', 'Pequeña', '/img/placeholder.svg', 130],
            ] as $machine) $q->execute($machine);
        }
        if ((int)$pdo->query('SELECT COUNT(*) FROM tarifas')->fetchColumn() === 0) {
            $q = $pdo->prepare('INSERT INTO tarifas (tipo,nombre,precio,unidad,descripcion,destacada,orden) VALUES (?,?,?,?,?,?,?)');
            foreach ([
                ['alquiler', 'Máquinas pequeñas', '120', '€/día', "Pantalla 15–17\nJuegos clásicos\nFácil transporte e instalación\nCables y adaptadores incluidos\nSoporte técnico remoto", 0, 10],
                ['alquiler', 'Máquinas grandes', '180', '€/día', "Pantalla 21–27\nJuegos clásicos\nAltavoces integrados\nMontaje y desmontaje incluidos\nSoporte técnico en evento\nTransporte incluido (radio 50 km)", 1, 20],
                ['extra', 'Transporte fuera de 50 km', '1,50', '€/km adicional', '', 0, 10],
                ['extra', 'Día extra de alquiler', '70', '% del precio base', '', 0, 20],
                ['extra', 'Técnico en evento (jornada)', '80', '€', '', 0, 30],
                ['extra', 'Personalización de pantalla de inicio', '40', '€', '', 0, 40],
            ] as $tariff) $q->execute($tariff);
        }

        $config = "<?php\nreturn " . var_export([
            'db' => $db,
            'notification_email' => 'thorzanocade@gmail.com',
            'mail' => $mail,
            'admin' => [
                'user' => $adminUser,
                'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
            ],
        ], true) . ";\n";
        if (!is_dir(__DIR__ . '/app') || !is_writable(__DIR__ . '/app')) {
            throw new RuntimeException('La carpeta app no permite escritura temporal.');
        }
        file_put_contents(__DIR__ . '/app/config.php', $config, LOCK_EX);
        $done = true;
    } catch (Throwable $e) {
        $error = 'No se pudo completar la instalación: ' . $e->getMessage();
    }
}

$dbDefaults = [
    'db_host' => 'db5021020332.hosting-data.io',
    'db_port' => '3306',
    'db_name' => 'dbs15944211',
    'db_user' => 'dbu4360934',
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Instalación</title>
    <link rel="stylesheet" href="/css/site.css">
</head>
<body>
<main>
    <div class="container">
        <div class="page-header">
            <h1>Instalación inicial</h1>
            <p>Introduce estos datos directamente en IONOS. No los compartas por chat.</p>
        </div>
        <?php if ($done): ?>
            <div class="alert alert-success">Instalación completada. Elimina setup.php antes de abrir la web.</div>
            <a class="btn btn-primary" href="/">Abrir web</a>
        <?php else: ?>
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <form method="post" class="solicitud-form">
                <fieldset>
                    <legend>Base de datos</legend>
                    <?php foreach (['db_host' => 'Servidor', 'db_port' => 'Puerto', 'db_name' => 'Base de datos', 'db_user' => 'Usuario', 'db_password' => 'Contraseña'] as $name => $label): ?>
                        <div class="form-group">
                            <label><?= $label ?></label>
                            <input class="form-control" name="<?= $name ?>" type="<?= $name === 'db_password' ? 'password' : 'text' ?>" value="<?= htmlspecialchars($_POST[$name] ?? ($dbDefaults[$name] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
                <fieldset>
                    <legend>Correo SMTP de IONOS</legend>
                    <div class="form-group">
                        <label>Dirección de correo de IONOS</label>
                        <input class="form-control" name="mail_username" type="email" value="<?= htmlspecialchars($_POST['mail_username'] ?? 'info@crazy4arcade.com', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña del correo</label>
                        <input class="form-control" name="mail_password" type="password" required>
                    </div>
                    <div class="form-group">
                        <label>Remitente</label>
                        <input class="form-control" name="mail_from" type="email" value="<?= htmlspecialchars($_POST['mail_from'] ?? ($_POST['mail_username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Administrador</legend>
                    <div class="form-group">
                        <label>Usuario</label>
                        <input class="form-control" name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input class="form-control" name="admin_password" type="password" minlength="12" required>
                    </div>
                </fieldset>
                <button class="btn btn-primary">Instalar</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
