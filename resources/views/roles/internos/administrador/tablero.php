<?php
/**
 * @var string $tituloModulo
 * @var string $mensaje
 * @var array<string, mixed> $usuarioActual
 * @var string $fechaHoyVista
 * @var array<int, array<string, mixed>> $ventasMensuales
 * @var string $atributoVentasVaciasOculto
 * @var string $atributoGraficoVentasOculto
 * @var string $atributoPedidosRecientesVaciosOculto
 * @var array<int, array<string, mixed>> $pedidosRecientes
 * @var string $atributoExistenciasBajasVaciasOculto
 * @var string $atributoExistenciasBajasListaOculto
 * @var array<string, mixed> $inteligencia
 */
?>

<header class="admin-page-head">

    <div>
        <h1>
            Buenos días, <?= e($usuarioActual['nombre'] ?? 'Administrador') ?> <i class="bi bi-hand-thumbs-up"></i>
        </h1>
        <p>
            Resumen general del negocio de MD Technology Digital Cell S.A.C.
        </p>
    </div>
    <span class="admin-date-chip"><i class="bi bi-calendar3"></i><span>Hoy es<br><b><?= e($fechaHoyVista) ?></b></span></span>
</header>
<?php require dirname(__DIR__, 3) . '/componentes/administracion/tarjetas-kpi.php'; ?>

<div class="admin-grid-main">
    <div class="admin-stack">
        <section class="admin-panel">

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-bar-chart-fill"></i>
                    <div>
                        <h2>
                Ventas del período
                        </h2>
                        <p>
                Ventas no canceladas de los últimos 12 meses con registros
                        </p>
                    </div>
                </div>
            </header>
            <div class="admin-empty" <?= $atributoVentasVaciasOculto ?>>Sin información de ventas disponible para graficar.</div>
            <div class="admin-chart" aria-label="Ventas mensuales" <?= $atributoGraficoVentasOculto ?>>
                <?php foreach ($ventasMensuales as $fila): ?>

                <div class="admin-chart-column" title="<?= e($fila['periodo']) ?>: <?= e(formatear_dinero($fila['total'])) ?>">
                <i style="--chart-height:<?= e($fila['altura_vista']) ?>%"></i><span><?= e($fila['periodo_etiqueta_vista']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="admin-panel" data-admin-table-container>

            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-cart3"></i>
                    <div>
                        <h2>
                Pedidos recientes
                        </h2>
                        <p>
                Últimos registros disponibles
                        </p>
                    </div>
                </div>
                <a class="admin-secondary-button" href="<?= e(url_interna('admin/orders')) ?>">Ver todos
                </a>
            </header>

            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-table>
                    <thead>
                        <tr>
                            <th>
                Pedido
                            </th>
                            <th>
                Cliente
                            </th>
                            <th>
                Fecha
                            </th>
                            <th>
                Total
                            </th>
                            <th>
                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                <tr <?= $atributoPedidosRecientesVaciosOculto ?>><td colspan="5" class="admin-table-empty">No hay pedidos registrados.</td></tr>
                <?php foreach ($pedidosRecientes as $pedido): ?>
                        <tr data-data-row>
                            <td>
                                <a href="<?= e(url_interna('admin/orders/' . (int) $pedido['id'])) ?>"><b>#<?= e($pedido['codigo_pedido_vista']) ?></b>
                                </a>
                            </td>
                            <td>
                <?= e($pedido['nombre']) ?>
                            </td>
                            <td>
                <?= e($pedido['fecha_creacion_vista']) ?>
                            </td>
                            <td>
                <?= e(formatear_dinero($pedido['total'])) ?>
                            </td>
                            <td>
                <span
                class="admin-status <?= e($pedido['clase_estado_tablero_vista']) ?>"><?= e($pedido['estado']) ?></span>
                            </td>
                        </tr>
                <?php endforeach; ?>
                    </tbody></table></div>
        </section>
    </div>
    <aside class="admin-panel">

        <header class="admin-panel-header">
            <div class="admin-panel-title">
                <i class="bi bi-box-seam"></i>
                <div>
                    <h2>
                Productos con stock bajo
                    </h2>
                    <p>
                Existencias reales registradas
                    </p>
                </div>
            </div>
            <a class="admin-secondary-button" href="<?= e(url_interna('admin/inventory')) ?>">Ver todos
            </a>
        </header>
        <div class="admin-empty" <?= $atributoExistenciasBajasVaciasOculto ?>>No hay productos con stock bajo.</div><div class="admin-list" <?= $atributoExistenciasBajasListaOculto ?>>
            <?php foreach ($inteligencia['existencias_bajas'] as $producto): ?>
            <div class="admin-list-item">
                <span class="admin-list-icon"><img src="<?= e($producto['imagen_tablero_vista']) ?>" alt="" loading="lazy" <?= $producto['atributoImagenOculta'] ?>><i class="bi <?= e($producto['icono_tablero_vista']) ?>" <?= $producto['atributoIconoOculto'] ?>></i></span>
                <div>
                <b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b><small>Stock disponible</small>
                </div>
                <strong class="<?= e($producto['clase_stock_bajo_vista']) ?>"><?= (int) $producto['existencias'] ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
    </aside>
</div>
