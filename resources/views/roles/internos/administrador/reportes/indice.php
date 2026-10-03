<?php
/**
 * @var array<array-key, mixed> $tiposReporte
 * @var array<array-key, mixed> $reporte
 * @var string $atributoNotaRangoOculta
 * @var string $tipoReporteMinuscula
 * @var string $atributoMensajeSerieOculto
 * @var string $atributoGraficoSerieOculto
 * @var string $tipoReporte
 * @var array<array-key, mixed> $columnas
 * @var string $atributoDetalleReporteVacioOculto
 */ ?><div class="admin-reports-page">
    <header class="admin-page-head">
        <div><h1>Centro de reportes <i class="bi bi-bar-chart"></i></h1><p>Genera y visualiza reportes con datos reales del negocio.</p></div>
        <div class="admin-page-actions"><button class="admin-secondary-button" type="button" disabled aria-disabled="true">Exportación PDF · Próxima iteración</button></div>
    </header>

    <form class="admin-panel admin-report-filters" method="get" action="<?= e(url_interna('admin/reports')) ?>">

        <div class="admin-report-filter-field">
            <label for="report-type">Tipo de reporte</label>
            <select id="report-type" name="tipo">
                <?php foreach ($tiposReporte as $valor => $texto): ?>
                    <option value="<?= e($valor) ?>" <?= $reporte['tipo'] === $valor ? 'selected' : '' ?>>
                        <?= e($texto) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-report-filter-field">
            <label for="report-from">Desde</label><input id="report-from" type="date" name="desde" value="<?= e($reporte['desde']) ?>" required>
        </div>

        <div class="admin-report-filter-field">
            <label for="report-to">Hasta</label><input id="report-to" type="date" name="hasta" value="<?= e($reporte['hasta']) ?>" required>
        </div>
        <button class="admin-primary-button admin-report-generate" type="submit"><i class="bi bi-file-earmark-bar-graph"></i> Generar reporte</button>
        <p class="admin-report-filter-note" <?= $atributoNotaRangoOculta ?>>
            <i class="bi bi-info-circle"></i> El reporte de <?= e($tipoReporteMinuscula) ?> presenta información actual; el rango de fechas no filtra esos registros.
        </p>
    </form>

    <?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

    <div class="admin-report-results">
        <section class="admin-panel admin-report-chart-panel">

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-graph-up"></i>
                    <div>
                        <h2>
                <?= e($reporte['titulo_grafico_vista']) ?>
                        </h2>
                        <p>
                Del <?= e($reporte['desde_vista']) ?> al <?= e($reporte['hasta_vista']) ?>
                        </p>
                    </div>
                </div>
            </header>
            <div class="admin-empty admin-report-chart-empty" <?= $atributoMensajeSerieOculto ?>><?= e($reporte['mensaje_serie_vista']) ?></div>
            <div class="admin-report-chart-area" <?= $atributoGraficoSerieOculto ?>>
                <div class="admin-report-y-axis" aria-hidden="true">
                <?php foreach ($reporte['eje_vista'] as $etiquetaEje): ?><span><?= e($etiquetaEje) ?></span><?php endforeach; ?>
                </div>

                <div class="admin-chart" role="img" aria-label="Gráfico de ventas diarias para el período seleccionado">
                <?php foreach ($reporte['serie'] as $fila): ?>
                    <div class="admin-chart-column" title="<?= e($fila['fecha_vista']) ?>: <?= e(formatear_dinero($fila['total'])) ?>">
                <i style="--chart-height:<?= $fila['altura_vista'] ?>%"></i><span><?= e($fila['fecha_corta_vista']) ?></span>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="admin-panel admin-report-preview" data-admin-table-container>

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-file-earmark-text"></i>
                    <div>
                        <h2>
                Vista previa del reporte
                        </h2>
                        <p>
                <?= e($tipoReporte) ?> · <?= count($reporte['detalle']) ?> filas obtenidas
                        </p>
                    </div>
                </div>
            </header>

            <div class="admin-report-preview-meta">
                <span><?= e($reporte['desde_vista']) ?> – <?= e($reporte['hasta_vista']) ?></span><span><?= count($reporte['detalle']) ?> resultados</span>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-table>
                    <thead>
                        <tr>
                <?php foreach ($columnas as $etiqueta): ?>
                            <th>
                <?= e($etiqueta) ?>
                            </th>
                <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr <?= $atributoDetalleReporteVacioOculto ?>>
                            <td colspan="<?= count($columnas) ?>" class="admin-table-empty">
                No se encontraron resultados para este reporte.
                            </td>
                        </tr>
                <?php foreach ($reporte['detalle'] as $fila): ?>
                        <tr data-data-row>
                <?php foreach ($fila['celdas_vista'] as $celda): ?>
                            <td>
                    <span class="admin-status <?= e($celda['clase_estado']) ?>" <?= $celda['es_estado'] ? '' : 'hidden' ?>><?= e($celda['valor']) ?></span>
                    <span <?= $celda['es_estado'] ? 'hidden' : '' ?>><?= e($celda['valor']) ?></span>
                            </td>
                <?php endforeach; ?>
                        </tr>
                <?php endforeach; ?>
                    </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>
    </div>
</div>
