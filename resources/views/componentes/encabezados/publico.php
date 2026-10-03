<?php

/**
 * @var array<array-key, mixed> $enlacesEncabezado
 * @var mixed $busquedaEncabezado
 * @var string $atributoCarritoOculto
 * @var int $carritoUnidades
 * @var string $atributoCuentaOculto
 * @var string $etiquetaCuentaEncabezado
 * @var array<array-key, mixed> $usuario
 * @var string $atributoInvitadoOculto
 */ ?><header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="<?= e(url_interna()) ?>">
            <img src="<?= e(url_recurso_estatico('assets/img/marca/logo.jpeg')) ?>" alt="MD Technology Digital Cell">
        </a>
        <button
            class="menu-toggle"
            type="button"
            aria-label="Abrir menú"
            aria-controls="navegacionPrincipal"
            aria-expanded="false">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        <div class="navlinks" id="navegacionPrincipal">
            <?php foreach ($enlacesEncabezado as $enlace): ?>
                <a
                    class="<?= $enlace['activo'] ? 'active' : '' ?>"
                    href="<?= e(url_interna($enlace['destino'])) ?>"
                    <?= $enlace['activo'] ? 'aria-current="page"' : '' ?>>
                    <?= e($enlace['etiqueta']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <form class="header-search" method="get" action="<?= e(url_interna('catalog')) ?>" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="search"
                name="q"
                value="<?= e($busquedaEncabezado) ?>"
                placeholder="Buscar productos, marcas..."
                aria-label="Buscar productos o marcas">
        </form>
        <div class="nav-actions">
            <a class="icon-btn" <?= $atributoCarritoOculto ?> href="<?= e(url_interna('cart')) ?>" title="Carrito" aria-label="Ver carrito">
                <i class="bi bi-cart3" aria-hidden="true"></i>
                <sup><?= (int) $carritoUnidades ?></sup>
            </a>
            <a class="header-account" <?= $atributoCuentaOculto ?> href="<?= e(url_interna('panel')) ?>">
                <i class="bi bi-person-circle" aria-hidden="true"></i>
                <span>
                    <b><?= e($etiquetaCuentaEncabezado) ?></b>
                    <small><?= e((string) ($usuario['nombre'] ?? 'Cliente')) ?></small>
                </span>
            </a>
            <form <?= $atributoCuentaOculto ?> method="post" action="<?= e(url_interna('logout')) ?>">
                <?= csrf_field() ?>
                <button class="icon-btn" title="Salir" aria-label="Cerrar sesión">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                </button>
            </form>
            <a class="btn btn-ghost" <?= $atributoInvitadoOculto ?> href="<?= e(url_interna('login')) ?>">Ingresar</a>
            <a class="btn btn-primary" <?= $atributoInvitadoOculto ?> href="<?= e(url_interna('register')) ?>">Registrarme</a>
        </div>
    </nav>
</header>