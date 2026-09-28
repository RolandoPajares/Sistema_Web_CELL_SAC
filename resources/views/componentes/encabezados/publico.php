<?php

/**
 * Encabezado público reutilizable.
 * Replica la navegación de los mockups y adapta la herramienta destacada a cada pantalla.
 */
$rutaEncabezado = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$esPublicidad = str_ends_with(rtrim($rutaEncabezado, '/'), '/smart/ads');
$busquedaEncabezado = is_scalar($_GET['q'] ?? null) ? trim((string) $_GET['q']) : '';
$rolEncabezado = user_role();
$enlacesEncabezado = [
    ['etiqueta' => 'Inicio', 'destino' => '', 'activo' => $rutaEncabezado === '/' || str_ends_with($rutaEncabezado, '/public/')],
    ['etiqueta' => 'Catálogo', 'destino' => 'catalog', 'activo' => str_contains($rutaEncabezado, '/catalog') || str_contains($rutaEncabezado, '/products/')],
    ['etiqueta' => 'SmartMatch', 'destino' => 'smart/recommend', 'activo' => str_contains($rutaEncabezado, '/smart/recommend')],
    ['etiqueta' => 'Mayorista', 'destino' => 'mayorista', 'activo' => str_contains($rutaEncabezado, '/mayorista')],
    ['etiqueta' => 'Nosotros', 'destino' => 'about', 'activo' => str_contains($rutaEncabezado, '/about')],
];
if ($esPublicidad) {
    $enlacesEncabezado[] = ['etiqueta' => 'MD Ads', 'destino' => 'smart/ads', 'activo' => true];
}
?>
<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="<?= e(url()) ?>">
            <img src="<?= e(asset('assets/img/marca/logo.jpeg')) ?>" alt="MD Technology Digital Cell">
        </a>
        <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-controls="navegacionPrincipal" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i></button>
        <div class="navlinks" id="navegacionPrincipal">
            <?php foreach ($enlacesEncabezado as $enlace): ?>
                <a class="<?= $enlace['activo'] ? 'active' : '' ?>" href="<?= e(url($enlace['destino'])) ?>" <?= $enlace['activo'] ? ' aria-current="page"' : '' ?>><?= e($enlace['etiqueta']) ?></a>
            <?php endforeach; ?>
        </div>
        <form class="header-search" method="get" action="<?= e(url('catalog')) ?>" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= e($busquedaEncabezado) ?>" placeholder="Buscar productos, marcas..." aria-label="Buscar productos o marcas">
        </form>
        <div class="nav-actions">
            <?php if (!$usuario || $rolEncabezado === 'cliente_minorista'): ?>
                <a class="icon-btn" href="<?= e(url('cart')) ?>" title="Carrito" aria-label="Ver carrito">
                    <i class="bi bi-cart3" aria-hidden="true"></i><sup><?= cart_count() ?></sup>
                </a>
            <?php endif; ?>
            <?php if ($usuario): ?>
                <a class="header-account" href="<?= e(url('panel')) ?>"><i class="bi bi-person-circle" aria-hidden="true"></i><span><b><?= is_admin() ? 'Panel' : 'Mi cuenta' ?></b><small><?= e((string) ($usuario['nombre'] ?? 'Cliente')) ?></small></span></a>
                <form method="post" action="<?= e(url('logout')) ?>">
                    <?= csrf_field() ?>
                    <button class="icon-btn" title="Salir" aria-label="Cerrar sesión">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>
            <?php else: ?>
                <a class="btn btn-ghost" href="<?= e(url('login')) ?>">Ingresar</a>
                <a class="btn btn-primary" href="<?= e(url('register')) ?>">Registrarme</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
