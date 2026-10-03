<?php
/**
 * @var array<int, array{url:string,clase_detalle:string,clase_modal:string,etiqueta_detalle:string,etiqueta_modal:string,atributo_actual:string}> $imagenesProductoDetalleVista
 * @var array<int, array{0:string,1:string}> $atributos
 
 * @var string $atributoCategoriaOculto
 * @var mixed $enlaceCategoria
 * @var string $categoria
 * @var string $nombre
 * @var mixed $imagen
 * @var string $textoAlternativoProducto
 * @var string $tipoVisualProducto
 * @var string $atributoTextoMarcaVisualOculto
 * @var mixed $visualMarca
 * @var string $iconoProducto
 * @var string $atributoIconoVisualOculto
 * @var string $marca
 * @var string $atributoMarcaOculto
 * @var string $atributoEtiquetaOculto
 * @var string $etiquetaProducto
 * @var mixed $precioActual
 * @var string $atributoPrecioAnteriorOculto
 * @var mixed $precioOriginal
 * @var string $atributoDescuentoOculto
 * @var mixed $descuento
 * @var string $claseStock
 * @var string $iconoStock
 * @var string $textoStock
 * @var array<array-key, mixed> $producto
 * @var bool $disponible
 * @var string $atributoDescripcionOculto
 * @var string $descripcion
 * @var string $atributoAtributosOculto
 */
?>
<section class="product-page-light" data-product-detail>
    <div class="container product-detail-container">
        <nav class="product-breadcrumbs" aria-label="Ruta de navegación">
            <a href="<?= e(url_interna()) ?>">Inicio</a>
            <span aria-hidden="true">›</span>
            <a href="<?= e(url_interna('catalog')) ?>">Catálogo</a>
            <span aria-hidden="true" <?= $atributoCategoriaOculto ?>>›</span>
            <a <?= $atributoCategoriaOculto ?> href="<?= e($enlaceCategoria) ?>"><?= e($categoria) ?></a>
            <span aria-hidden="true">›</span>
            <span aria-current="page"><?= e($nombre) ?></span>
        </nav>

        <div class="product-detail-modern">
            <div class="detail-gallery" aria-label="Imágenes del producto">
                <div class="gallery-thumbs" data-gallery-thumbs <?= $imagen === '' ? 'hidden' : '' ?>>
                <?php foreach ($imagenesProductoDetalleVista as $imagenGaleria): ?>

                    <button
                class="gallery-thumb <?= e($imagenGaleria['clase_detalle']) ?>"
                type="button"
                data-src="<?= e($imagenGaleria['url']) ?>"
                aria-label="Ver imagen <?= e($imagenGaleria['etiqueta_detalle']) ?> de <?= e($nombre) ?>"
                <?= $imagenGaleria['atributo_actual'] ?>>
                <img src="<?= e($imagenGaleria['url']) ?>" alt="">
                    </button>
                <?php endforeach; ?>
                </div>
                <div class="detail-main-image">
                <img data-main-product-image src="<?= e($imagen) ?>" alt="<?= e($textoAlternativoProducto) ?>" <?= $imagen === '' ? 'hidden' : '' ?>>

                    <div
                class="detail-product-placeholder detail-product-placeholder--<?= e($tipoVisualProducto) ?>"
                data-image-placeholder
                <?=
                $imagen
                !==
                ''
                ?
                'hidden'
                :
                ''
                ?>
                role="img"
                aria-label="Imagen no disponible">
                <span class="detail-product-icon"><strong <?= $atributoTextoMarcaVisualOculto ?>><?= e($visualMarca) ?></strong><i class="bi <?= e($iconoProducto) ?>" <?= $atributoIconoVisualOculto ?> aria-hidden="true"></i></span>
                <span><?= e($marca) ?></span>
                    </div>

                    <button class="gallery-arrow prev" type="button" data-gallery-prev aria-label="Imagen anterior" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </button>

                    <button class="gallery-arrow next" type="button" data-gallery-next aria-label="Imagen siguiente" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="detail-info">
                <div class="product-detail-heading">
                    <div class="product-detail-meta">
                <span <?= $atributoMarcaOculto ?>><?= e($marca) ?></span>
                <span <?= $atributoCategoriaOculto ?>><?= e($categoria) ?></span>
                <span class="product-detail-badge" <?= $atributoEtiquetaOculto ?>><?= e($etiquetaProducto) ?></span>
                    </div>
                    <h1><?= e($nombre) ?></h1>
                </div>

                <div class="product-detail-price" aria-label="Precio">
                <strong><?= e(formatear_dinero($precioActual)) ?></strong>
                    <div class="product-detail-old-price" <?= $atributoPrecioAnteriorOculto ?>>
                <del><?= e(formatear_dinero($precioOriginal)) ?></del>
                <span class="product-detail-discount" <?= $atributoDescuentoOculto ?>><?= e($descuento) ?></span>
                    </div>
                </div>

                <p class="product-detail-stock <?= e($claseStock) ?>">
                <i class="bi <?= e($iconoStock) ?>" aria-hidden="true"></i>
                <?= e($textoStock) ?>
                </p>

                <form class="product-detail-cart" action="<?= e(url_interna('cart')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="add" value="<?= (int) $producto['id'] ?>">
                    <button class="product-detail-buy" type="submit" <?= $disponible ? '' : 'disabled' ?>>
                <i class="bi bi-cart3" aria-hidden="true"></i>
                <?= $disponible ? 'Agregar al carrito' : 'Sin stock' ?>
                    </button>
                </form>

                <section class="product-detail-description" <?= $atributoDescripcionOculto ?>>
                    <h2>Descripción</h2>
                    <p><?= nl2br(e($descripcion)) ?></p>
                </section>

                <section class="product-detail-specs" <?= $atributoAtributosOculto ?>>
                    <h2>Información del producto</h2>
                    <ul>
                <?php foreach ($atributos as [$etiqueta, $valor]): ?>
                        <li><span><?= e($etiqueta) ?></span><strong><?= e($valor) ?></strong></li>
                <?php endforeach; ?>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</section>
