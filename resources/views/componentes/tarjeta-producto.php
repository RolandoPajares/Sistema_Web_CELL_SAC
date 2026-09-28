<?php
$categoria = (string) ($producto['categoria'] ?? 'Celular');
$esAudio = stripos($categoria, 'aud') !== false;
$esLaptop = stripos($categoria, 'laptop') !== false;
$esReloj = stripos($categoria, 'reloj') !== false;
$esMuestra = !empty($producto['demo']);
$enlaceProducto = $esMuestra ? url('catalog') : url('products/' . (int) $producto['id']);
?>
<article class="product-card">
    <a href="<?= e($enlaceProducto) ?>">
        <div class="product-art">
            <span class="badge"><?= e($producto['etiqueta'] ?? $producto['marca']) ?></span>
            <?php if ($esAudio): ?>
                <div class="audio-shape"><?= e(product_visual((string) $producto['marca'])) ?></div>
            <?php elseif ($esLaptop): ?>
                <div class="laptop-shape"><span><?= e(product_visual((string) $producto['marca'])) ?></span></div>
            <?php elseif ($esReloj): ?>
                <div class="watch-shape"><?= e(product_visual((string) $producto['marca'])) ?></div>
            <?php else: ?>
                <div class="phone-shape"><?= e(product_visual((string) $producto['marca'])) ?></div>
            <?php endif; ?>
        </div>
    </a>
    <div class="product-body">
        <div class="product-meta">
            <span><?= e($producto['marca']) ?></span>
            <span><?= e($categoria) ?></span>
        </div>
        <h3><?= e($producto['nombre']) ?></h3>
        <div class="price"><?= money($producto['precio']) ?></div>
        <div class="stock"><?= (int) $producto['existencias'] ?> unidades disponibles</div>
        <div class="product-actions">
            <a class="btn btn-ghost" href="<?= e($enlaceProducto) ?>"><?= $esMuestra ? 'Ver catálogo' : 'Detalles' ?></a>
            <?php if (!$esMuestra): ?>
                <form action="<?= e(url('cart')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="add" value="<?= (int) $producto['id'] ?>">
                    <button class="btn btn-primary">Añadir</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>
