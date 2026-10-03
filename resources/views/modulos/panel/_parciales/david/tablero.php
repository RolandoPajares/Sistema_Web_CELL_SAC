<?php

/**
 * @var string $textoPeriodoDashboard
 * @var string $textoDistribucionDashboard
 * @var string $textoDistribucionVaciaDashboard
 * @var array<string, mixed> $interfaz
 * @var array<int, array<string, mixed>> $tendenciaCompras
 * @var array<int, array<string, mixed>> $proveedoresDashboard
 
 * @var string $nombreUsuarioCortoVista
 * @var string $fechaHoyEtiqueta
 * @var string $fechaHoyIso
 * @var string $destinoModuloAccion
 * @var array<array-key, mixed> $opcionesPeriodoDashboard
 * @var mixed $mesesDashboard
 * @var string $atributoTendenciaVaciaOculto
 * @var string $atributoGraficoTendenciaOculto
 * @var string $atributoProveedoresVaciosOculto
 * @var string $atributoGraficosProveedoresOculto
 * @var array<array-key, mixed> $segmentos
 * @var array<array-key, mixed> $navegacionSecundaria
 * @var string $atributoActividadComprasVaciaOculto
 * @var array<array-key, mixed> $actividadCompras
 * @var string $atributoListaPrioridadesComprasOculta
 * @var array<array-key, mixed> $prioridadesCompras
 * @var array<array-key, mixed> $rutasAlertasTablero
 * @var string $atributoPrioridadesComprasVaciasOculto
 */
?>
<header class="admin-page-head admin-page-head--mockup">
    <div>
        <span class="eyebrow">¡Hola, <?= e($nombreUsuarioCortoVista) ?>!</span>
        <h1><?= e($interfaz['titulo']) ?></h1>
        <p><?= e($interfaz['descripcion']) ?></p>
    </div>
    <div class="acciones-cabecera">
        <label class="boton-fecha" title="Seleccionar fecha"><i class="bi bi-calendar3"></i><span data-dashboard-date-label>Hoy, <?= e($fechaHoyEtiqueta) ?></span><i class="bi bi-chevron-down"></i><input class="dashboard-date-input" type="date" value="<?= e($fechaHoyIso) ?>" data-dashboard-date></label>
        <a class="btn btn-primary" href="<?= e(url_interna($destinoModuloAccion)) ?>"><i class="bi bi-plus-lg"></i> Nueva operación</a>
    </div>
</header>

<div class="metricas-mockup">
    <?php foreach ($interfaz['metricas'] as $metrica) : ?>
        <article class="metrica-mockup <?= e($metrica['tono']) ?>">
            <div class="metrica-icono"><i class="bi <?= e($metrica['icono']) ?>"></i></div>
            <div><span><?= e($metrica['etiqueta']) ?></span><strong>—</strong><small>Indicador pendiente</small></div>
        </article>
    <?php endforeach; ?>
</div>

<div class="rejilla-analitica dashboard-compras" data-dashboard-compras>
    <section class="panel panel-grafico panel-grafico--ancho panel-claro">

        <div class="titulo-panel">
            <div>
                <i class="bi bi-bar-chart-fill"></i>
                <h2>
                    <?= e($interfaz['grafico_principal']) ?>
                </h2>
                <small><?= e($textoPeriodoDashboard) ?></small>
            </div>
            <select class="dashboard-period-select" data-dashboard-period disabled title="Pendiente de integrar la consulta por período" aria-label="Período del gráfico">
                <?php foreach ($opcionesPeriodoDashboard as $numeroMeses => $textoPeriodo): ?><option value="<?= $numeroMeses ?>" <?= (int)($mesesDashboard ?? 6) === $numeroMeses ? 'selected' : '' ?>><?= e($textoPeriodo) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="dashboard-empty" <?= $atributoTendenciaVaciaOculto ?>>
            <i class="bi bi-bar-chart"></i><b>Serie del período no disponible</b><span>Esta vista requiere la consulta por período de una próxima integración.</span>
        </div>
        <div class="grafico-barras grafico-barras--dinamico" aria-label="Tendencia de compras reales" <?= $atributoGraficoTendenciaOculto ?>>
            <?php foreach ($tendenciaCompras as $punto): ?>
                <span style="--altura:<?= $punto['altura_vista'] ?>px" title="S/ <?= e($punto['total_vista']) ?>"><b><?= e($punto['fecha_vista']) ?></b></span>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="panel panel-grafico panel-claro">

        <div class="titulo-panel">
            <div>
                <i class="bi bi-pie-chart-fill"></i>
                <h2>
                    <?= e($interfaz['grafico_secundario']) ?>
                </h2>
                <small><?= e($textoDistribucionDashboard) ?></small>
            </div>
        </div>
        <div class="dashboard-empty" <?= $atributoProveedoresVaciosOculto ?>>
            <i class="bi bi-pie-chart"></i><b><?= e($textoDistribucionVaciaDashboard) ?></b><span>Pendiente de conectar la consulta correspondiente.</span>
        </div>
        <div <?= $atributoGraficosProveedoresOculto ?>>
            <div class="grafico-donut grafico-donut--datos" style="--donut:<?= e(implode(', ', $segmentos)) ?>">
                <div>
                    <b><?= count($proveedoresDashboard) ?></b><span>Proveedores</span>
                </div>
            </div>

            <ul class="leyenda-donut">
                <?php foreach ($proveedoresDashboard as $proveedor): ?>
                    <li>
                        <i style="background:<?= e($proveedor['color_vista']) ?>"></i><span><?= e((string)$proveedor['nombre']) ?></span><b><?= $proveedor['porcentaje_vista'] ?>%</b>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</div>
<section class="panel acciones-rapidas">
    <div class="titulo-panel">
        <div><i class="bi bi-lightning-charge-fill"></i>
            <h2>Acciones rápidas</h2>
        </div>
    </div>
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
                    <tr <?= $atributoActividadComprasVaciaOculto ?>>
                        <td colspan="5" class="dashboard-empty-cell">Actividad reciente no disponible en esta versión.</td>
                    </tr>
                    <?php foreach ($actividadCompras as $fila): ?>

                        <tr>
                            <td>
                                <?= e((string)($fila['codigo'] ?? '—')) ?>
                            </td>
                            <td>
                                <?= e((string)($fila['responsable'] ?? '—')) ?>
                            </td>
                            <td>
                                <?= e((string)($fila['actividad'] ?? '—')) ?>
                            </td>
                            <td>
                                <?= e($fila['total_vista']) ?>
                            </td>
                            <td>
                                <span class="estado estado--verde"><?= e($fila['estado_vista']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section class="panel lista-alertas">
        <div class="titulo-panel">
            <div><i class="bi bi-exclamation-triangle-fill"></i>
                <h2>Prioridades de hoy</h2>
            </div>
        </div>
        <div <?= $atributoListaPrioridadesComprasOculta ?>>
            <?php foreach ($prioridadesCompras as $alerta): ?>
                <a href="<?= e(url_interna($rutasAlertasTablero[(string) $alerta['modulo']] ?? $destinoModuloAccion)) ?>"><i class="punto <?= e((string)$alerta['tono']) ?>"></i><span><b><?= e((string)$alerta['titulo']) ?></b><small><?= e((string)$alerta['detalle']) ?></small></span><i class="bi bi-chevron-right"></i>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="dashboard-empty dashboard-empty--compact" <?= $atributoPrioridadesComprasVaciasOculto ?>>
            <b>Prioridades no disponibles</b><span>Pendiente de conectar la consulta de prioridades.</span>
        </div>
    </section>
</div>

<a class="franja-panel" href="<?= e(url_interna('catalog')) ?>"><span><b>La tecnología impulsa grandes negocios</b><small>Gestiona, vende y haz crecer tu empresa con MD Technology Cell.</small></span><strong>Ver catálogo <i class="bi bi-arrow-right"></i></strong>
</a>