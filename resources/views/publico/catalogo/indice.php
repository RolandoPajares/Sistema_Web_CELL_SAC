<?php
$enlaceConFiltros = static function (array $ajustes = []) use ($filtros): string {
    $consulta = [
        'q' => $filtros['q'],
        'brand' => $filtros['marca'],
        'cat' => $filtros['cat'],
        'min_price' => $filtros['min_price'],
        'max_price' => $filtros['max_price'],
        'sort' => $filtros['sort'],
    ];
    $consulta = array_merge($consulta, $ajustes);
    unset($consulta['page']);

    return url('catalog?' . http_build_query($consulta));
};
?>
<section class="catalog-page">
    <div class="container">
        <nav class="catalog-breadcrumbs" aria-label="Ruta de navegación">
            <a href="<?= e(url()) ?>">Inicio</a>
            <span aria-hidden="true">›</span>
            <a href="<?= e(url('catalog')) ?>">Catálogo</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page"><?= e($filtros['cat'] !== '' ? $filtros['cat'] : 'Productos') ?></span>
        </nav>

        <div class="catalog-layout">
            <aside class="catalog-sidebar" aria-label="Filtros del catálogo">
                <section class="catalog-filter-section">
                    <h2>Categorías</h2>
                    <nav class="catalog-filter-list" aria-label="Categorías">
                        <a class="<?= $filtros['cat'] === '' ? 'is-active' : '' ?>" href="<?= e($enlaceConFiltros(['cat' => ''])) ?>" <?= $filtros['cat'] === '' ? 'aria-current="page"' : '' ?>>Todas las categorías</a>
                        <?php foreach ($categorias as $categoria): ?>
                            <a class="<?= $filtros['cat'] === $categoria ? 'is-active' : '' ?>" href="<?= e($enlaceConFiltros(['cat' => $categoria])) ?>" <?= $filtros['cat'] === $categoria ? 'aria-current="page"' : '' ?>><?= e($categoria) ?></a>
                        <?php endforeach; ?>
                    </nav>
                </section>

                <section class="catalog-filter-section">
                    <h2>Marca</h2>
                    <nav class="catalog-filter-list" aria-label="Marcas">
                        <a class="<?= $filtros['marca'] === '' ? 'is-active' : '' ?>" href="<?= e($enlaceConFiltros(['brand' => ''])) ?>" <?= $filtros['marca'] === '' ? 'aria-current="page"' : '' ?>>Todas las marcas</a>
                        <?php foreach ($marcas as $marca): ?>
                            <a class="<?= $filtros['marca'] === $marca ? 'is-active' : '' ?>" href="<?= e($enlaceConFiltros(['brand' => $marca])) ?>" <?= $filtros['marca'] === $marca ? 'aria-current="page"' : '' ?>><?= e($marca) ?></a>
                        <?php endforeach; ?>
                    </nav>
                </section>

                <form class="catalog-price-filter" method="get" action="<?= e(url('catalog')) ?>">
                    <h2>Rango de precio</h2>
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

            <div class="catalog-content">
                <div class="catalog-toolbar">
                    <div class="catalog-heading">
                        <h1><?= e($filtros['cat'] !== '' ? $filtros['cat'] : 'Catálogo') ?></h1>
                        <p>Encuentra el celular ideal para ti.</p>
                    </div>
                    <form id="catalog-search-form" class="catalog-search" method="get" action="<?= e(url('catalog')) ?>" role="search">
                        <input type="hidden" name="brand" value="<?= e($filtros['marca']) ?>">
                        <input type="hidden" name="cat" value="<?= e($filtros['cat']) ?>">
                        <input type="hidden" name="min_price" value="<?= e($filtros['min_price']) ?>">
                        <input type="hidden" name="max_price" value="<?= e($filtros['max_price']) ?>">
                        <label class="visually-hidden" for="catalog-search-input">Buscar productos</label>
                        <input class="input" id="catalog-search-input" name="q" value="<?= e($filtros['q']) ?>" placeholder="Buscar celulares, marcas o modelos...">
                        <button class="btn btn-primary" type="submit" aria-label="Buscar"><i class="bi bi-search" aria-hidden="true"></i></button>
                        <label class="visually-hidden" for="catalog-sort">Ordenar por</label>
                        <select id="catalog-sort" name="sort" data-catalog-sort>
                            <option value="newest" <?= $filtros['sort'] === 'newest' ? 'selected' : '' ?>>Más recientes</option>
                            <option value="price_asc" <?= $filtros['sort'] === 'price_asc' ? 'selected' : '' ?>>Menor precio</option>
                            <option value="price_desc" <?= $filtros['sort'] === 'price_desc' ? 'selected' : '' ?>>Mayor precio</option>
                            <option value="name" <?= $filtros['sort'] === 'name' ? 'selected' : '' ?>>Nombre</option>
                        </select>
                    </form>
                </div>

                <?php if ($productos): ?>
                    <div class="product-grid catalog-product-grid">
                        <?php foreach ($productos as $producto): ?>
                            <?php require __DIR__ . '/_tarjeta-producto.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($paginacion['ultima_pagina'] > 1): ?>
                        <nav class="pagination" aria-label="Paginación">
                            <?php for ($pagina = 1; $pagina <= $paginacion['ultima_pagina']; $pagina++): ?>
                                <?php $consulta = array_merge($filtros, ['page' => $pagina]); ?>
                                <a class="btn <?= $pagina === $paginacion['pagina'] ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('catalog?' . http_build_query($consulta))) ?>" <?= $pagina === $paginacion['pagina'] ? 'aria-current="page"' : '' ?>><?= $pagina ?></a>
                            <?php endfor; ?>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty panel catalog-empty">No encontramos productos con esos filtros.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <dialog class="catalog-modal" data-catalog-modal aria-labelledby="catalog-modal-title">
        <div class="catalog-modal-panel">
            <button class="catalog-modal-x" type="button" data-modal-close aria-label="Cerrar detalle"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            <div class="catalog-modal-gallery">
                <div class="catalog-modal-thumbnails" data-modal-thumbnails hidden aria-label="Galería de imágenes"></div>
                <div class="catalog-modal-image-stage">
                    <button class="catalog-gallery-arrow is-prev" type="button" data-modal-prev aria-label="Imagen anterior" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button class="catalog-modal-image-button" type="button" data-modal-zoom aria-label="Ampliar imagen">
                        <img data-modal-main-image alt="" hidden>
                        <span class="catalog-modal-image-placeholder" data-modal-image-placeholder><i class="bi bi-phone" aria-hidden="true"></i></span>
                    </button>
                    <button class="catalog-gallery-arrow is-next" type="button" data-modal-next aria-label="Imagen siguiente" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </div>

            <div class="catalog-modal-details">
                <p class="catalog-modal-brand" data-modal-brand></p>
                <h2 id="catalog-modal-title" data-modal-name></h2>
                <p class="catalog-modal-price" data-modal-price></p>
                <p class="catalog-modal-description" data-modal-description hidden></p>
                <section class="catalog-modal-attribute" data-modal-color-section hidden>
                    <h3>Color</h3>
                    <p data-modal-color></p>
                </section>
                <section class="catalog-modal-attribute" data-modal-storage-section hidden>
                    <h3>Almacenamiento</h3>
                    <p data-modal-storage></p>
                </section>
                <div class="catalog-modal-actions">
                    <form method="post" action="<?= e(url('cart')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="add" value="" data-modal-cart-id>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-cart3" aria-hidden="true"></i> Agregar al carrito</button>
                    </form>
                    <button class="btn btn-ghost" type="button" data-modal-close>Cerrar</button>
                </div>
            </div>
        </div>
    </dialog>
</section>
<script src="<?= e(asset('assets/js/david/producto.js?v=20260929-1')) ?>" defer></script>
