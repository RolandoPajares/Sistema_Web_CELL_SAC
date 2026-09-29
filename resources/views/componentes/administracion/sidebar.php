<?php
$navegacionAdministrativa = \App\Soporte\Autorizacion\AccesoRol::navigation('administrador');
?>
<aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación administrativa">
    <div class="admin-sidebar-brand">
        <a href="<?= e(url('admin')) ?>" title="MD Technology Digital Cell">
            <span class="admin-brand-mark"><i class="bi bi-phone"></i></span>
            <span class="admin-brand-copy"><strong>TECHNOLOGY <b>CELL</b></strong><small>MD Technology Digital Cell S.A.C.</small></span>
        </a>
    </div>
    <nav class="admin-sidebar-nav">
        <?php foreach ($navegacionAdministrativa as $elemento): ?>
            <?php
            $destino = \App\Soporte\Autorizacion\AccesoRol::destino('administrador', $elemento['slug']);
            $activo = $destino === 'admin' ? $rutaActual === 'admin' : str_starts_with($rutaActual, $destino);
            $etiqueta = $elemento['slug'] === 'dashboard' ? 'Dashboard' : ($elemento['slug'] === 'publicidad' ? 'Campañas' : $elemento['label']);
            ?>
            <a class="<?= $activo ? 'is-active' : '' ?>" href="<?= e(url($destino)) ?>" title="<?= e($etiqueta) ?>">
                <i class="bi <?= e($elemento['icon']) ?>" aria-hidden="true"></i><span><?= e($etiqueta) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-footer">
        <a class="<?= $rutaActual === 'admin/account' ? 'is-active' : '' ?>" href="<?= e(url('admin/account')) ?>" title="Mi cuenta"><i class="bi bi-gear"></i><span>Mi cuenta</span></a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button type="submit" title="Cerrar sesión"><i class="bi bi-box-arrow-right"></i><span>Cerrar sesión</span></button></form>
    </div>
</aside>
