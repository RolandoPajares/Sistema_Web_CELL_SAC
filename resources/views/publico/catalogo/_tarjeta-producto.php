<?php
$imagenesProducto = $producto['imagenes'] ?? [];
if (!is_array($imagenesProducto)) {
    $imagenesProducto = [];
}
$imagenUnica = trim((string) ($producto['imagen_referencia'] ?? $producto['url_imagen'] ?? $producto['imagen'] ?? ''));
if ($imagenUnica !== '' && $imagenesProducto === []) {
    $imagenesProducto[] = $imagenUnica;
}
$imagenesProducto = array_values(array_filter(array_map(
    static function (mixed $imagen): string {
        if (is_array($imagen)) {
            $imagen = $imagen['ruta_imagen'] ?? $imagen['url'] ?? '';
        }

        return is_scalar($imagen) ? trim((string) $imagen) : '';
    },
    $imagenesProducto
), static fn (string $imagen): bool => $imagen !== ''));
$imagenesProducto = array_values(array_filter(array_map('product_image_url', $imagenesProducto)));
$marcaProducto = (string) ($producto['marca'] ?? '');
$nombreProducto = (string) ($producto['nombre'] ?? '');
$categoriaProducto = (string) ($producto['categoria'] ?? '');
$almacenamientoProducto = trim((string) ($producto['almacenamiento'] ?? ''));
$precioOfertaProducto = (float) ($producto['precio_oferta'] ?? $producto['precio'] ?? 0);
$precioOriginalProducto = (float) ($producto['precio_original'] ?? 0);
$descuentoProducto = trim((string) ($producto['descuento'] ?? ''));
$existenciasProducto = (int) ($producto['existencias'] ?? 0);
$esAudio = stripos($categoriaProducto, 'audio') !== false;
$esCelular = stripos($categoriaProducto, 'celular') !== false;
$descripcionTipoProducto = $esCelular ? 'Equipo original' : ($esAudio ? 'Audio original' : 'Accesorio original');
$etiquetaProducto = trim((string) ($producto['etiqueta'] ?? ''));
$detallesProducto = [
    'id' => (int) ($producto['id'] ?? 0),
    'marca' => $marcaProducto,
    'nombre' => $nombreProducto,
    'precio' => money($precioOfertaProducto),
    'descripcion' => trim((string) ($producto['descripcion'] ?? '')),
    'color' => trim((string) ($producto['color'] ?? '')),
    'almacenamiento' => $almacenamientoProducto,
    'imagenes' => $imagenesProducto,
];
?>
<article class="product-card catalog-product-card">
    <div class="product-art catalog-product-art">
        <span class="badge"><?= e($etiquetaProducto !== '' ? $etiquetaProducto : 'Oferta') ?></span>
        <?php if ($imagenesProducto !== []): ?>
            <img class="catalog-product-photo" src="<?= e($imagenesProducto[0]) ?>" alt="<?= e($marcaProducto . ' ' . $nombreProducto) ?>" loading="lazy">
        <?php elseif ($esAudio): ?>
            <div class="audio-shape" aria-hidden="true"><?= e(product_visual($marcaProducto)) ?></div>
        <?php elseif ($esCelular): ?>
            <div class="phone-shape" aria-hidden="true"><?= e(product_visual($marcaProducto)) ?></div>
        <?php else: ?>
            <div class="catalog-accessory-shape" aria-hidden="true"><i class="bi bi-box-seam"></i></div>
        <?php endif; ?>
    </div>
    <div class="product-body catalog-product-body">
        <div class="catalog-product-meta"><span><?= e($marcaProducto) ?></span><span><?= e($categoriaProducto) ?></span></div>
        <h2><?= e($nombreProducto) ?></h2>
        <div class="catalog-product-specs"><?= e($almacenamientoProducto !== '' ? $almacenamientoProducto . ' · ' : '') ?><?= e($descripcionTipoProducto) ?></div>
        <div class="catalog-product-pricing">
            <?php if ($precioOriginalProducto > $precioOfertaProducto && $descuentoProducto !== ''): ?>
                <del><?= money($precioOriginalProducto) ?></del>
                <span><?= e($descuentoProducto) ?></span>
            <?php endif; ?>
        </div>
        <p class="price catalog-product-sale-price"><?= money($precioOfertaProducto) ?></p>
        <div class="stock catalog-product-stock"><?= $existenciasProducto ?> unidades disponibles</div>
        <div class="catalog-product-actions">
            <?php if ($existenciasProducto > 0): ?>
                <form action="<?= e(url('cart')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="add" value="<?= (int) ($producto['id'] ?? 0) ?>">
                    <button class="btn btn-primary catalog-buy-button" type="submit"><i class="bi bi-cart3" aria-hidden="true"></i> Comprar</button>
                </form>
            <?php else: ?>
                <button class="btn btn-primary catalog-buy-button" type="button" disabled>Agotado</button>
            <?php endif; ?>
            <button class="btn btn-ghost catalog-details-button" type="button" data-open-product data-product-details="<?= e(json_encode($detallesProducto, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}') ?>">
                Ver detalles
            </button>
        </div>
    </div>
</article>
