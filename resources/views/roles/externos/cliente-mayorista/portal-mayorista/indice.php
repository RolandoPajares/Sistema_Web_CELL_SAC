<section class="mayorista-hero">
    <div class="container mayorista-hero-grid">
        <div class="mayorista-copy">
            <span class="eyebrow">Portal mayorista · Empresas que impulsan el futuro</span>
            <h1>Haz crecer tu negocio con <em>MD Technology Cell</em></h1>
            <p>Accede a precios mayoristas, compra por volumen y recibe asesoría personalizada para tu empresa. Tecnología original, stock garantizado y el respaldo de las mejores marcas.</p>
            <div class="hero-actions"><a class="btn btn-primary" href="#solicitar-cotizacion">Solicitar cotización <i class="bi bi-arrow-right"></i></a><a class="btn btn-ghost" href="#catalogo-b2b"><i class="bi bi-grid"></i> Ver catálogo mayorista</a></div>
            <div class="mayorista-beneficios"><?php foreach ([['bi-cash-stack', 'Precios por volumen'], ['bi-box-seam', 'Stock garantizado'], ['bi-headset', 'Atención personalizada'], ['bi-truck', 'Envíos seguros'], ['bi-shield-check', 'Productos originales']] as $beneficio): ?><span><i class="bi <?= e($beneficio[0]) ?>"></i><?= e($beneficio[1]) ?></span><?php endforeach; ?></div>
        </div>
        <div class="mayorista-ilustracion"><span>Tu aliado mayorista<br>en tecnología</span>
            <div class="cajas-mayorista"><i class="bi bi-box-seam"></i><i class="bi bi-laptop"></i><i class="bi bi-phone"></i><i class="bi bi-earbuds"></i></div>
            <ul>
                <li><i class="bi bi-graph-up-arrow"></i> Más negocios, más posibilidades</li>
                <li><i class="bi bi-building-check"></i> Equipamos empresas</li>
                <li><i class="bi bi-tags"></i> Precios especiales</li>
                <li><i class="bi bi-headset"></i> Asesoría dedicada</li>
            </ul>
        </div>
    </div>
</section>
<section class="container marcas-mayorista"><b>Las mejores marcas<br>para tu negocio</b><?php foreach (['SAMSUNG', 'Apple', 'oppo', 'XIAOMI', 'HONOR', 'JBL', 'Beats'] as $marca): ?><span><?= e($marca) ?></span><?php endforeach; ?></section>
<section class="container mayorista-cuerpo">
    <div class="mayorista-principal">
        <header class="section-head">
            <div><span class="eyebrow">Herramientas B2B</span>
                <h2>Potencia tu negocio</h2>
            </div>
        </header>
        <div class="herramientas-b2b"><a href="<?= e(url('smart/recommend')) ?>"><i class="bi bi-lightbulb"></i><b>SmartMatch B2B</b><small>Encuentra el mix ideal para tu negocio con IA.</small></a><a href="<?= e(url('smart/compare')) ?>"><i class="bi bi-columns-gap"></i><b>Comparador inteligente</b><small>Compara precios y toma mejores decisiones.</small></a><a href="<?= e(url('smart/assistant')) ?>"><i class="bi bi-robot"></i><b>MD Assistant B2B</b><small>Asesoría especializada en compras empresariales.</small></a><a href="#solicitar-cotizacion"><i class="bi bi-file-earmark-text"></i><b>Cotizaciones rápidas</b><small>Genera y recibe tu propuesta en minutos.</small></a></div>
        <header class="section-head" id="catalogo-b2b">
            <div><span class="eyebrow">Precios especiales por volumen</span>
                <h2>Productos destacados para mayoristas</h2>
            </div><a href="<?= e(url('catalog')) ?>">Ver todo el catálogo <i class="bi bi-arrow-right"></i></a>
        </header>
        <div class="productos-mayorista"><?php foreach ($productos as $producto): ?><article><span><?= e($producto['etiqueta'] ?: 'Disponible') ?></span><i class="bi bi-phone"></i><small><?= e($producto['marca']) ?></small>
                    <h3><?= e($producto['nombre']) ?></h3>
                    <p>1–9 und. <b><?= money($producto['precio']) ?></b><br>10–49 und. <b><?= money((float) $producto['precio'] * .95) ?></b><br>50+ und. <b><?= money((float) $producto['precio'] * .9) ?></b></p><a href="<?= e(url('products/' . (int) $producto['id'])) ?>">Ver producto</a>
                </article><?php endforeach; ?></div>
    </div>
    <aside class="cotizacion-rapida" id="solicitar-cotizacion">
        <h2>Solicita tu cotización mayorista</h2>
        <p>Cuéntanos sobre tu empresa y te enviaremos una propuesta personalizada.</p>
        <form action="<?= e(url('contact')) ?>" method="get"><label><i class="bi bi-buildings"></i><input name="empresa" placeholder="Nombre de la empresa" required></label><label><i class="bi bi-card-text"></i><input name="ruc" placeholder="RUC de la empresa" required></label><label><i class="bi bi-geo-alt"></i><input name="ciudad" placeholder="Ciudad" required></label><label><i class="bi bi-box-seam"></i><input name="productos" placeholder="Productos de interés" required></label><button class="btn btn-primary">Enviar solicitud <i class="bi bi-arrow-right"></i></button></form><small><i class="bi bi-shield-check"></i> Nos pondremos en contacto en menos de 24 horas.</small><a class="asesor-b2b" href="<?= e(url('smart/assistant')) ?>"><i class="bi bi-people"></i><span><b>Un equipo dedicado a tu crecimiento</b>Asesoría, mejores condiciones y soporte continuo.</span></a>
    </aside>
</section>