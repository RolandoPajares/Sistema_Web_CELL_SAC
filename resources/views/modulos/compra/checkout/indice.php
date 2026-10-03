<?php

/**
 * @var string $atributoMensajeOculto
 * @var string $mensaje
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoCheckoutConArticulosOculto
 * @var string $atributoCheckoutSinArticulosOculto
 */ ?><section>
    <div class="container">
        <div class="auth-wrap panel">
            <h1>Finalizar pedido</h1>
            <div class="alert alert-success" <?= $atributoMensajeOculto ?>><?= e($mensaje) ?></div>
            <a class="btn btn-ghost" <?= $atributoMensajeOculto ?> href="<?= e(url_interna('panel/pedidos')) ?>">Ver mis pedidos</a>
            <div class="alert alert-error" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
            <div <?= $atributoCheckoutConArticulosOculto ?>>
                <p>Este proyecto registra una solicitud de compra; no procesa pagos en línea.</p>
                <form method="post" action="<?= e(url_interna('checkout')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary" style="width:100%">Confirmar pedido</button>
                </form>
            </div>
            <div <?= $atributoCheckoutSinArticulosOculto ?>>
                <p>No hay productos pendientes.</p>
                <a class="btn btn-primary" href="<?= e(url_interna('catalog')) ?>">Volver al catálogo</a>
            </div>
        </div>
    </div>
</section>