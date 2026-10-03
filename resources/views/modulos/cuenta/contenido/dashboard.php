<?php
/** @var array<string, mixed> $interfaz 
 * @var string $iconoBannerCuenta
 * @var string $tipoCuentaVista
 * @var string $tituloCuenta
 * @var string $descripcionCuentaVista
 * @var array<array-key, mixed> $accionesCabeceraCuenta
 * @var string $destinoPedidosCuenta
 * @var array<array-key, mixed> $accesosRapidosCuenta
 */
?>
<header class="cuenta-banner">
    <div class="cuenta-banner-texto">
        <span class="cuenta-banner-etiqueta"><i class="bi <?= e($iconoBannerCuenta) ?>"></i> Cliente <?= e($tipoCuentaVista) ?></span>
        <h1><?= e($tituloCuenta) ?></h1>
        <p><?= e($descripcionCuentaVista) ?></p>
        <div class="cuenta-banner-botones">
            <?php foreach ($accionesCabeceraCuenta as $accionCuenta): ?>
                <a class="<?= e($accionCuenta['clase']) ?>" href="<?= e(url_interna($accionCuenta['destino'])) ?>"><i class="bi <?= e($accionCuenta['icono']) ?>"></i> <?= e($accionCuenta['etiqueta']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <img src="<?= e(url_recurso_estatico('assets/img/publico/inicio/secciones/hero-devices.png')) ?>" alt="Celulares y accesorios">
</header>
<div class="cuenta-resumen">
    <?php foreach ($interfaz['metricas'] as $metrica): ?>
        <article><i class="bi <?= e($metrica['icono']) ?>"></i><div><strong><?= e($metrica['valor']) ?></strong><span><?= e($metrica['etiqueta']) ?></span></div></article>
    <?php endforeach; ?>
</div>
<section class="cuenta-panel">
    <div class="cuenta-panel-titulo"><h2>Pedidos recientes</h2><a href="<?= e(url_interna($destinoPedidosCuenta)) ?>">Ver todos <i class="bi bi-chevron-right"></i></a></div>
    <?php require dirname(__DIR__) . '/_parciales/lista-pedidos.php'; ?>
</section>
<div class="cuenta-dos-columnas">
    <section class="cuenta-panel">
        <div class="cuenta-panel-titulo"><h2>Accesos rápidos</h2></div>
        <div class="cuenta-accesos">
            <?php foreach ($accesosRapidosCuenta as $acceso): ?>
                <a href="<?= e(url_interna($acceso['destino'])) ?>"><i class="bi <?= e($acceso['icono']) ?>"></i> <?= e($acceso['etiqueta']) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="cuenta-panel cuenta-recomendado"><i class="bi bi-stars"></i><div><h2>Recomendado para ti</h2><p>Encuentra equipos según tus compras anteriores.</p><a class="btn btn-primary" href="<?= e(url_interna('smart/recommend')) ?>">Probar SmartMatch</a></div></section>
</div>
