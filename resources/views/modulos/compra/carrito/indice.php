<?php
$articulos = $resumen['articulos'];
$total = $resumen['total'];
?>
<section>
    <div class="container">
        <h1>Tu carrito</h1>
        <?php if (!$articulos): ?>
            <div class="empty panel">
                Tu carrito está vacío.<br><br>
                <a class="btn btn-primary" href="<?= e(url('catalog')) ?>"><i class="bi bi-bag" aria-hidden="true"></i> Explorar productos</a>
            </div>
        <?php else: ?>
            <div class="panel table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articulos as $producto): ?>
                            <tr>
                                <td><b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b></td>
                                <td><?= money($producto['precio']) ?></td>
                                <td><?= (int) $producto['cantidad'] ?></td>
                                <td><?= money($producto['subtotal']) ?></td>
                                <td>
                                    <form method="post" action="<?= e(url('cart')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-ghost" name="remove" value="<?= (int) $producto['id'] ?>">
                                            <i class="bi bi-trash3" aria-hidden="true"></i> Quitar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-summary">
                    <form method="post" action="<?= e(url('cart')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost" name="clear" value="1"><i class="bi bi-x-circle" aria-hidden="true"></i> Vaciar</button>
                    </form>
                    <div>
                        <b>Total: <span class="price"><?= money($total) ?></span></b>
                        <a class="btn btn-primary" href="<?= e(url('checkout')) ?>"><i class="bi bi-arrow-right" aria-hidden="true"></i> Continuar</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($sugerencias)): ?>
            <section class="smart-cart panel">
                <div class="section-head">
                    <div><span class="eyebrow">Carrito inteligente</span>
                        <h2><i class="bi bi-cart-check" aria-hidden="true"></i> Completa tu compra</h2>
                        <p>Recomendaciones compatibles o complementarias según lo que ya agregaste.</p>
                    </div>
                </div>
                <div class="smart-cart-grid">
                    <?php foreach ($sugerencias as $sugerencia): ?>
                        <article class="smart-cart-item">
                            <div><b><?= e($sugerencia['marca'] . ' ' . $sugerencia['nombre']) ?></b><small><?= e($sugerencia['compatibilidad']) ?></small></div>
                            <span><?= money($sugerencia['precio']) ?></span>
                            <form method="post" action="<?= e(url('cart')) ?>"><?= csrf_field() ?><button class="btn btn-ghost" name="add" value="<?= (int)$sugerencia['id'] ?>">+ Agregar</button></form>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="compatibility-note"><i class="bi bi-shield-check"></i>
                    <div><b>Control de compatibilidad</b>
                        <p>El sistema prioriza accesorios de la misma marca y marca como “universal / verificar modelo” aquellos que requieren validación adicional.</p>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>
