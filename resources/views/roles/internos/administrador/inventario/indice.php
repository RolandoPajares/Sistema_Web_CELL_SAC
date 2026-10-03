<?php
/**
 * @var string $fechaHoyVista
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var array<array-key, mixed> $categoriasInventario
 * @var array<array-key, mixed> $existenciasInventario
 * @var array<array-key, mixed> $movimientos
 * @var array<array-key, mixed> $existencias
 */ ?><header class="admin-page-head">
    <div>
        <h1>
            Inventario <i class="bi bi-box-seam"></i>
        </h1>
        <p>
            Control de existencias y movimientos de stock con trazabilidad.
        </p>
    </div>
    <div class="admin-page-actions">
        <span class="admin-date-chip"><i class="bi bi-calendar-event"></i><span>Hoy es<br><strong><?= e($fechaHoyVista) ?></strong></span></span>
        <button class="admin-primary-button" type="button" data-admin-dialog-open="inventory-dialog"><i class="bi bi-plus-lg"></i> Nuevo movimiento
        </button>
    </div>
</header>
<div class="admin-alert admin-alert--error" role="alert" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
<div class="admin-alert admin-alert--success" role="status" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>
<section class="admin-panel" data-admin-table-container>

    <header class="admin-panel-header admin-inventory-list-header">
        <div class="admin-panel-title">
            <i class="bi bi-box"></i>
            <div>
                <h2>
                Lista de inventario
                </h2>
                <p>
                Productos y existencias actuales
                </p>
            </div>
        </div>
        <div class="admin-inventory-filters">
            <label class="admin-toolbar-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                    type="search"
                    data-table-search
                    placeholder="Buscar producto..."
                    aria-label="Buscar productos en inventario"
                >
            </label>
            <label class="admin-inventory-filter">
                <span>Categoría</span>
                <select
                    data-inventory-category-filter
                    aria-label="Filtrar por categoría"
                >
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categoriasInventario as $categoria): ?>
                        <option value="<?= e($categoria) ?>"><?= e($categoria) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="admin-inventory-filter">
                <span>Estado</span>
                <select
                    data-inventory-state-filter
                    aria-label="Filtrar por estado"
                >
                    <option value="">Todos los estados</option>
                    <option value="out">Sin stock</option>
                    <option value="low">Stock bajo</option>
                    <option value="available">En stock</option>
                </select>
            </label>
        </div>
    </header>

    <div class="admin-table-wrap">
        <table class="admin-table" data-admin-table>
            <thead>
                <tr>
                    <th>
                Producto
                    </th>
                    <th>
                Categoría
                    </th>
                    <th>
                Stock actual
                    </th>
                    <th>
                Stock mínimo
                    </th>
                    <th>
                Estado
                    </th>
                    <th>
                Último movimiento
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if ($existenciasInventario === []): ?>
                <tr data-inventory-empty>
                    <td colspan="6" class="admin-table-empty">
                No hay productos activos para consultar en el inventario.
                    </td>
                </tr>
                <?php endif; ?>
        <?php foreach ($existenciasInventario as $producto): ?>
                <tr data-data-row data-inventory-category="<?= e($producto['categoria']) ?>" data-inventory-state="<?= e($producto['estado_inventario_vista']) ?>">
                    <td class="admin-product-cell">
                            <img <?= $producto['atributoImagenOculta'] ?> src="<?= e($producto['imagen_inventario_vista']) ?>" alt="" loading="lazy">
                            <span class="admin-product-image-placeholder" <?= $producto['atributoIconoOculto'] ?> aria-hidden="true">
                                <i class="bi <?= e($producto['icono_inventario_vista']) ?>"></i>
                            </span>
                        <b><?= e($producto['marca'] . ' ' . $producto['producto']) ?></b>
                    </td>
                    <td>
                <?= e($producto['categoria']) ?>
                    </td>
                    <td>
                <b><?= e($producto['cantidad_inventario_vista']) ?></b>
                    </td>
                    <td>
                —
                    </td>
                    <td>
                <span class="admin-status <?= e($producto['clase_inventario_vista']) ?>"><?= e($producto['etiqueta_inventario_vista']) ?></span>
                    </td>
                    <td>
                <?= e($producto['ultimo_movimiento_vista']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr data-inventory-filter-empty hidden>
                    <td colspan="6" class="admin-table-empty">
                No hay existencias que coincidan con los filtros.
                    </td>
                </tr>
            </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
</section>
<section class="admin-panel admin-section-spaced" data-admin-table-container>

    <header class="admin-panel-header">
        <div class="admin-panel-title">
            <i class="bi bi-clock-history"></i>
            <div>
                <h2>
                Historial de movimientos
                </h2>
                <p>
                <?= count($movimientos) ?> movimientos disponibles
                </p>
            </div>
        </div>
        <label class="admin-inventory-period">
            <span>Período</span>
            <select data-inventory-period-filter aria-label="Filtrar historial por período">
                <option value="7">Últimos 7 días</option>
                <option value="30">Últimos 30 días</option>
                <option value="all">Todos los movimientos cargados</option>
            </select>
        </label>
    </header>

    <div class="admin-table-wrap">
        <table class="admin-table" data-admin-table>
            <thead>
                <tr>
                    <th>
                Fecha
                    </th>
                    <th>
                Producto
                    </th>
                    <th>
                Tipo
                    </th>
                    <th>
                Cantidad
                    </th>
                    <th>
                Usuario
                    </th>
                    <th>
                Observación
                    </th>
                </tr>
            </thead>
            <tbody>
        <?php if ($movimientos === []): ?>
        <tr data-inventory-empty><td colspan="6" class="admin-table-empty">Aún no existen movimientos registrados.</td></tr>
        <?php endif; ?>
        <?php foreach ($movimientos as $movimiento): ?>
                <tr data-data-row data-inventory-time="<?= e($movimiento['fecha_timestamp_vista']) ?>">
                    <td>
                <?= e($movimiento['fecha_vista']) ?>
                    </td>
                    <td>
                <b><?= e($movimiento['producto']) ?></b>
                    </td>
                    <td>
                <span
                class="admin-status <?= e($movimiento['clase_tipo_vista']) ?>"><?= e($movimiento['etiqueta_tipo_vista']) ?></span>
                    </td>
                    <td>
                <?= (int) $movimiento['cantidad'] ?>
                    </td>
                    <td>
                <?= e($movimiento['responsable']) ?>
                    </td>
                    <td>
                <?= e($movimiento['notas']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr data-inventory-filter-empty hidden>
                    <td colspan="6" class="admin-table-empty">
                No hay movimientos en el período seleccionado.
                    </td>
                </tr>
            </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
</section>
<dialog class="admin-dialog" id="inventory-dialog">
<header class="admin-dialog-header">
    <div>
        <h2>
            Nuevo movimiento
        </h2>
        <p>
            La operación se ejecuta en una transacción con bloqueo del producto.
        </p>
    </div>
    <button class="admin-dialog-close" type="button" data-admin-dialog-close><i class="bi bi-x-lg"></i>
    </button>
</header>
<div class="admin-dialog-body">
    <form method="post" action="<?= e(url_interna('admin/inventory')) ?>">
        <?= csrf_field() ?>
        <div class="admin-form-grid">
    <label class="admin-form-group is-full">
        <span>Producto *</span>
        <select name="producto_id" required>
            <option value="">Selecciona un producto</option>
            <?php foreach ($existencias as $producto): ?>
                <option value="<?= (int) $producto['id'] ?>">
                    <?= e($producto['marca'] . ' ' . $producto['producto'] . ' · stock ' . $producto['existencias']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="admin-form-group"><span>Tipo *</span><select name="tipo_movimiento" required><option value="entrada">Entrada</option><option value="salida">Salida</option><option value="ajuste">Ajuste de stock final</option></select></label>
    <label class="admin-form-group"><span>Cantidad *</span><input type="number" name="cantidad" min="1" required></label>
    <label class="admin-form-group is-full"><span>Motivo u observación *</span><textarea name="notas" maxlength="500" rows="4" required></textarea></label>
        </div>
        <div class="admin-form-actions">
            <button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar
            </button>
            <button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> Registrar movimiento
            </button>
        </div>
    </form>
</div>
</dialog>
