<section>
    <div class="container">
        <div class="section-head">
            <div>
                <h1>Catálogo de productos</h1>
                <p>Busca por marca, categoría o modelo. Stock y precios son administrables.</p>
            </div>
        </div>
        <form class="filters" method="get" action="<?= e(url('catalog')) ?>">
            <input class="input" name="q" value="<?= e($filtros['q']) ?>" placeholder="Buscar iPhone, Galaxy, JBL...">
            <select name="brand">
                <option value="">Todas las marcas</option>
                <?php foreach ($marcas as $marca): ?>
                    <option value="<?= e($marca) ?>" <?= $filtros['marca'] === $marca ? 'selected' : '' ?>><?= e($marca) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="cat">
                <option value="">Todas las categorias</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= e($categoria) ?>" <?= $filtros['cat'] === $categoria ? 'selected' : '' ?>><?= e($categoria) ?></option>
                <?php endforeach; ?>
            </select>
            <input class="input" name="min_price" type="number" min="0" step="0.01" value="<?= e($filtros['min_price']) ?>" placeholder="Precio mínimo">
            <input class="input" name="max_price" type="number" min="0" step="0.01" value="<?= e($filtros['max_price']) ?>" placeholder="Precio máximo">
            <select name="sort">
                <option value="newest" <?= $filtros['sort'] === 'newest' ? 'selected' : '' ?>>Más recientes</option>
                <option value="price_asc" <?= $filtros['sort'] === 'price_asc' ? 'selected' : '' ?>>Menor precio</option>
                <option value="price_desc" <?= $filtros['sort'] === 'price_desc' ? 'selected' : '' ?>>Mayor precio</option>
                <option value="name" <?= $filtros['sort'] === 'name' ? 'selected' : '' ?>>Nombre</option>
            </select>
            <button class="btn btn-primary">Filtrar</button>
        </form>
        <?php if ($productos): ?>
            <div class="product-grid">
                <?php foreach ($productos as $producto): ?>
                    <?php require __DIR__ . '/_tarjeta-producto.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php if ($paginacion['ultima_pagina'] > 1): ?>
                <nav class="pagination" aria-label="Paginación">
                    <?php for ($pagina = 1; $pagina <= $paginacion['ultima_pagina']; $pagina++): ?>
                        <?php $consulta = array_merge($filtros, ['page' => $pagina]); ?>
                        <a class="btn <?= $pagina === $paginacion['pagina'] ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('catalog?' . http_build_query($consulta))) ?>"><?= $pagina ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty panel">No encontramos productos con esos filtros.</div>
        <?php endif; ?>
    </div>
</section>
