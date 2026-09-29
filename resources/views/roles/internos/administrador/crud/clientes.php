<?php
$tarjetasKpi = [
    ['etiqueta' => 'Total de clientes', 'valor' => (string) $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-people', 'tono' => 'azul'],
    ['etiqueta' => 'Minoristas', 'valor' => (string) $resumen['minoristas'], 'detalle' => 'Clientes activos', 'icono' => 'bi-bag-check', 'tono' => 'verde'],
    ['etiqueta' => 'Mayoristas', 'valor' => (string) $resumen['mayoristas'], 'detalle' => 'Clientes activos', 'icono' => 'bi-buildings', 'tono' => 'violeta'],
    ['etiqueta' => 'Inactivos', 'valor' => (string) $resumen['inactivos'], 'detalle' => 'Registros conservados', 'icono' => 'bi-person-x', 'tono' => 'rojo'],
];
$clientesConPedidos = array_values(array_filter($registros, static fn (array $cliente): bool => (int) $cliente['pedidos'] > 0));
usort($clientesConPedidos, static fn (array $a, array $b): int => (int) $b['pedidos'] <=> (int) $a['pedidos']);
?>
<header class="admin-page-head">
    <div><h1>Clientes <i class="bi bi-people-fill"></i></h1><p><?= e($descripcionModulo) ?></p></div>
    <div class="admin-page-actions"><button class="admin-primary-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> Nuevo cliente</button></div>
</header>
<?php if ($error): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-clients-page" data-admin-table-container>
<div class="admin-clients-toolbar admin-toolbar">
    <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar por nombre, documento, teléfono o correo..." aria-label="Buscar clientes"></label>
    <label class="admin-clients-filter"><span>Tipo de cliente</span><select data-client-type-filter aria-label="Filtrar por tipo de cliente"><option value="">Todos</option><option value="minorista">Minorista</option><option value="mayorista">Mayorista</option></select></label>
    <label class="admin-clients-filter"><span>Estado</span><select data-client-state-filter aria-label="Filtrar por estado"><option value="">Todos</option><option value="active">Activos</option><option value="inactive">Inactivos</option></select></label>
</div>

<div class="admin-clients-layout">
    <section class="admin-panel admin-clients-table-panel">
        <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-people-fill"></i><div><h2>Lista de clientes</h2><p><?= count($registros) ?> registros reales</p></div></div></header>
        <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>Cliente</th><th>Documento</th><th>Tipo</th><th>Teléfono</th><th>Correo</th><th>Pedidos</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            <?php if ($registros === []): ?><tr data-client-empty><td colspan="8" class="admin-table-empty">Aún no hay clientes registrados.</td></tr><?php endif; ?>
            <?php foreach ($registros as $registro): $tipoCliente = (string) $registro['tipo']; ?><tr data-data-row data-client-type="<?= e($tipoCliente) ?>" data-client-state="<?= (int) $registro['activo'] === 1 ? 'active' : 'inactive' ?>">
                <td><div class="admin-client-name"><span><i class="bi bi-person"></i></span><div><strong><?= e($registro['contacto']) ?></strong><?php if (!empty($registro['empresa'])): ?><small><?= e($registro['empresa']) ?></small><?php endif; ?></div></div></td>
                <td><?= e($registro['documento']) ?></td>
                <td><span class="admin-client-type admin-client-type--<?= $tipoCliente === 'mayorista' ? 'mayorista' : 'minorista' ?>"><?= e(ucfirst($tipoCliente)) ?></span></td>
                <td><?= e((string) ($registro['telefono'] ?: '—')) ?></td>
                <td class="admin-client-email"><?= e($registro['correo']) ?></td>
                <td><strong><?= (int) $registro['pedidos'] ?></strong></td>
                <td><span class="admin-status <?= (int) $registro['activo'] === 1 ? '' : 'admin-status--danger' ?>"><?= (int) $registro['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
                <td><div class="admin-table-actions"><a class="admin-action-icon" href="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/edit')) ?>" aria-label="Editar cliente"><i class="bi bi-pencil"></i></a><?php if ((int) $registro['activo'] === 1): ?><form method="post" action="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/deactivate')) ?>"><?= csrf_field() ?><button class="admin-action-icon is-danger" data-confirm="¿Deseas desactivar este cliente?" aria-label="Desactivar cliente"><i class="bi bi-person-dash"></i></button></form><?php endif; ?></div></td>
            </tr><?php endforeach; ?>
            <?php if ($registros !== []): ?><tr data-client-filter-empty hidden><td colspan="8" class="admin-table-empty">No se encontraron clientes con esos criterios.</td></tr><?php endif; ?>
        </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
    </section>

    <aside class="admin-clients-aside">
        <section class="admin-panel">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-activity"></i><div><h2>Actividad reciente del cliente</h2><p>Eventos disponibles en esta vista</p></div></div></header>
            <div class="admin-empty admin-clients-no-activity"><i class="bi bi-clock-history"></i><span>No hay actividad reciente disponible para mostrar.</span></div>
        </section>
        <section class="admin-panel">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-bar-chart-fill"></i><div><h2>Clientes con más pedidos</h2><p>Ordenados por pedidos registrados</p></div></div></header>
            <?php if ($clientesConPedidos === []): ?><div class="admin-empty admin-clients-no-ranking">Aún no hay pedidos registrados para generar este listado.</div>
            <?php else: ?><ol class="admin-client-ranking">
                <?php foreach (array_slice($clientesConPedidos, 0, 5) as $posicion => $cliente): ?><li><span><?= $posicion + 1 ?></span><div><strong><?= e($cliente['empresa'] ?: $cliente['contacto']) ?></strong><small><?= (int) $cliente['pedidos'] ?> <?= (int) $cliente['pedidos'] === 1 ? 'pedido' : 'pedidos' ?></small></div><b><?= (int) $cliente['pedidos'] ?></b></li><?php endforeach; ?>
            </ol><?php endif; ?>
        </section>
    </aside>
</div>
</div>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">
    <header class="admin-dialog-header"><div><h2><?= $edicion ? 'Editar cliente' : 'Nuevo cliente' ?></h2><p>La información se valida en el servidor antes de guardarse.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header>
    <div class="admin-dialog-body"><form method="post" action="<?= e(url($edicion ? $rutaBase . '/' . (int) $edicion['id'] : $rutaBase)) ?>"><?= csrf_field() ?><div class="admin-form-grid">
        <?php foreach ($campos as $nombre => $campo): $valor = (string) ($edicion[$nombre] ?? ''); $clase = ($campo['type'] ?? '') === 'textarea' ? ' is-full' : ''; ?>
            <label class="admin-form-group<?= $clase ?>"><span><?= e($campo['label']) ?><?= !empty($campo['required']) ? ' *' : '' ?></span>
                <?php if (($campo['type'] ?? 'text') === 'textarea'): ?><textarea name="<?= e($nombre) ?>" maxlength="<?= (int) ($campo['max'] ?? 500) ?>" rows="4"><?= e($valor) ?></textarea>
                <?php elseif (($campo['type'] ?? 'text') === 'select'): ?><select name="<?= e($nombre) ?>" <?= !empty($campo['required']) ? 'required' : '' ?>><?php foreach ($campo['options'] as $clave => $texto): ?><option value="<?= e($clave) ?>" <?= $valor === (string) $clave ? 'selected' : '' ?>><?= e($texto) ?></option><?php endforeach; ?></select>
                <?php else: ?><input type="<?= e($campo['type'] ?? 'text') ?>" name="<?= e($nombre) ?>" value="<?= e($valor) ?>" maxlength="<?= (int) ($campo['max'] ?? 160) ?>" <?= !empty($campo['required']) ? 'required' : '' ?>><?php endif; ?>
            </label>
        <?php endforeach; ?>
    </div><div class="admin-form-actions"><button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar</button><button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar cliente' ?></button></div></form></div>
</dialog>
