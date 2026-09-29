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
        <label class="boton-fecha" title="Seleccionar fecha"><i class="bi bi-calendar3"></i><span data-dashboard-date-label>Hoy, <?= e(date('d M Y')) ?></span><i class="bi bi-chevron-down"></i><input class="dashboard-date-input" type="date" value="<?= e(date('Y-m-d')) ?>" data-dashboard-date></label>
        <a class="btn btn-primary" href="<?= e(url(\App\Soporte\Autorizacion\AccesoRol::destino($rolTablero, $moduloAccion))) ?>"><i class="bi bi-plus-lg"></i> Nueva operación</a>
    </div>
</header>

<div class="metricas-mockup">
    <?php foreach ($interfaz['metricas'] as $indice => $metrica) : ?>
        <article class="metrica-mockup <?= e($metrica[3]) ?>">
            <div class="metrica-icono"><i class="bi <?= e($metrica[2]) ?>"></i></div>
            <div><span><?= e($metrica[0]) ?></span><strong>—</strong><small>Indicador pendiente</small></div>
        </article>
    <?php endforeach; ?>
</div>

<?php
$datosCompras = is_array($dashboardCompras ?? null) ? $dashboardCompras : ['tendencia'=>[], 'proveedores'=>[]];
$tendenciaCompras = $datosCompras['tendencia'] ?? [];
$proveedoresDashboard = $datosCompras['proveedores'] ?? [];
$maxCompra = 0.0;
foreach ($tendenciaCompras as $punto) { $maxCompra = max($maxCompra, (float)($punto['total'] ?? 0)); }
$totalProveedores = array_sum(array_map(static fn($x)=>(float)($x['total'] ?? 0), $proveedoresDashboard));
$coloresDonut = ['#1687ff','#22c58b','#ffb72f','#8b4fe8'];
$acumulado = 0.0; $segmentos = [];
foreach ($proveedoresDashboard as $i=>$prov) { $pct=$totalProveedores>0 ? ((float)$prov['total']/$totalProveedores*100) : 0; $segmentos[]=$coloresDonut[$i%4].' '.$acumulado.'% '.($acumulado+$pct).'%'; $acumulado += $pct; }
?>
<div class="rejilla-analitica dashboard-compras" data-dashboard-compras>
    <section class="panel panel-grafico panel-grafico--ancho panel-claro">
        <div class="titulo-panel"><div><i class="bi bi-bar-chart-fill"></i><h2><?= e($interfaz['grafico_principal']) ?></h2><small><?= $rolTablero === 'marketing' ? 'Rendimiento por período pendiente de conectar' : 'Compras por período pendientes de conectar' ?></small></div>
            <select class="dashboard-period-select" data-dashboard-period disabled title="Pendiente de integrar la consulta por período" aria-label="Período del gráfico">
                <?php foreach ([3=>'Últimos 3 meses',6=>'Últimos 6 meses',12=>'Últimos 12 meses'] as $n=>$texto): ?><option value="<?= $n ?>" <?= (int)($mesesDashboard ?? 6)===$n?'selected':'' ?>><?= e($texto) ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php if ($tendenciaCompras): ?>
        <div class="grafico-barras grafico-barras--dinamico" aria-label="Tendencia de compras reales">
            <?php foreach ($tendenciaCompras as $punto): $altura=$maxCompra>0?max(12,(int)(((float)$punto['total']/$maxCompra)*145)):12; $fecha=DateTime::createFromFormat('Y-m',(string)$punto['periodo']); ?>
                <span style="--altura:<?= $altura ?>px" title="S/ <?= e(number_format((float)$punto['total'],2,'.',',')) ?>"><b><?= e($fecha ? ['Jan'=>'Ene','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Abr','May'=>'May','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Ago','Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dic'][$fecha->format('M')] : (string)$punto['periodo']) ?></b></span>
            <?php endforeach; ?>
        </div>
        <?php else: ?><div class="dashboard-empty"><i class="bi bi-bar-chart"></i><b>Serie del período no disponible</b><span>Esta vista requiere la consulta por período de una próxima integración.</span></div><?php endif; ?>
    </section>
    <section class="panel panel-grafico panel-claro">
        <div class="titulo-panel"><div><i class="bi bi-pie-chart-fill"></i><h2><?= e($interfaz['grafico_secundario']) ?></h2><small><?= $rolTablero === 'marketing' ? 'Distribución por canales pendiente de conectar' : 'Participación por monto comprado' ?></small></div></div>
        <?php if ($proveedoresDashboard): ?>
        <div class="grafico-donut grafico-donut--datos" style="--donut:<?= e(implode(', ', $segmentos)) ?>"><div><b><?= count($proveedoresDashboard) ?></b><span>Proveedores</span></div></div>
        <ul class="leyenda-donut"><?php foreach ($proveedoresDashboard as $i=>$prov): $pct=$totalProveedores>0?round((float)$prov['total']/$totalProveedores*100):0; ?><li><i style="background:<?= e($coloresDonut[$i%4]) ?>"></i><span><?= e((string)$prov['nombre']) ?></span><b><?= $pct ?>%</b></li><?php endforeach; ?></ul>
        <?php else: ?><div class="dashboard-empty"><i class="bi bi-pie-chart"></i><b><?= $rolTablero === 'marketing' ? 'Distribución no disponible' : 'Distribución de proveedores no disponible' ?></b><span>Pendiente de conectar la consulta correspondiente.</span></div><?php endif; ?>
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
            <?php $actividadTablero = $rolTablero === 'compras_logistica' ? ($actividadCompras ?? []) : []; ?>
            <?php if ($actividadTablero): foreach ($actividadTablero as $fila): ?>
                <tr><td><?= e((string)($fila['codigo'] ?? '—')) ?></td><td><?= e((string)($fila['responsable'] ?? '—')) ?></td><td><?= e((string)($fila['actividad'] ?? '—')) ?></td><td><?= (float)($fila['total'] ?? 0)>0 ? 'S/ '.e(number_format((float)$fila['total'],2,'.',',')) : '—' ?></td><td><span class="estado estado--verde"><?= e(ucfirst((string)($fila['estado'] ?? 'Registrado'))) ?></span></td></tr>
            <?php endforeach; else: ?><tr><td colspan="5" class="dashboard-empty-cell">Actividad reciente no disponible en esta versión.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
    <section class="panel lista-alertas"><div class="titulo-panel"><div><i class="bi bi-exclamation-triangle-fill"></i><h2>Prioridades de hoy</h2></div></div>
        <?php $prioridadesTablero = $rolTablero === 'compras_logistica' ? ($prioridadesCompras ?? []) : []; ?>
        <?php if ($prioridadesTablero): foreach ($prioridadesTablero as $alerta): ?>
            <?php $destinoAlerta = \App\Soporte\Autorizacion\AccesoRol::can($rolTablero, (string)$alerta['modulo']) ? (string)$alerta['modulo'] : $moduloAccion; ?>
            <a href="<?= e(url(\App\Soporte\Autorizacion\AccesoRol::destino($rolTablero, $destinoAlerta))) ?>"><i class="punto <?= e((string)$alerta['tono']) ?>"></i><span><b><?= e((string)$alerta['titulo']) ?></b><small><?= e((string)$alerta['detalle']) ?></small></span><i class="bi bi-chevron-right"></i></a>
        <?php endforeach; else: ?><div class="dashboard-empty dashboard-empty--compact"><b>Prioridades no disponibles</b><span>Pendiente de conectar la consulta de prioridades.</span></div><?php endif; ?>
    </section>
</div>

<a class="franja-panel" href="<?= e(url('catalog')) ?>"><span><b>La tecnología impulsa grandes negocios</b><small>Gestiona, vende y haz crecer tu empresa con MD Technology Cell.</small></span><strong>Ver catálogo <i class="bi bi-arrow-right"></i></strong></a>
