<?php
/**
 * @var string $descripcionModulo
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var array<array-key, mixed> $opcionesCiudadProveedor
 * @var string $atributoRegistrosVaciosOculto
 * @var array<array-key, mixed> $registros
 * @var string $atributoProveedoresRecientesVacioOculto
 * @var string $atributoProveedoresRecientesListaOculto
 * @var array<array-key, mixed> $proveedoresRecientesDestacados
 * @var string $atributoCiudadesVacioOculto
 * @var string $atributoCiudadesListaOculto
 * @var array<array-key, mixed> $ciudadesDestacadasVista
 * @var mixed $edicion
 * @var string $urlFormularioCrud
 * @var array<array-key, mixed> $camposFormularioVista
 */ ?><header class="admin-page-head">
    <div><h1>Proveedores <i class="bi bi-truck"></i></h1><p><?= e($descripcionModulo) ?></p></div>
</header>
<div class="admin-alert admin-alert--error" role="alert" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
<div class="admin-alert admin-alert--success" role="status" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-suppliers-page" data-admin-table-container>
    <div class="admin-suppliers-layout">
        <section class="admin-panel admin-suppliers-list">
            <header class="admin-panel-header">
                <div class="admin-panel-title"><i class="bi bi-truck"></i><div><h2>Listado de proveedores</h2><p>Administra la información de tus proveedores</p></div></div>

                <button class="admin-primary-button admin-suppliers-header-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> Nuevo proveedor
                </button>
            </header>
            <div class="admin-suppliers-toolbar">
                <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar proveedor, RUC o contacto..." aria-label="Buscar proveedores"></label>
                <label class="admin-suppliers-filter">
                    <span>Estado</span>
                    <select data-supplier-state-filter aria-label="Filtrar por estado">
                        <option value="">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </label>
                <label class="admin-suppliers-filter">
                    <span>Ciudad</span>
                    <select data-supplier-city-filter aria-label="Filtrar por ciudad">
                        <option value="">Todas las ciudades</option>
                        <?php foreach ($opcionesCiudadProveedor as $ciudad): ?>
                            <option value="<?= e($ciudad['valor']) ?>"><?= e($ciudad['etiqueta']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-table>
                    <thead>
                        <tr>
                            <th>
                Proveedor
                            </th>
                            <th>
                RUC
                            </th>
                            <th>
                Contacto
                            </th>
                            <th>
                Teléfono
                            </th>
                            <th>
                Correo
                            </th>
                            <th>
                Ciudad
                            </th>
                            <th>
                Estado
                            </th>
                            <th>
                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                <tr data-supplier-empty <?= $atributoRegistrosVaciosOculto ?>><td colspan="8" class="admin-table-empty">Aún no hay proveedores registrados.</td></tr>
                <?php foreach ($registros as $registro): ?>
                        <tr
                data-data-row
                data-supplier-state="<?= e($registro['estado_filtro_vista']) ?>"
                data-supplier-city="<?= e($registro['ciudad_filtro_vista']) ?>">

                            <td>
                                <div class="admin-supplier-name">
                <span><?= e($registro['inicial_vista']) ?></span><strong><?= e($registro['nombre']) ?></strong>
                                </div>
                            </td>

                            <td>
                <?= e((string) $registro['ruc']) ?>
                            </td>
                            <td>
                <?= e($registro['contacto_vista']) ?>
                            </td>
                            <td>
                <?= e($registro['telefono_vista']) ?>
                            </td>
                            <td class="admin-supplier-email">
                <?= e((string) $registro['correo']) ?>
                            </td>
                            <td>
                <?= e($registro['ciudad_vista']) ?>
                            </td>

                            <td>
                <span class="admin-status <?= e($registro['estado_clase_vista']) ?>"><?= e($registro['estado_vista']) ?></span>
                            </td>

                            <td>
                                <div class="admin-table-actions">
                            <a class="admin-action-icon" href="<?= e($registro['url_editar_vista']) ?>" aria-label="Editar proveedor"><i class="bi bi-pencil"></i>
                                    </a>
                            <form <?= $registro['atributoDesactivarOculto'] ?> method="post" action="<?= e($registro['url_desactivar_vista']) ?>">
                <?= csrf_field() ?>
                                        <button class="admin-action-icon is-danger" data-confirm="¿Deseas desactivar este proveedor?" aria-label="Desactivar proveedor"><i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr><?php endforeach; ?>
                        <tr data-supplier-filter-empty hidden>
                            <td colspan="8" class="admin-table-empty">
                No se encontraron proveedores con esos criterios.
                            </td>
                        </tr>
                    </tbody></table></div>
            <div class="admin-pagination" data-admin-pagination></div>
        </section>

        <aside class="admin-suppliers-aside">
            <section class="admin-panel">

                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                <i class="bi bi-clock-history"></i>
                        <div>
                            <h2>
                Últimos proveedores registrados
                            </h2>
                            <p>
                Ordenados por fecha de registro
                            </p>
                        </div>
                    </div>
                </header>
                <div class="admin-empty admin-suppliers-empty" <?= $atributoProveedoresRecientesVacioOculto ?>>No hay proveedores registrados.</div>
                <ul class="admin-supplier-recent" <?= $atributoProveedoresRecientesListaOculto ?>>
                <?php foreach ($proveedoresRecientesDestacados as $proveedor): ?>
                    <li>
                        <span><?= e($proveedor['inicial_vista']) ?></span>
                        <strong><?= e((string) $proveedor['nombre']) ?></strong>
                        <time
                            datetime="<?= e($proveedor['fecha_registro_iso_vista']) ?>">
                            <?= e($proveedor['fecha_registro_vista']) ?>
                        </time>
                        <small class="admin-status <?= e($proveedor['estado_clase_vista']) ?>">
                            <?= e($proveedor['estado_vista']) ?>
                        </small>
                    </li>
                <?php endforeach; ?>
                </ul>
            </section>
            <section class="admin-panel">

                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                <i class="bi bi-bar-chart-fill"></i>
                        <div>
                            <h2>
                Cobertura por ciudad
                            </h2>
                            <p>
                Proveedores registrados por ciudad
                            </p>
                        </div>
                    </div>
                </header>
                <div class="admin-empty admin-suppliers-empty" <?= $atributoCiudadesVacioOculto ?>>No hay ciudades disponibles en los registros.</div>
                <ul class="admin-supplier-cities" <?= $atributoCiudadesListaOculto ?>>
                <?php foreach ($ciudadesDestacadasVista as $ciudad): ?>
                    <li>
                        <span><?= e($ciudad['nombre']) ?></span>
                        <b><?= $ciudad['cantidad'] ?></b>
                        <i aria-hidden="true">
                            <span style="width: <?= $ciudad['porcentaje'] ?>%"></span>
                        </i>
                    </li>
                <?php endforeach; ?>
                </ul>
            </section>
        </aside>
    </div>
</div>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">

<header class="admin-dialog-header">
    <div>
        <h2>
            <?= $edicion ? 'Editar proveedor' : 'Nuevo proveedor' ?>
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
            <?php foreach ($camposFormularioVista as $campo): ?>
                <label class="admin-form-group<?= e($campo['clase']) ?>">
                    <span><?= e($campo['etiqueta']) ?><?= e($campo['marcadorRequerido']) ?></span>
                    <?= $campo['controlHtml'] ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="admin-form-actions">
            <button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar
            </button>
            <button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar proveedor' ?>
            </button>
        </div>
    </form>
</div>
</dialog>
