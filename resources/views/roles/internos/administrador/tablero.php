<?php
$tarjetasKpi = [
    ['etiqueta' => 'Ventas del mes', 'valor' => money($estadisticas['ventas_periodo']), 'detalle' => 'Pedidos no cancelados', 'icono' => 'bi-cart3', 'tono' => 'azul'],
    ['etiqueta' => 'Pedidos', 'valor' => (string) $estadisticas['pedidos'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-file-earmark-text', 'tono' => 'verde'],
    ['etiqueta' => 'Productos activos', 'valor' => (string) $estadisticas['productos'], 'detalle' => $estadisticas['productos_total'] . ' productos totales', 'icono' => 'bi-box-seam', 'tono' => 'violeta'],
    ['etiqueta' => 'Stock bajo', 'valor' => (string) $estadisticas['stock_bajo'], 'detalle' => 'Productos activos con 8 unidades o menos', 'icono' => 'bi-exclamation-triangle', 'tono' => 'rojo'],
];
$maximoVentas = max(array_merge([1.0], array_map(static fn (array $fila): float => (float) $fila['total'], $ventasMensuales)));
?>
<header class="admin-page-head">
    <div><h1>Buenos días, <?= e(current_user()['nombre'] ?? 'Administrador') ?> <i class="bi bi-hand-thumbs-up"></i></h1><p>Resumen general del negocio de MD Technology Digital Cell S.A.C.</p></div>
    <span class="admin-date-chip"><i class="bi bi-calendar3"></i><span>Hoy es<br><b><?= e(date('d/m/Y')) ?></b></span></span>
</header>
<?php require dirname(__DIR__, 3) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-grid-main">
    <div class="admin-stack">
        <section class="admin-panel">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-bar-chart-fill"></i><div><h2>Ventas del período</h2><p>Ventas no canceladas de los últimos 12 meses con registros</p></div></div></header>
            <?php if ($ventasMensuales === []): ?><div class="admin-empty">Sin información de ventas disponible para graficar.</div><?php else: ?>
                <div class="admin-chart" aria-label="Ventas mensuales">
                    <?php foreach ($ventasMensuales as $fila): $altura = max(2, (int) round(((float) $fila['total'] / $maximoVentas) * 100)); ?>
                        <div class="admin-chart-column" title="<?= e($fila['periodo']) ?>: <?= e(money($fila['total'])) ?>"><i style="--chart-height:<?= $altura ?>%"></i><span><?= e(substr((string) $fila['periodo'], 5, 2)) ?>/<?= e(substr((string) $fila['periodo'], 2, 2)) ?></span></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <section class="admin-panel" data-admin-table-container>
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-cart3"></i><div><h2>Pedidos recientes</h2><p>Últimos registros disponibles</p></div></div><a class="admin-secondary-button" href="<?= e(url('admin/orders')) ?>">Ver todos</a></header>
            <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead><tbody>
                <?php if ($pedidosRecientes === []): ?><tr><td colspan="5" class="admin-table-empty">No hay pedidos registrados.</td></tr><?php endif; ?>
                <?php foreach ($pedidosRecientes as $pedido): ?><tr data-data-row><td><a href="<?= e(url('admin/orders/' . (int) $pedido['id'])) ?>"><b>#<?= str_pad((string) $pedido['id'], 6, '0', STR_PAD_LEFT) ?></b></a></td><td><?= e($pedido['nombre']) ?></td><td><?= e(date('d/m/Y', strtotime((string) $pedido['creado_en']))) ?></td><td><?= e(money($pedido['total'])) ?></td><td><span class="admin-status <?= $pedido['estado'] === 'Cancelado' ? 'admin-status--danger' : ($pedido['estado'] === 'Pendiente' ? 'admin-status--warning' : 'admin-status--info') ?>"><?= e($pedido['estado']) ?></span></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </section>
    </div>
    <aside class="admin-panel">
        <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-box-seam"></i><div><h2>Productos con stock bajo</h2><p>Existencias reales registradas</p></div></div><a class="admin-secondary-button" href="<?= e(url('admin/inventory')) ?>">Ver todos</a></header>
        <?php if ($inteligencia['existencias_bajas'] === []): ?><div class="admin-empty">No hay productos con stock bajo.</div><?php else: ?><div class="admin-list">
            <?php foreach ($inteligencia['existencias_bajas'] as $producto): ?><div class="admin-list-item"><span class="admin-list-icon"><i class="bi bi-phone"></i></span><div><b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b><small>Stock disponible</small></div><strong class="<?= (int) $producto['existencias'] <= 3 ? 'admin-status admin-status--danger' : 'admin-status admin-status--warning' ?>"><?= (int) $producto['existencias'] ?></strong></div><?php endforeach; ?>
        </div><?php endif; ?>
    </aside>
</div>
