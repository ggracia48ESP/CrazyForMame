<?php $content = $view ?? ''; ?><!DOCTYPE html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> - CrazyForMame Bartops Renting</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323:wght@400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/site.css"><script src="https://unpkg.com/htmx.org@2/dist/htmx.min.js"></script></head>
<body><header><div class="header-container"><a href="/" class="brand">CrazyForMame Bartops Renting</a><nav>
<a href="/">Inicio</a><a href="/catalogo.php">Catálogo</a><a href="/tarifas.php">Tarifas</a><a href="/calendario.php">Disponibilidad</a><a href="/solicitud.php">Solicitar</a>
</nav></div></header><main><div class="container"><?php require $view_file; ?></div></main>
<footer><div class="footer-container"><p>&copy; <?= date('Y') ?> CrazyForMame Bartops Renting — Máquinas recreativas para eventos</p></div></footer></body></html>
