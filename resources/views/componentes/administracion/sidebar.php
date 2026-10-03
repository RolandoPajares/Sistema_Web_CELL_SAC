<?php
/**
 * @var array<array-key, mixed> $navegacionAdministrativa
 * @var bool $cuentaAdministrativaActiva
 */ ?><aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación administrativa">
    <div class="admin-sidebar-brand">
        <a href="<?= e(url_interna('admin')) ?>" title="MD Technology Digital Cell">
            <span class="admin-brand-mark">
                <i class="bi bi-phone"></i>
            </span>
            <span class="admin-brand-copy">
                <strong>TECHNOLOGY <b>CELL</b></strong>
                <small>MD Technology Digital Cell S.A.C.</small>
            </span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <?php foreach ($navegacionAdministrativa as $elemento): ?>
        <a
                class="<?= $elemento['activo'] ? 'is-active' : '' ?>"
                href="<?= e(url_interna($elemento['destino'])) ?>"
                title="<?= e($elemento['etiqueta']) ?>"
            >
                <i class="bi <?= e($elemento['icon']) ?>" aria-hidden="true"></i>
                <span><?= e($elemento['etiqueta']) ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-footer">
        <a
            class="<?= $cuentaAdministrativaActiva ? 'is-active' : '' ?>"
            href="<?= e(url_interna('admin/account')) ?>"
            title="Mi cuenta"
        >
            <i class="bi bi-gear"></i>
            <span>Mi cuenta</span>
        </a>

        <form method="post" action="<?= e(url_interna('logout')) ?>">
            <?= csrf_field() ?>
            <button type="submit" title="Cerrar sesión">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
