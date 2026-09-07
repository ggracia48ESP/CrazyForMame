<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Catálogo'; $filtro = $_GET['filtro'] ?? '';
$sql = 'SELECT * FROM maquinas'; $params = [];
if (in_array($filtro, ['Grande','Pequeña'], true)) { $sql .= ' WHERE categoria = ?'; $params[] = $filtro; }
$sql .= ' ORDER BY id'; $stmt = $pdo->prepare($sql); $stmt->execute($params); $maquinas = $stmt->fetchAll();
$view_file = __DIR__ . '/catalogo.view.php'; require __DIR__ . '/layout.php';
