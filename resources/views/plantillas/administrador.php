<?php
/**
 * @var mixed $tokenCsrf
 * @var string $tituloPagina
 * @var array<array-key, mixed> $estilosContextuales
 * @var string $clasesCuerpo
 * @var string $contenido
 */ ?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e($tokenCsrf) ?>">
    <title><?= e($tituloPagina ?? 'Administración') ?> | MD Technology</title>
    <link rel="stylesheet" href="<?= e(url_recurso_estatico('assets/css/estilos.css?v=20260929-1')) ?>">
    <?php foreach ($estilosContextuales as $estilo): ?>
        <link rel="stylesheet" href="<?= e(url_recurso_estatico($estilo . '?v=20261001-1')) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e(url_recurso_estatico('assets/vendor/bootstrap-icons/bootstrap-icons.min.css?v=20261001-1')) ?>">
</head>
<body class="admin-body admin-theme <?= e($clasesCuerpo) ?>">
    <div class="admin-app" data-admin-app>
    <?php require dirname(__DIR__) . '/componentes/administracion/sidebar.php'; ?>
        <div class="admin-content-shell">
        <?php require dirname(__DIR__) . '/componentes/administracion/topbar.php'; ?>
            <main class="admin-main" id="contenido-principal"><?= $contenido ?></main>
        </div>
    </div>
    <button class="admin-sidebar-backdrop" type="button" data-admin-sidebar-close aria-label="Cerrar menú"></button>
<?php require dirname(__DIR__) . '/componentes/administracion/asistente.php'; ?>
<script src="<?= e(url_recurso_estatico('assets/js/administrador.js?v=20261001-1')) ?>"></script>
</body>
</html>
