<?php
/**
 * @var mixed $tokenCsrf
 * @var string $tituloPagina
 * @var string $nombreAplicacion
 * @var array<array-key, mixed> $estilosContextualesVista
 * @var string $clasesCuerpo
 * @var string $contenido
 * @var array<array-key, mixed> $scriptsFinalesVista
 */ ?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e($tokenCsrf) ?>">
    <title><?= e($tituloPagina ?? $nombreAplicacion) ?> | <?= e($nombreAplicacion) ?></title>
    <meta name="description" content="Celulares y audífonos originales en Bagua. Catálogo, stock y atención de MD Technology Digital Cell.">
    <link rel="stylesheet" href="<?= e(url_recurso_estatico('assets/css/estilos.css?v=20260927-8')) ?>">
    <?php foreach ($estilosContextualesVista as $archivoCss): ?>
        <link rel="stylesheet" href="<?= e($archivoCss) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e(url_recurso_estatico('assets/vendor/bootstrap-icons/bootstrap-icons.min.css?v=20261001-1')) ?>">
</head>
<body class="<?= e($clasesCuerpo) ?>">
<?php require dirname(__DIR__) . '/componentes/encabezados/publico.php'; ?>
    <main>
    <?= $contenido ?>
    </main>
<?php require dirname(__DIR__) . '/componentes/pies/pie-pagina.php'; ?>
<?php require dirname(__DIR__) . '/componentes/publicidad-dinamica.php'; ?>
    <button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
<?php foreach ($scriptsFinalesVista as $archivoScript): ?>
<script src="<?= e($archivoScript) ?>"></script>
<?php endforeach; ?>
</body>
</html>
