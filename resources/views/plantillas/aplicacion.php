<?php
$usuario = current_user();
$campanias = active_campaigns();
$campaniaEmergente = $campanias['emergente'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($tituloPagina ?? config('app.name')) ?> | <?= e(config('app.name')) ?></title>
    <meta name="description" content="Celulares y audífonos originales en Bagua. Catálogo, stock y atención de MD Technology Digital Cell.">
    <link rel="stylesheet" href="<?= e(asset('assets/css/estilos.css?v=20260927-7')) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<?php require dirname(__DIR__) . '/componentes/encabezado.php'; ?>
<main>
    <?= $contenido ?>
</main>
<?php require dirname(__DIR__) . '/componentes/pie_pagina.php'; ?>
<?php require dirname(__DIR__) . '/componentes/publicidad_dinamica.php'; ?>
<button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
<script src="<?= e(asset('assets/js/aplicacion.js?v=20260927-5')) ?>"></script>
</body>
</html>
