<?php $pedidosCuenta = [['MD-2024-000158', '15 ene. 2024, 10:24 a. m.', 'iPhone 15 128GB Azul', 'S/ 4,399.00', 'Entregado', 'Descargar comprobante'], ['MD-2024-000142', '8 ene. 2024, 4:18 p. m.', 'Parlante JBL Flip 6', 'S/ 499.00', 'En tránsito', 'Seguir envío'], ['MD-2024-000128', '3 ene. 2024, 11:06 a. m.', 'Audífonos Beats Studio Pro', 'S/ 1,299.00', 'Preparando', 'Ver detalle'], ['MD-2023-000987', '28 dic. 2023, 2:30 p. m.', 'Xiaomi Redmi Note 13', 'S/ 849.00', 'Entregado', 'Descargar comprobante'], ['MD-2023-000876', '15 dic. 2023, 9:15 a. m.', 'Samsung Galaxy A55 5G', 'S/ 1,299.00', 'Entregado', 'Descargar comprobante']]; ?>
<div class="lista-pedidos-cuenta">
    <?php foreach (array_slice($pedidosCuenta, 0, $mostrarPedidos ? 3 : 5) as $indice => $pedido): ?><article>
            <div class="pedido-codigo"><b>Pedido #<?= e($pedido[0]) ?></b><small><i class="bi bi-calendar3"></i> <?= e($pedido[1]) ?></small></div>
            <div class="pedido-producto"><span><i class="bi <?= $indice % 2 ? 'bi-speaker' : 'bi-phone' ?>"></i></span>
                <div><b><?= e($pedido[2]) ?></b><small><?= $indice + 1 ?> unidad</small></div>
            </div>
            <div class="pedido-total"><b><?= e($pedido[3]) ?></b><small>Pago confirmado</small></div>
            <div><span class="pedido-estado <?= $indice === 2 ? 'preparando' : '' ?>"><i class="bi bi-check-circle-fill"></i> <?= e($pedido[4]) ?></span></div><a href="<?= e(url('panel/historial')) ?>"><?= e($pedido[5]) ?> <i class="bi bi-chevron-right"></i></a>
        </article><?php endforeach; ?>
</div>