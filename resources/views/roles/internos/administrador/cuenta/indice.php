<?php
$nombreActual = trim((string) ($usuarioCuenta['nombre'] ?? ''));
$iniciales = '';
foreach (preg_split('/\s+/u', $nombreActual, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $parte) {
    $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
    if (mb_strlen($iniciales) >= 2) {
        break;
    }
}
$rolActual = ucfirst(str_replace('_', ' ', (string) ($usuarioCuenta['rol'] ?? '')));
$fechaRegistro = strtotime((string) ($usuarioCuenta['creado_en'] ?? ''));
?>
<div class="admin-account-page">
    <header class="admin-page-head admin-account-heading">
        <div class="admin-account-title-icon"><i class="bi bi-person-circle" aria-hidden="true"></i></div>
        <div><h1>Mi Cuenta</h1><p>Gestiona tu información personal y la seguridad de tu cuenta.</p></div>
    </header>

    <?php if ($error !== ''): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($exito !== ''): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>

    <div class="admin-account-grid">
        <section class="admin-panel admin-account-profile">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-person-badge"></i><div><h2>Información del perfil</h2><p>Datos principales de tu cuenta de administrador</p></div></div></header>
            <div class="admin-account-profile-body">
                <div class="admin-account-avatar" aria-label="Iniciales del usuario"><?= e($iniciales) ?></div>
                <div class="admin-account-profile-details">
                    <h3><?= e($nombreActual) ?></h3>
                    <?php if ($rolActual !== ''): ?><span class="admin-account-role"><?= e($rolActual) ?></span><?php endif; ?>
                    <p><i class="bi bi-envelope"></i><span><?= e((string) $usuarioCuenta['correo']) ?></span></p>
                    <?php if ($fechaRegistro !== false): ?><p><i class="bi bi-calendar3"></i><span>Miembro desde: <?= e(date('d/m/Y', $fechaRegistro)) ?></span></p><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="admin-panel admin-account-photo">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-image"></i><div><h2>Foto de perfil</h2><p>Personaliza la imagen de tu cuenta</p></div></div></header>
            <div class="admin-account-photo-placeholder"><i class="bi bi-cloud-arrow-up"></i><strong>Foto de perfil no disponible</strong><span>El sistema no almacena imágenes de usuario actualmente.</span><button class="admin-secondary-button" type="button" disabled aria-disabled="true">Función no disponible</button></div>
        </section>

        <section class="admin-panel admin-account-edit">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-pencil-square"></i><div><h2>Editar información personal</h2><p>Actualiza el nombre y correo asociados a tu sesión</p></div></div></header>
            <form class="admin-account-form" method="post" action="<?= e(url('admin/account/profile')) ?>"><?= csrf_field() ?>
                <div class="admin-account-fields">
                    <label class="admin-account-field"><span>Nombre completo <b>*</b></span><input type="text" name="nombre" value="<?= e($nombreActual) ?>" maxlength="120" autocomplete="name" required></label>
                    <label class="admin-account-field"><span>Correo electrónico <b>*</b></span><input type="email" name="correo" value="<?= e((string) $usuarioCuenta['correo']) ?>" maxlength="160" autocomplete="email" required></label>
                </div>
                <div class="admin-form-actions"><button class="admin-primary-button" type="submit"><i class="bi bi-floppy"></i> Guardar cambios</button></div>
            </form>
        </section>

        <section class="admin-panel admin-account-password">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-shield-lock"></i><div><h2>Cambiar contraseña</h2><p>Confirma tu contraseña actual para continuar</p></div></div></header>
            <form class="admin-account-form" method="post" action="<?= e(url('admin/account/password')) ?>"><?= csrf_field() ?>
                <label class="admin-account-field"><span>Contraseña actual <b>*</b></span><span class="admin-password-input"><i class="bi bi-lock"></i><input type="password" name="contrasena_actual" autocomplete="current-password" required><button type="button" data-password-toggle aria-label="Mostrar contraseña actual"><i class="bi bi-eye"></i></button></span></label>
                <label class="admin-account-field"><span>Nueva contraseña <b>*</b></span><span class="admin-password-input"><i class="bi bi-lock"></i><input type="password" name="contrasena_nueva" minlength="10" maxlength="255" autocomplete="new-password" required><button type="button" data-password-toggle aria-label="Mostrar nueva contraseña"><i class="bi bi-eye"></i></button></span><small>Debe tener al menos 10 caracteres.</small></label>
                <label class="admin-account-field"><span>Confirmar nueva contraseña <b>*</b></span><span class="admin-password-input"><i class="bi bi-lock"></i><input type="password" name="contrasena_confirmacion" minlength="10" maxlength="255" autocomplete="new-password" required><button type="button" data-password-toggle aria-label="Mostrar confirmación"><i class="bi bi-eye"></i></button></span></label>
                <div class="admin-form-actions"><button class="admin-primary-button" type="submit"><i class="bi bi-key"></i> Actualizar contraseña</button></div>
            </form>
        </section>
    </div>
</div>
