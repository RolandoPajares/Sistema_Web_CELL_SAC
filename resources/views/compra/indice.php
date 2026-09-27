<?php
$articulos = $resumen['articulos'];
?>
<section>
    <div class="container">
        <div class="auth-wrap panel">
            <h1>Finalizar pedido</h1>
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= e($mensaje) ?></div>
                <a class="btn btn-ghost" href="<?= e(url('panel/pedidos')) ?>">Ver mis pedidos</a>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($articulos): ?>
                <p>Este proyecto registra una solicitud de compra; no procesa pagos en línea.</p>
                <form method="post" action="<?= e(url('checkout')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary" style="width:100%">Confirmar pedido</button>
                </form>
            <?php else: ?>
                <p>No hay productos pendientes.</p>
                <a class="btn btn-primary" href="<?= e(url('catalog')) ?>">Volver al catálogo</a>
            <?php endif; ?>
        </div>
    </div>
</section>
