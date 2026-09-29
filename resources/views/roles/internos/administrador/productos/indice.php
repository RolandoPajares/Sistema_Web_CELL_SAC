<?php
$categoriasFiltro = array_values(array_unique(array_filter(array_merge(
    array_map(static fn (array $categoria): string => (string) $categoria['nombre'], $categorias),
    array_map(static fn (array $producto): string => (string) $producto['categoria'], $productos)
), static fn (string $nombre): bool => trim($nombre) !== '')));
sort($categoriasFiltro, SORT_NATURAL | SORT_FLAG_CASE);

$tarjetasKpi = [
    ['etiqueta' => 'Total de productos', 'valor' => (string) $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-box-seam', 'tono' => 'violeta'],
    ['etiqueta' => 'Productos activos', 'valor' => (string) $resumen['activos'], 'detalle' => 'Visibles en catálogo', 'icono' => 'bi-file-earmark-check', 'tono' => 'verde'],
    ['etiqueta' => 'Stock bajo', 'valor' => (string) $resumen['stock_bajo'], 'detalle' => '8 unidades o menos', 'icono' => 'bi-exclamation-triangle', 'tono' => 'rojo'],
    ['etiqueta' => 'Productos inactivos', 'valor' => (string) $resumen['inactivos'], 'detalle' => 'No visibles en catálogo', 'icono' => 'bi-eye-slash', 'tono' => 'azul'],
];
?>
<header class="admin-page-head"><div><h1>Productos <i class="bi bi-box-seam"></i></h1><p>Gestiona el catálogo comercial. Las existencias se modifican exclusivamente desde Inventario.</p></div><div class="admin-page-actions"><button class="admin-primary-button" type="button" data-admin-dialog-open="product-dialog"><i class="bi bi-plus-lg"></i> Nuevo producto</button></div></header>
<?php if (!$baseDatosDisponible): ?><div class="admin-alert admin-alert--error">La base de datos no está disponible.</div><?php else: ?>
<?php if ($error): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>
<div data-admin-table-container>
<div class="admin-toolbar admin-products-toolbar">
    <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar por nombre, marca o categoría..." aria-label="Buscar productos"></label>
    <label class="admin-products-filter"><span>Categoría</span><select data-product-category-filter aria-label="Filtrar por categoría"><option value="">Todas las categorías</option><?php foreach ($categoriasFiltro as $nombreCategoria): ?><option value="<?= e($nombreCategoria) ?>"><?= e($nombreCategoria) ?></option><?php endforeach; ?></select></label>
    <label class="admin-products-filter"><span>Estado</span><select data-product-state-filter aria-label="Filtrar por estado"><option value="">Todos los estados</option><option value="active">Activos</option><option value="inactive">Inactivos</option></select></label>
</div>
<section class="admin-panel">
    <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-box"></i><div><h2>Listado de productos</h2><p><?= count($productos) ?> registros reales</p></div></div></header>
    <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr></thead><tbody>
        <?php if ($productos === []): ?><tr data-product-empty><td colspan="7" class="admin-table-empty">Aún no existen productos.</td></tr><?php endif; ?>
        <?php foreach ($productos as $producto): ?><tr data-data-row data-product-category="<?= e($producto['categoria']) ?>" data-product-state="<?= (int) $producto['activo'] === 1 ? 'active' : 'inactive' ?>">
            <td><b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b><small>MDP<?= str_pad((string) $producto['id'], 5, '0', STR_PAD_LEFT) ?><?= $producto['almacenamiento'] ? ' · ' . e($producto['almacenamiento']) : '' ?></small></td>
            <td><?= e($producto['categoria']) ?></td><td><?= e(money($producto['precio'])) ?></td><td><b><?= (int) $producto['existencias'] ?></b></td>
            <td><span class="admin-status <?= (int) $producto['activo'] === 1 ? '' : 'admin-status--muted' ?>"><?= (int) $producto['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
            <td><?= e(date('d/m/Y', strtotime((string) $producto['creado_en']))) ?></td>
            <td><div class="admin-table-actions"><a class="admin-action-icon" href="<?= e(url('admin/products/' . (int) $producto['id'] . '/edit')) ?>" aria-label="Editar"><i class="bi bi-pencil"></i></a><?php if ((int) $producto['activo'] === 1): ?><form method="post" action="<?= e(url('admin/products/' . (int) $producto['id'] . '/deactivate')) ?>"><?= csrf_field() ?><button class="admin-action-icon is-danger" data-confirm="¿Desactivar este producto?" aria-label="Desactivar"><i class="bi bi-eye-slash"></i></button></form><?php endif; ?></div></td>
        </tr><?php endforeach; ?>
        <?php if ($productos !== []): ?><tr data-product-filter-empty hidden><td colspan="7" class="admin-table-empty">No se encontraron productos con los criterios seleccionados.</td></tr><?php endif; ?>
    </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
</section>
</div>

<dialog class="admin-dialog" id="product-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">
    <header class="admin-dialog-header"><div><h2><?= $edicion ? 'Editar producto' : 'Nuevo producto' ?></h2><p>Los campos se validan nuevamente en el servidor.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header>
    <div class="admin-dialog-body"><form method="post" action="<?= e(url($edicion ? 'admin/products/' . (int) $edicion['id'] : 'admin/products')) ?>"><?= csrf_field() ?>
        <div class="admin-form-grid">
            <label class="admin-form-group is-full"><span>Nombre del producto *</span><input name="name" maxlength="160" value="<?= e($edicion['nombre'] ?? '') ?>" required></label>
            <label class="admin-form-group"><span>Marca *</span><input name="brand" maxlength="80" value="<?= e($edicion['marca'] ?? '') ?>" required></label>
            <label class="admin-form-group"><span>Categoría *</span><select name="category_id" required><option value="">Selecciona una categoría</option><?php foreach ($categorias as $categoria): ?><option value="<?= (int) $categoria['id'] ?>" <?= (int) ($edicion['categoria_id'] ?? 0) === (int) $categoria['id'] ? 'selected' : '' ?>><?= e($categoria['nombre']) ?></option><?php endforeach; ?></select></label>
            <label class="admin-form-group"><span>Precio (S/) *</span><input name="price" type="number" min="0.01" step=".01" value="<?= e($edicion['precio'] ?? '') ?>" required></label>
            <label class="admin-form-group"><span>Stock actual</span><input value="<?= (int) ($edicion['existencias'] ?? 0) ?>" disabled><small>Todo cambio de stock se registra desde Inventario.</small></label>
            <label class="admin-form-group"><span>Capacidad</span><input name="storage" maxlength="80" value="<?= e($edicion['almacenamiento'] ?? '') ?>"></label>
            <label class="admin-form-group"><span>Color</span><input name="color" maxlength="80" value="<?= e($edicion['color'] ?? '') ?>"></label>
            <label class="admin-form-group is-full"><span>Descripción</span><textarea name="description" rows="4"><?= e($edicion['descripcion'] ?? '') ?></textarea></label>
        </div><input type="hidden" name="badge" value="<?= e($edicion['etiqueta'] ?? '') ?>"><div class="admin-form-actions"><button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar</button><button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar producto' ?></button></div>
    </form></div>
</dialog>
<?php endif; ?>
