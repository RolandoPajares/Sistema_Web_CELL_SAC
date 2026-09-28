<section class="home-hero">
    <div class="container home-hero-grid">
        <div class="home-hero-copy">
            <span class="eyebrow">Tecnología original en Bagua</span>
            <h1>Tu próximo equipo está en <span>MD Technology Cell</span></h1>
            <p>Explora celulares, laptops y accesorios de las mejores marcas, con confianza, atención local y precios para ti.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url('catalog')) ?>">Ver catálogo <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                <a class="btn btn-ghost" href="<?= e(url('contact')) ?>"><i class="bi bi-geo-alt" aria-hidden="true"></i> Cómo llegar</a>
            </div>
            <div class="hero-trust" aria-label="Beneficios de compra">
                <span><i class="bi bi-shield-check" aria-hidden="true"></i><b>100%</b><small>Productos originales</small></span>
                <span><i class="bi bi-truck" aria-hidden="true"></i><b>Envíos en Bagua</b><small>Rápido y seguro</small></span>
                <span><i class="bi bi-shop" aria-hidden="true"></i><b>Atención local</b><small>Expertos en tecnología</small></span>
            </div>
        </div>

        <div class="home-hero-visual" aria-label="Celular, laptop, audífonos y reloj inteligente">
            <span class="hero-orbit hero-orbit-one" aria-hidden="true"></span>
            <span class="hero-orbit hero-orbit-two" aria-hidden="true"></span>
            <img src="<?= e(asset('assets/img/publico/inicio/secciones/hero-devices.png')) ?>" alt="Selección de equipos tecnológicos: celular, laptop, audífonos y reloj inteligente">
            <div class="hero-note"><span>Tecnología</span> más cerca de ti <i class="bi bi-arrow-down-left" aria-hidden="true"></i></div>
            <div class="hero-benefits">
                <div><i class="bi bi-patch-check-fill" aria-hidden="true"></i><span><b>Equipos originales</b><small>Con garantía oficial</small></span></div>
                <div><i class="bi bi-truck" aria-hidden="true"></i><span><b>Precios competitivos</b><small>Para todos</small></span></div>
                <div><i class="bi bi-headset" aria-hidden="true"></i><span><b>Asesoría especializada</b><small>En Bagua</small></span></div>
            </div>
        </div>
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
        <div class="brand-pill dell">DELL</div>
        <a class="all-brands" href="<?= e(url('catalog')) ?>">Ver todas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
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
        $muestrasPortada = [
            ['id' => 0, 'marca' => 'Apple', 'nombre' => 'iPhone 15 128GB', 'categoria' => 'Celular', 'precio' => 2999, 'existencias' => 8, 'etiqueta' => 'Más vendido', 'demo' => true],
            ['id' => 0, 'marca' => 'Samsung', 'nombre' => 'Galaxy S24 256GB', 'categoria' => 'Celular', 'precio' => 2399, 'existencias' => 12, 'etiqueta' => 'Oferta', 'demo' => true],
            ['id' => 0, 'marca' => 'Xiaomi', 'nombre' => 'Redmi Note 13 Pro', 'categoria' => 'Celular', 'precio' => 899, 'existencias' => 15, 'etiqueta' => 'Nuevo', 'demo' => true],
            ['id' => 0, 'marca' => 'Lenovo', 'nombre' => 'ThinkPad E14 Gen 5', 'categoria' => 'Laptop', 'precio' => 2199, 'existencias' => 6, 'etiqueta' => 'Nuevo', 'demo' => true],
            ['id' => 0, 'marca' => 'JBL', 'nombre' => 'Audífonos Tune 520BT', 'categoria' => 'Audio', 'precio' => 249, 'existencias' => 18, 'etiqueta' => 'Popular', 'demo' => true],
            ['id' => 0, 'marca' => 'Apple', 'nombre' => 'Watch SE', 'categoria' => 'Reloj', 'precio' => 1099, 'existencias' => 7, 'etiqueta' => 'Popular', 'demo' => true],
            ['id' => 0, 'marca' => 'Apple', 'nombre' => 'AirPods Pro', 'categoria' => 'Audio', 'precio' => 1299, 'existencias' => 9, 'etiqueta' => 'Original', 'demo' => true],
        ];
        $productosPortada = $muestrasPortada;
        ?>
        <?php if ($productosPortada): ?>
            <div class="product-grid home-product-grid">
                <?php foreach ($productosPortada as $producto): ?>
                    <?php require dirname(__DIR__, 2) . '/componentes/tarjeta-producto.php'; ?>
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

<a class="assistant-fab" href="<?= e(url('smart/assistant')) ?>" aria-label="Abrir MD Assistant">
    <span><i class="bi bi-robot" aria-hidden="true"></i></span><span><b>¿Necesitas ayuda?</b><small>Asesor virtual</small></span><i class="assistant-status" aria-hidden="true"></i>
</a>
