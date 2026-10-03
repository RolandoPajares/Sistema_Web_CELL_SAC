<?php

/**
 * @var string $tituloPagina
 * @var array<array-key, mixed> $usuarioActual
 */ ?><header class="admin-topbar">
    <button
        class="admin-icon-button admin-menu-toggle"
        type="button"
        data-admin-sidebar-toggle
        aria-label="Contraer o abrir menú"
        aria-controls="adminSidebar"
        aria-expanded="true">
        <i class="bi bi-list"></i>
    </button>
    <div class="admin-breadcrumb" aria-label="Migas de pan">
        <span>Administración</span>
        <i class="bi bi-chevron-right"></i>
        <strong><?= e($tituloPagina ?? 'Dashboard') ?></strong>
    </div>
    <label class="admin-search">
        <i class="bi bi-search"></i>
        <input
            type="search"
            data-admin-global-search
            placeholder="Buscar en esta página..."
            aria-label="Buscar en esta página">
    </label>
    <button
        class="admin-icon-button"
        type="button"
        disabled
        title="Sin notificaciones disponibles">
        <i class="bi bi-bell"></i>
    </button>
    <div class="admin-user-block">
        <span class="admin-avatar"><i class="bi bi-person-fill"></i></span>
        <span>
            <strong><?= e($usuarioActual['nombre'] ?? 'Administrador') ?></strong>
            <small>Administrador</small>
        </span>
    </div>
</header>