<?php
$tarjetasKpi = [
    ['etiqueta' => 'Total de categorías', 'valor' => (string) $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-tags', 'tono' => 'azul'],
    ['etiqueta' => 'Activas', 'valor' => (string) $resumen['activas'], 'detalle' => 'Disponibles para productos', 'icono' => 'bi-check-circle', 'tono' => 'verde'],
    ['etiqueta' => 'Con productos', 'valor' => (string) $resumen['con_productos'], 'detalle' => 'Asociaciones activas', 'icono' => 'bi-box-seam', 'tono' => 'violeta'],
    ['etiqueta' => 'Sin productos', 'valor' => (string) $resumen['sin_productos'], 'detalle' => 'Sin asociaciones activas', 'icono' => 'bi-slash-square', 'tono' => 'rojo'],
];
$categoriasConProductos = array_values(array_filter(
    $registros,
    static fn (array $categoria): bool => (int) $categoria['productos_asociados'] > 0
));
$categoriasSinProductos = array_values(array_filter(
    $registros,
    static fn (array $categoria): bool => (int) $categoria['productos_asociados'] === 0
));
usort($categoriasConProductos, static fn (array $a, array $b): int => (int) $b['productos_asociados'] <=> (int) $a['productos_asociados']);
$totalProductosAsociados = array_sum(array_map(static fn (array $categoria): int => (int) $categoria['productos_asociados'], $registros));
?>
<header class="admin-page-head">
    <div><h1>Categorías <i class="bi bi-tags"></i></h1><p><?= e($descripcionModulo) ?></p></div>
    <div class="admin-page-actions"><button class="admin-primary-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> Nueva categoría</button></div>
</header>
<?php if ($error): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-categories-layout">
    <div class="admin-categories-main" data-admin-table-container>
        <div class="admin-toolbar admin-categories-toolbar">
            <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar por nombre o descripción..." aria-label="Buscar categorías"></label>
            <label class="admin-categories-filter"><span>Estado</span><select data-category-status-filter aria-label="Filtrar categorías por estado"><option value="">Todos</option><option value="active">Activas</option><option value="inactive">Inactivas</option></select></label>
            <label class="admin-categories-filter"><span>Ordenar por</span><select data-category-order-filter aria-label="Ordenar categorías"><option value="newest">Más recientes</option><option value="name-asc">Nombre A-Z</option></select></label>
        </div>

        <section class="admin-panel admin-categories-table-panel" id="category-table">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-tags"></i><div><h2>Listado de categorías</h2><p><?= count($registros) ?> registros reales</p></div></div></header>
            <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>Categoría</th><th>Descripción</th><th>Productos asociados</th><th>Estado</th><th>Fecha de creación</th><th>Acciones</th></tr></thead><tbody>
                <?php if ($registros === []): ?><tr data-category-empty><td colspan="6" class="admin-table-empty">Aún no existen categorías. Registra una para organizar los productos.</td></tr><?php endif; ?>
                <?php foreach ($registros as $registro): ?><tr data-data-row data-category-status="<?= (int) $registro['activo'] === 1 ? 'active' : 'inactive' ?>" data-category-name="<?= e($registro['nombre']) ?>" data-category-created="<?= e(date('Y-m-d', strtotime((string) $registro['creado_en']))) ?>">
                    <td><div class="admin-category-name"><span><i class="bi bi-tag"></i></span><strong><?= e($registro['nombre']) ?></strong></div></td>
                    <td class="admin-category-description"><?= e((string) ($registro['descripcion'] ?: '—')) ?></td>
                    <td><?= (int) $registro['productos_asociados'] ?></td>
                    <td><span class="admin-status <?= (int) $registro['activo'] === 1 ? '' : 'admin-status--muted' ?>"><?= (int) $registro['activo'] === 1 ? 'Activa' : 'Inactiva' ?></span></td>
                    <td><?= e(date('d/m/Y', strtotime((string) $registro['creado_en']))) ?></td>
                    <td><div class="admin-table-actions"><a class="admin-action-icon" href="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/edit')) ?>" aria-label="Editar categoría"><i class="bi bi-pencil"></i></a><?php if ((int) $registro['activo'] === 1): ?><form method="post" action="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/deactivate')) ?>"><?= csrf_field() ?><button class="admin-action-icon is-danger" data-confirm="¿Desactivar esta categoría?" aria-label="Desactivar categoría"><i class="bi bi-slash-circle"></i></button></form><?php endif; ?></div></td>
                </tr><?php endforeach; ?>
                <?php if ($registros !== []): ?><tr data-category-filter-empty hidden><td colspan="6" class="admin-table-empty">No se encontraron categorías con los criterios seleccionados.</td></tr><?php endif; ?>
            </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>
    </div>

    <aside class="admin-categories-aside">
        <section class="admin-panel">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-bar-chart-fill"></i><div><h2>Distribución por categorías</h2><p><?= $totalProductosAsociados ?> productos asociados</p></div></div></header>
            <?php if ($categoriasConProductos === []): ?><div class="admin-empty">No hay productos asociados a categorías.</div>
            <?php else: ?><ul class="admin-category-distribution">
                <?php foreach ($categoriasConProductos as $categoria): $cantidad = (int) $categoria['productos_asociados']; $porcentaje = $totalProductosAsociados > 0 ? (int) round($cantidad * 100 / $totalProductosAsociados) : 0; ?>
                    <li><span class="admin-category-badge"><i class="bi bi-tag"></i></span><span class="admin-category-distribution-name"><?= e($categoria['nombre']) ?></span><progress max="100" value="<?= $porcentaje ?>" aria-label="<?= e($categoria['nombre']) ?>: <?= $porcentaje ?> por ciento"></progress><strong><?= $cantidad ?></strong><small><?= $porcentaje ?>%</small></li>
                <?php endforeach; ?>
            </ul><?php endif; ?>
        </section>

        <section class="admin-panel">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-exclamation-triangle"></i><div><h2>Categorías sin productos</h2><p>Sin productos activos asociados</p></div></div><a href="#category-table">Ver todas</a></header>
            <?php if ($categoriasSinProductos === []): ?><div class="admin-empty admin-categories-empty">Todas las categorías tienen productos activos asociados.</div>
            <?php else: ?><ul class="admin-category-empty-list">
                <?php foreach ($categoriasSinProductos as $categoria): ?><li><span class="admin-category-badge"><i class="bi bi-tag"></i></span><strong><?= e($categoria['nombre']) ?></strong><span class="admin-status admin-status--danger">Sin productos</span><time datetime="<?= e(date('Y-m-d', strtotime((string) $categoria['creado_en']))) ?>"><?= e(date('d/m/Y', strtotime((string) $categoria['creado_en']))) ?></time></li><?php endforeach; ?>
            </ul><?php endif; ?>
        </section>
    </aside>
</div>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">
    <header class="admin-dialog-header"><div><h2><?= $edicion ? 'Editar categoría' : 'Nueva categoría' ?></h2><p>La información se valida en el servidor antes de guardarse.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header>
    <div class="admin-dialog-body"><form method="post" action="<?= e(url($edicion ? $rutaBase . '/' . (int) $edicion['id'] : $rutaBase)) ?>"><?= csrf_field() ?><div class="admin-form-grid">
        <label class="admin-form-group is-full"><span>Nombre de la categoría *</span><input type="text" name="nombre" value="<?= e((string) ($edicion['nombre'] ?? '')) ?>" maxlength="120" required></label>
        <label class="admin-form-group is-full"><span>Descripción</span><textarea name="descripcion" maxlength="500" rows="4"><?= e((string) ($edicion['descripcion'] ?? '')) ?></textarea></label>
    </div><div class="admin-form-actions"><button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar</button><button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar categoría' ?></button></div></form></div>
</dialog>
