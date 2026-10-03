<?php
/**
 * Definición de tipos para las variables inyectadas en la vista del catálogo.
 * 
 * @var array<string, mixed>|null $detalleProducto 
 * @var array<array-key, mixed> $filtros
 * @var mixed $enlaceConFiltros
 * @var array<array-key, mixed> $categorias
 * @var array<array-key, mixed> $marcas
 * @var string $atributoProductosCatalogoGridOculto
 * @var array<array-key, mixed> $tarjetasProducto
 * @var string $atributoPaginacionOculto
 * @var array<array-key, mixed> $enlacesPaginacion
 * @var array<array-key, mixed> $paginacion
 * @var string $atributoProductosCatalogoVacioOculto
 * @var string $atributoDialogoDetalleAbierto
 */
?>
<section class="catalog-page">
    <div class="container">
        <!-- Barra de navegación (Breadcrumbs) basada en la categoría actual -->
        <nav class="catalog-breadcrumbs" aria-label="Ruta de navegación">
            <a href="<?= e(url_interna()) ?>">Inicio</a> 
            <span aria-hidden="true">›</span>
            <a href="<?= e(url_interna('catalog')) ?>">Catálogo</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page"><?= e($filtros['cat'] !== '' ? $filtros['cat'] : 'Productos') ?></span>
        </nav>

        <div class="catalog-layout">
            <!-- Barra lateral con filtros de búsqueda -->
            <aside class="catalog-sidebar" aria-label="Filtros del catálogo">
                
                <!-- Sección de filtro por Categorías -->
                <section class="catalog-filter-section">
                    <h2>Categorías</h2>
                    <nav class="catalog-filter-list" aria-label="Categorías">
                        <!-- Enlace para restablecer el filtro de categoría -->
                        <a class="<?= $filtros['cat'] === '' ? 'is-active' : '' ?>"
                           href="<?= e($enlaceConFiltros(['cat' => ''])) ?>"
                           <?= $filtros['cat'] === '' ? 'aria-current="page"' : '' ?>>
                            Todas las categorías
                        </a>

                        <!-- Bucle para listar las categorías disponibles -->
                        <?php foreach ($categorias as $categoria): ?>
                            <a class="<?= $filtros['cat'] === $categoria ? 'is-active' : '' ?>"
                               href="<?= e($enlaceConFiltros(['cat' => $categoria])) ?>"
                               <?= $filtros['cat'] === $categoria ? 'aria-current="page"' : '' ?>>
                                <?= e($categoria) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </section>

                <!-- Sección de filtro por Marcas -->
                <section class="catalog-filter-section">
                    <h2>Marca</h2>
                    <nav class="catalog-filter-list" aria-label="Marcas">
                        <!-- Enlace para restablecer el filtro de marca -->
                        <a class="<?= $filtros['marca'] === '' ? 'is-active' : '' ?>"
                           href="<?= e($enlaceConFiltros(['brand' => ''])) ?>"
                           <?= $filtros['marca'] === '' ? 'aria-current="page"' : '' ?>>
                            Todas las marcas
                        </a>

                        <!-- Bucle para listar las marcas disponibles -->
                        <?php foreach ($marcas as $marca): ?>
                            <a class="<?= $filtros['marca'] === $marca ? 'is-active' : '' ?>"
                               href="<?= e($enlaceConFiltros(['brand' => $marca])) ?>"
                               <?= $filtros['marca'] === $marca ? 'aria-current="page"' : '' ?>>
                                <?= e($marca) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </section>

                <!-- Formulario de filtro por rango de precios -->
                <form class="catalog-price-filter" method="get" action="<?= e(url_interna('catalog')) ?>">
                    <h2>Rango de precio</h2>
                    <!-- Campos ocultos para mantener los filtros actuales activos -->
                    <input type="hidden" name="q" value="<?= e($filtros['q']) ?>">
                    <input type="hidden" name="brand" value="<?= e($filtros['marca']) ?>">
                    <input type="hidden" name="cat" value="<?= e($filtros['cat']) ?>">
                    <input type="hidden" name="sort" value="<?= e($filtros['sort']) ?>">

                    <label for="catalog-min-price">Mínimo</label>
                    <input class="input" id="catalog-min-price" name="min_price" type="number" min="0" step="0.01" value="<?= e($filtros['min_price']) ?>">
                    
                    <label for="catalog-max-price">Máximo</label>
                    <input class="input" id="catalog-max-price" name="max_price" type="number" min="0" step="0.01" value="<?= e($filtros['max_price']) ?>">
                    
                    <button class="btn btn-ghost" type="submit">Aplicar</button>
                </form>
            </aside>

            <!-- Contenido principal del catálogo -->
            <div class="catalog-content">
                <div class="catalog-toolbar">
                    <div class="catalog-heading">
                        <h1><?= e($filtros['cat'] !== '' ? $filtros['cat'] : 'Catálogo') ?></h1>
                        <p>Encuentra el celular ideal para ti.</p>
                    </div>

                    <!-- Formulario de búsqueda textual y ordenamiento -->
                    <form id="catalog-search-form" class="catalog-search" method="get" action="<?= e(url_interna('catalog')) ?>" role="search">
                        <input type="hidden" name="brand" value="<?= e($filtros['marca']) ?>">
                        <input type="hidden" name="cat" value="<?= e($filtros['cat']) ?>">
                        <input type="hidden" name="min_price" value="<?= e($filtros['min_price']) ?>">
                        <input type="hidden" name="max_price" value="<?= e($filtros['max_price']) ?>">

                        <label class="visually-hidden" for="catalog-search-input">Buscar productos</label>
                        <input class="input" id="catalog-search-input" name="q" value="<?= e($filtros['q']) ?>" placeholder="Buscar celulares, marcas o modelos...">
                        
                        <button class="btn btn-primary" type="submit" aria-label="Buscar">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>

                        <label class="visually-hidden" for="catalog-sort">Ordenar por</label>
                        <select id="catalog-sort" name="sort" data-catalog-sort>
                            <option value="newest" <?= $filtros['sort'] === 'newest' ? 'selected' : '' ?>>Más recientes</option>
                            <option value="price_asc" <?= $filtros['sort'] === 'price_asc' ? 'selected' : '' ?>>Menor precio</option>
                            <option value="price_desc" <?= $filtros['sort'] === 'price_desc' ? 'selected' : '' ?>>Mayor precio</option>
                            <option value="name" <?= $filtros['sort'] === 'name' ? 'selected' : '' ?>>Nombre</option>
                        </select>
                    </form>
                </div>

                <!-- Cuadrícula de tarjetas de productos -->
                <div class="product-grid catalog-product-grid" <?= $atributoProductosCatalogoGridOculto ?>>
                    <?php foreach ($tarjetasProducto as $tarjetaProducto): ?>
                        <?php require __DIR__ . '/_tarjeta-producto.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación de resultados -->
                <nav class="pagination" aria-label="Paginación" <?= $atributoPaginacionOculto ?>>
                    <?php foreach ($enlacesPaginacion as $enlacePaginacion): ?>
                        <?php $pagina = $enlacePaginacion['pagina']; ?>
                        <a class="btn <?= $pagina === $paginacion['pagina'] ? 'btn-primary' : 'btn-ghost' ?>"
                           href="<?= e($enlacePaginacion['url']) ?>"
                           <?= $pagina === $paginacion['pagina'] ? 'aria-current="page"' : '' ?>>
                            <?= $pagina ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <!-- Mensaje cuando no se encuentran resultados -->
                <div class="empty panel catalog-empty" <?= $atributoProductosCatalogoVacioOculto ?>>
                    No encontramos productos con esos filtros.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de detalle de producto -->
    <dialog class="catalog-modal" data-catalog-modal <?= $atributoDialogoDetalleAbierto ?> aria-labelledby="catalog-modal-title">
        <div class="catalog-modal-panel">
            <button class="catalog-modal-x" type="button" data-modal-close aria-label="Cerrar detalle">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>

            <div class="catalog-modal-gallery <?= ($detalleProducto['imagen_principal'] ?? '') === '' ? 'is-without-thumbnails' : '' ?>">
                <!-- Miniaturas de la galería de imágenes del detalle -->
                <div class="catalog-modal-thumbnails" data-modal-thumbnails aria-label="Galería de imágenes" <?= $detalleProducto['atributoMiniaturasOculto'] ?? '' ?>>
                    <?php foreach ($detalleProducto['imagenes_vista'] ?? [] as $imagenGaleria): ?>
                        <button class="catalog-modal-thumb <?= e($imagenGaleria['clase_modal']) ?>"
                                type="button"
                                data-modal-thumb
                                data-src="<?= e($imagenGaleria['url']) ?>"
                                aria-label="Ver imagen <?= e($imagenGaleria['etiqueta_modal']) ?>"
                                <?= $imagenGaleria['atributo_actual'] ?>>
                            <img src="<?= e($imagenGaleria['url']) ?>" alt="">
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Contenedor principal de la imagen del modal -->
                <div class="catalog-modal-image-stage">
                    <button class="catalog-modal-image-button" type="button" data-modal-zoom aria-label="Ampliar imagen">
                        <img data-modal-main-image <?= $detalleProducto['atributoImagenOculto'] ?? '' ?> src="<?= e($detalleProducto['imagen_principal'] ?? '') ?>" alt="<?= e($detalleProducto['texto_alternativo'] ?? '') ?>">
                        <span class="catalog-modal-image-placeholder" data-modal-image-placeholder <?= ($detalleProducto['imagen_principal'] ?? '') !== '' ? 'hidden' : '' ?>>
                            <i class="bi <?= e($detalleProducto['icono_imagen'] ?? '') ?>" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Información detallada del producto -->
            <div class="catalog-modal-details">
                <p class="catalog-modal-brand"><span>Marca</span> <?= e($detalleProducto['marca'] ?? '') ?></p>
                <p class="catalog-modal-product-label">Nombre del producto</p>
                <h2 id="catalog-modal-title"><?= e($detalleProducto['nombre'] ?? '') ?></h2>

                <div class="catalog-modal-price-row">
                    <p class="catalog-modal-price"><?= e(formatear_dinero($detalleProducto['precio_oferta'] ?? 0)) ?></p>
                    <del class="catalog-modal-original-price" <?= $detalleProducto['atributoPrecioAnteriorOculto'] ?? '' ?>><?= e(formatear_dinero((float) ($detalleProducto['precio_original'] ?? 0))) ?></del>
                    <span class="catalog-modal-discount" <?= $detalleProducto['atributoDescuentoOculto'] ?? '' ?>><?= e($detalleProducto['descuento'] ?? '') ?></span>
                </div>

                <p class="catalog-modal-stock <?= e($detalleProducto['clase_stock'] ?? '') ?>">
                    <i class="bi <?= e($detalleProducto['icono_stock'] ?? '') ?>" aria-hidden="true"></i>
                    <?= e($detalleProducto['texto_stock'] ?? '') ?>
                </p>

                <!-- Descripción del producto -->
                <section class="catalog-modal-description" <?= $detalleProducto['atributoDescripcionOculto'] ?? '' ?>>
                    <h3>Descripción</h3>
                    <p><?= nl2br(e($detalleProducto['descripcion'] ?? '')) ?></p>
                </section>

                <!-- Ficha técnica / características del producto -->
                <section class="catalog-modal-specs" aria-labelledby="catalog-modal-specs-title" <?= $detalleProducto['atributoCaracteristicasOculto'] ?? '' ?>>
                    <h3 id="catalog-modal-specs-title">Ficha técnica</h3>
                    <dl>
                        <?php foreach ($detalleProducto['caracteristicas'] ?? [] as $caracteristicaDetalle): ?>
                            <div>
                                <dt><?= e($caracteristicaDetalle['etiqueta']) ?></dt>
                                <dd><?= e($caracteristicaDetalle['valor']) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </section>

                <!-- Acciones del modal (Agregar al carrito y cerrar) -->
                <div class="catalog-modal-actions">
                    <form method="post" action="<?= e(url_interna('cart')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="add" value="<?= (int) ($detalleProducto['id'] ?? 0) ?>">
                        <button class="btn btn-primary" type="submit" <?= !($detalleProducto['disponible'] ?? false) ? 'disabled' : '' ?>>
                            <i class="bi bi-cart3" aria-hidden="true"></i> Agregar al carrito
                        </button>
                    </form>
                    <button class="btn btn-ghost" type="button" data-modal-close>Cerrar</button>
                </div>
            </div>
        </div>
    </dialog>
</section>

<!-- Script dinámico para el módulo de producto -->
<script src="<?= e(url_recurso_estatico('assets/js/david/producto.js?v=20260929-4')) ?>" defer></script>