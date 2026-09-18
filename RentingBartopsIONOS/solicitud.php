<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Solicitar alquiler'; $errors = []; $sent = false; $mail_ok = false;
$input = ['maquina_id'=>(int)($_GET['maquinaId'] ?? 0),'fecha_inicio'=>date_value($_GET['desde'] ?? null),'fecha_fin'=>date_value($_GET['hasta'] ?? null),'nombre_cliente'=>'','email_cliente'=>'','telefono_cliente'=>'','notas'=>''];
$maquinas = $pdo->query('SELECT * FROM maquinas ORDER BY id')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach ($input as $key => $value) $input[$key] = trim((string)($_POST[$key] ?? ''));
    $input['maquina_id'] = (int)$input['maquina_id'];
    if (!$input['maquina_id'] || !$input['nombre_cliente'] || !filter_var($input['email_cliente'], FILTER_VALIDATE_EMAIL) || !$input['telefono_cliente'] || !$input['fecha_inicio'] || !$input['fecha_fin']) $errors[] = 'Revisa todos los campos obligatorios.';
    if (mb_strlen($input['notas']) > 2000) $errors[] = 'Las notas no pueden superar los 2.000 caracteres.';
    if ($input['fecha_fin'] < $input['fecha_inicio']) $errors[] = 'La fecha de fin debe ser igual o posterior a la de inicio.';
    if (!$errors) {
        $q=$pdo->prepare("SELECT COUNT(*) FROM bloqueos_fecha WHERE maquina_id=? AND fecha_inicio<=? AND fecha_fin>=? UNION ALL SELECT COUNT(*) FROM solicitudes WHERE maquina_id=? AND estado='Aprobada' AND fecha_inicio<=? AND fecha_fin>=?");
        $q->execute([$input['maquina_id'],$input['fecha_fin'],$input['fecha_inicio'],$input['maquina_id'],$input['fecha_fin'],$input['fecha_inicio']]);
        if (array_sum(array_column($q->fetchAll(), 'COUNT(*)')) > 0) $errors[]='La máquina no está disponible en las fechas seleccionadas.';
        else {
            $q=$pdo->prepare('INSERT INTO solicitudes (maquina_id,nombre_cliente,email_cliente,telefono_cliente,notas,fecha_inicio,fecha_fin) VALUES (?,?,?,?,?,?,?)');
            $q->execute([$input['maquina_id'],$input['nombre_cliente'],$input['email_cliente'],$input['telefono_cliente'],$input['notas'],$input['fecha_inicio'],$input['fecha_fin']]);
            $machine = array_values(array_filter($maquinas, fn(array $m): bool => (int)$m['id'] === $input['maquina_id']))[0] ?? ['nombre'=>''];
            $mail_ok = notify_request($input + ['maquina_nombre' => $machine['nombre']]);
            $sent=true;
        }
    }
}
$view_file=__DIR__.'/solicitud.view.php'; require __DIR__.'/layout.php';
