<?php
/**
 * @var mixed $tokenCsrf
 * @var string $tituloPagina
 * @var array<array-key, mixed> $estilosContextuales
 * @var string $clasesCuerpo
 * @var string $contenido
 * @var array<array-key, mixed> $scriptsFinalesVista
 */ ?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e($tokenCsrf) ?>">
    <title><?= e($tituloPagina ?? 'Panel') ?> | MD Technology</title>
    <link rel="stylesheet" href="<?= e(url_recurso_estatico('assets/css/estilos.css?v=20260927-8')) ?>">
    <?php foreach ($estilosContextuales as $archivoCss): ?>
        <link rel="stylesheet" href="<?= e(url_recurso_estatico($archivoCss . '?v=20260927-8')) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e(url_recurso_estatico('assets/vendor/bootstrap-icons/bootstrap-icons.min.css?v=20261001-1')) ?>">
</head>
<body class="admin-body <?= e($clasesCuerpo) ?>">
    <div class="admin-layout">
    <?php require dirname(__DIR__) . '/componentes/navegacion/sidebar-interno.php'; ?>
        <div class="admin-shell">
        <?php require dirname(__DIR__) . '/componentes/navegacion/navbar-interno.php'; ?>
            <main class="admin-main"><?= $contenido ?></main>
        </div>
    </div>
    <div class="sidebar-backdrop" data-sidebar-close></div>
    <button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
<?php foreach ($scriptsFinalesVista as $archivoScript): ?>
<script src="<?= e($archivoScript) ?>"></script>
<?php endforeach; ?>
</body>
</html>
