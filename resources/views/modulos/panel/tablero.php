<?php
/** @var array<string, mixed> $interfaz 
 * @var array<array-key, mixed> $rolActual
 * @var string $nombreUsuarioCortoVista
 * @var string $fechaHoyEtiqueta
 * @var string $destinoModuloAccion
 * @var array<array-key, mixed> $resumen
 * @var array<array-key, mixed> $navegacionSecundaria
 * @var array<array-key, mixed> $rutasAlertasTablero
 */
// Selección visual por rol: las rutas y controladores permanecen intactos.
if (in_array($rolActual, ['compras_logistica', 'marketing'], true)) {
    require __DIR__ . '/_parciales/david/tablero.php';
    return;
}
?>
<header class="admin-page-head admin-page-head--mockup">
    <div>
        <span class="eyebrow">¡Hola, <?= e($nombreUsuarioCortoVista) ?>!</span>
        <h1><?= e($interfaz['titulo']) ?></h1>
        <p><?= e($interfaz['descripcion']) ?></p>
    </div>
    <div class="acciones-cabecera">
        <button class="boton-fecha" type="button"><i class="bi bi-calendar3"></i> Hoy, <?= e($fechaHoyEtiqueta) ?> <i class="bi bi-chevron-down"></i></button>
        <a class="btn btn-primary" href="<?= e(url_interna($destinoModuloAccion)) ?>"><i class="bi bi-plus-lg"></i> Nueva operación</a>
    </div>
</header>

<div class="metricas-mockup">
    <?php foreach ($interfaz['metricas'] as $metrica) : ?>
    <article class="metrica-mockup <?= e($metrica['tono']) ?>">
        <div class="metrica-icono"><i class="bi <?= e($metrica['icono']) ?>"></i></div>
        <div><span><?= e($metrica['etiqueta']) ?></span><strong><?= e($metrica['valor']) ?></strong><small><b>↑ <?= $metrica['variacion_vista'] ?>%</b> vs. periodo anterior</small></div>
            <svg class="mini-tendencia" viewBox="0 0 90 34" role="img" aria-label="Tendencia positiva"><polyline points="2,30 18,23 32,26 49,14 64,18 88,3"/></svg>
    </article>
    <?php endforeach; ?>
</div>

<div class="rejilla-analitica">
    <section class="panel panel-grafico panel-grafico--ancho">

        <div class="titulo-panel">
            <div>
                <i class="bi bi-bar-chart-fill"></i>
                <h2>
                <?= e($interfaz['grafico_principal']) ?>
                </h2>
                <small>Valor y evolución de los últimos seis meses</small>
            </div>
            <button type="button">Últimos 6 meses <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="grafico-barras" aria-label="Gráfico de evolución">
            <?php foreach ($interfaz['serie_demo'] as $punto): ?>
                <span style="--altura:<?= $punto['altura'] ?>px"><b><?= e($punto['mes']) ?></b></span>
            <?php endforeach; ?>
            <svg viewBox="0 0 600 180" preserveAspectRatio="none"><polyline points="0,145 100,105 200,122 300,82 400,92 500,38 600,18"/></svg>
        </div>
    </section>
    <section class="panel panel-grafico">
        <div class="titulo-panel"><div><i class="bi bi-pie-chart-fill"></i><h2><?= e($interfaz['grafico_secundario']) ?></h2></div></div>
        <div class="grafico-donut"><div><b><?= e((string) ($resumen['clientes'] ?? 56)) ?></b><span>Total</span></div></div>

        <ul class="leyenda-donut">
            <?php foreach ($interfaz['leyenda_demo'] as $fila): ?>
                <li><i class="<?= e($fila['color']) ?>"></i> <?= e($fila['etiqueta']) ?> <b><?= e($fila['porcentaje']) ?></b></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>

<section class="panel acciones-rapidas">
    <div class="titulo-panel"><div><i class="bi bi-lightning-charge-fill"></i><h2>Acciones rápidas</h2></div></div>
    <div>
        <?php foreach ($navegacionSecundaria as $elemento) : ?>

        <a href="<?= e(url_interna($elemento['destino'])) ?>"><i class="bi <?= e($elemento['icon']) ?>"></i><span><?= e($elemento['label']) ?></span><i class="bi bi-chevron-right"></i>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<div class="rejilla-listados">

    <section class="panel tabla-resumen">
        <div class="titulo-panel">
            <div>
                <i class="bi bi-clock-history"></i>
                <h2>
                Actividad reciente
                </h2>
            </div>
            <a href="<?= e(url_interna($destinoModuloAccion)) ?>">Ver todas
            </a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                Código
                        </th>
                        <th>
                Cliente / Responsable
                        </th>
                        <th>
                Actividad
                        </th>
                        <th>
                Total
                        </th>
                        <th>
                Estado
                        </th>
                    </tr>
                </thead>
                <tbody>
            <?php foreach ($interfaz['actividad_demo'] as $fila): ?>
                    <tr>
                        <td><?= e($fila['codigo']) ?></td>
                        <td><?= e($fila['responsable']) ?></td>
                        <td><?= e($fila['actividad']) ?></td>
                        <td><?= e($fila['total']) ?></td>
                        <td><span class="estado estado--verde"><?= e($fila['estado']) ?></span></td>
                    </tr>
            <?php endforeach; ?>
                </tbody></table></div>
    </section>
    <section class="panel lista-alertas"><div class="titulo-panel"><div><i class="bi bi-exclamation-triangle-fill"></i><h2>Prioridades de hoy</h2></div></div>
        <?php foreach ($interfaz['prioridades_demo'] as $alerta): ?>

        <a href="<?= e(url_interna($rutasAlertasTablero[$alerta['modulo']] ?? $destinoModuloAccion)) ?>"><i class="punto <?= e($alerta['tono']) ?>"></i><span><b><?= e($alerta['titulo']) ?></b><small><?= e($alerta['detalle']) ?></small></span><i class="bi bi-chevron-right"></i>
        </a>
        <?php endforeach; ?>
    </section>
</div>

<a class="franja-panel" href="<?= e(url_interna('catalog')) ?>"><span><b>La tecnología impulsa grandes negocios</b><small>Gestiona, vende y haz crecer tu empresa con MD Technology Cell.</small></span><strong>Ver catálogo <i class="bi bi-arrow-right"></i></strong>
</a>
