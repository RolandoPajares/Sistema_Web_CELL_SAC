<?php

/**
 * @var array<array-key, mixed> $interfaz
 */ ?><header class="cuenta-titulo">
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
        <p>Selecciona una opción del menú para continuar con este recorrido.</p><a class="btn btn-primary" href="<?= e(url_interna('panel')) ?>">Volver al portal</a>
    </div>
</section>