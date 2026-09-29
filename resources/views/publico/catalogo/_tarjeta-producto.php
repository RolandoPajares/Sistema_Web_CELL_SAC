<?php
$enlaceProducto = url('products/' . (int) $producto['id']);
$stock = (int)($producto['existencias'] ?? 0);
?>
<article class="product-card ecommerce-card">
    <a href="<?= e($enlaceProducto) ?>" class="product-link">
        <div class="product-art">
            <span class="badge"><?= e($producto['etiqueta'] ?? 'Nuevo') ?></span>
            <?php if(!empty($producto['imagen_referencia'])): ?><img class="product-reference-image" src="<?= e(asset($producto['imagen_referencia'])) ?>" alt="<?= e($producto['nombre'] ?? '') ?>"><?php else: ?><div class="phone-shape"><?= e(product_visual((string)($producto['marca'] ?? ''))) ?></div><?php endif; ?>
        </div>
    </a>
    <div class="product-body">
        <div class="product-meta">
            <span><?= e($producto['marca'] ?? '') ?></span>
            <span><?= e($producto['categoria'] ?? 'Celular') ?></span>
        </div>
        <h3><?= e($producto['nombre'] ?? '') ?></h3>
        <div class="product-specs">Producto original · <?= $stock > 0 ? 'Disponible' : 'Sin existencias' ?></div>
        <div class="price"><?= money($producto['precio']) ?></div>
        <div class="stock"><?= $stock ?> unidades disponibles</div>
        <div class="product-actions">
            <form action="<?= e(url('cart')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="add" value="<?= (int)$producto['id'] ?>">
                <button class="btn btn-primary">Comprar</button>
            </form>
            <a class="btn btn-ghost" href="<?= e($enlaceProducto) ?>">Ver detalles</a>
        </div>
    </div>
</article>
