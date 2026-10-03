<?php
/**
 * @var array<array-key, mixed> $consultaAuditoria
 * @var array<array-key, mixed> $usuarios
 * @var bool $usuarioSeleccionado
 * @var array<array-key, mixed> $entidadesVista
 * @var bool $entidadSeleccionada
 * @var string $atributoRegistrosAuditoriaVaciosOculto
 * @var string $atributoActividadAuditoriaVaciaOculto
 * @var string $atributoListaActividadAuditoriaOculta
 */ ?><div class="admin-audit-page">
    <header class="admin-page-head">
        <div><h1>Auditoría <i class="bi bi-shield-check"></i></h1><p>Supervisa acciones administrativas y trazabilidad operativa.</p></div>
        <div class="admin-page-actions"><button class="admin-secondary-button" type="button" disabled aria-disabled="true">Exportar · Próxima iteración</button></div>
    </header>

    <?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

    <form class="admin-panel admin-audit-filters" method="get" action="<?= e(url_interna('admin/audit')) ?>">
        <div class="admin-audit-filter-field"><label for="audit-from">Desde</label><input id="audit-from" type="date" name="desde" value="<?= e($consultaAuditoria['desde']) ?>"></div>
        <div class="admin-audit-filter-field"><label for="audit-to">Hasta</label><input id="audit-to" type="date" name="hasta" value="<?= e($consultaAuditoria['hasta']) ?>"></div>

        <div class="admin-audit-filter-field">
            <label for="audit-user">Usuario</label><select id="audit-user" name="usuario"><option value="">Todos</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int) $usuario['id'] ?>" <?= $usuarioSeleccionado === (int) $usuario['id'] ? 'selected' : '' ?>><?= e($usuario['nombre']) ?></option><?php endforeach; ?></select>
        </div>

        <div class="admin-audit-filter-field">
            <label for="audit-entity">Entidad</label><select id="audit-entity" name="entidad"><option value="">Todas</option><?php foreach ($entidadesVista as $entidad): ?><option value="<?= e($entidad['valor']) ?>" <?= $entidadSeleccionada === $entidad['valor'] ? 'selected' : '' ?>><?= e($entidad['etiqueta']) ?></option><?php endforeach; ?></select>
        </div>

        <div class="admin-audit-filter-actions">
            <button class="admin-primary-button" type="submit"><i class="bi bi-funnel"></i> Aplicar filtros
            </button>
            <a class="admin-secondary-button" href="<?= e(url_interna('admin/audit')) ?>">Limpiar filtros
            </a>
        </div>
    </form>

    <div class="admin-audit-layout">
        <section class="admin-panel admin-audit-list" data-admin-table-container>

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-file-earmark-text"></i>
                    <div>
                        <h2>
                Registros de auditoría
                        </h2>
                        <p>
                <?= count($consultaAuditoria['registros']) ?> eventos en el rango seleccionado
                        </p>
                    </div>
                </div>
                <label class="admin-toolbar-search admin-audit-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar en los registros..." aria-label="Buscar registros de auditoría"></label>
            </header>

            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-table>
                    <thead>
                        <tr>
                            <th>
                ID
                            </th>
                            <th>
                Fecha y hora
                            </th>
                            <th>
                Usuario
                            </th>
                            <th>
                Acción
                            </th>
                            <th>
                Entidad
                            </th>
                            <th>
                IP
                            </th>
                            <th>
                Detalle
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr <?= $atributoRegistrosAuditoriaVaciosOculto ?>>
                            <td colspan="7" class="admin-table-empty">
                                <div class="admin-audit-empty">
                <i class="bi bi-journal-x"></i><strong>No hay registros de auditoría disponibles.</strong><span>Prueba otro rango de fechas o cambia los filtros seleccionados.</span>
                                </div>
                            </td>
                        </tr>
                <?php foreach ($consultaAuditoria['registros'] as $registro): ?>
                        <tr data-data-row>

                            <td>
                <span class="admin-audit-id">#<?= (int) $registro['id'] ?></span>
                            </td>
                            <td>
                <?= e($registro['fecha_registro_vista']) ?>
                            </td>
                            <td>
                <?= e((string) $registro['usuario']) ?>
                            </td>
                            <td>
                <?= e((string) $registro['accion']) ?>
                            </td>
                            <td>
                <?= e($registro['entidad_vista']) ?>
                            </td>
                            <td>
                <?= e((string) ($registro['direccion_ip'] ?: '—')) ?>
                            </td>

                            <td>
                                <div class="admin-audit-detail-cell">
                <span><?= e($registro['detalle_vista']) ?></span>
                                    <button
                type="button"
                class="admin-action-icon"
                data-admin-dialog-open="audit-detail-dialog"
                data-audit-id="<?= (int) $registro['id'] ?>"
                data-audit-date="<?= e($registro['fecha_registro_vista']) ?>"
                data-audit-user="<?= e((string) $registro['usuario']) ?>"
                data-audit-action="<?= e((string) $registro['accion']) ?>"
                data-audit-entity="<?= e($registro['entidad_vista']) ?>"
                data-audit-ip="<?= e((string) ($registro['direccion_ip'] ?: '—')) ?>"
                data-audit-old="<?= e($registro['json_anterior_vista']) ?>"
                data-audit-new="<?= e($registro['json_nuevo_vista']) ?>"
                aria-label="Ver detalle del registro #<?= (int) $registro['id'] ?>"><i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                <?php endforeach; ?>
                    </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>

        <aside class="admin-audit-aside">

            <section class="admin-panel">
                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                <i class="bi bi-bar-chart"></i>
                        <div>
                            <h2>
                Actividad por entidad
                            </h2>
                            <p>
                Distribución real de registros
                            </p>
                        </div>
                    </div>
                </header>
                <div class="admin-empty admin-audit-side-empty" <?= $atributoActividadAuditoriaVaciaOculto ?>>Sin actividad registrada.</div>
                <div class="admin-audit-activity" <?= $atributoListaActividadAuditoriaOculta ?>>
                <?php foreach ($consultaAuditoria['actividad'] as $fila): ?>
                    <div class="admin-audit-activity-item">
                <span><i class="bi bi-grid"></i></span>
                        <div>
                <b><?= e($fila['entidad_vista']) ?></b><small>Registros almacenados</small>
                        </div>
                <strong><?= (int) $fila['total'] ?></strong>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

            <section class="admin-panel">
                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                <i class="bi bi-bell"></i>
                        <div>
                            <h2>
                Alertas recientes
                            </h2>
                            <p>
                Sin fuente de alertas disponible
                            </p>
                        </div>
                    </div>
                </header>
                <div class="admin-empty admin-audit-side-empty">
                <i class="bi bi-bell-slash"></i><span>Funcionalidad prevista para próxima iteración.</span>
                </div>
            </section>
        </aside>
    </div>
</div>

<dialog class="admin-dialog admin-audit-dialog" id="audit-detail-dialog">

<header class="admin-dialog-header">
    <div>
        <h2>
            Detalle del registro
        </h2>
        <p>
            Valores disponibles del historial de auditoría
        </p>
    </div>
    <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i>
    </button>
</header>

<div class="admin-dialog-body">
    <dl class="admin-audit-detail-grid">
        <div>
            <dt>
                ID
            </dt>
            <dd data-audit-detail="id">
                —
            </dd>
        </div>
        <div>
            <dt>
                Fecha y hora
            </dt>
            <dd data-audit-detail="date">
                —
            </dd>
        </div>
        <div>
            <dt>
                Usuario
            </dt>
            <dd data-audit-detail="user">
                —
            </dd>
        </div>
        <div>
            <dt>
                Acción
            </dt>
            <dd data-audit-detail="action">
                —
            </dd>
        </div>
        <div>
            <dt>
                Entidad
            </dt>
            <dd data-audit-detail="entity">
                —
            </dd>
        </div>
        <div>
            <dt>
                IP
            </dt>
            <dd data-audit-detail="ip">
                —
            </dd>
        </div>
        <div>
            <dt>
                Valores anteriores
            </dt>
            <dd>
                <pre data-audit-detail="old">Sin datos</pre>
            </dd>
        </div>
        <div>
            <dt>
                Valores nuevos
            </dt>
            <dd>
                <pre data-audit-detail="new">Sin datos</pre>
            </dd>
        </div>
    </dl>
</div>
</dialog>
