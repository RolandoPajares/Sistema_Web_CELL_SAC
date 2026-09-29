<?php
$marca = trim((string) ($producto['marca'] ?? ''));
$nombre = trim((string) ($producto['nombre'] ?? ''));
$categoria = trim((string) ($producto['categoria'] ?? ''));
$precioActual = (float) ($producto['precio_oferta'] ?? $producto['precio'] ?? 0);
$precioOriginal = isset($producto['precio_original']) && is_numeric($producto['precio_original'])
    ? (float) $producto['precio_original']
    : null;
$tieneDescuento = $precioOriginal !== null && $precioOriginal > $precioActual;
$descuento = trim((string) ($producto['descuento'] ?? ''));
if ($tieneDescuento && $descuento === '' && $precioOriginal > 0) {
    $descuento = '-' . (string) round((1 - ($precioActual / $precioOriginal)) * 100) . '%';
}
$imagen = product_image_url((string) ($producto['url_imagen'] ?? $producto['imagen'] ?? ''));
$stock = max(0, (int) ($producto['existencias'] ?? 0));
$etiquetaProducto = trim((string) ($producto['etiqueta'] ?? ''));
$atributos = [];
foreach ([
    'almacenamiento' => 'Almacenamiento',
    'color' => 'Color',
] as $campo => $etiqueta) {
    $valor = trim((string) ($producto[$campo] ?? ''));
    if ($valor !== '') {
        $atributos[] = [$etiqueta, $valor];
    }
}
$descripcion = trim((string) ($producto['descripcion'] ?? ''));
?>
<section class="product-page-light" data-product-detail>
    <div class="container product-detail-container">
        <nav class="product-breadcrumbs" aria-label="Ruta de navegación">
            <a href="<?= e(url()) ?>">Inicio</a>
            <span aria-hidden="true">›</span>
            <a href="<?= e(url('catalog')) ?>">Catálogo</a>
            <?php if ($categoria !== ''): ?>
                <span aria-hidden="true">›</span>
                <a href="<?= e(url('catalog?' . http_build_query(['cat' => $categoria]))) ?>"><?= e($categoria) ?></a>
            <?php endif; ?>
            <span aria-hidden="true">›</span>
            <span aria-current="page"><?= e($nombre) ?></span>
        </nav>

        <div class="product-detail-modern">
            <div class="detail-gallery" aria-label="Imágenes del producto">
                <div class="gallery-thumbs" data-gallery-thumbs <?= $imagen === '' ? 'hidden' : '' ?>>
                    <?php if ($imagen !== ''): ?>
                        <button class="gallery-thumb active" type="button" data-src="<?= e($imagen) ?>" aria-label="Ver imagen de <?= e($nombre) ?>" aria-current="true">
                            <img src="<?= e($imagen) ?>" alt="">
                        </button>
                    <?php endif; ?>
                </div>
                <div class="detail-main-image">
                    <img data-main-product-image src="<?= e($imagen) ?>" alt="<?= e(trim($marca . ' ' . $nombre)) ?>" <?= $imagen === '' ? 'hidden' : '' ?>>
                    <div class="detail-product-placeholder" data-image-placeholder <?= $imagen !== '' ? 'hidden' : '' ?> aria-label="Imagen no registrada">
                        <span class="detail-product-icon"><strong><?= e(product_visual($marca)) ?></strong></span>
                        <span><?= e($marca) ?></span>
                    </div>
                    <button class="gallery-arrow prev" type="button" data-gallery-prev aria-label="Imagen anterior" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button class="gallery-arrow next" type="button" data-gallery-next aria-label="Imagen siguiente" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </div>

            <div class="detail-info">
                <div class="product-detail-heading">
                    <div class="product-detail-meta">
                        <?php if ($marca !== ''): ?><span><?= e($marca) ?></span><?php endif; ?>
                        <?php if ($categoria !== ''): ?><span><?= e($categoria) ?></span><?php endif; ?>
                        <?php if ($etiquetaProducto !== ''): ?><span class="product-detail-badge"><?= e($etiquetaProducto) ?></span><?php endif; ?>
                    </div>
                    <h1><?= e($nombre) ?></h1>
                </div>

                <div class="product-detail-price" aria-label="Precio">
                    <strong><?= e(money($precioActual)) ?></strong>
                    <?php if ($tieneDescuento): ?>
                        <div class="product-detail-old-price">
                            <del><?= e(money($precioOriginal)) ?></del>
                            <?php if ($descuento !== ''): ?><span class="product-detail-discount"><?= e($descuento) ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <p class="product-detail-stock <?= $stock > 0 ? 'is-available' : 'is-unavailable' ?>">
                    <i class="bi <?= $stock > 0 ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>" aria-hidden="true"></i>
                    <?= $stock > 0 ? e(number_format($stock, 0, ',', '.')) . ' unidades disponibles' : 'Agotado' ?>
                </p>

                <form class="product-detail-cart" action="<?= e(url('cart')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="add" value="<?= (int) $producto['id'] ?>">
                    <button class="product-detail-buy" type="submit" <?= $stock < 1 ? 'disabled' : '' ?>>
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <?= $stock > 0 ? 'Agregar al carrito' : 'Sin stock' ?>
                    </button>
                </form>

                <?php if ($descripcion !== ''): ?>
                    <section class="product-detail-description">
                        <h2>Descripción</h2>
                        <p><?= nl2br(e($descripcion)) ?></p>
                    </section>
                <?php endif; ?>

                <?php if ($atributos !== []): ?>
                    <section class="product-detail-specs">
                        <h2>Información del producto</h2>
                        <ul>
                            <?php foreach ($atributos as [$etiqueta, $valor]): ?>
                                <li><span><?= e($etiqueta) ?></span><strong><?= e($valor) ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
