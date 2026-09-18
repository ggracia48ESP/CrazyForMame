<?php $content = $view ?? ''; ?><!DOCTYPE html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> - Crazy4Arcade</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323:wght@400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/site.css"><script src="https://unpkg.com/htmx.org@2/dist/htmx.min.js"></script></head>
<body><header><div class="header-container"><a href="/" class="brand" aria-label="Crazy4Arcade, inicio"><img src="/img/logo-transparent.png" alt="Crazy4Arcade"></a><nav>
<a href="/">Inicio</a><a href="/catalogo.php">Catálogo</a><a href="/tarifas.php">Tarifas</a><a href="/calendario.php">Disponibilidad</a><a href="/galeria.php">Galería</a><a href="/solicitud.php">Solicitar</a>
</nav></div></header><main><div class="container"><?php require $view_file; ?></div></main>
<footer><div class="footer-container"><p>&copy; <?= date('Y') ?> Crazy4Arcade — Máquinas recreativas para eventos</p><div class="social-links social-links-footer"><a href="https://wa.me/34679504035" target="_blank" rel="noopener noreferrer">WhatsApp</a><a href="https://www.instagram.com/crazy4arcade/" target="_blank" rel="noopener noreferrer">Instagram</a><a href="https://www.youtube.com/@crazy4arcade19" target="_blank" rel="noopener noreferrer">YouTube</a></div></div></footer><script src="/js/site.js" defer></script></body></html>
