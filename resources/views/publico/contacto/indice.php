<?php
// Enlace para abrir la ubicación en Google Maps (usa la dirección de la configuración)
$direccion = config('app.address');
$comoLlegar = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($direccion);
?>
<!-- Hoja de estilos solo para esta página -->
<link rel="stylesheet" href="<?= e(asset('assets/css/publico/contacto.css')) ?>">

<!-- ===== 1. Banner principal ===== -->
<section class="contacto-banner">
    <div class="container contacto-banner-grid">
        <div>
            <span class="contacto-etiqueta"><i class="bi bi-chat-dots"></i> Estamos para ayudarte</span>
            <h1>Hablemos de tu <span>próximo equipo</span></h1>
            <p>Escríbenos tu consulta o visítanos en nuestra tienda de Bagua. Te ayudamos a elegir el celular o accesorio ideal, con productos originales y atención cercana.</p>
            <div class="contacto-botones">
                <a class="btn btn-primary" href="#formulario"><i class="bi bi-send"></i> Enviar consulta</a>
                <a class="btn btn-ghost" href="#mapa"><i class="bi bi-geo-alt"></i> Cómo llegar</a>
            </div>
            <ul class="contacto-ventajas">
                <li><i class="bi bi-patch-check-fill"></i> Productos originales</li>
                <li><i class="bi bi-person-heart"></i> Asesoría personalizada</li>
                <li><i class="bi bi-shop"></i> Atención presencial</li>
            </ul>
        </div>
        <div class="contacto-foto">
            <img src="<?= e(asset('assets/img/publico/nosotros/local.jpg')) ?>" alt="Fachada de la tienda MD Technology Digital Cell">
        </div>
    </div>
</section>

<!-- ===== 4. Datos de la tienda ===== -->
<section class="container contacto-datos">
    <article>
        <i class="bi bi-geo-alt"></i>
        <div><h2>Dirección</h2><p><?= e($direccion) ?></p></div>
    </article>
    <article>
        <i class="bi bi-signpost-2"></i>
        <div><h2>Referencia</h2><p>Cerca de Plásticos Jireh</p></div>
    </article>
    <article>
        <i class="bi bi-calendar-week"></i>
        <div><h2>Horario</h2><p>Lunes a sábado, horario comercial</p></div>
    </article>
    <article>
        <i class="bi bi-envelope-paper"></i>
        <div><h2>Consultas web</h2><p>Déjanos tu mensaje y te respondemos</p></div>
    </article>
</section>

<!-- ===== 2. Formulario de consulta ===== -->
<!-- IMPORTANTE: el formulario sigue enviando los mismos campos (name, contact, message) por POST -->
<section class="container contacto-seccion" id="formulario">
    <div class="contacto-formulario">
        <div class="contacto-formulario-titulo">
            <i class="bi bi-chat-square-text"></i>
            <div>
                <h2>Envíanos tu consulta</h2>
                <p>Cuéntanos qué producto buscas o en qué te podemos ayudar.</p>
            </div>
        </div>
        <?php if (!empty($exito)): ?><div class="alert alert-success"><?= e($exito) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="<?= e(url('contact')) ?>">
            <?= csrf_field() ?>
            <div class="contacto-dos-campos">
                <div class="form-group">
                    <label for="contact-name">Nombre</label>
                    <div class="contacto-campo"><i class="bi bi-person"></i><input id="contact-name" name="name" class="input" required maxlength="120" placeholder="Tu nombre completo"></div>
                </div>
                <div class="form-group">
                    <label for="contact-email">Correo o teléfono</label>
                    <div class="contacto-campo"><i class="bi bi-envelope"></i><input id="contact-email" name="contact" class="input" required maxlength="160" placeholder="ejemplo@correo.com o 9XX XXX XXX"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="contact-message">Mensaje</label>
                <div class="contacto-campo"><i class="bi bi-chat-left-text"></i><textarea id="contact-message" name="message" rows="5" required maxlength="2000" placeholder="Ej.: ¿Tienen disponible el Samsung Galaxy A55 en color azul?"></textarea></div>
            </div>
            <div class="contacto-enviar">
                <small><i class="bi bi-shield-lock"></i> Tus datos solo se usan para responder tu consulta.</small>
                <button type="submit" class="btn btn-primary">Enviar consulta <i class="bi bi-send"></i></button>
            </div>
        </form>
    </div>

    <!-- ===== 3. Ubicación de la tienda ===== -->
    <div class="contacto-mapa" id="mapa">
        <div class="contacto-mapa-dibujo">
            <div class="contacto-pin">
                <i class="bi bi-geo-alt-fill"></i>
                <span>MD Technology Cell</span>
            </div>
        </div>
        <div class="contacto-mapa-pie">
            <div><b>MD Technology Digital Cell</b><small><?= e($direccion) ?></small></div>
            <a class="btn btn-primary" href="<?= e($comoLlegar) ?>" target="_blank"><i class="bi bi-sign-turn-right"></i> Ver en Google Maps</a>
        </div>
    </div>
</section>
