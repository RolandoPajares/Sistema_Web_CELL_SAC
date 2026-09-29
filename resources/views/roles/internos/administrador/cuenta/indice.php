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
<link rel="stylesheet" href="<?= e(asset('assets/css/roles/internos/administrador-cuenta.css?v=20260929-2')) ?>">
<div class="admin-account-page">
    <header class="admin-page-head admin-account-heading">
        <div class="admin-account-heading-main">
            <div class="admin-account-title-icon"><i class="bi bi-person-fill" aria-hidden="true"></i></div>
            <div><h1>Mi Cuenta</h1><p>Gestiona tu información personal y la configuración de tu cuenta.</p></div>
        </div>
        <nav class="admin-account-breadcrumb" aria-label="Migas de pan de Mi Cuenta">
            <a href="<?= e(url('admin')) ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page">Mi Cuenta</span>
        </nav>
    </header>

    <?php if ($error !== ''): ?><div class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($exito !== ''): ?><div class="admin-alert admin-alert--success" role="status"><?= e($exito) ?></div><?php endif; ?>

    <div class="admin-account-grid">
        <section class="admin-panel admin-account-profile">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-person-badge"></i><div><h2>Información del perfil</h2><p>Datos principales de tu cuenta de administrador</p></div></div></header>
            <div class="admin-account-profile-body">
                <div class="admin-account-avatar" role="img" aria-label="<?= e('Avatar de ' . $nombreActual) ?>">
                    <?php if ($iniciales !== ''): ?><span><?= e($iniciales) ?></span><?php else: ?><i class="bi bi-person-fill" aria-hidden="true"></i><?php endif; ?>
                    <span class="admin-account-avatar-camera" aria-hidden="true"><i class="bi bi-camera-fill"></i></span>
                </div>
                <div class="admin-account-profile-details">
                    <h3><?= e($nombreActual) ?></h3>
                    <?php if ($rolActual !== ''): ?><span class="admin-account-role"><?= e($rolActual) ?></span><?php endif; ?>
                    <p><i class="bi bi-envelope"></i><span><?= e((string) $usuarioCuenta['correo']) ?></span></p>
                    <?php if ($fechaRegistro !== false): ?><p><i class="bi bi-calendar3"></i><span>Miembro desde: <?= e(date('d/m/Y', $fechaRegistro)) ?></span></p><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="admin-panel admin-account-photo">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-image"></i><div><h2>Foto de perfil</h2><p>Imagen asociada a tu perfil</p></div></div></header>
            <div class="admin-account-photo-placeholder">
                <i class="bi bi-cloud-arrow-up-fill" aria-hidden="true"></i>
                <strong>Foto de perfil no disponible</strong>
                <span>Por ahora tu avatar utiliza las iniciales de tu nombre.</span>
                <button class="admin-secondary-button" type="button" disabled aria-disabled="true" aria-describedby="admin-account-photo-help"><i class="bi bi-image" aria-hidden="true"></i> Elegir foto</button>
                <small id="admin-account-photo-help">La carga de fotografías aún no está habilitada.</small>
            </div>
        </section>

        <section class="admin-panel admin-account-edit">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-pencil-square"></i><div><h2>Editar información personal</h2><p>Actualiza tus datos personales</p></div></div></header>
            <form class="admin-account-form" method="post" action="<?= e(url('admin/account/profile')) ?>"><?= csrf_field() ?>
                <div class="admin-account-fields">
                    <label class="admin-account-field"><span>Nombre completo <b>*</b></span><input type="text" name="nombre" value="<?= e($nombreActual) ?>" maxlength="120" autocomplete="name" required></label>
                    <label class="admin-account-field"><span>Correo electrónico <b>*</b></span><input type="email" name="correo" value="<?= e((string) $usuarioCuenta['correo']) ?>" maxlength="160" autocomplete="email" required></label>
                </div>
                <div class="admin-form-actions"><button class="admin-primary-button" type="submit"><i class="bi bi-floppy"></i> Guardar cambios</button></div>
            </form>
        </section>

        <section class="admin-panel admin-account-password">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-lock-fill"></i><div><h2>Cambiar contraseña</h2><p>Actualiza tu contraseña de acceso</p></div></div></header>
            <form class="admin-account-form" method="post" action="<?= e(url('admin/account/password')) ?>"><?= csrf_field() ?>
                <label class="admin-account-field"><span>Contraseña actual <b>*</b></span><span class="admin-password-input"><i class="bi bi-lock-fill" aria-hidden="true"></i><input type="password" name="contrasena_actual" autocomplete="current-password" placeholder="Ingresa tu contraseña actual" required><button type="button" data-password-toggle aria-label="Mostrar contraseña actual"><i class="bi bi-eye" aria-hidden="true"></i></button></span></label>
                <label class="admin-account-field"><span>Nueva contraseña <b>*</b></span><span class="admin-password-input"><i class="bi bi-lock-fill" aria-hidden="true"></i><input type="password" name="contrasena_nueva" minlength="10" maxlength="255" autocomplete="new-password" placeholder="Ingresa tu nueva contraseña" aria-describedby="admin-account-password-help" required><button type="button" data-password-toggle aria-label="Mostrar nueva contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button></span><small id="admin-account-password-help">Debe tener al menos 10 caracteres.</small></label>
                <label class="admin-account-field"><span>Confirmar nueva contraseña <b>*</b></span><span class="admin-password-input"><i class="bi bi-lock-fill" aria-hidden="true"></i><input type="password" name="contrasena_confirmacion" minlength="10" maxlength="255" autocomplete="new-password" placeholder="Confirma tu nueva contraseña" required><button type="button" data-password-toggle aria-label="Mostrar confirmación"><i class="bi bi-eye" aria-hidden="true"></i></button></span></label>
                <div class="admin-form-actions"><button class="admin-primary-button" type="submit"><i class="bi bi-key"></i> Actualizar contraseña</button></div>
            </form>
        </section>
        <?php if ($rolActual !== '' || $fechaRegistro !== false): ?>
        <section class="admin-panel admin-account-summary">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-card-checklist" aria-hidden="true"></i><div><h2>Información de la cuenta</h2><p>Rol asignado y fecha de registro</p></div></div></header>
            <div class="admin-account-facts">
                <?php if ($rolActual !== ''): ?><article class="admin-account-fact"><span class="admin-account-fact-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><strong><?= e($rolActual) ?></strong><span>Rol de la cuenta</span></div></article><?php endif; ?>
                <?php if ($fechaRegistro !== false): ?><article class="admin-account-fact admin-account-fact--registration"><span class="admin-account-fact-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span><div><strong><?= e(date('d/m/Y', $fechaRegistro)) ?></strong><span>Fecha de registro</span></div></article><?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>
