<?php
/**
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var string $atributoExitoOculto
 * @var mixed $exito
 */ ?><div class="auth-wrap panel">
    <h1>Crear cuenta</h1>
    <p>El registro publico crea una cuenta de cliente. El acceso administrativo solo se concede a usuarios con rol administrador.</p>

    <div class="alert alert-error" <?= $atributoErrorOculto ?>><?= e($error) ?></div>
    <div class="alert alert-success" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>

    <form method="post" action="<?= e(url_interna('register')) ?>">
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
