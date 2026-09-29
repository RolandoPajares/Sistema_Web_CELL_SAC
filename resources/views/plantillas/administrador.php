<?php
$usuarioActual = current_user() ?? [];
$rolActual = user_role();
$rutaActual = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$rutaActual = preg_replace('#^.*?/public/#', '', $rutaActual) ?: 'admin';
$clasesCuerpo = \App\Soporte\Presentacion\CatalogoEstilos::clasesCuerpo('/' . $rutaActual, $rolActual);
$estilosContextuales = \App\Soporte\Presentacion\CatalogoEstilos::para('/' . $rutaActual, 'administrador', $rolActual);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($tituloPagina ?? 'Administración') ?> | MD Technology</title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/estilos.css?v=20260929-1')) ?>">
    <?php foreach ($estilosContextuales as $estilo): ?>
        <link rel="stylesheet" href="<?= e(asset($estilo . '?v=20260929-1')) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
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
<script src="<?= e(asset('assets/js/administrador.js?v=20260929-1')) ?>"></script>
</body>
</html>
