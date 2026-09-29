<?php
$proveedoresDelMes = array_values(array_filter($registros, static function (array $proveedor): bool {
    $fecha = strtotime((string) ($proveedor['creado_en'] ?? ''));
    return $fecha !== false && date('Y-m', $fecha) === date('Y-m');
}));
$proveedoresRecientes = $registros;
usort($proveedoresRecientes, static fn (array $a, array $b): int => strcmp((string) ($b['creado_en'] ?? ''), (string) ($a['creado_en'] ?? '')));
$proveedoresPorCiudad = [];
foreach ($registros as $proveedor) {
    $ciudad = trim((string) ($proveedor['ciudad'] ?? ''));
    if ($ciudad !== '') {
        $proveedoresPorCiudad[$ciudad] = ($proveedoresPorCiudad[$ciudad] ?? 0) + 1;
    }
}
arsort($proveedoresPorCiudad);
$ciudadMaxima = max($proveedoresPorCiudad ?: [0]);
$ciudadesProveedor = array_keys($proveedoresPorCiudad);
?>
<header class="admin-page-head">
    <div><h1>Proveedores <i class="bi bi-truck"></i></h1><p><?= e($descripcionModulo) ?></p></div>
</header>
<?php if ($error): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>
<?php
$tarjetasKpi = [
    ['etiqueta' => 'Total proveedores', 'valor' => (string) $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-truck', 'tono' => 'azul'],
    ['etiqueta' => 'Activos', 'valor' => (string) $resumen['activos'], 'detalle' => 'Proveedores disponibles', 'icono' => 'bi-people', 'tono' => 'verde'],
    ['etiqueta' => 'Nuevos este mes', 'valor' => (string) count($proveedoresDelMes), 'detalle' => 'Según fecha de registro', 'icono' => 'bi-person-plus', 'tono' => 'violeta'],
    ['etiqueta' => 'Ciudades', 'valor' => (string) $resumen['ciudades'], 'detalle' => 'Con proveedores registrados', 'icono' => 'bi-geo-alt', 'tono' => 'rojo'],
];
require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php';
?>

<div class="admin-suppliers-page" data-admin-table-container>
    <div class="admin-suppliers-layout">
        <section class="admin-panel admin-suppliers-list">
            <header class="admin-panel-header">
                <div class="admin-panel-title"><i class="bi bi-truck"></i><div><h2>Listado de proveedores</h2><p>Administra la información de tus proveedores</p></div></div>
                <button class="admin-primary-button admin-suppliers-header-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> Nuevo proveedor</button>
            </header>
            <div class="admin-suppliers-toolbar">
                <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar proveedor, RUC o contacto..." aria-label="Buscar proveedores"></label>
                <label class="admin-suppliers-filter"><span>Estado</span><select data-supplier-state-filter aria-label="Filtrar por estado"><option value="">Todos</option><option value="active">Activos</option><option value="inactive">Inactivos</option></select></label>
                <label class="admin-suppliers-filter"><span>Ciudad</span><select data-supplier-city-filter aria-label="Filtrar por ciudad"><option value="">Todas las ciudades</option><?php foreach ($ciudadesProveedor as $ciudad): ?><option value="<?= e(mb_strtolower($ciudad)) ?>"><?= e($ciudad) ?></option><?php endforeach; ?></select></label>
            </div>
            <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>Proveedor</th><th>RUC</th><th>Contacto</th><th>Teléfono</th><th>Correo</th><th>Ciudad</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
                <?php if ($registros === []): ?><tr data-supplier-empty><td colspan="8" class="admin-table-empty">Aún no hay proveedores registrados.</td></tr><?php endif; ?>
                <?php foreach ($registros as $registro): $nombreProveedor = (string) $registro['nombre']; $ciudadProveedor = trim((string) ($registro['ciudad'] ?? '')); ?><tr data-data-row data-supplier-state="<?= (int) $registro['activo'] === 1 ? 'active' : 'inactive' ?>" data-supplier-city="<?= e(mb_strtolower($ciudadProveedor)) ?>">
                    <td><div class="admin-supplier-name"><span><?= e(mb_strtoupper(mb_substr($nombreProveedor, 0, 1))) ?></span><strong><?= e($nombreProveedor) ?></strong></div></td>
                    <td><?= e((string) $registro['ruc']) ?></td><td><?= e((string) ($registro['contacto'] ?? '—')) ?></td><td><?= e((string) ($registro['telefono'] ?: '—')) ?></td><td class="admin-supplier-email"><?= e((string) $registro['correo']) ?></td><td><?= e($ciudadProveedor !== '' ? $ciudadProveedor : '—') ?></td>
                    <td><span class="admin-status <?= (int) $registro['activo'] === 1 ? '' : 'admin-status--muted' ?>"><?= (int) $registro['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
                    <td><div class="admin-table-actions"><a class="admin-action-icon" href="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/edit')) ?>" aria-label="Editar proveedor"><i class="bi bi-pencil"></i></a><?php if ((int) $registro['activo'] === 1): ?><form method="post" action="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/deactivate')) ?>"><?= csrf_field() ?><button class="admin-action-icon is-danger" data-confirm="¿Deseas desactivar este proveedor?" aria-label="Desactivar proveedor"><i class="bi bi-person-dash"></i></button></form><?php endif; ?></div></td>
                </tr><?php endforeach; ?>
                <?php if ($registros !== []): ?><tr data-supplier-filter-empty hidden><td colspan="8" class="admin-table-empty">No se encontraron proveedores con esos criterios.</td></tr><?php endif; ?>
            </tbody></table></div>
            <div class="admin-pagination" data-admin-pagination></div>
        </section>

        <aside class="admin-suppliers-aside">
            <section class="admin-panel">
                <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-clock-history"></i><div><h2>Últimos proveedores registrados</h2><p>Ordenados por fecha de registro</p></div></div></header>
                <?php if ($proveedoresRecientes === []): ?><div class="admin-empty admin-suppliers-empty">No hay proveedores registrados.</div>
                <?php else: ?><ul class="admin-supplier-recent"><?php foreach (array_slice($proveedoresRecientes, 0, 5) as $proveedor): ?><li><span><?= e(mb_strtoupper(mb_substr((string) $proveedor['nombre'], 0, 1))) ?></span><strong><?= e((string) $proveedor['nombre']) ?></strong><time datetime="<?= e(date('Y-m-d', strtotime((string) $proveedor['creado_en']))) ?>"><?= e(date('d/m/Y', strtotime((string) $proveedor['creado_en']))) ?></time><small class="admin-status <?= (int) $proveedor['activo'] === 1 ? '' : 'admin-status--muted' ?>"><?= (int) $proveedor['activo'] === 1 ? 'Activo' : 'Inactivo' ?></small></li><?php endforeach; ?></ul><?php endif; ?>
            </section>
            <section class="admin-panel">
                <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-bar-chart-fill"></i><div><h2>Cobertura por ciudad</h2><p>Proveedores registrados por ciudad</p></div></div></header>
                <?php if ($proveedoresPorCiudad === []): ?><div class="admin-empty admin-suppliers-empty">No hay ciudades disponibles en los registros.</div>
                <?php else: ?><ul class="admin-supplier-cities"><?php foreach (array_slice($proveedoresPorCiudad, 0, 8, true) as $ciudad => $cantidad): ?><li><span><?= e($ciudad) ?></span><b><?= (int) $cantidad ?></b><i aria-hidden="true"><span style="width: <?= $ciudadMaxima > 0 ? round($cantidad / $ciudadMaxima * 100) : 0 ?>%"></span></i></li><?php endforeach; ?></ul><?php endif; ?>
            </section>
        </aside>
    </div>
</div>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">
    <header class="admin-dialog-header"><div><h2><?= $edicion ? 'Editar proveedor' : 'Nuevo proveedor' ?></h2><p>La información se valida en el servidor antes de guardarse.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header>
    <div class="admin-dialog-body"><form method="post" action="<?= e(url($edicion ? $rutaBase . '/' . (int) $edicion['id'] : $rutaBase)) ?>"><?= csrf_field() ?><div class="admin-form-grid">
        <?php foreach ($campos as $nombre => $campo): $valor = (string) ($edicion[$nombre] ?? ''); $clase = ($campo['type'] ?? '') === 'textarea' ? ' is-full' : ''; ?>
            <label class="admin-form-group<?= $clase ?>"><span><?= e($campo['label']) ?><?= !empty($campo['required']) ? ' *' : '' ?></span>
                <?php if (($campo['type'] ?? 'text') === 'textarea'): ?><textarea name="<?= e($nombre) ?>" maxlength="<?= (int) ($campo['max'] ?? 500) ?>" rows="4"><?= e($valor) ?></textarea>
                <?php elseif (($campo['type'] ?? 'text') === 'select'): ?><select name="<?= e($nombre) ?>" <?= !empty($campo['required']) ? 'required' : '' ?>><?php foreach ($campo['options'] as $clave => $texto): ?><option value="<?= e($clave) ?>" <?= $valor === (string) $clave ? 'selected' : '' ?>><?= e($texto) ?></option><?php endforeach; ?></select>
                <?php else: ?><input type="<?= e($campo['type'] ?? 'text') ?>" name="<?= e($nombre) ?>" value="<?= e($valor) ?>" maxlength="<?= (int) ($campo['max'] ?? 160) ?>" <?= !empty($campo['required']) ? 'required' : '' ?>><?php endif; ?>
            </label>
        <?php endforeach; ?>
    </div><div class="admin-form-actions"><button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar</button><button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar proveedor' ?></button></div></form></div>
</dialog>
