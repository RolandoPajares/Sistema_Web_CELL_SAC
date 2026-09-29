<?php
$rolCuenta = \App\Soporte\Autorizacion\AccesoRol::normalize((string) ($usuarioCuenta['rol'] ?? 'cliente_minorista'));
$opcionesCuenta = \App\Soporte\Autorizacion\AccesoRol::navigation($rolCuenta);
$nombreCuenta = (string) ($usuarioCuenta['nombre'] ?? 'Cliente');
$esCuentaMayorista = $rolCuenta === 'cliente_mayorista';
$tituloCuenta = $esCuentaMayorista ? 'Portal mayorista B2B' : 'Mi cuenta';
?>
<section class="cuenta-fondo">
    <div class="container cuenta-migas"><a href="<?= e(url()) ?>">Inicio</a><i class="bi bi-chevron-right"></i><span><?= e($tituloCuenta) ?></span><?php if ($modulo !== 'dashboard'): ?><i class="bi bi-chevron-right"></i><b><?= e($interfaz['titulo']) ?></b><?php endif; ?></div>
    <div class="container cuenta-diseno">
        <aside class="cuenta-lateral">
            <div class="cuenta-identidad"><span><i class="bi bi-person-fill"></i></span>
                <div><b>Hola, <?= e(explode(' ', $nombreCuenta)[0]) ?></b><small>Cliente <?= ($usuarioCuenta['rol'] ?? '') === 'cliente_mayorista' ? 'mayorista' : 'minorista' ?></small><em><i></i> Cuenta activa</em></div>
            </div>
            <nav>
                <?php foreach ($opcionesCuenta as $opcionCuenta): ?>
                    <?php $destinoCuenta = \App\Soporte\Autorizacion\AccesoRol::destino($rolCuenta, $opcionCuenta['slug']); ?>
                    <a class="<?= $modulo === $opcionCuenta['slug'] ? 'activo' : '' ?>" href="<?= e(url($destinoCuenta)) ?>"><i class="bi <?= e($opcionCuenta['icon']) ?>"></i><span><?= e($opcionCuenta['label']) ?></span></a>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form>
            <a class="cuenta-anuncio" href="<?= e(url('catalog')) ?>"><b>Tecnología que te acompaña siempre</b><span>Ver catálogo <i class="bi bi-arrow-right"></i></span></a>
        </aside>
        <main class="cuenta-contenido">
            <?php if ($modulo === 'dashboard'): ?>
                <!-- Banner de bienvenida (cambia el texto si es cliente mayorista o minorista) -->
                <header class="cuenta-banner">
                    <div class="cuenta-banner-texto">
                        <?php if ($esCuentaMayorista): ?>
                            <span class="cuenta-banner-etiqueta"><i class="bi bi-buildings"></i> Cliente mayorista</span>
                        <?php else: ?>
                            <span class="cuenta-banner-etiqueta"><i class="bi bi-bag-heart"></i> Cliente minorista</span>
                        <?php endif; ?>
                        <h1><?= e($tituloCuenta) ?></h1>
                        <p><?= $esCuentaMayorista ? 'Gestiona tus cotizaciones, pedidos y compras por volumen.' : 'Gestiona tus compras, datos y preferencias.' ?></p>
                        <div class="cuenta-banner-botones">
                            <?php if ($esCuentaMayorista): ?>
                                <a class="cuenta-btn-blanco" href="<?= e(url('mayorista')) ?>"><i class="bi bi-grid"></i> Catálogo B2B</a>
                                <a class="cuenta-btn-borde" href="<?= e(url('panel/cotizaciones')) ?>"><i class="bi bi-file-earmark-text"></i> Mis cotizaciones</a>
                            <?php else: ?>
                                <a class="cuenta-btn-blanco" href="<?= e(url('catalog')) ?>"><i class="bi bi-phone"></i> Ver catálogo</a>
                                <a class="cuenta-btn-borde" href="<?= e(url('panel/pedidos')) ?>"><i class="bi bi-box-seam"></i> Mis pedidos</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <img src="<?= e(asset('assets/img/publico/inicio/secciones/hero-devices.png')) ?>" alt="Celulares y accesorios">
                </header>
                <div class="cuenta-resumen">
                    <?php foreach ($interfaz['metricas'] as $metrica): ?><article><i class="bi <?= e($metrica[2]) ?>"></i>
                            <div><strong><?= e($metrica[1]) ?></strong><span><?= e($metrica[0]) ?></span></div>
                        </article><?php endforeach; ?>
                </div>
                <section class="cuenta-panel">
                    <div class="cuenta-panel-titulo">
                        <h2>Pedidos recientes</h2><a href="<?= e(url('panel/pedidos')) ?>">Ver todos <i class="bi bi-chevron-right"></i></a>
                    </div><?php $mostrarPedidos = true;
                            require __DIR__ . '/_parciales/lista-pedidos.php'; ?>
                </section>
                <div class="cuenta-dos-columnas">
                    <section class="cuenta-panel">
                        <div class="cuenta-panel-titulo">
                            <h2>Accesos rápidos</h2>
                        </div>
                        <div class="cuenta-accesos"><?php if ($rolCuenta === 'cliente_mayorista'): ?><a href="<?= e(url('mayorista')) ?>"><i class="bi bi-grid"></i> Catálogo B2B</a><a href="<?= e(url('panel/cotizaciones')) ?>"><i class="bi bi-file-earmark-text"></i> Cotizaciones</a><a href="<?= e(url('panel/pedidos-mayoristas')) ?>"><i class="bi bi-truck"></i> Mis pedidos</a><a href="<?= e(url('panel/historial')) ?>"><i class="bi bi-clock-history"></i> Historial</a><?php else: ?><a href="<?= e(url('panel/perfil')) ?>"><i class="bi bi-person"></i> Mis datos</a><a href="<?= e(url('panel/direcciones')) ?>"><i class="bi bi-geo-alt"></i> Direcciones</a><a href="<?= e(url('panel/pedidos')) ?>"><i class="bi bi-box-seam"></i> Mis pedidos</a><a href="<?= e(url('panel/historial')) ?>"><i class="bi bi-clock-history"></i> Historial</a><?php endif; ?></div>
                    </section>
                    <section class="cuenta-panel cuenta-recomendado"><i class="bi bi-stars"></i>
                        <div>
                            <h2>Recomendado para ti</h2>
                            <p>Encuentra equipos según tus compras anteriores.</p><a class="btn btn-primary" href="<?= e(url('smart/recommend')) ?>">Probar SmartMatch</a>
                        </div>
                    </section>
                </div>
            <?php elseif (in_array($modulo, ['pedidos', 'pedidos-mayoristas'], true)): ?>
                <header class="cuenta-titulo">
                    <div><span><i class="bi bi-box-seam"></i></span>
                        <div>
                            <h1>Mis pedidos</h1>
                            <p>Consulta y da seguimiento a todos tus pedidos.</p>
                        </div>
                    </div>
                </header>
                <section class="cuenta-filtros"><label>Estado del pedido<select>
                            <option>Todos los estados</option>
                            <option>En proceso</option>
                            <option>Enviado</option>
                            <option>Entregado</option>
                        </select></label><label>Rango de fechas<input type="date"></label><a class="btn btn-primary" href="#lista-pedidos"><i class="bi bi-search"></i> Buscar pedidos</a></section>
                <section class="cuenta-panel" id="lista-pedidos"><?php $mostrarPedidos = false;
                                                                    require __DIR__ . '/_parciales/lista-pedidos.php'; ?></section>
            <?php elseif ($modulo === 'historial'): ?>
                <header class="cuenta-titulo">
                    <div><span><i class="bi bi-cart-check"></i></span>
                        <div>
                            <h1>Historial de compras</h1>
                            <p>Consulta tus pedidos, descarga comprobantes y vuelve a comprar.</p>
                        </div>
                    </div>
                </header>
                <?php require dirname(__DIR__) . '/panel/_parciales/tipos/historial.php'; ?>
            <?php elseif ($modulo === 'perfil'): ?>
                <header class="cuenta-titulo">
                    <div><span><i class="bi bi-person"></i></span>
                        <div>
                            <h1>Mis datos</h1>
                            <p>Mantén actualizada tu información personal.</p>
                        </div>
                    </div>
                </header>
                <?php require dirname(__DIR__) . '/panel/_parciales/tipos/perfil.php'; ?>
            <?php elseif ($modulo === 'direcciones'): ?>
                <header class="cuenta-titulo">
                    <div><span><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <h1>Mis direcciones</h1>
                            <p>Gestiona dónde deseas recibir tus compras.</p>
                        </div>
                    </div><a class="btn btn-primary" href="#nueva-direccion"><i class="bi bi-plus-lg"></i> Nueva dirección</a>
                </header>
                <div class="cuenta-tarjetas-direccion">
                    <article><span class="estado-cuenta">Principal</span><i class="bi bi-house-door"></i>
                        <h2>Casa</h2>
                        <p>Av. Héroes del Cenepa 123<br>Bagua, Amazonas</p><small>Referencia: frente al parque</small>
                        <div><button>Editar</button><button>Eliminar</button></div>
                    </article>
                    <article><i class="bi bi-building"></i>
                        <h2>Trabajo</h2>
                        <p>Jr. Amazonas 563<br>Bagua, Amazonas</p><small>Horario: 9:00 a. m. – 6:00 p. m.</small>
                        <div><button>Editar</button><button>Marcar principal</button></div>
                    </article>
                </div>
            <?php elseif ($modulo === 'favoritos'): ?>
                <header class="cuenta-titulo">
                    <div><span><i class="bi bi-heart"></i></span>
                        <div>
                            <h1>Mis favoritos</h1>
                            <p>Productos guardados para decidir después.</p>
                        </div>
                    </div>
                </header>
                <?php require dirname(__DIR__) . '/panel/_parciales/tipos/tarjetas.php'; ?>
            <?php else: ?>
                <header class="cuenta-titulo">
                    <div><span><i class="bi bi-grid"></i></span>
                        <div>
                            <h1><?= e($interfaz['titulo']) ?></h1>
                            <p><?= e($interfaz['descripcion']) ?></p>
                        </div>
                    </div>
                </header>
                <section class="cuenta-panel">
                    <div class="cuenta-vacio"><i class="bi bi-arrow-right-circle"></i>
                        <h2><?= e($interfaz['titulo']) ?></h2>
                        <p>Selecciona una opción del menú para continuar con este recorrido.</p><a class="btn btn-primary" href="<?= e(url('panel')) ?>">Volver al portal</a>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</section>
