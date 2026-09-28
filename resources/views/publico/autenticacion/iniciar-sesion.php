<div class="auth-wrap panel">
    <h1>Iniciar sesión</h1>
    <p>Los administradores son enviados automáticamente al panel de gestión.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('login')) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Correo</label>
            <input class="input" type="email" name="email" maxlength="160" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input class="input" type="password" name="password" maxlength="4096" required>
        </div>
        <button class="btn btn-primary" style="width:100%">Ingresar</button>
    </form>

    <p>No tienes cuenta? <a style="color: var(--primary)" href="<?= e(url('register')) ?>">Registrate aqui</a>.</p>
</div>
