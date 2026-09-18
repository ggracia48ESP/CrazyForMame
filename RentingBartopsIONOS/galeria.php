<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Galería';
$eventos = $pdo->query('SELECT * FROM galeria_eventos ORDER BY COALESCE(fecha_evento, fecha_creacion) DESC, id DESC')->fetchAll();
$imagenes = $pdo->query('SELECT * FROM galeria_imagenes ORDER BY id')->fetchAll();
$porEvento = [];
foreach ($imagenes as $imagen) $porEvento[(int)$imagen['evento_id']][] = $imagen;
$eventos = array_values(array_filter($eventos, fn(array $evento): bool => !empty($porEvento[(int)$evento['id']])));
$view_file = __DIR__ . '/galeria.view.php'; require __DIR__ . '/layout.php';
