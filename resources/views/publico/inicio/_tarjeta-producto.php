<?php
/**
 * @var array<array-key, mixed> $tarjetaProducto
 */ ?><article class="product-card home-product-card">
    <a class="home-product-image-link" href="<?= e($tarjetaProducto['enlace_detalle']) ?>" aria-label="Ver <?= e($tarjetaProducto['texto_alternativo']) ?>">
    <div class="product-art home-product-art">
            <span class="badge"><?= e($tarjetaProducto['etiqueta_inicio']) ?></span>
            <img class="home-product-photo" <?= $tarjetaProducto['atributoImagenOculta'] ?> src="<?= e($tarjetaProducto['imagen_principal']) ?>" alt="<?= e($tarjetaProducto['texto_alternativo']) ?>" loading="lazy">
        <div class="home-product-placeholder" <?= $tarjetaProducto['atributoPlaceholderOculto'] ?> role="img" aria-label="Imagen no disponible">
                <i class="bi <?= e($tarjetaProducto['icono_imagen']) ?>" aria-hidden="true"></i>
                <span><?= e($tarjetaProducto['visual_marca']) ?></span>
        </div>
    </div>
    </a>
    <div class="product-body home-product-body">
        <div class="product-meta"><span><?= e($tarjetaProducto['marca']) ?></span><span><?= e($tarjetaProducto['categoria']) ?></span></div>
        <h3><?= e($tarjetaProducto['nombre']) ?></h3>
        <div class="home-product-specs"><?= e($tarjetaProducto['especificaciones']) ?></div>
        <div class="home-product-pricing" <?= $tarjetaProducto['atributoDescuentoOculto'] ?>>
                <del><?= formatear_dinero($tarjetaProducto['precio_original']) ?></del>
                <span><?= e($tarjetaProducto['descuento']) ?></span>
        </div>
        <div class="price"><?= formatear_dinero($tarjetaProducto['precio_oferta']) ?></div>
        <div class="stock"><?= e($tarjetaProducto['texto_stock']) ?></div>
        <div class="home-product-actions">
            <form class="home-product-buy-form" action="<?= e(url_interna('cart')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="add" value="<?= $tarjetaProducto['id'] ?>">
                <button class="btn btn-primary home-product-action" type="submit" <?= $tarjetaProducto['atributoCompraDesactivada'] ?>><i class="bi bi-cart3" aria-hidden="true"></i> <?= e($tarjetaProducto['textoBotonCompra']) ?></button>
            </form>
            <a class="btn btn-ghost home-product-details" href="<?= e($tarjetaProducto['enlace_detalle']) ?>"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalles</a>
        </div>
    </div>
</article>
