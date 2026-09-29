<?php
$usuarioActual = current_user() ?? [];
$rolActual = user_role();
$navegacion = \App\Soporte\Autorizacion\AccesoRol::navigation($rolActual);
$rutaActual = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$estilosContextuales = \App\Soporte\Presentacion\CatalogoEstilos::para(
    '/' . $rutaActual,
    'interno',
    $rolActual,
    (string) ($modulo ?? '')
);
$clasesCuerpo = \App\Soporte\Presentacion\CatalogoEstilos::clasesCuerpo(
    '/' . $rutaActual,
    $rolActual,
    (string) ($modulo ?? '')
);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($tituloPagina ?? 'Panel') ?> | MD Technology</title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/estilos.css?v=20260927-8')) ?>">
    <?php foreach ($estilosContextuales as $archivoCss): ?>
        <link rel="stylesheet" href="<?= e(asset($archivoCss . '?v=20260927-8')) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
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
<script src="<?= e(asset('assets/js/aplicacion.js')) ?>"></script>
<?php if (in_array($rolActual, ['compras_logistica', 'marketing'], true)): ?>
<script src="<?= e(asset('assets/js/david/panel.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
