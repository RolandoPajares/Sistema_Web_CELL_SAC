<div class="auth-wrap panel">
    <h1>Crear cuenta</h1>
    <p>El registro publico crea una cuenta de cliente. El acceso administrativo solo se concede a usuarios con rol administrador.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($exito): ?>
        <div class="alert alert-success"><?= e($exito) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('register')) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Nombre completo</label>
            <input class="input" name="name" maxlength="120" required>
        </div>
        <div class="form-group">
            <label>Correo</label>
            <input class="input" type="email" name="email" maxlength="160" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input class="input" type="password" name="password" minlength="8" maxlength="4096" required>
        </div>
        <button class="btn btn-primary" style="width:100%">Registrarme</button>
    </form>
</div>
