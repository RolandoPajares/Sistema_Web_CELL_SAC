<?php
// Selección visual por rol: las rutas y controladores permanecen intactos.
if (in_array(user_role(), ['compras_logistica', 'marketing'], true)) {
    require __DIR__ . '/_parciales/david/tablero.php';
    return;
}
?>
<?php
$usuario = current_user() ?? [];
$rolTablero = \App\Soporte\Autorizacion\AccesoRol::normalize((string) ($usuario['rol'] ?? ''));
$moduloAccion = match ($rolTablero) {
    'compras_logistica' => 'compras',
    'ventas_mayoristas' => 'cotizaciones',
    'ventas_minoristas' => 'ventas',
    'marketing' => 'campanias',
    default => 'productos',
};
?>
<header class="admin-page-head admin-page-head--mockup">
    <div>
        <span class="eyebrow">¡Hola, <?= e(explode(' ', (string) ($usuario['nombre'] ?? 'Usuario'))[0]) ?>!</span>
        <h1><?= e($interfaz['titulo']) ?></h1>
        <p><?= e($interfaz['descripcion']) ?></p>
    </div>
    <div class="acciones-cabecera">
        <button class="boton-fecha" type="button"><i class="bi bi-calendar3"></i> Hoy, <?= e(date('d M Y')) ?> <i class="bi bi-chevron-down"></i></button>
        <a class="btn btn-primary" href="<?= e(url(\App\Soporte\Autorizacion\AccesoRol::destino($rolTablero, $moduloAccion))) ?>"><i class="bi bi-plus-lg"></i> Nueva operación</a>
    </div>
</header>

<div class="metricas-mockup">
    <?php foreach ($interfaz['metricas'] as $indice => $metrica) : ?>
        <article class="metrica-mockup <?= e($metrica[3]) ?>">
            <div class="metrica-icono"><i class="bi <?= e($metrica[2]) ?>"></i></div>
            <div><span><?= e($metrica[0]) ?></span><strong><?= e($metrica[1]) ?></strong><small><b>↑ <?= 12 + ($indice * 5) ?>%</b> vs. periodo anterior</small></div>
            <svg class="mini-tendencia" viewBox="0 0 90 34" role="img" aria-label="Tendencia positiva"><polyline points="2,30 18,23 32,26 49,14 64,18 88,3"/></svg>
        </article>
    <?php endforeach; ?>
</div>

<div class="rejilla-analitica">
    <section class="panel panel-grafico panel-grafico--ancho">
        <div class="titulo-panel"><div><i class="bi bi-bar-chart-fill"></i><h2><?= e($interfaz['grafico_principal']) ?></h2><small>Valor y evolución de los últimos seis meses</small></div><button type="button">Últimos 6 meses <i class="bi bi-chevron-down"></i></button></div>
        <div class="grafico-barras" aria-label="Gráfico de evolución">
            <?php foreach ([42, 58, 50, 72, 65, 91, 76, 96, 82, 100, 88, 112] as $indice => $altura) : ?>
                <span style="--altura:<?= $altura ?>px"><b><?= e(['Ene','Feb','Mar','Abr','May','Jun'][$indice % 6]) ?></b></span>
            <?php endforeach; ?>
            <svg viewBox="0 0 600 180" preserveAspectRatio="none"><polyline points="0,145 100,105 200,122 300,82 400,92 500,38 600,18"/></svg>
        </div>
    </section>
    <section class="panel panel-grafico">
        <div class="titulo-panel"><div><i class="bi bi-pie-chart-fill"></i><h2><?= e($interfaz['grafico_secundario']) ?></h2></div></div>
        <div class="grafico-donut"><div><b><?= e((string) ($resumen['clientes'] ?? 56)) ?></b><span>Total</span></div></div>
        <ul class="leyenda-donut"><li><i class="azul"></i> Tecnología <b>38%</b></li><li><i class="verde"></i> Accesorios <b>26%</b></li><li><i class="ambar"></i> Audio <b>18%</b></li><li><i class="violeta"></i> Otros <b>18%</b></li></ul>
    </section>
</div>

<section class="panel acciones-rapidas">
    <div class="titulo-panel"><div><i class="bi bi-lightning-charge-fill"></i><h2>Acciones rápidas</h2></div></div>
    <div>
        <?php foreach (array_slice($navegacionRol, 1, 6) as $elemento) : ?>
            <a href="<?= e(url(\App\Soporte\Autorizacion\AccesoRol::destino((string) ($usuario['rol'] ?? ''), $elemento['slug']))) ?>"><i class="bi <?= e($elemento['icon']) ?>"></i><span><?= e($elemento['label']) ?></span><i class="bi bi-chevron-right"></i></a>
        <?php endforeach; ?>
    </div>
</section>

<div class="rejilla-listados">
    <section class="panel tabla-resumen"><div class="titulo-panel"><div><i class="bi bi-clock-history"></i><h2>Actividad reciente</h2></div><a href="<?= e(url(\App\Soporte\Autorizacion\AccesoRol::destino($rolTablero, $moduloAccion))) ?>">Ver todas</a></div>
        <div class="table-responsive"><table class="table"><thead><tr><th>Código</th><th>Cliente / Responsable</th><th>Actividad</th><th>Total</th><th>Estado</th></tr></thead><tbody>
            <?php foreach ([['#001245','Juan Pérez','Pedido registrado','S/ 1,299','En proceso'],['#001244','Distribuidora Andina','Cotización enviada','S/ 5,680','Enviada'],['#001243','María Torres','Venta completada','S/ 899','Completada'],['#001242','Inversiones R&G','Recepción registrada','S/ 7,450','Entregada'],['#001241','Carlos Mendoza','Cliente actualizado','S/ 449','Activo']] as $fila) : ?>
                <tr><?php foreach ($fila as $i => $celda) :
                    ?><td><?= $i === 4 ? '<span class="estado estado--verde">' . e($celda) . '</span>' : e($celda) ?></td><?php
                    endforeach; ?></tr>
            <?php endforeach; ?>
        </tbody></table></div>
    </section>
    <section class="panel lista-alertas"><div class="titulo-panel"><div><i class="bi bi-exclamation-triangle-fill"></i><h2>Prioridades de hoy</h2></div></div>
        <?php foreach ([['Stock crítico de iPhone 15','3 unidades disponibles','rojo','inventario'],['Cotizaciones por vencer','6 propuestas pendientes','ambar','cotizaciones'],['Pedidos listos para envío','12 pedidos preparados','verde','pedidos'],['Nuevos clientes','8 registros por revisar','azul','clientes']] as $alerta) : ?>
            <?php $destinoAlerta = \App\Soporte\Autorizacion\AccesoRol::can($rolTablero, $alerta[3]) ? $alerta[3] : $moduloAccion; ?>
            <a href="<?= e(url(\App\Soporte\Autorizacion\AccesoRol::destino($rolTablero, $destinoAlerta))) ?>"><i class="punto <?= e($alerta[2]) ?>"></i><span><b><?= e($alerta[0]) ?></b><small><?= e($alerta[1]) ?></small></span><i class="bi bi-chevron-right"></i></a>
        <?php endforeach; ?>
    </section>
</div>

<a class="franja-panel" href="<?= e(url('catalog')) ?>"><span><b>La tecnología impulsa grandes negocios</b><small>Gestiona, vende y haz crecer tu empresa con MD Technology Cell.</small></span><strong>Ver catálogo <i class="bi bi-arrow-right"></i></strong></a>
