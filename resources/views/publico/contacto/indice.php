<section>
    <div class="container detail-grid">
        <div class="panel">
            <h1>Visitanos</h1>
            <p><b>Direccion:</b> Jr. Amazonas 563, Bagua 01721.</p>
            <p><b>Referencia:</b> cerca de Plasticos Jireh.</p>
            <p>Para la version final del negocio se puede anadir telefono, WhatsApp, horario exacto y mapa embebido.</p>
            <a class="btn btn-primary" href="<?= e(url('catalog')) ?>">Ver catálogo</a>
        </div>
        <div class="panel">
            <h2>Consulta</h2>
            <?php if (!empty($exito)): ?><div class="alert alert-success"><?= e($exito) ?></div><?php endif; ?>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <form method="post" action="<?= e(url('contact')) ?>">
                <?= csrf_field() ?>
                <div class="form-group"><label for="contact-name">Nombre</label><input id="contact-name" name="name" class="input" required maxlength="120"></div>
                <div class="form-group"><label for="contact-email">Correo o telefono</label><input id="contact-email" name="contact" class="input" required maxlength="160"></div>
                <div class="form-group"><label for="contact-message">Mensaje</label><textarea id="contact-message" name="message" rows="5" required maxlength="2000"></textarea></div>
                <button type="submit" class="btn btn-primary">Enviar consulta</button>
            </form>
        </div>
    </div>
</section>
