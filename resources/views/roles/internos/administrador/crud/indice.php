<?php
if ($tipoModulo === 'categorias') {
    require __DIR__ . '/categorias.php';
    return;
}
if ($tipoModulo === 'clientes') {
    require __DIR__ . '/clientes.php';
    return;
}
if ($tipoModulo === 'proveedores') {
    require __DIR__ . '/proveedores.php';
    return;
}

$configuracionKpi = match ($tipoModulo) {
    'categorias' => [
        ['etiqueta' => 'Total de categorías', 'valor' => $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-tags', 'tono' => 'azul'],
        ['etiqueta' => 'Activas', 'valor' => $resumen['activas'], 'detalle' => 'Disponibles para productos', 'icono' => 'bi-check-circle', 'tono' => 'verde'],
        ['etiqueta' => 'Con productos', 'valor' => $resumen['con_productos'], 'detalle' => 'Asociaciones activas', 'icono' => 'bi-box-seam', 'tono' => 'violeta'],
        ['etiqueta' => 'Sin productos', 'valor' => $resumen['sin_productos'], 'detalle' => 'Sin asociaciones activas', 'icono' => 'bi-slash-square', 'tono' => 'rojo'],
    ],
    'proveedores' => [
        ['etiqueta' => 'Total proveedores', 'valor' => $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-truck', 'tono' => 'azul'],
        ['etiqueta' => 'Activos', 'valor' => $resumen['activos'], 'detalle' => 'Disponibles', 'icono' => 'bi-people', 'tono' => 'verde'],
        ['etiqueta' => 'Inactivos', 'valor' => $resumen['inactivos'], 'detalle' => 'Registros conservados', 'icono' => 'bi-person-dash', 'tono' => 'violeta'],
        ['etiqueta' => 'Ciudades', 'valor' => $resumen['ciudades'], 'detalle' => 'Ciudades registradas', 'icono' => 'bi-geo-alt', 'tono' => 'rojo'],
    ],
    default => [
        ['etiqueta' => 'Total clientes', 'valor' => $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-people', 'tono' => 'azul'],
        ['etiqueta' => 'Minoristas activos', 'valor' => $resumen['minoristas'], 'detalle' => 'Clientes minoristas', 'icono' => 'bi-bag-check', 'tono' => 'verde'],
        ['etiqueta' => 'Mayoristas activos', 'valor' => $resumen['mayoristas'], 'detalle' => 'Clientes mayoristas', 'icono' => 'bi-buildings', 'tono' => 'violeta'],
        ['etiqueta' => 'Inactivos', 'valor' => $resumen['inactivos'], 'detalle' => 'Registros conservados', 'icono' => 'bi-person-x', 'tono' => 'rojo'],
    ],
};
$tarjetasKpi = $configuracionKpi;
?>
<header class="admin-page-head"><div><h1><?= e($tituloModulo) ?> <i class="bi <?= e($iconoModulo) ?>"></i></h1><p><?= e($descripcionModulo) ?></p></div><div class="admin-page-actions"><button class="admin-primary-button" type="button" data-admin-dialog-open="crud-dialog"><i class="bi bi-plus-lg"></i> <?= e($botonNuevo) ?></button></div></header>
<?php if ($error): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>
<div class="admin-toolbar"><label class="admin-toolbar-search"><i class="bi bi-search"></i><input type="search" data-table-search placeholder="Buscar en el listado..."></label><span class="admin-planned">Filtros adicionales: próxima iteración</span></div>
<section class="admin-panel" data-admin-table-container>
    <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi <?= e($iconoModulo) ?>"></i><div><h2>Listado de <?= e(mb_strtolower($tituloModulo)) ?></h2><p><?= count($registros) ?> registros reales</p></div></div></header>
    <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><?php foreach ($columnas as $etiqueta): ?><th><?= e($etiqueta) ?></th><?php endforeach; ?><th>Acciones</th></tr></thead><tbody>
        <?php if ($registros === []): ?><tr><td colspan="<?= count($columnas) + 1 ?>" class="admin-table-empty">Aún no existen registros.</td></tr><?php endif; ?>
        <?php foreach ($registros as $registro): ?><tr data-data-row><?php foreach ($columnas as $clave => $etiqueta): ?><td>
            <?php if ($clave === 'activo'): ?><span class="admin-status <?= (int) $registro[$clave] === 1 ? '' : 'admin-status--muted' ?>"><?= (int) $registro[$clave] === 1 ? 'Activo' : 'Inactivo' ?></span>
            <?php elseif ($clave === 'tipo'): ?><span class="admin-status admin-status--info"><?= e(ucfirst((string) $registro[$clave])) ?></span>
            <?php elseif ($clave === 'creado_en'): ?><?= e(date('d/m/Y', strtotime((string) $registro[$clave]))) ?>
            <?php else: ?><?= e((string) (($registro[$clave] ?? '') !== '' ? $registro[$clave] : '—')) ?><?php endif; ?>
        </td><?php endforeach; ?><td><div class="admin-table-actions"><a class="admin-action-icon" href="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/edit')) ?>" aria-label="Editar"><i class="bi bi-pencil"></i></a><?php if ((int) ($registro['activo'] ?? 0) === 1): ?><form method="post" action="<?= e(url($rutaBase . '/' . (int) $registro['id'] . '/deactivate')) ?>"><?= csrf_field() ?><button class="admin-action-icon is-danger" data-confirm="¿Deseas desactivar este registro?" aria-label="Desactivar"><i class="bi bi-slash-circle"></i></button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
    </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
</section>

<dialog class="admin-dialog" id="crud-dialog" data-auto-open="<?= $edicion ? '1' : '0' ?>">
    <header class="admin-dialog-header"><div><h2><?= $edicion ? 'Editar registro' : e($botonNuevo) ?></h2><p>La información se valida en el servidor antes de guardarse.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header>
    <div class="admin-dialog-body"><form method="post" action="<?= e(url($edicion ? $rutaBase . '/' . (int) $edicion['id'] : $rutaBase)) ?>"><?= csrf_field() ?><div class="admin-form-grid">
        <?php foreach ($campos as $nombre => $campo): $valor = (string) ($edicion[$nombre] ?? ''); $clase = ($campo['type'] ?? '') === 'textarea' ? ' is-full' : ''; ?>
            <label class="admin-form-group<?= $clase ?>"><span><?= e($campo['label']) ?><?= !empty($campo['required']) ? ' *' : '' ?></span>
                <?php if (($campo['type'] ?? 'text') === 'textarea'): ?><textarea name="<?= e($nombre) ?>" maxlength="<?= (int) ($campo['max'] ?? 500) ?>" rows="4"><?= e($valor) ?></textarea>
                <?php elseif (($campo['type'] ?? 'text') === 'select'): ?><select name="<?= e($nombre) ?>" <?= !empty($campo['required']) ? 'required' : '' ?>><?php foreach ($campo['options'] as $clave => $texto): ?><option value="<?= e($clave) ?>" <?= $valor === (string) $clave ? 'selected' : '' ?>><?= e($texto) ?></option><?php endforeach; ?></select>
                <?php else: ?><input type="<?= e($campo['type'] ?? 'text') ?>" name="<?= e($nombre) ?>" value="<?= e($valor) ?>" maxlength="<?= (int) ($campo['max'] ?? 160) ?>" <?= !empty($campo['required']) ? 'required' : '' ?>><?php endif; ?>
            </label>
        <?php endforeach; ?>
    </div><div class="admin-form-actions"><button class="admin-secondary-button" type="button" data-admin-dialog-close>Cancelar</button><button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> <?= $edicion ? 'Guardar cambios' : 'Registrar' ?></button></div></form></div>
</dialog>
