<?php
/**
 * @var string $etiquetaRol
 * @var array<array-key, mixed> $interfaz
 * @var string $fechaHoyEtiqueta
 * @var string $atributoAccionNuevoOculta
 * @var string $atributoAccionGestionOculta
 * @var string $atributoExitoOculto
 * @var mixed $exito
 * @var string $atributoErrorOculto
 * @var mixed $error
 * @var mixed $soloActualizar
 * @var string $atributoCrudOculto
 * @var string $atributoVistaModuloOculta
 * @var string $tipoInterfazVista
 */ ?><header class="admin-page-head">
    <div>
        <span class="eyebrow"><?= e($etiquetaRol) ?></span>
        <h1><?= e($interfaz['titulo']) ?></h1>
        <p><?= e($interfaz['descripcion']) ?></p>
    </div>
    <div class="acciones-cabecera">
        <button class="boton-fecha" type="button"><i class="bi bi-calendar3"></i> Hoy, <?= e($fechaHoyEtiqueta) ?> <i class="bi bi-chevron-down"></i></button>
        <a class="btn btn-primary" href="#editor" <?= $atributoAccionNuevoOculta ?>><i class="bi bi-plus-lg"></i> Nuevo registro</a>
        <button class="btn btn-primary" type="button" <?= $atributoAccionGestionOculta ?>><i class="bi bi-plus-lg"></i> Nueva gestión</button>
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
            <span><?= e($metrica['etiqueta']) ?></span><strong><?= e($metrica['valor']) ?></strong><small><b>↑ <?= $metrica['variacion_vista'] ?>%</b> vs. periodo anterior</small>
        </div>
        <svg class="mini-tendencia" viewBox="0 0 90 34"><polyline points="2,30 18,23 32,26 49,14 64,18 88,3"/></svg>
    </article>
    <?php endforeach; ?>
</div>

<div class="module-workspace <?= $soloActualizar ? 'single' : '' ?>" <?= $atributoCrudOculto ?>>
    <?php require __DIR__ . '/_parciales/listado-crud.php'; ?>
    <?php require __DIR__ . '/_parciales/editor.php'; ?>
</div>
<div <?= $atributoVistaModuloOculta ?>>
    <?php require __DIR__ . '/_parciales/tipos/' . $tipoInterfazVista . '.php'; ?>
</div>
