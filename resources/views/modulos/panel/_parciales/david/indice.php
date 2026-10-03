<?php

/**
 * @var string $etiquetaRol
 * @var array<array-key, mixed> $interfaz
 * @var string $fechaHoyEtiqueta
 * @var string $fechaHoyIso
 * @var string $atributoAccionNuevoOculta
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var mixed $soloActualizar
 * @var string $atributoCrudOculto
 * @var string $atributoVistaModuloOculta
 */ ?><header class="admin-page-head">
    <div>
        <span class="eyebrow"><?= e($etiquetaRol) ?></span>
        <h1><?= e($interfaz['titulo']) ?></h1>
        <p><?= e($interfaz['descripcion']) ?></p>
    </div>
    <div class="acciones-cabecera">
        <label class="boton-fecha" title="Seleccionar fecha"><i class="bi bi-calendar3"></i><span data-module-date-label>Hoy, <?= e($fechaHoyEtiqueta) ?></span><i class="bi bi-chevron-down"></i><input class="dashboard-date-input" type="date" value="<?= e($fechaHoyIso) ?>" data-module-date></label>
        <a class="btn btn-primary" href="#editor" <?= $atributoAccionNuevoOculta ?>><i class="bi bi-plus-lg"></i> Nuevo registro</a>
    </div>
</header>

<div class="alert alert-success" role="status" <?= $atributoExitoOculto ?>><?= e($exito) ?></div>
<div class="alert alert-error" role="alert" <?= $atributoErrorOculto ?>><?= e($error) ?></div>

<div class="metricas-mockup module-kpis">
    <?php foreach ($interfaz['metricas'] as $metrica) : ?>

        <article class="metrica-mockup <?= e($metrica['color']) ?>">
            <div class="metrica-icono">
                <i class="bi <?= e($metrica['icono']) ?>"></i>
            </div>
            <div>
                <span><?= e($metrica['etiqueta']) ?></span><strong>—</strong><small>Indicador pendiente</small>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<div class="module-workspace <?= $soloActualizar ? 'single' : '' ?>" <?= $atributoCrudOculto ?>>
    <?php require dirname(__DIR__) . '/listado-crud.php'; ?>
    <?php require dirname(__DIR__) . '/editor.php'; ?>
</div>
<div <?= $atributoVistaModuloOculta ?>>
    <?php require __DIR__ . '/tabla.php'; ?>
</div>