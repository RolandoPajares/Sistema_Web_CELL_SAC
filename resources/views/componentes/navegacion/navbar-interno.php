<?php

/**
 * @var mixed $inicialesUsuarioVista
 * @var array<array-key, mixed> $usuarioActual
 * @var string $etiquetaRol
 */ ?><header class="admin-topbar">
    <button
        class="icon-btn admin-menu-toggle"
        type="button"
        aria-label="Abrir menú del panel"
        aria-controls="adminSidebar"
        aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>
    <label class="admin-global-search">
        <i class="bi bi-search"></i>
        <input type="search" placeholder="Buscar productos, pedidos, clientes...">
    </label>
    <div class="admin-user">
        <span class="admin-avatar">
            <?= e($inicialesUsuarioVista) ?>
        </span>
        <div>
            <b><?= e($usuarioActual['nombre'] ?? 'Usuario') ?></b>
            <small><?= e($etiquetaRol) ?></small>
        </div>
    </div>
    <form method="post" action="<?= e(url_interna('logout')) ?>">
        <?= csrf_field() ?>
        <button class="icon-btn" aria-label="Cerrar sesión" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>
</header>