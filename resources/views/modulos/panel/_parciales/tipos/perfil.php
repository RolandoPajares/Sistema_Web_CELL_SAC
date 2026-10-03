<?php
/**
 * @var array<array-key, mixed> $datosDemostracion
 */ ?><div class="perfil-rejilla">
    <aside class="panel tarjeta-perfil">
        <span class="avatar-grande"><?= e($datosDemostracion['perfil']['iniciales']) ?></span>
        <h2>
            <?= e($datosDemostracion['perfil']['nombre']) ?>
        </h2>
        <p>
            <?= e($datosDemostracion['perfil']['tipo_cliente']) ?>
        </p>
        <span class="estado estado--verde"><?= e($datosDemostracion['perfil']['estado']) ?></span>
        <hr>
        <a class="activo" href="<?= e(url_interna('panel/perfil')) ?>"><i class="bi bi-person"></i> Datos personales
        </a>
        <a href="<?= e(url_interna('panel/direcciones')) ?>"><i class="bi bi-geo-alt"></i> Direcciones
        </a>
        <a href="<?= e(url_interna('panel/perfil#metodos-pago')) ?>"><i class="bi bi-credit-card"></i> Métodos de pago
        </a>
        <a href="<?= e(url_interna('panel/perfil#notificaciones')) ?>"><i class="bi bi-bell"></i> Notificaciones
        </a>
    </aside>
    <section class="panel formulario-perfil">
        <div class="titulo-panel">
            <div>
                <i class="bi bi-person-lines-fill"></i>
                <h2>
                Datos personales
                </h2>
                <small>Mantén actualizada tu información de contacto.</small>
            </div>
            <a class="btn btn-primary" href="<?= e(url_interna('panel/perfil')) ?>">Editar información
            </a>
        </div>
        <div class="campos-perfil">
            <?php foreach ($datosDemostracion['perfil']['datos'] as $dato): ?>
                <label><span><?= e($dato['etiqueta']) ?></span><input value="<?= e($dato['valor']) ?>" readonly></label>
            <?php endforeach; ?>
        </div>
        <div class="seguridad-perfil">
                <i class="bi bi-shield-lock"></i>
            <div>
                <b>Seguridad de la cuenta</b>
                <p>
                Tu contraseña fue actualizada hace <?= $datosDemostracion['perfil']['dias_desde_actualizacion'] ?> días.
                </p>
            </div>
            <a class="btn btn-outline" href="<?= e(url_interna('panel/perfil#seguridad')) ?>">Cambiar contraseña
            </a>
        </div>
    </section>
</div>
