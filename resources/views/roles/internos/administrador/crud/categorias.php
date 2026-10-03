<?php
/**
 * @var string $descripcionModulo
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var array<array-key, mixed> $registros
 * @var string $atributoRegistrosVaciosOculto
 * @var mixed $totalProductosAsociados
 * @var string $atributoDistribucionVaciaOculto
 * @var string $atributoDistribucionListaOculto
 * @var array<array-key, mixed> $categoriasConProductos
 * @var string $atributoSinProductosVacioOculto
 * @var string $atributoSinProductosListaOculto
 * @var array<array-key, mixed> $categoriasSinProductos
 * @var array<array-key, mixed> $edicion
 * @var string $urlFormularioCrud
 */ ?><header class="admin-page-head">
    <div><h1>Categorías <i class="bi bi-tags"></i></h1><p><?= e($descripcionModulo) ?></p></div>

    <div class="admin-page-actions">
        <button class="admin-primary-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> Nueva categoría
        </button>
    </div>
</header>
<div class="admin-alert admin-alert--error" role="alert" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
<div class="admin-alert admin-alert--success" role="status" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-categories-layout">
    <div class="admin-categories-main" data-admin-table-container>
        <div class="admin-toolbar admin-categories-toolbar">
            <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar por nombre o descripción..." aria-label="Buscar categorías"></label>
            <label class="admin-categories-filter">
                <span>Estado</span>
                <select data-category-status-filter aria-label="Filtrar categorías por estado">
                    <option value="">Todos</option>
                    <option value="active">Activas</option>
                    <option value="inactive">Inactivas</option>
                </select>
            </label>
            <label class="admin-categories-filter"><span>Ordenar por</span><select data-category-order-filter aria-label="Ordenar categorías"><option value="newest">Más recientes</option><option value="name-asc">Nombre A-Z</option></select></label>
        </div>

        <section class="admin-panel admin-categories-table-panel" id="category-table">

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-tags"></i>
                    <div>
                        <h2>
                Listado de categorías
                        </h2>
                        <p>
                <?= count($registros) ?> registros reales
                        </p>
                    </div>
                </div>
            </header>

            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-table>
                    <thead>
                        <tr>
                            <th>
                Categoría
                            </th>
                            <th>
                Descripción
                            </th>
                            <th>
                Productos asociados
                            </th>
                            <th>
                Estado
                            </th>
                            <th>
                Fecha de creación
                            </th>
                            <th>
                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-category-empty <?= $atributoRegistrosVaciosOculto ?>>
                            <td colspan="6" class="admin-table-empty">
                Aún no existen categorías. Registra una para organizar los productos.
                            </td>
                        </tr>
                <?php foreach ($registros as $registro): ?>
                        <tr
                data-data-row
                data-category-status="<?= e($registro['estado_filtro_vista']) ?>"
                data-category-name="<?= e($registro['nombre']) ?>"
                data-category-created="<?= e($registro['creado_en_iso_vista']) ?>">
                            <td><div class="admin-category-name"><span><i class="bi bi-tag"></i></span><strong><?= e($registro['nombre']) ?></strong></div></td>
                            <td class="admin-category-description"><?= e((string) ($registro['descripcion'] ?: '—')) ?></td>
                            <td><?= (int) $registro['productos_asociados'] ?></td>

                            <td>
                <span class="admin-status <?= e($registro['estado_clase_vista']) ?>"><?= e($registro['estado_vista']) ?></span>
                            </td>
                            <td><?= e($registro['creado_en_vista']) ?></td>

                            <td>
                                <div class="admin-table-actions">
                                    <a class="admin-action-icon" href="<?= e($registro['url_editar_vista']) ?>" aria-label="Editar categoría"><i class="bi bi-pencil"></i>
                                    </a>
                                    <form <?= $registro['atributoDesactivarOculto'] ?> method="post" action="<?= e($registro['url_desactivar_vista']) ?>">
                <?= csrf_field() ?>
                                        <button class="admin-action-icon is-danger" data-confirm="¿Desactivar esta categoría?" aria-label="Desactivar categoría"><i class="bi bi-slash-circle"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr><?php endforeach; ?>
                        <tr data-category-filter-empty hidden>
                            <td colspan="6" class="admin-table-empty">
                No se encontraron categorías con los criterios seleccionados.
                            </td>
                        </tr>
                    </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>
    </div>

    <aside class="admin-categories-aside">
        <section class="admin-panel">

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-bar-chart-fill"></i>
                    <div>
                        <h2>
                Distribución por categorías
                        </h2>
                        <p>
                <?= $totalProductosAsociados ?> productos asociados
                        </p>
                    </div>
                </div>
            </header>
            <div class="admin-empty" <?= $atributoDistribucionVaciaOculto ?>>No hay productos asociados a categorías.</div>
            <ul class="admin-category-distribution" <?= $atributoDistribucionListaOculto ?>>
                <?php foreach ($categoriasConProductos as $categoria): ?>
                <li>
                <span class="admin-category-badge"><i class="bi bi-tag"></i></span>
                <span class="admin-category-distribution-name"><?= e($categoria['nombre']) ?></span>
                <progress
                    max="100"
                    value="<?= $categoria['porcentaje_vista'] ?>"
                    aria-label="<?= e($categoria['nombre']) ?>: <?= $categoria['porcentaje_vista'] ?> por ciento"></progress>
                <strong><?= $categoria['cantidad_vista'] ?></strong>
                <small><?= $categoria['porcentaje_vista'] ?>%</small>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="admin-panel">

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <h2>
                Categorías sin productos
                        </h2>
                        <p>
                Sin productos activos asociados
                        </p>
                    </div>
                </div>
                <a href="#category-table">Ver todas
                </a>
            </header>
            <div class="admin-empty admin-categories-empty" <?= $atributoSinProductosVacioOculto ?>>Todas las categorías tienen productos activos asociados.</div>
            <ul class="admin-category-empty-list" <?= $atributoSinProductosListaOculto ?>>
                <?php foreach ($categoriasSinProductos as $categoria): ?>
                <li>
                <span class="admin-category-badge"><i class="bi bi-tag"></i></span>
                <strong><?= e($categoria['nombre']) ?></strong>
                <span class="admin-status admin-status--danger">Sin productos</span>
                <time datetime="<?= e($categoria['creado_en_iso_vista']) ?>">
                    <?= e($categoria['creado_en_vista']) ?>
                </time>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </aside>
</div>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">

<header class="admin-dialog-header">
    <div>
        <h2>
            <?= $edicion ? 'Editar categoría' : 'Nueva categoría' ?>
        </h2>
        <p>
            La información se valida en el servidor antes de guardarse.
        </p>
    </div>
    <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i>
    </button>
</header>

<div class="admin-dialog-body">
    <form method="post" action="<?= e($urlFormularioCrud) ?>">
        <?= csrf_field() ?>
        <div class="admin-form-grid">
        <label class="admin-form-group is-full"><span>Nombre de la categoría *</span><input type="text" name="nombre" value="<?= e((string) ($edicion['nombre'] ?? '')) ?>" maxlength="120" required></label>
        <label class="admin-form-group is-full"><span>Descripción</span><textarea name="descripcion" maxlength="500" rows="4"><?= e((string) ($edicion['descripcion'] ?? '')) ?></textarea></label>

        </div>
        <div class="admin-form-actions">
            <button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar
            </button>
            <button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar categoría' ?>
            </button>
        </div>
    </form>
</div>
</dialog>
