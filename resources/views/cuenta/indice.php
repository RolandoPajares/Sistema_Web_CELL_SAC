<section>
    <div class="container">
        <div class="section-head">
            <div>
                <h1>Hola, <?= e($usuario['nombre'] ?? '') ?></h1>
                <p>Tu espacio personal en MD Technology Digital Cell.</p>
            </div>
        </div>
        <div class="feature-grid">
            <div class="feature">
                <span><i class="bi bi-person-circle" aria-hidden="true"></i></span>
                <h3>Perfil</h3>
                <p><?= e($usuario['correo'] ?? '') ?></p>
            </div>
            <div class="feature">
                <span><i class="bi bi-cart3" aria-hidden="true"></i></span>
                <h3>Carrito</h3>
                <p>Tienes <?= cart_count() ?> articulo(s) en el carrito.</p>
            </div>
            <div class="feature">
                <span><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
                <h3>Rol</h3>
                <p><?= e($usuario['rol'] ?? '') ?></p>
            </div>
        </div>
        <?php if (is_admin()): ?>
            <p><a class="btn btn-primary" href="<?= e(url('admin')) ?>">Ir al panel administrativo</a></p>
        <?php endif; ?>
    </div>
</section>
