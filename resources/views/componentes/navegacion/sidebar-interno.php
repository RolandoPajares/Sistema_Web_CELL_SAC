<?php

/**
 * @var string $destinoInicioPanel
 * @var array<array-key, mixed> $navegacion
 * @var array<array-key, mixed> $enlacesComercioInteligente
 */ ?><aside class="sidebar" id="adminSidebar" aria-label="Navegación del panel">
    <a class="admin-brand" href="<?= e(url_interna($destinoInicioPanel)) ?>">
        <img src="<?= e(url_recurso_estatico('assets/img/marca/logo.jpeg')) ?>" alt="MD Technology Digital Cell">
        <span>Digital Cell</span>
    </a>
    <nav class="sidebar-nav">
        <?php foreach ($navegacion as $elemento): ?>
            <a
                class="<?= $elemento['activo'] ? 'active' : '' ?>"
                href="<?= e(url_interna($elemento['destino'])) ?>">
                <i class="bi <?= e($elemento['icon']) ?>" aria-hidden="true"></i>
                <span><?= e($elemento['label']) ?></span>
            </a>
        <?php endforeach; ?>

        <div class="sidebar-divider">Comercio inteligente</div>
        <?php foreach ($enlacesComercioInteligente as $enlace): ?>
            <a href="<?= e(url_interna($enlace['destino'])) ?>">
                <i class="bi <?= e($enlace['icono']) ?>"></i>
                <span><?= e($enlace['etiqueta']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-support"><i class="bi bi-headset"></i>
        <div><b>Soporte interno</b><small>Ayuda para tu operación</small></div>
    </div>
</aside>