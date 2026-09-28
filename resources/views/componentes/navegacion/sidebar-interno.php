<aside class="sidebar" id="adminSidebar" aria-label="Navegación del panel">
    <a class="admin-brand" href="<?= e(url('panel')) ?>">
        <img src="<?= e(asset('assets/img/marca/logo.jpeg')) ?>" alt="MD Technology Digital Cell">
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
