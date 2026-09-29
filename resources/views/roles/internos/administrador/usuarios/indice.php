<?php
$tarjetasKpi = [
    ['etiqueta' => 'Usuarios registrados', 'valor' => (string) $resumen['total'], 'detalle' => 'Cuentas en MySQL', 'icono' => 'bi-people', 'tono' => 'azul'],
    ['etiqueta' => 'Administradores', 'valor' => (string) $resumen['administradores'], 'detalle' => 'Rol oficial administrador', 'icono' => 'bi-shield-check', 'tono' => 'violeta'],
    ['etiqueta' => 'Otros roles', 'valor' => (string) $resumen['otros_roles'], 'detalle' => 'Seis roles oficiales restantes', 'icono' => 'bi-person', 'tono' => 'verde'],
    ['etiqueta' => 'Últimos accesos', 'valor' => '—', 'detalle' => 'Dato no disponible en el esquema', 'icono' => 'bi-clock', 'tono' => 'rojo'],
];
$etiquetasRol = [
    'cliente_minorista' => 'Cliente minorista', 'cliente_mayorista' => 'Cliente mayorista',
    'administrador' => 'Administrador', 'compras_logistica' => 'Compras y logística',
    'ventas_mayoristas' => 'Ventas mayoristas', 'ventas_minoristas' => 'Ventas minoristas', 'marketing' => 'Marketing',
];
?>
<header class="admin-page-head"><div><h1>Usuarios y accesos <i class="bi bi-people"></i></h1><p>Consulta usuarios y los siete roles oficiales del sistema.</p></div><div class="admin-page-actions"><button class="admin-secondary-button" type="button" disabled title="Funcionalidad prevista para una siguiente iteración"><i class="bi bi-plus-lg"></i> Nuevo usuario · Próxima iteración</button></div></header>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>
<div class="admin-grid-main">
    <section class="admin-panel" data-admin-table-container><header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-people"></i><div><h2>Listado de usuarios</h2><p><?= count($usuarios) ?> cuentas reales</p></div></div><label class="admin-toolbar-search"><i class="bi bi-search"></i><input type="search" data-table-search placeholder="Buscar usuario o correo..."></label></header>
        <div class="admin-table-wrap"><table class="admin-table" data-admin-table><thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Registro</th><th>Último acceso</th></tr></thead><tbody><?php foreach ($usuarios as $usuario): ?><tr data-data-row><td><b><?= e($usuario['nombre']) ?></b></td><td><?= e($usuario['correo']) ?></td><td><span class="admin-status admin-status--info"><?= e($etiquetasRol[$usuario['rol']] ?? $usuario['rol']) ?></span></td><td><?= e(date('d/m/Y', strtotime((string) $usuario['creado_en']))) ?></td><td>—</td></tr><?php endforeach; ?></tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
    </section>
    <aside class="admin-panel"><header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-diagram-3"></i><div><h2>Roles del sistema</h2><p>Únicamente roles definidos en la base de datos</p></div></div></header><div class="admin-list">
        <?php foreach ($etiquetasRol as $rol => $etiqueta): ?><div class="admin-list-item"><span class="admin-list-icon"><i class="bi <?= $rol === 'administrador' ? 'bi-shield-check' : 'bi-person-badge' ?>"></i></span><div><b><?= e($etiqueta) ?></b><small><?= e($rol) ?></small></div><strong><?= (int) ($resumen['por_rol'][$rol] ?? 0) ?></strong></div><?php endforeach; ?>
    </div><p class="admin-planned">Crear, editar, cambiar rol y desactivar usuarios queda preparado para una siguiente iteración; la tabla actual no incluye estado activo.</p></aside>
</div>
