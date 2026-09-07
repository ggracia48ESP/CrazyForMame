<?php
require __DIR__ . '/app/bootstrap.php'; $title = 'Disponibilidad'; $desde = $_GET['desde'] ?? ''; $hasta = $_GET['hasta'] ?? ''; $consultado = $desde !== '' && $hasta !== '';
$disponibles = $ocupadas = [];
if ($consultado) { $maquinas = $pdo->query('SELECT * FROM maquinas ORDER BY id')->fetchAll(); $q = $pdo->prepare("SELECT DISTINCT maquina_id FROM bloqueos_fecha WHERE fecha_inicio <= ? AND fecha_fin >= ? UNION SELECT DISTINCT maquina_id FROM solicitudes WHERE estado = 'Aprobada' AND fecha_inicio <= ? AND fecha_fin >= ?"); $q->execute([$hasta,$desde,$hasta,$desde]); $ids = array_column($q->fetchAll(), 'maquina_id'); foreach ($maquinas as $m) in_array($m['id'], $ids) ? $ocupadas[] = $m : $disponibles[] = $m; }
$view_file = __DIR__ . '/calendario.view.php'; require __DIR__ . '/layout.php';
