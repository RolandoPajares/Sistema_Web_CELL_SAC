<?php
$categoria = (string) ($producto['categoria'] ?? 'Celular');
$esAudio = stripos($categoria, 'aud') !== false;
?>
<section>
    <div class="container detail-grid">
        <div class="panel product-art detail-art">
            <?php if ($esAudio): ?>
                <div class="audio-shape"><?= e(product_visual((string) $producto['marca'])) ?></div>
            <?php else: ?>
                <div class="phone-shape"><?= e(product_visual((string) $producto['marca'])) ?></div>
            <?php endif; ?>
        </div>
        <div>
            <span class="eyebrow"><?= e($producto['marca']) ?> &middot; <?= e($categoria) ?></span>
            <h1><?= e($producto['nombre']) ?></h1>
            <p><?= e($producto['descripcion'] ?? 'Producto original disponible en MD Technology Digital Cell.') ?></p>
            <div class="price"><?= money($producto['precio']) ?></div>
            <div class="info-list">
                <div class="info-item"><b>Disponibilidad</b><br><?= (int) $producto['existencias'] ?> unidades</div>
                <div class="info-item"><b>Capacidad / conexión</b><br><?= e($producto['almacenamiento'] ?? 'Consultar') ?></div>
                <div class="info-item"><b>Color</b><br><?= e($producto['color'] ?? 'Consultar') ?></div>
                <div class="info-item"><b>Garantia</b><br>Consultar en tienda</div>
            </div>
            <form action="<?= e(url('cart')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="add" value="<?= (int) $producto['id'] ?>">
                <button class="btn btn-primary"><i class="bi bi-cart-plus" aria-hidden="true"></i> Anadir al carrito</button>
            </form>
            <p class="product-note">Los precios mostrados son datos demostrativos y pueden actualizarse desde el panel administrativo.</p>
        </div>
    </div>
</section>
