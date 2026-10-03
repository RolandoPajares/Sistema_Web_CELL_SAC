<?php
/** @var array<int, array{codigo:string,fecha:string,producto:string,cantidad_vista:string,total:string,estado:string,icono:string,clase_estado:string,url_detalle:string,texto_accion:string}> $pedidosCuenta 
 */
?>


<div class="lista-pedidos-cuenta">
    <?php foreach ($pedidosCuenta as $pedido): ?>
        <article>
            <div class="pedido-codigo">
                <b>Pedido #<?= e($pedido['codigo']) ?></b>
                <small>
                    <i class="bi bi-calendar3"></i>
                    <?= e($pedido['fecha']) ?>
                </small>
            </div>

            <div class="pedido-producto">
                <span>
                    <i class="bi <?= e($pedido['icono']) ?>"></i>
                </span>
                <div>
                    <b><?= e($pedido['producto']) ?></b>
                    <small><?= e($pedido['cantidad_vista']) ?></small>
                </div>
            </div>

            <div class="pedido-total">
                <b><?= e($pedido['total']) ?></b>
                <small>Pago confirmado</small>
            </div>

            <div>
                <span class="pedido-estado <?= e($pedido['clase_estado']) ?>">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= e($pedido['estado']) ?>
                </span>
            </div>

            <a href="<?= e($pedido['url_detalle']) ?>">
                <?= e($pedido['texto_accion']) ?>
                <i class="bi bi-chevron-right"></i>
            </a>
        </article>
    <?php endforeach; ?>
</div>
