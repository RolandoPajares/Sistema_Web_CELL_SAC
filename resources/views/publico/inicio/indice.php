<section class="home-hero home-hero-banner">
    <div class="home-hero-banner-link">
        <img class="home-hero-banner-image" src="<?= e(asset('assets/img/publico/inicio/banners/bannerInicio.png')) ?>" alt="Celulares y accesorios de MD Technology Digital Cell">
    </div>
    <div class="home-hero-banner-content">
        <span class="home-hero-banner-kicker">Tecnología original en Bagua</span>
        <h1>Tu próximo celular está en <span>MD Technology Cell</span></h1>
        <p>Celulares y accesorios originales con atención local y precios para ti.</p>
        <div class="home-hero-banner-actions">
            <a class="btn btn-primary" href="<?= e(url('catalog?category=Celular')) ?>">Ver celulares <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a class="btn btn-ghost" href="<?= e(url('catalog?category=Accesorio')) ?>"><i class="bi bi-tag" aria-hidden="true"></i> Explorar accesorios</a>
        </div>
    </div>
</section>

<section class="home-assurance" aria-label="Beneficios de compra">
    <div class="container hero-trust">
        <span><i class="bi bi-shield-check" aria-hidden="true"></i><b>Productos originales</b><small>Con garantía</small></span>
        <span><i class="bi bi-truck" aria-hidden="true"></i><b>Envíos en Bagua</b><small>Rápido y seguro</small></span>
        <span><i class="bi bi-headset" aria-hidden="true"></i><b>Asesoría para elegir</b><small>Atención local</small></span>
    </div>
</section>

<section class="brand-strip" aria-label="Marcas disponibles">
    <div class="container brands">
        <div class="brand-pill samsung">SAMSUNG</div>
        <div class="brand-pill apple"><i class="bi bi-apple" aria-hidden="true"></i> Apple</div>
        <div class="brand-pill oppo">oppo</div>
        <div class="brand-pill xiaomi"><b>mi</b> XIAOMI</div>
        <div class="brand-pill honor">HONOR</div>
        <div class="brand-pill jbl">JBL</div>
    </div>
</section>

<section class="smart-home-strip">
    <div class="container smart-row">
        <div class="smart-intro">
            <h2>Compra<br>más inteligente</h2>
            <p>Herramientas con IA y filtros avanzados para encontrar exactamente lo que necesitas.</p>
        </div>
        <div class="smart-home-grid">
            <a class="smart-tile smart-purple" href="<?= e(url('smart/recommend')) ?>"><span><i class="bi bi-lightbulb-fill" aria-hidden="true"></i></span>
                <div><b>SmartMatch</b><small>Encuentra el equipo ideal según tu estilo de vida.</small></div><i class="bi bi-chevron-right"></i>
            </a>
            <a class="smart-tile smart-orange" href="<?= e(url('smart/compare')) ?>"><span><i class="bi bi-columns-gap" aria-hidden="true"></i></span>
                <div><b>Comparador</b><small>Compara hasta 3 equipos con puntajes.</small></div><i class="bi bi-chevron-right"></i>
            </a>
            <a class="smart-tile smart-gold" href="<?= e(url('mayorista')) ?>"><span><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
                <div><b>Modo emprendedor</b><small>Arma una compra mayorista según tu presupuesto.</small></div><i class="bi bi-chevron-right"></i>
            </a>
            <a class="smart-tile smart-assistant" href="<?= e(url('smart/assistant')) ?>"><span><i class="bi bi-robot" aria-hidden="true"></i></span>
                <div><b>MD Assistant <em>NUEVO</em></b><small>Conversa con IA y recibe recomendaciones al instante.</small></div><i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <div class="section-head home-section-head">
            <div>
                <h2>Productos destacados</h2>
                <p>Lo más buscado, al mejor precio. Equipos originales y con garantía.</p>
            </div>
            <a href="<?= e(url('catalog')) ?>" class="home-view-all">Ver todos los productos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <?php

        // Los destacados provienen exclusivamente de productos activos existentes en la base de datos.
        $productosPortada = array_slice(is_array($productos ?? null) ? $productos : [], 0, 7);

        ?>
        <?php if ($productosPortada): ?>
            <div class="product-grid home-product-grid">
                <?php foreach ($productosPortada as $producto): ?>
                    <?php
                    $categoriaProducto = (string) ($producto['categoria'] ?? 'Celular');
                    $nombreProductoPortada = (string) ($producto['nombre'] ?? '');
                    preg_match('/(\d+)\s*GB/i', $nombreProductoPortada, $coincidenciaAlmacenamiento);
                    $almacenamientoPortada = $coincidenciaAlmacenamiento[1] ?? '128';
                    $precioProductoPortada = (float) ($producto['precio'] ?? 0);
                    $precioAnteriorPortada = round(($precioProductoPortada / 0.8) / 10) * 10;
                    $descuentoPortada = $precioAnteriorPortada > 0
                        ? (int) round((1 - ($precioProductoPortada / $precioAnteriorPortada)) * 100)
                        : 0;
                    $enlaceCatalogoPortada = url('catalog?category=Celular');
                    $enlaceDetallePortada = url('catalog?q=' . rawurlencode($nombreProductoPortada));
                    ?>
                    <article class="product-card home-product-card">
                        <a class="home-product-image-link" href="<?= e($enlaceDetallePortada) ?>" aria-label="Explorar <?= e($producto['marca'] . ' ' . $nombreProductoPortada) ?>">
                            <div class="product-art home-product-art">
                                <span class="badge"><?= e($producto['etiqueta'] ?? $producto['marca']) ?></span>
                                <div class="phone-shape"><?= e(product_visual((string) $producto['marca'])) ?></div>
                            </div>
                        </a>
                        <div class="product-body home-product-body">
                            <div class="product-meta"><span><?= e($producto['marca']) ?></span><span><?= e($categoriaProducto) ?></span></div>
                            <h3><?= e($nombreProductoPortada) ?></h3>
                            <div class="home-product-specs"><?= e($almacenamientoPortada) ?> GB · Equipo original</div>
                            <div class="home-product-pricing">
                                <del><?= money($precioAnteriorPortada) ?></del>
                                <span>-<?= $descuentoPortada ?>%</span>
                            </div>
                            <div class="price"><?= money($precioProductoPortada) ?></div>
                            <div class="stock"><?= (int) $producto['existencias'] ?> unidades disponibles</div>
                            <div class="home-product-actions">
                                <a class="btn btn-primary home-product-action" href="<?= e($enlaceCatalogoPortada) ?>"><i class="bi bi-cart3" aria-hidden="true"></i> Ver catálogo</a>
                                <a class="btn btn-ghost home-product-details" href="<?= e($enlaceDetallePortada) ?>">Ver detalles</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="home-shortcuts">
    <div class="container shortcut-grid">
        <a href="<?= e(url('catalog')) ?>"><i class="bi bi-percent" aria-hidden="true"></i><span><b>Ofertas de la semana</b><small>Equipos seleccionados con precios especiales</small></span><i class="bi bi-arrow-right"></i></a>
        <a href="<?= e(url('catalog')) ?>"><i class="bi bi-laptop" aria-hidden="true"></i><span><b>Laptops para productividad</b><small>Trabajo, estudio y más</small></span><i class="bi bi-arrow-right"></i></a>
        <a href="<?= e(url('catalog')) ?>"><i class="bi bi-phone" aria-hidden="true"></i><span><b>Celulares que te conectan</b><small>Últimos lanzamientos</small></span><i class="bi bi-arrow-right"></i></a>
        <a href="<?= e(url('catalog')) ?>"><i class="bi bi-headphones" aria-hidden="true"></i><span><b>Accesorios originales</b><small>Completa tu experiencia</small></span><i class="bi bi-arrow-right"></i></a>
    </div>
</section>

<a class="assistant-fab" href="<?= e(url('smart/assistant')) ?>" aria-label="Abrir Cell AI">
    <span><i class="bi bi-robot" aria-hidden="true"></i></span><span><b>Cell AI</b><small>Asesor virtual</small></span><i class="assistant-status" aria-hidden="true"></i>
</a>
