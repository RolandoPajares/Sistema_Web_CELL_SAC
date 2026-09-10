<?php
require 'includes/functions.php';
$products = array_slice(all_products(), 0, 4);
$page_title = 'Inicio'; 
require 'includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">Tecnología original en Bagua</span>
            <h1>Tu próximo equipo está en <span>MD Technology Cell</span></h1>
            <p>Explora celulares y audífonos de marcas reconocidas, revisa disponibilidad y encuentra el producto ideal antes de visitar nuestro local.</p>
            
            <div class="hero-actions">
                <a class="btn btn-primary" href="catalogo.php">Ver catálogo</a>
                <a class="btn btn-ghost" href="contacto.php">Cómo llegar</a>
            </div>
            
            <div class="stats">
                <div class="stat">
                    <strong>7+</strong>Marcas
                </div>
                <div class="stat">
                    <strong>100%</strong>Originales
                </div>
                <div class="stat">
                    <strong>Bagua</strong>Atención local
                </div>
            </div>
        </div>
        
        <div class="hero-card">
            <img src="assets/img/local.jpeg" alt="Fachada de MD Technology Digital Cell">
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Marcas que encuentras</h2>
                <p>Celulares y audio para diferentes presupuestos y necesidades.</p>
            </div>
        </div>
        <div class="brands">
            <div class="brand-pill">Samsung</div>
            <div class="brand-pill">Apple</div>
            <div class="brand-pill">OPPO</div>
            <div class="brand-pill">Xiaomi</div>
            <div class="brand-pill">HONOR</div>
            <div class="brand-pill">JBL</div>
            <div class="brand-pill">Beats</div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Productos destacados</h2>
                <p>Precios referenciales para la demostración del sistema.</p>
            </div>
            <a href="catalogo.php" class="btn btn-ghost">Ver todos</a>
        </div>
        <div class="product-grid">
            <?php foreach($products as $p): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section>
    <div class="container feature-grid">
        <div class="feature">
            <span><i class="bi bi-patch-check-fill" aria-hidden="true"></i></span>
            <h3>Productos originales</h3>
            <p>Catálogo orientado a equipos y accesorios de marcas reconocidas.</p>
        </div>
        <div class="feature">
            <span><i class="bi bi-box-seam" aria-hidden="true"></i></span>
            <h3>Stock visible</h3>
            <p>El cliente puede consultar disponibilidad antes de dirigirse al local.</p>
        </div>
        <div class="feature">
            <span><i class="bi bi-gear-fill" aria-hidden="true"></i></span>
            <h3>Gestión centralizada</h3>
            <p>El encargado administra productos, inventario, usuarios y pedidos desde el panel.</p>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Conoce el local</h2>
                <p>Parte de los productos observados en la evidencia proporcionada por el negocio.</p>
            </div>
        </div>
        <div class="gallery">
            <img src="assets/img/showcase1.jpg" alt="Exhibición del local 1">
            <img src="assets/img/showcase2.jpg" alt="Exhibición del local 2">
            <img src="assets/img/showcase3.jpg" alt="Exhibición del local 3">
            <img src="assets/img/showcase4.jpg" alt="Exhibición del local 4">
        </div>
    </div>
</section>

<section>
    <div class="container cta">
        <div>
            <h2>Consulta desde casa, compra con confianza</h2>
            <p>Revisa el catálogo, crea tu cuenta y guarda tus datos para futuras compras.</p>
        </div>
        <a href="registro.php" class="btn btn-primary">Crear cuenta</a>
    </div>
</section>

<?php require 'includes/footer.php'; ?>