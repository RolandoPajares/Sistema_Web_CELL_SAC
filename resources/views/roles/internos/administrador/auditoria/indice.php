<?php
$datos = $consultaAuditoria;
$tarjetasKpi = [
    ['etiqueta' => 'Registros hoy', 'valor' => (string) $datos['resumen']['hoy'], 'detalle' => 'Eventos almacenados hoy', 'icono' => 'bi-file-earmark-text', 'tono' => 'azul'],
    ['etiqueta' => 'Registros totales', 'valor' => (string) $datos['resumen']['total'], 'detalle' => 'Historial disponible', 'icono' => 'bi-database-check', 'tono' => 'verde'],
    ['etiqueta' => 'Usuarios registrados', 'valor' => (string) $datos['resumen']['usuarios'], 'detalle' => 'Usuarios con eventos', 'icono' => 'bi-people', 'tono' => 'violeta'],
    ['etiqueta' => 'Entidades auditadas', 'valor' => (string) $datos['resumen']['entidades'], 'detalle' => 'Módulos con eventos', 'icono' => 'bi-shield-check', 'tono' => 'rojo'],
];
$entidades = array_values(array_unique(array_map(static fn (array $fila): string => (string) $fila['entidad'], $datos['actividad'])));
sort($entidades);
?>
<div class="admin-audit-page">
    <header class="admin-page-head">
        <div><h1>Auditoría <i class="bi bi-shield-check"></i></h1><p>Supervisa acciones administrativas y trazabilidad operativa.</p></div>
        <div class="admin-page-actions"><button class="admin-secondary-button" type="button" disabled aria-disabled="true">Exportar · Próxima iteración</button></div>
    </header>

    <?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

    <form class="admin-panel admin-audit-filters" method="get" action="<?= e(url('admin/audit')) ?>">
        <div class="admin-audit-filter-field"><label for="audit-from">Desde</label><input id="audit-from" type="date" name="desde" value="<?= e($datos['desde']) ?>"></div>
        <div class="admin-audit-filter-field"><label for="audit-to">Hasta</label><input id="audit-to" type="date" name="hasta" value="<?= e($datos['hasta']) ?>"></div>
        <div class="admin-audit-filter-field"><label for="audit-user">Usuario</label><select id="audit-user" name="usuario"><option value="">Todos</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int) $usuario['id'] ?>" <?= $usuarioSeleccionado === (int) $usuario['id'] ? 'selected' : '' ?>><?= e($usuario['nombre']) ?></option><?php endforeach; ?></select></div>
        <div class="admin-audit-filter-field"><label for="audit-entity">Entidad</label><select id="audit-entity" name="entidad"><option value="">Todas</option><?php foreach ($entidades as $entidad): ?><option value="<?= e($entidad) ?>" <?= $entidadSeleccionada === $entidad ? 'selected' : '' ?>><?= e(ucfirst($entidad)) ?></option><?php endforeach; ?></select></div>
        <div class="admin-audit-filter-actions"><button class="admin-primary-button" type="submit"><i class="bi bi-funnel"></i> Aplicar filtros</button><a class="admin-secondary-button" href="<?= e(url('admin/audit')) ?>">Limpiar filtros</a></div>
    </form>

    <div class="admin-audit-layout">
        <section class="admin-panel admin-audit-list" data-admin-table-container>
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-file-earmark-search"></i><div><h2>Registros de auditoría</h2><p><?= count($datos['registros']) ?> eventos en el rango seleccionado</p></div></div><label class="admin-toolbar-search admin-audit-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar en los registros..." aria-label="Buscar registros de auditoría"></label></header>
            <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>ID</th><th>Fecha y hora</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>IP</th><th>Detalle</th></tr></thead><tbody>
                <?php if ($datos['registros'] === []): ?><tr><td colspan="7" class="admin-table-empty"><div class="admin-audit-empty"><i class="bi bi-journal-x"></i><strong>No hay registros de auditoría disponibles.</strong><span>Prueba otro rango de fechas o cambia los filtros seleccionados.</span></div></td></tr><?php endif; ?>
                <?php foreach ($datos['registros'] as $registro):
                    $anteriores = json_decode((string) ($registro['valores_anteriores'] ?? ''), true);
                    $nuevos = json_decode((string) ($registro['valores_nuevos'] ?? ''), true);
                    $anteriores = is_array($anteriores) ? $anteriores : [];
                    $nuevos = is_array($nuevos) ? $nuevos : [];
                    unset($anteriores['contrasena'], $anteriores['token'], $anteriores['csrf'], $nuevos['contrasena'], $nuevos['token'], $nuevos['csrf']);
                    $detalle = $nuevos !== [] ? implode(', ', array_keys($nuevos)) : ($anteriores !== [] ? implode(', ', array_keys($anteriores)) : 'Sin detalle adicional');
                    $instante = strtotime((string) $registro['creado_en']);
                    $fechaRegistro = $instante !== false ? date('d/m/Y H:i:s', $instante) : '—';
                    $entidadRegistro = (string) $registro['entidad'] . (!empty($registro['entidad_id']) ? ' #' . (int) $registro['entidad_id'] : '');
                    $jsonAnterior = json_encode($anteriores, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?: '{}';
                    $jsonNuevo = json_encode($nuevos, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?: '{}';
                ?>
                    <tr data-data-row>
                        <td><span class="admin-audit-id">#<?= (int) $registro['id'] ?></span></td><td><?= e($fechaRegistro) ?></td><td><?= e((string) $registro['usuario']) ?></td><td><?= e((string) $registro['accion']) ?></td><td><?= e($entidadRegistro) ?></td><td><?= e((string) ($registro['direccion_ip'] ?: '—')) ?></td>
                        <td><div class="admin-audit-detail-cell"><span><?= e($detalle) ?></span><button type="button" class="admin-action-icon" data-admin-dialog-open="audit-detail-dialog" data-audit-id="<?= (int) $registro['id'] ?>" data-audit-date="<?= e($fechaRegistro) ?>" data-audit-user="<?= e((string) $registro['usuario']) ?>" data-audit-action="<?= e((string) $registro['accion']) ?>" data-audit-entity="<?= e($entidadRegistro) ?>" data-audit-ip="<?= e((string) ($registro['direccion_ip'] ?: '—')) ?>" data-audit-old="<?= e($jsonAnterior) ?>" data-audit-new="<?= e($jsonNuevo) ?>" aria-label="Ver detalle del registro #<?= (int) $registro['id'] ?>"><i class="bi bi-eye"></i></button></div></td>
                    </tr>
                <?php endforeach; ?>
            </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>

        <aside class="admin-audit-aside">
            <section class="admin-panel"><header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-bar-chart"></i><div><h2>Actividad por entidad</h2><p>Distribución real de registros</p></div></div></header>
                <?php if ($datos['actividad'] === []): ?><div class="admin-empty admin-audit-side-empty">Sin actividad registrada.</div>
                <?php else: ?><div class="admin-audit-activity"><?php foreach ($datos['actividad'] as $fila): ?><div class="admin-audit-activity-item"><span><i class="bi bi-grid"></i></span><div><b><?= e(ucfirst((string) $fila['entidad'])) ?></b><small>Registros almacenados</small></div><strong><?= (int) $fila['total'] ?></strong></div><?php endforeach; ?></div><?php endif; ?>
            </section>
            <section class="admin-panel"><header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-bell"></i><div><h2>Alertas recientes</h2><p>Sin fuente de alertas disponible</p></div></div></header><div class="admin-empty admin-audit-side-empty"><i class="bi bi-bell-slash"></i><span>Funcionalidad prevista para próxima iteración.</span></div></section>
        </aside>
    </div>
</div>

<dialog class="admin-dialog admin-audit-dialog" id="audit-detail-dialog">
    <header class="admin-dialog-header"><div><h2>Detalle del registro</h2><p>Valores disponibles del historial de auditoría</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header>
    <div class="admin-dialog-body"><dl class="admin-audit-detail-grid"><div><dt>ID</dt><dd data-audit-detail="id">—</dd></div><div><dt>Fecha y hora</dt><dd data-audit-detail="date">—</dd></div><div><dt>Usuario</dt><dd data-audit-detail="user">—</dd></div><div><dt>Acción</dt><dd data-audit-detail="action">—</dd></div><div><dt>Entidad</dt><dd data-audit-detail="entity">—</dd></div><div><dt>IP</dt><dd data-audit-detail="ip">—</dd></div><div><dt>Valores anteriores</dt><dd><pre data-audit-detail="old">Sin datos</pre></dd></div><div><dt>Valores nuevos</dt><dd><pre data-audit-detail="new">Sin datos</pre></dd></div></dl></div>
</dialog>
