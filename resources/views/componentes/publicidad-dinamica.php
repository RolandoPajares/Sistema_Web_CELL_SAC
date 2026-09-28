<?php
$rutaPublicidad = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$rolPublicidad = (string) ($usuario['rol'] ?? 'visitante');
$perfilPublicidad = $rolPublicidad === 'cliente_mayorista'
    ? 'mayorista'
    : ($rolPublicidad === 'cliente_minorista' ? 'minorista' : ($usuario ? 'registrado' : 'visitante'));

$contextoPublicidad = match (true) {
    str_contains($rutaPublicidad, '/products/') => 'producto',
    str_contains($rutaPublicidad, '/catalog') => 'catalogo',
    str_contains($rutaPublicidad, '/cart') => 'carrito',
    str_contains($rutaPublicidad, '/checkout') && !empty($mensaje ?? null) => 'postcompra',
    str_contains($rutaPublicidad, '/checkout') => 'checkout',
    str_contains($rutaPublicidad, '/panel') || str_contains($rutaPublicidad, '/account') => 'cuenta',
    str_contains($rutaPublicidad, '/mayorista') || str_contains($rutaPublicidad, '/smart/optimizer') => 'mayorista',
    str_contains($rutaPublicidad, '/register') => 'registro',
    str_contains($rutaPublicidad, '/login') => 'ingreso',
    str_contains($rutaPublicidad, '/smart/') => 'inteligente',
    str_contains($rutaPublicidad, '/about') => 'nosotros',
    str_contains($rutaPublicidad, '/contact') => 'contacto',
    default => 'inicio',
};

$claveFrecuenciaPublicidad = $contextoPublicidad;
if ($contextoPublicidad === 'catalogo') {
    $categoriaPublicidad = (string) ($_GET['category'] ?? $_GET['categoria'] ?? 'todos');
    $claveFrecuenciaPublicidad .= '-' . strtolower((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $categoriaPublicidad));
}

$ofertasContextuales = [
    'inicio' => ['Oferta inteligente para ti', 'Explora equipos originales y encuentra una opción dentro de tu presupuesto.', 'Ver ofertas', 'catalog', 'bi-lightning-charge-fill', 'OFERTA DE HOY'],
    'catalogo' => ['Ahorra en tu próxima compra', 'Usa SmartMatch para filtrar por presupuesto y descubre accesorios compatibles.', 'Encontrar mi equipo', 'smart/recommend', 'bi-stars', 'RECOMENDACIÓN IA'],
    'producto' => ['Completa tu equipo', 'Añade cargador, funda o audífonos compatibles y arma un combo más conveniente.', 'Ver accesorios', 'catalog?category=Accesorio', 'bi-bag-plus', 'COMBO SUGERIDO'],
    'carrito' => ['Tu compra puede rendir más', 'Revisa accesorios relacionados antes de finalizar y evita pagar otro envío.', 'Agregar complemento', 'catalog?category=Accesorio', 'bi-cart-plus', 'ÚLTIMA OPORTUNIDAD'],
    'checkout' => ['Beneficio por comprar hoy', 'Finaliza tu pedido y recibe recomendaciones para cuidar mejor tu equipo.', 'Continuar compra', 'checkout', 'bi-shield-check', 'COMPRA SEGURA'],
    'postcompra' => ['Gracias por tu compra', 'Conserva un beneficio especial para tu siguiente pedido en MD Technology Cell.', 'Ver recomendados', 'smart/recommend', 'bi-gift-fill', 'CUPÓN DE FIDELIDAD'],
    'cuenta' => ['Una oferta exclusiva te espera', 'Vuelve a comprar tus favoritos y descubre sugerencias según tu historial.', 'Ver mis recomendados', 'smart/recommend', 'bi-heart-fill', 'SOLO PARA CLIENTES'],
    'mayorista' => ['Más unidades, mejor precio', 'Cotiza por lote, revisa productos de alta rotación y optimiza tu inversión.', 'Solicitar cotización', 'smart/optimizer', 'bi-box-seam-fill', 'BENEFICIO B2B'],
    'registro' => ['Regístrate y compra mejor', 'Guarda favoritos, consulta pedidos y recibe beneficios exclusivos.', 'Crear mi cuenta', 'register', 'bi-person-plus-fill', 'BIENVENIDA'],
    'ingreso' => ['Tus beneficios están guardados', 'Ingresa para ver ofertas, pedidos y recomendaciones personalizadas.', 'Ingresar', 'login', 'bi-person-check-fill', 'CLIENTE MD'],
    'inteligente' => ['Compra con más información', 'Compara opciones y recibe una recomendación clara antes de decidir.', 'Explorar catálogo', 'catalog', 'bi-cpu-fill', 'SMARTCOMMERCE'],
    'nosotros' => ['Tecnología con atención cercana', 'Conoce nuestro catálogo y encuentra productos originales con soporte local.', 'Ver productos', 'catalog', 'bi-patch-check-fill', 'COMPRA CON CONFIANZA'],
    'contacto' => ['¿Necesitas ayuda para elegir?', 'Cuéntanos qué buscas o deja que SmartMatch encuentre una opción para ti.', 'Probar SmartMatch', 'smart/recommend', 'bi-chat-heart-fill', 'TE AYUDAMOS'],
];

if ($perfilPublicidad === 'mayorista') {
    $ofertaEntrada = ['Precio especial por volumen', 'Accede a descuentos por lote, stock destacado y productos de alta rotación.', 'Cotizar mi pedido', 'smart/optimizer', 'bi-boxes', 'CLIENTE MAYORISTA'];
} elseif ($perfilPublicidad === 'minorista') {
    $ofertaEntrada = ['Beneficio exclusivo para ti', 'Descubre descuentos por recompra, accesorios y productos según tus intereses.', 'Ver mis ofertas', 'smart/recommend', 'bi-gift-fill', 'CLIENTE FRECUENTE'];
} elseif ($perfilPublicidad === 'registrado') {
    $ofertaEntrada = ['Tenemos una oferta para tu cuenta', 'Explora productos y recomendaciones seleccionadas para una compra más conveniente.', 'Descubrir ahora', 'catalog', 'bi-stars', 'OFERTA PERSONALIZADA'];
} else {
    $ofertaEntrada = ['¡Bienvenido a MD Technology Cell!', 'Regístrate para guardar favoritos, consultar pedidos y recibir ofertas exclusivas.', 'Quiero registrarme', 'register', 'bi-gift-fill', 'BENEFICIO DE BIENVENIDA'];
}

$ofertaContextual = $ofertasContextuales[$contextoPublicidad] ?? $ofertasContextuales['inicio'];
if ($perfilPublicidad === 'mayorista' && !in_array($contextoPublicidad, ['carrito', 'checkout', 'postcompra'], true)) {
    $ofertaContextual = $ofertasContextuales['mayorista'];
}

$campaniaPrincipal = $perfilPublicidad === 'visitante' ? ($campaniaEmergente ?? null) : null;
$idCampania = (int) ($campaniaPrincipal['id'] ?? 0);
$urlSeguimiento = $idCampania > 0 ? url('campaigns/' . $idCampania . '/track') : '';
?>
<div
    class="publicidad-dinamica publicidad-<?= e($contextoPublicidad) ?>"
    id="publicidadDinamica"
    data-contexto="<?= e($claveFrecuenciaPublicidad) ?>"
    data-perfil="<?= e($perfilPublicidad) ?>"
    data-limite="4"
    data-espera="12000">
    <div class="publicidad-modal-fondo js-publicidad-modal" hidden>
        <section class="publicidad-modal" role="dialog" aria-modal="true" aria-labelledby="publicidadTitulo"
            <?= $urlSeguimiento ? 'data-track-url="' . e($urlSeguimiento) . '"' : '' ?>>
            <button class="publicidad-cerrar js-publicidad-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
            <div class="publicidad-modal-arte">
                <span><i class="bi <?= e($ofertaEntrada[4]) ?>"></i></span>
                <b>MD</b><strong>SMART DEALS</strong>
                <small>Una mejor compra empieza con una buena recomendación.</small>
            </div>
            <div class="publicidad-modal-contenido">
                <span class="publicidad-etiqueta"><i class="bi bi-lightning-charge-fill"></i> <?= e($ofertaEntrada[5]) ?></span>
                <h2 id="publicidadTitulo"><?= e($campaniaPrincipal['titulo'] ?? $ofertaEntrada[0]) ?></h2>
                <p><?= e($campaniaPrincipal['descripcion'] ?? $ofertaEntrada[1]) ?></p>
                <?php if (($campaniaPrincipal['precio_oferta'] ?? null) !== null): ?>
                    <div class="publicidad-precio"><?php if (($campaniaPrincipal['precio_anterior'] ?? null) !== null): ?><del><?= money($campaniaPrincipal['precio_anterior']) ?></del><?php endif; ?><strong><?= money($campaniaPrincipal['precio_oferta']) ?></strong></div>
                <?php else: ?>
                    <div class="publicidad-beneficios"><span><i class="bi bi-check-circle-fill"></i> Fácil de usar</span><span><i class="bi bi-check-circle-fill"></i> Sin compromiso</span></div>
                <?php endif; ?>
                <a class="btn btn-primary js-publicidad-accion" href="<?= e($campaniaPrincipal ? campaign_url((string) $campaniaPrincipal['url_boton']) : url($ofertaEntrada[3])) ?>"><?= e($campaniaPrincipal['texto_boton'] ?? $ofertaEntrada[2]) ?> <i class="bi bi-arrow-right"></i></a>
                <button class="publicidad-despues js-publicidad-cerrar" type="button">Seguir navegando</button>
            </div>
        </section>
    </div>

    <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden>
        <button class="publicidad-cerrar js-contextual-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
        <span class="publicidad-contextual-icono"><i class="bi <?= e($ofertaContextual[4]) ?>"></i></span>
        <div><small><?= e($ofertaContextual[5]) ?></small><b><?= e($ofertaContextual[0]) ?></b>
            <p><?= e($ofertaContextual[1]) ?></p>
        </div>
        <a class="btn btn-primary" href="<?= e(url($ofertaContextual[3])) ?>"><?= e($ofertaContextual[2]) ?></a>
    </aside>

    <button class="publicidad-lanzador js-publicidad-lanzador" type="button" aria-label="Ver oferta disponible" title="Ver oferta">
        <i class="bi bi-gift-fill"></i><span>Oferta</span>
    </button>
</div>
