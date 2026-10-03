<?php
/**
 * @var array<array-key, mixed> $tarjetaProducto
 * @var array<array-key, mixed> $filtros
 * @var array<array-key, mixed> $paginacion
 */ ?><article class="product-card catalog-product-card">
    <div class="product-art catalog-product-art">
        <span class="badge" <?= $tarjetaProducto['atributoEtiquetaOculta'] ?>><?= e($tarjetaProducto['etiqueta']) ?></span>
        <img class="catalog-product-photo" <?= $tarjetaProducto['atributoImagenOculta'] ?> src="<?= e($tarjetaProducto['imagen_principal']) ?>" alt="<?= e($tarjetaProducto['texto_alternativo']) ?>" loading="lazy">
        <div class="audio-shape" <?= $tarjetaProducto['atributoAudioOculto'] ?> role="img" aria-label="Imagen no disponible"><?= e($tarjetaProducto['visual_marca']) ?></div>
        <div class="phone-shape" <?= $tarjetaProducto['atributoCelularOculto'] ?> role="img" aria-label="Imagen no disponible"><?= e($tarjetaProducto['visual_marca']) ?></div>
        <div class="catalog-accessory-shape" <?= $tarjetaProducto['atributoAccesorioOculto'] ?> role="img" aria-label="Imagen no disponible"><i class="bi bi-box-seam"></i></div>
    </div>
    <div class="product-body catalog-product-body">
        <div class="catalog-product-meta"><span><?= e($tarjetaProducto['marca']) ?></span><span><?= e($tarjetaProducto['categoria']) ?></span></div>
        <h2><?= e($tarjetaProducto['nombre']) ?></h2>
        <div class="catalog-product-specs"><?= e($tarjetaProducto['especificaciones']) ?></div>
        <div class="catalog-product-pricing">
            <div <?= $tarjetaProducto['atributoDescuentoOculto'] ?>>
                <del><?= formatear_dinero($tarjetaProducto['precio_original']) ?></del>
                <span><?= e($tarjetaProducto['descuento']) ?></span>
            </div>
        </div>
        <p class="price catalog-product-sale-price"><?= formatear_dinero($tarjetaProducto['precio_oferta']) ?></p>
        <div class="stock catalog-product-stock"><?= e($tarjetaProducto['texto_stock']) ?></div>
        <div class="catalog-product-actions">
            <form action="<?= e(url_interna('cart')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="add" value="<?= $tarjetaProducto['id'] ?>">
                <button class="btn btn-primary catalog-buy-button" type="submit" <?= $tarjetaProducto['atributoCompraDesactivada'] ?>><i class="bi bi-cart3" aria-hidden="true"></i> <?= e($tarjetaProducto['textoBotonCompra']) ?></button>
            </form>
            <form class="catalog-details-form" action="<?= e(url_interna('catalog')) ?>" method="get">
                <input type="hidden" name="producto" value="<?= $tarjetaProducto['id'] ?>">
                <input type="hidden" name="q" value="<?= e($filtros['q']) ?>">
                <input type="hidden" name="brand" value="<?= e($filtros['marca']) ?>">
                <input type="hidden" name="cat" value="<?= e($filtros['cat']) ?>">
                <input type="hidden" name="min_price" value="<?= e($filtros['min_price']) ?>">
                <input type="hidden" name="max_price" value="<?= e($filtros['max_price']) ?>">
                <input type="hidden" name="sort" value="<?= e($filtros['sort']) ?>">
                <input type="hidden" name="page" value="<?= (int) ($paginacion['pagina'] ?? 1) ?>">
                <button class="btn btn-ghost catalog-details-button" type="submit">Ver detalles</button>
            </form>
        </div>
    </div>
</article>
