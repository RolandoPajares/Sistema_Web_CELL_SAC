<?php

/**
 * El contenido del módulo se selecciona en PanelRolController y se renderiza por separado.
 * @var string $tituloCuenta
 * @var string $nombreCortoCuenta
 * @var string $tipoCuentaVista
 * @var array<int, array<string, mixed>> $navegacion
 * @var string $atributoCuentaBreadcrumbModuloOculto
 * @var string $contenidoCuentaHtml
 
 * @var array<array-key, mixed> $interfaz
 * @var mixed $modulo
 */
?>
<section class="cuenta-fondo">
    <div class="container cuenta-migas">
        <a href="<?= e(url_interna()) ?>">Inicio</a>
        <i class="bi bi-chevron-right"></i>
        <span><?= e($tituloCuenta) ?></span>
        <i class="bi bi-chevron-right" <?= $atributoCuentaBreadcrumbModuloOculto ?>></i>
        <b <?= $atributoCuentaBreadcrumbModuloOculto ?>><?= e($interfaz['titulo'] ?? '') ?></b>
    </div>
    <div class="container cuenta-diseno">
        <aside class="cuenta-lateral">
            <div class="cuenta-identidad">
                <span><i class="bi bi-person-fill"></i></span>
                <div><b>Hola, <?= e($nombreCortoCuenta) ?></b><small>Cliente <?= e($tipoCuentaVista) ?></small><em><i></i> Cuenta activa</em></div>
            </div>
            <nav>
                <?php foreach ($navegacion as $opcionCuenta): ?>
                    <a class="<?= $modulo === $opcionCuenta['slug'] ? 'activo' : '' ?>" href="<?= e(url_interna($opcionCuenta['destino'])) ?>"><i class="bi <?= e($opcionCuenta['icon']) ?>"></i><span><?= e($opcionCuenta['label']) ?></span></a>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="<?= e(url_interna('logout')) ?>"><?= csrf_field() ?><button><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form>
            <a class="cuenta-anuncio" href="<?= e(url_interna('catalog')) ?>"><b>Tecnología que te acompaña siempre</b><span>Ver catálogo <i class="bi bi-arrow-right"></i></span></a>
        </aside>
        <main class="cuenta-contenido">
            <?= $contenidoCuentaHtml ?>
        </main>
    </div>
</section>