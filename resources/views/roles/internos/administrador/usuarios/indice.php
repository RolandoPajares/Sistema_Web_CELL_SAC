<?php

/**
 * Datos inyectados por App\Controladores\Usuarios\AdministradorUsuarioController::indice()
 * mediante App\Nucleo\Presentacion\Vista::renderizar(), que los extrae al ámbito de esta vista.
 *
 * @var array<int, array<string, mixed>> $usuarios Registros reales de la tabla de usuarios.
 * @var array{total:int, administradores:int, otros_roles:int, por_rol:array<string, int>} $resumen Resumen consolidado de roles.
 * @var array<int, array<string, mixed>> $tarjetasKpi Tarjetas KPI consumidas por componentes/administracion/tarjetas-kpi.php.
 * @var array<string, string> $etiquetasRol Etiquetas legibles de los roles oficiales del sistema.
 
 */
?>
<header class="admin-page-head">
    <div>
        <h1>
            Usuarios y accesos <i class="bi bi-people"></i>
        </h1>
        <p>
            Consulta usuarios y los siete roles oficiales del sistema.
        </p>
    </div>
    <div class="admin-page-actions">
        <button class="admin-secondary-button" type="button" disabled title="Funcionalidad prevista para una siguiente iteración"><i class="bi bi-plus-lg"></i> Nuevo usuario · Próxima iteración
        </button>
    </div>
</header>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>
<div class="admin-grid-main">

    <section class="admin-panel" data-admin-table-container>
        <header class="admin-panel-header">
            <div class="admin-panel-title">
                <i class="bi bi-people"></i>
                <div>
                    <h2>
                Listado de usuarios
                    </h2>
                    <p>
                <?= count($usuarios) ?> cuentas reales
                    </p>
                </div>
            </div>
            <label class="admin-toolbar-search"><i class="bi bi-search"></i><input type="search" data-table-search placeholder="Buscar usuario o correo..."></label>
        </header>

        <div class="admin-table-wrap">
            <table class="admin-table" data-admin-table>
                <thead>
                    <tr>
                        <th> Usuario  </th>
                        <th> Correo </th>
                        <th> Rol </th>
                        <th> Registro </th>
                        <th> Último acceso</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr data-data-row>
                        <td>
                <b><?= e($usuario['nombre']) ?></b>
                        </td>
                        <td>
                <?= e($usuario['correo']) ?>
                        </td>
                        <td>
                <span class="admin-status admin-status--info"><?= e($etiquetasRol[$usuario['rol']] ?? $usuario['rol']) ?></span>
                        </td>
                        <td>
                <?= e($usuario['fecha_registro_vista']) ?>
                        </td>
                        <td>
                —
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="admin-pagination" data-admin-pagination>
        </div>
    </section>

    <aside class="admin-panel">
        <header class="admin-panel-header">
            <div class="admin-panel-title">
                <i class="bi bi-diagram-3"></i>
                <div>
                    <h2>
                Roles del sistema
                    </h2>
                    <p>
                Únicamente roles definidos en la base de datos
                    </p>
                </div>
            </div>
        </header>
        <div class="admin-list">
        <?php foreach ($etiquetasRol as $rol => $etiqueta): ?>
            <div class="admin-list-item">
                <span class="admin-list-icon"><i class="bi <?= $rol === 'administrador' ? 'bi-shield-check' : 'bi-person-badge' ?>"></i></span>
                <div>
                <b><?= e($etiqueta) ?></b><small><?= e($rol) ?></small>
                </div>
                <strong><?= (int) ($resumen['por_rol'][$rol] ?? 0) ?></strong>
            </div>
            <?php endforeach; ?>

        </div>
        <p class="admin-planned">
            Crear, editar, cambiar rol y desactivar usuarios queda preparado para una siguiente iteración; la tabla actual no incluye estado activo.
        </p>
    </aside>
</div>
