<?php
$usuarioActual = current_user() ?? [];
$rolActual = user_role();
$navegacion = \App\Soporte\Autorizacion\AccesoRol::navigation($rolActual);
$rutaActual = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($tituloPagina ?? 'Panel') ?> | MD Technology</title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/estilos.css')) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="admin-body">
<div class="admin-layout">
    <aside class="sidebar" id="adminSidebar" aria-label="Navegación del panel">
        <a class="admin-brand" href="<?= e(url('panel')) ?>">
            <img src="<?= e(asset('assets/img/logo.jpeg')) ?>" alt="MD Technology Digital Cell">
            <span>Digital Cell</span>
        </a>
        <nav class="sidebar-nav">
            <?php foreach ($navegacion as $elemento): ?>
                <?php
                $destino = \App\Soporte\Autorizacion\AccesoRol::destino($rolActual, $elemento['slug']);
                $activo = str_contains('/' . $rutaActual, '/' . $destino);
                ?>
                <a class="<?= $activo ? 'active' : '' ?>" href="<?= e(url($destino)) ?>"><i class="bi <?= e($elemento['icon']) ?>" aria-hidden="true"></i><span><?= e($elemento['label']) ?></span></a>
            <?php endforeach; ?>
            <div class="sidebar-divider">Comercio inteligente</div>
            <?php $slugsNavegacion = array_column($navegacion, 'slug'); ?>
            <?php if (!in_array('recomendador', $slugsNavegacion, true)): ?><a href="<?= e(url('smart/recommend')) ?>"><i class="bi bi-lightbulb"></i><span>Recomendador</span></a><?php endif; ?>
            <?php if (!in_array('comparador', $slugsNavegacion, true)): ?><a href="<?= e(url('smart/compare')) ?>"><i class="bi bi-columns-gap"></i><span>Comparador</span></a><?php endif; ?>
            <?php if (!in_array('asistente', $slugsNavegacion, true)): ?><a href="<?= e(url('smart/assistant')) ?>"><i class="bi bi-robot"></i><span>MD Assistant</span></a><?php endif; ?>
        </nav>
        <div class="sidebar-support"><i class="bi bi-headset"></i><div><b>Soporte interno</b><small>Ayuda para tu operación</small></div></div>
    </aside>
    <div class="admin-shell">
        <header class="admin-topbar">
            <button class="icon-btn admin-menu-toggle" type="button" aria-label="Abrir menú del panel" aria-controls="adminSidebar" aria-expanded="false"><i class="bi bi-list"></i></button>
            <label class="admin-global-search"><i class="bi bi-search"></i><input type="search" placeholder="Buscar productos, pedidos, clientes..."></label>
            <div class="admin-user"><span class="admin-avatar"><?= e(mb_strtoupper(mb_substr((string) ($usuarioActual['nombre'] ?? 'U'), 0, 2))) ?></span><div><b><?= e($usuarioActual['nombre'] ?? 'Usuario') ?></b><small><?= e(\App\Soporte\Autorizacion\AccesoRol::label($rolActual)) ?></small></div></div>
            <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="icon-btn" aria-label="Cerrar sesión" title="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></button></form>
        </header>
        <main class="admin-main"><?= $contenido ?></main>
    </div>
</div>
<div class="sidebar-backdrop" data-sidebar-close></div>
<button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
<script src="<?= e(asset('assets/js/aplicacion.js')) ?>"></script>
</body>
</html>
