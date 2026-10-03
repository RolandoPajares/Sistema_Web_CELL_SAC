<?php
/**
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var array<array-key, mixed> $estadosPedido
 * @var array<array-key, mixed> $pedidos
 * @var string $atributoPedidosVaciosOculto
 * @var array<array-key, mixed> $detalle
 * @var string $atributoPedidoDetalleOculto
 * @var string $atributoPedidoDetalleVacioOculto
 */ ?><header class="admin-page-head">
    <div>
        <h1>
            Pedidos <i class="bi bi-cart3"></i>
        </h1>
        <p>
            Seguimiento y control de pedidos comerciales.
        </p>
    </div>
    <div class="admin-page-actions">
        <button
            class="admin-primary-button admin-orders-new-button"
            type="button"
            disabled
            aria-disabled="true"
            aria-describedby="admin-orders-new-note"
            title="Creación administrativa prevista para una siguiente iteración"><i class="bi bi-plus-lg"></i> Nuevo pedido
        </button>
        <span class="admin-orders-action-note" id="admin-orders-new-note">Próxima iteración</span>
    </div>
</header>
<div class="admin-alert admin-alert--error" role="alert" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
<div class="admin-alert admin-alert--success" role="status" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>
<?php require dirname(__DIR__, 4) . '/componentes/administracion/tarjetas-kpi.php'; ?>
<div class="admin-orders-content" data-admin-table-container>
    <div class="admin-toolbar admin-orders-toolbar">
        <label class="admin-toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-table-search placeholder="Buscar pedido o cliente..." aria-label="Buscar pedidos o clientes"></label>
        <label class="admin-orders-filter">
            <span>Estado</span>
            <select data-order-status-filter aria-label="Filtrar pedidos por estado">
                <option value="">Todos los estados</option>
                <?php foreach ($estadosPedido as $estadoOpcion): ?>
                    <option value="<?= e($estadoOpcion) ?>"><?= e($estadoOpcion) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="admin-orders-filter">
            <span>Fecha</span>
            <select data-order-period-filter aria-label="Filtrar pedidos por período">
                <option value="all">Todos los períodos</option>
                <option value="30">Últimos 30 días</option>
                <option value="90">Últimos 90 días</option>
            </select>
        </label>
    </div>
    <div class="admin-grid-main">

        <section class="admin-panel" data-orders-list>
            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-cart3"></i>
                    <div>
                        <h2>
                Lista de pedidos (<?= count($pedidos) ?>)
                        </h2>
                        <p>
                Pedidos almacenados en MySQL
                        </p>
                    </div>
                </div>
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
                            <th>
                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
            <tr data-orders-empty <?= $atributoPedidosVaciosOculto ?>><td colspan="6" class="admin-table-empty">Todavía no hay pedidos registrados.</td></tr>
            <?php foreach ($pedidos as $pedido): ?>
                        <tr
                data-data-row
                data-order-status="<?= e($pedido['estado']) ?>"
                data-order-time="<?= e($pedido['fecha_creacion_timestamp_vista']) ?>"
                class="<?= e($pedido['clase_fila_vista']) ?>">
                            <td>
                                <a href="<?= e(url_interna('admin/orders/' . (int) $pedido['id'])) ?>"><b>#<?= e($pedido['codigo_pedido_vista']) ?></b>
                                </a>
                            </td>
                            <td>
                <?= e($pedido['nombre']) ?><small><?= e($pedido['correo']) ?></small>
                            </td>
                            <td>
                <?= e($pedido['fecha_creacion_vista']) ?>
                            </td>
                            <td>
                <b><?= e(formatear_dinero($pedido['total'])) ?></b>
                            </td>
                            <td>
                <span class="admin-status <?= e($pedido['clase_estado_vista']) ?>"><?= e($pedido['estado']) ?></span>
                            </td>
                            <td>
                                <a class="admin-action-icon" href="<?= e(url_interna('admin/orders/' . (int) $pedido['id'])) ?>" aria-label="Ver detalle"><i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                <?php endforeach; ?>
                        <tr data-orders-filter-empty hidden>
                            <td colspan="6" class="admin-table-empty">
                No se encontraron pedidos para esos filtros.
                            </td>
                        </tr>
                    </tbody></table></div><div class="admin-pagination" data-admin-pagination></div>
        </section>

        <aside class="admin-panel admin-order-detail">
            <header class="admin-panel-header">
                <div class="admin-panel-title">
                <i class="bi bi-receipt"></i>
                    <div>
                        <h2>
                Detalle del pedido
                        </h2>
                        <p>
                <?= e($detalle['titulo_detalle_vista']) ?>
                        </p>
                    </div>
                </div>
                <a class="admin-action-icon" href="<?= e(url_interna('admin/orders')) ?>" aria-label="Cerrar detalle" <?= $atributoPedidoDetalleOculto ?>><i class="bi bi-x-lg"></i>
                </a>
            </header>
        <div class="admin-order-detail-content" <?= $atributoPedidoDetalleOculto ?>>
            <div class="admin-order-detail-summary">
                <div>
                <strong>#<?= e($detalle['codigo_pedido_vista']) ?></strong><span class="admin-status <?= e($detalle['clase_estado_vista']) ?>"><?= e($detalle['estado']) ?></span>
                </div>
                <small>Realizado el <?= e($detalle['fecha_creacion_vista']) ?></small>
            </div>

            <section class="admin-order-customer">
                <h3>
                <i class="bi bi-person"></i> Datos del cliente
                </h3>
                <b><?= e($detalle['nombre']) ?></b>
                <a href="mailto:<?= e($detalle['correo']) ?>"><?= e($detalle['correo']) ?>
                </a>
            </section>
            <section class="admin-order-items"><h3><i class="bi bi-box-seam"></i> Productos (<?= count($detalle['detalle']) ?>)</h3>
            <?php foreach ($detalle['detalle'] as $detalleProducto): ?>
                <div class="admin-order-item">
                <img class="admin-order-product-photo" src="<?= e($detalleProducto['imagen_pedido_vista']) ?>" alt="" loading="lazy" <?= $detalleProducto['atributoImagenOculta'] ?>><span class="admin-list-icon" <?= $detalleProducto['atributoIconoOculto'] ?>><i class="bi <?= e($detalleProducto['icono_pedido_vista']) ?>"></i></span>
                    <div>
                <b><?= e($detalleProducto['marca'] . ' ' . $detalleProducto['nombre']) ?></b><small><?= (int) $detalleProducto['cantidad'] ?> × <?= e(formatear_dinero($detalleProducto['precio_unitario'])) ?></small>
                    </div>
                <strong><?= e(formatear_dinero($detalleProducto['subtotal'])) ?></strong>
                </div>
                <?php endforeach; ?>
            </section>
            <div class="admin-order-total"><span>Total</span><strong><?= e(formatear_dinero($detalle['total'])) ?></strong></div>

            <form class="admin-order-status-form" method="post" action="<?= e($detalle['url_actualizar_estado_vista']) ?>">
                <?= csrf_field() ?>
                <label class="admin-form-group">
                    <span>Actualizar estado</span>
                    <select name="estado" required>
                        <?php foreach ($detalle['estados_vista'] as $estadoOpcion): ?>
                            <option value="<?= e($estadoOpcion['valor']) ?>" <?= $estadoOpcion['atributoSeleccionada'] ?>>
                                <?= e($estadoOpcion['valor']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="admin-form-actions">
                    <button class="admin-primary-button" type="submit"><i class="bi bi-arrow-repeat"></i> Actualizar estado
                    </button>
                </div>
            </form>
        </div>
            <div class="admin-empty admin-order-empty" <?= $atributoPedidoDetalleVacioOculto ?>>
                Selecciona un pedido para consultar sus productos.
            </div>
            <div class="admin-order-status-placeholder" <?= $atributoPedidoDetalleVacioOculto ?>>
                <label class="admin-form-group"><span>Actualizar estado</span><select disabled aria-label="Selecciona un pedido para habilitar el estado"><option>Selecciona un pedido</option></select></label>
                <button class="admin-primary-button" type="button" disabled aria-disabled="true"><i class="bi bi-arrow-repeat"></i> Actualizar estado
                </button>
            </div>
        </aside>
    </div>
</div>
