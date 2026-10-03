<?php
/**
 * @var string $atributoProductosInicioGridOculto
 * @var array<array-key, mixed> $tarjetasProducto
 * @var string $atributoProductosInicioVacioOculto
 */ ?><section class="home-hero home-hero-banner">
    <div class="home-hero-banner-link">
        <img class="home-hero-banner-image" src="<?= e(url_recurso_estatico('assets/img/publico/inicio/banners/bannerInicio.png')) ?>" alt="Celulares y accesorios de MD Technology Digital Cell">
    </div>
    <div class="home-hero-banner-content">
        <span class="home-hero-banner-kicker">Tecnología original en Bagua</span>
        <h1>Tu próximo celular está en <span>MD Technology Cell</span></h1>
        <p>Celulares y accesorios originales con atención local y precios para ti.</p>
        <div class="home-hero-banner-actions">
            <a class="btn btn-primary" href="<?= e(url_interna('catalog?category=Celular')) ?>">Ver celulares <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a class="btn btn-ghost" href="<?= e(url_interna('catalog?category=Accesorio')) ?>"><i class="bi bi-tag" aria-hidden="true"></i> Explorar accesorios</a>
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
        <div class="brand-pill beats"><span aria-hidden="true">b</span> Beats</div>
    </div>
</section>

<section class="smart-home-strip">
    <div class="container smart-row">
        <div class="smart-intro">
            <h2>Compra<br>más inteligente</h2>
            <p>Herramientas con IA y filtros avanzados para encontrar exactamente lo que necesitas.</p>
        </div>

        <div class="smart-home-grid">
            <a class="smart-tile smart-purple" href="<?= e(url_interna('smart/recommend')) ?>"><span><i class="bi bi-lightbulb-fill" aria-hidden="true"></i></span>
            <div><b>SmartMatch</b><small>Encuentra el equipo ideal según tu estilo de vida.</small></div><i class="bi bi-chevron-right"></i>
            </a>
            <a class="smart-tile smart-orange" href="<?= e(url_interna('smart/compare')) ?>"><span><i class="bi bi-columns-gap" aria-hidden="true"></i></span>
            <div><b>Comparador</b><small>Compara hasta 3 equipos con puntajes.</small></div><i class="bi bi-chevron-right"></i>
            </a>
            <a class="smart-tile smart-gold" href="<?= e(url_interna('mayorista')) ?>"><span><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
            <div><b>Modo emprendedor</b><small>Arma una compra mayorista según tu presupuesto.</small></div><i class="bi bi-chevron-right"></i>
            </a>
            <a class="smart-tile smart-assistant" href="<?= e(url_interna('smart/assistant')) ?>"><span><i class="bi bi-robot" aria-hidden="true"></i></span>
            <div><b>MD Assistant <em>NUEVO</em></b><small>Conversa con IA y recibe recomendaciones al instante.</small></div><i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <div class="section-head home-section-head">
            <div>
                <h2>Los más populares en oferta</h2>
                <p>Ofertas vigentes seleccionadas de todas las categorías.</p>
            </div>
            <a href="<?= e(url_interna('catalog')) ?>" class="home-view-all">Ver todos los productos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="product-grid home-product-grid" <?= $atributoProductosInicioGridOculto ?>>
                <?php foreach ($tarjetasProducto as $tarjetaProducto): ?>
                <?php require __DIR__ . '/_tarjeta-producto.php'; ?>
                <?php endforeach; ?>
        </div>
        <p class="home-products-empty" <?= $atributoProductosInicioVacioOculto ?>>No hay productos disponibles por el momento.</p>
    </div>
</section>

<section class="home-shortcuts">
    <div class="container shortcut-grid">

        <a href="<?= e(url_interna('catalog')) ?>"><i class="bi bi-percent" aria-hidden="true"></i><span><b>Ofertas de la semana</b><small>Equipos seleccionados con precios especiales</small></span><i class="bi bi-arrow-right"></i>
        </a>

        <a href="<?= e(url_interna('catalog')) ?>"><i class="bi bi-laptop" aria-hidden="true"></i><span><b>Laptops para productividad</b><small>Trabajo, estudio y más</small></span><i class="bi bi-arrow-right"></i>
        </a>

        <a href="<?= e(url_interna('catalog')) ?>"><i class="bi bi-phone" aria-hidden="true"></i><span><b>Celulares que te conectan</b><small>Últimos lanzamientos</small></span><i class="bi bi-arrow-right"></i>
        </a>

        <a href="<?= e(url_interna('catalog')) ?>"><i class="bi bi-headphones" aria-hidden="true"></i><span><b>Accesorios originales</b><small>Completa tu experiencia</small></span><i class="bi bi-arrow-right"></i>
        </a>
    </div>
</section>

<a class="assistant-fab" href="<?= e(url_interna('smart/assistant')) ?>" aria-label="Abrir Cell AI">
    <span><i class="bi bi-robot" aria-hidden="true"></i></span><span><b>Cell AI</b><small>Asesor virtual</small></span><i class="assistant-status" aria-hidden="true"></i>
</a>
