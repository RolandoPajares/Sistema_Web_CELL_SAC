<?php
$tarjetasKpi = $reporte['indicadores'];
$maximo = max(array_merge([1.0], array_map(static fn (array $fila): float => (float) $fila['total'], $reporte['serie'])));
$tiposReporte = ['ventas' => 'Ventas', 'pedidos' => 'Pedidos', 'inventario' => 'Inventario', 'productos' => 'Productos', 'clientes' => 'Clientes'];
$tipoReporte = $tiposReporte[$reporte['tipo']] ?? $tiposReporte['ventas'];
$columnas = match ($reporte['tipo']) {
    'pedidos' => ['id' => 'Pedido', 'cliente' => 'Cliente', 'fecha' => 'Fecha', 'total' => 'Total', 'estado' => 'Estado'],
    'inventario' => ['producto' => 'Producto', 'categoria' => 'Categoría', 'stock' => 'Stock', 'estado' => 'Estado'],
    'productos' => ['producto' => 'Producto', 'categoria' => 'Categoría', 'precio' => 'Precio', 'stock' => 'Stock', 'estado' => 'Estado'],
    'clientes' => ['cliente' => 'Cliente', 'documento' => 'Documento', 'tipo' => 'Tipo', 'correo' => 'Correo', 'telefono' => 'Teléfono', 'estado' => 'Estado'],
    default => ['producto' => 'Producto', 'categoria' => 'Categoría', 'cantidad' => 'Cantidad', 'total' => 'Total'],
};
?>
<div class="admin-reports-page">
    <header class="admin-page-head">
        <div><h1>Centro de reportes <i class="bi bi-bar-chart"></i></h1><p>Genera y visualiza reportes con datos reales del negocio.</p></div>
        <div class="admin-page-actions"><button class="admin-secondary-button" type="button" disabled aria-disabled="true">Exportación PDF · Próxima iteración</button></div>
    </header>

    <form class="admin-panel admin-report-filters" method="get" action="<?= e(url('admin/reports')) ?>">
        <div class="admin-report-filter-field"><label for="report-type">Tipo de reporte</label><select id="report-type" name="tipo"><?php foreach ($tiposReporte as $valor => $texto): ?><option value="<?= e($valor) ?>" <?= $reporte['tipo'] === $valor ? 'selected' : '' ?>><?= e($texto) ?></option><?php endforeach; ?></select></div>
        <div class="admin-report-filter-field"><label for="report-from">Desde</label><input id="report-from" type="date" name="desde" value="<?= e($reporte['desde']) ?>" required></div>
        <div class="admin-report-filter-field"><label for="report-to">Hasta</label><input id="report-to" type="date" name="hasta" value="<?= e($reporte['hasta']) ?>" required></div>
        <button class="admin-primary-button admin-report-generate" type="submit"><i class="bi bi-file-earmark-bar-graph"></i> Generar reporte</button>
        <?php if (in_array($reporte['tipo'], ['inventario', 'productos', 'clientes'], true)): ?><p class="admin-report-filter-note"><i class="bi bi-info-circle"></i> El reporte de <?= e(mb_strtolower($tipoReporte)) ?> presenta información actual; el rango de fechas no filtra esos registros.</p><?php endif; ?>
    </form>

    <?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

    <div class="admin-report-results">
        <section class="admin-panel admin-report-chart-panel">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-graph-up"></i><div><h2><?= $reporte['tipo'] === 'ventas' ? 'Ventas por período' : 'Tendencia temporal' ?></h2><p>Del <?= e(date('d/m/Y', strtotime($reporte['desde']))) ?> al <?= e(date('d/m/Y', strtotime($reporte['hasta']))) ?></p></div></div></header>
            <?php if ($reporte['tipo'] !== 'ventas'): ?>
                <div class="admin-empty admin-report-chart-empty">La serie temporal no está disponible para este tipo de reporte.</div>
            <?php elseif ($reporte['serie'] === []): ?>
                <div class="admin-empty admin-report-chart-empty">No hay ventas en el rango seleccionado.</div>
            <?php else: ?>
                <div class="admin-report-chart-area"><div class="admin-report-y-axis" aria-hidden="true"><?php for ($tick = 4; $tick >= 0; $tick--): ?><span><?= e(money($maximo * $tick / 4)) ?></span><?php endfor; ?></div>
                    <div class="admin-chart" role="img" aria-label="Gráfico de ventas diarias para el período seleccionado"><?php foreach ($reporte['serie'] as $fila): $altura = max(2, (int) round(((float) $fila['total'] / $maximo) * 100)); ?><div class="admin-chart-column" title="<?= e(date('d/m/Y', strtotime((string) $fila['periodo']))) ?>: <?= e(money($fila['total'])) ?>"><i style="--chart-height:<?= $altura ?>%"></i><span><?= e(date('d/m', strtotime((string) $fila['periodo']))) ?></span></div><?php endforeach; ?></div>
                </div>
            <?php endif; ?>
        </section>

        <section class="admin-panel admin-report-preview" data-admin-table-container>
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-file-earmark-text"></i><div><h2>Vista previa del reporte</h2><p><?= e($tipoReporte) ?> · <?= count($reporte['detalle']) ?> filas obtenidas</p></div></div></header>
            <div class="admin-report-preview-meta"><span><?= e(date('d/m/Y', strtotime($reporte['desde']))) ?> – <?= e(date('d/m/Y', strtotime($reporte['hasta']))) ?></span><span><?= count($reporte['detalle']) ?> resultados</span></div>
            <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><?php foreach ($columnas as $etiqueta): ?><th><?= e($etiqueta) ?></th><?php endforeach; ?></tr></thead><tbody>
                <?php if ($reporte['detalle'] === []): ?><tr><td colspan="<?= count($columnas) ?>" class="admin-table-empty">No se encontraron resultados para este reporte.</td></tr><?php endif; ?>
                <?php foreach ($reporte['detalle'] as $fila): ?><tr data-data-row><?php foreach ($columnas as $clave => $etiqueta): ?><td><?php if ($clave === 'total' || $clave === 'precio'): ?><?= e(money($fila[$clave] ?? 0)) ?><?php elseif ($clave === 'estado' && is_numeric($fila[$clave] ?? null)): ?><span class="admin-status <?= (int) $fila[$clave] === 1 ? '' : 'admin-status--muted' ?>"><?= (int) $fila[$clave] === 1 ? 'Activo' : 'Inactivo' ?></span><?php elseif ($clave === 'fecha'): ?><?= e(date('d/m/Y H:i', strtotime((string) $fila[$clave]))) ?><?php else: ?><?= e((string) ($fila[$clave] ?? '—')) ?><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?>
            </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>
    </div>
</div>
