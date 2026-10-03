<?php
/**
 * @var array<int, array<string, mixed>> $registros
 * @var array<int, array<string, mixed>> $clientesConPedidosDestacados
 * @var array<int, array<string, mixed>> $camposFormularioVista
 * @var array<string, string> $columnas
 
 * @var string $descripcionModulo
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var string $atributoRegistrosVaciosOculto
 * @var string $atributoRankingVacioOculto
 * @var string $atributoRankingListaOculto
 * @var mixed $edicion
 * @var string $urlFormularioCrud
 */
?>
<header class="admin-page-head">
    <div><h1>Clientes <i class="bi bi-people-fill"></i></h1><p><?= e($descripcionModulo) ?></p></div>

    <div class="admin-page-actions">
        <button class="admin-primary-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> Nuevo cliente
        </button>
    </div>
</header>
<div class="admin-alert admin-alert--error" role="alert" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
<div class="admin-alert admin-alert--success" role="status" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-clients-page" data-admin-table-container>
    <div class="admin-clients-toolbar admin-toolbar">
    <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar por nombre, documento, teléfono o correo..." aria-label="Buscar clientes"></label>
    <label class="admin-clients-filter">
        <span>Tipo de cliente</span>
        <select data-client-type-filter aria-label="Filtrar por tipo de cliente">
            <option value="">Todos</option>
            <option value="minorista">Minorista</option>
            <option value="mayorista">Mayorista</option>
        </select>
    </label>
    <label class="admin-clients-filter"><span>Estado</span><select data-client-state-filter aria-label="Filtrar por estado"><option value="">Todos</option><option value="active">Activos</option><option value="inactive">Inactivos</option></select></label>
    </div>

    <div class="admin-clients-layout">
        <section class="admin-panel admin-clients-table-panel">

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-people-fill"></i>
                    <div>
                        <h2>
                Lista de clientes
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
                Cliente
                            </th>
                            <th>
                Documento
                            </th>
                            <th>
                Tipo
                            </th>
                            <th>
                Teléfono
                            </th>
                            <th>
                Correo
                            </th>
                            <th>
                Pedidos
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
            <tr data-client-empty <?= $atributoRegistrosVaciosOculto ?>><td colspan="8" class="admin-table-empty">Aún no hay clientes registrados.</td></tr>
            <?php foreach ($registros as $registro): ?>
                        <tr data-data-row data-client-type="<?= e((string) $registro['tipo']) ?>" data-client-state="<?= e($registro['estado_filtro_vista']) ?>">

                            <td>
                                <div class="admin-client-name">
                <span><i class="bi bi-person"></i></span>
                                    <div>
                <strong><?= e($registro['contacto']) ?></strong><small <?= $registro['atributoEmpresaOculta'] ?>><?= e((string) ($registro['empresa'] ?? '')) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($registro['documento']) ?></td>
                            <td><span class="admin-client-type admin-client-type--<?= e($registro['tipo_clase_vista']) ?>"><?= e($registro['tipo_vista']) ?></span></td>
                            <td><?= e($registro['telefono_vista']) ?></td>
                            <td class="admin-client-email"><?= e($registro['correo']) ?></td>
                            <td><strong><?= (int) $registro['pedidos'] ?></strong></td>

                            <td>
                <span class="admin-status <?= e($registro['estado_clase_vista']) ?>"><?= e($registro['estado_vista']) ?></span>
                            </td>

                            <td>
                                <div class="admin-table-actions">
                            <a class="admin-action-icon" href="<?= e($registro['url_editar_vista']) ?>" aria-label="Editar cliente"><i class="bi bi-pencil"></i>
                                    </a>
                            <form <?= $registro['atributoDesactivarOculto'] ?> method="post" action="<?= e($registro['url_desactivar_vista']) ?>">
                <?= csrf_field() ?>
                                        <button class="admin-action-icon is-danger" data-confirm="¿Deseas desactivar este cliente?" aria-label="Desactivar cliente"><i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr><?php endforeach; ?>
                        <tr data-client-filter-empty hidden>
                            <td colspan="8" class="admin-table-empty">
                No se encontraron clientes con esos criterios.
                            </td>
                        </tr>
                    </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>

        <aside class="admin-clients-aside">
            <section class="admin-panel">

                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                <i class="bi bi-activity"></i>
                        <div>
                            <h2>
                Actividad reciente del cliente
                            </h2>
                            <p>
                Eventos disponibles en esta vista
                            </p>
                        </div>
                    </div>
                </header>
                <div class="admin-empty admin-clients-no-activity"><i class="bi bi-clock-history"></i><span>No hay actividad reciente disponible para mostrar.</span></div>
            </section>
            <section class="admin-panel">

                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                <i class="bi bi-bar-chart-fill"></i>
                        <div>
                            <h2>
                Clientes con más pedidos
                            </h2>
                            <p>
                Ordenados por pedidos registrados
                            </p>
                        </div>
                    </div>
                </header>
            <div class="admin-empty admin-clients-no-ranking" <?= $atributoRankingVacioOculto ?>>Aún no hay pedidos registrados para generar este listado.</div>
            <ol class="admin-client-ranking" <?= $atributoRankingListaOculto ?>>
                <?php foreach ($clientesConPedidosDestacados as $cliente): ?>
                    <li>
                <span><?= (int) $cliente['puesto_vista'] ?></span>
                        <div>
                <strong><?= e($cliente['nombre_ranking_vista']) ?></strong><small><?= e($cliente['cantidad_pedidos_vista']) ?></small>
                        </div>
                <b><?= (int) $cliente['pedidos'] ?></b>
                    </li>
                <?php endforeach; ?>
                </ol>
            </section>
        </aside>
    </div>
</div>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">

<header class="admin-dialog-header">
    <div>
        <h2>
            <?= $edicion ? 'Editar cliente' : 'Nuevo cliente' ?>
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
        <?php foreach ($camposFormularioVista as $campoFormulario): ?>
            <label class="admin-form-group<?= e($campoFormulario['clase']) ?>"><span><?= e($campoFormulario['etiqueta']) ?><?= e($campoFormulario['marcadorRequerido']) ?></span>
                <?= $campoFormulario['controlHtml'] ?>
            </label>
        <?php endforeach; ?>

        </div>
        <div class="admin-form-actions">
            <button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar
            </button>
            <button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar cliente' ?>
            </button>
        </div>
    </form>
</div>
</dialog>
