<?php
/**
 * @var array<int, array<string, mixed>> $productos
 * @var array<string, string> $etiquetasPuntajeInteligente
 * @var array<int, array{numero:int,opcional:bool,producto_seleccionado:int}> $ranurasComparador
 
 * @var string $atributoComparacionOculta
 */
?>
<section class="smart-page">
    <div class="container">
        <div class="smart-hero">
            <span class="eyebrow">MD SmartCommerce</span>
            <h1><i class="bi bi-columns-gap" aria-hidden="true"></i> Comparador inteligente</h1>
            <p>Selecciona hasta tres celulares y compara precio, disponibilidad y SmartScore por perfil.</p>
        </div>

        <form class="panel smart-form compare-picker" method="get" id="compareForm">
            <?php foreach ($ranurasComparador as $ranura): ?>
                <label>
                Equipo <?= $ranura['numero'] ?>
                <select class="compare-select">
                <option value="" <?= $ranura['atributosOpcionVacia'] ?>>-- Opcional --</option>
                <?php foreach ($ranura['opciones'] as $producto): ?>
                <option value="<?= (int) $producto['id'] ?>" <?= $producto['atributoSeleccionado'] ?>>
                <?= e($producto['marca'] . ' ' . $producto['nombre']) ?>
                </option>
                <?php endforeach; ?>
                </select>
                </label>
            <?php endforeach; ?>
            <input type="hidden" name="ids" id="compareIds">
            <button class="btn btn-primary">Comparar ahora</button>
        </form>

        <div class="comparison-grid" <?= $atributoComparacionOculta ?>>
                <?php foreach ($productos as $producto): ?>
            <article class="panel comparison-card">
                <img <?= $producto['atributoImagenOculta'] ?>
                class="comparison-product-photo"
                src="<?= e($producto['imagen_comparacion_vista']) ?>"
                alt="<?= e($producto['texto_alternativo_vista']) ?>"
                loading="lazy">
                <div class="comparison-product-photo comparison-product-placeholder" <?= $producto['atributoIconoOculto'] ?> aria-hidden="true">
                <i
                class="bi <?= e($producto['icono_comparacion_vista']) ?>"></i>
                </div>
                <span class="eyebrow"><?= e($producto['marca']) ?></span>
                <h2><?= e($producto['nombre']) ?></h2>
                <div class="price"><?= formatear_dinero($producto['precio']) ?></div>
                <p><?= e($producto['almacenamiento'] ?? 'Consultar') ?> · Stock <?= (int) $producto['existencias'] ?></p>
                <?php foreach ($producto['puntajes_inteligentes'] as $clave => $puntaje): ?>
                <div class="score-row">
                <span><?= e($etiquetasPuntajeInteligente[$clave]) ?></span>
                <b><?= (int) $puntaje ?>/100</b>
                    <div class="scorebar"><i style="width:<?= (int) $puntaje ?>%"></i></div>
                </div>
                <?php endforeach; ?>

                <a
                class="btn btn-ghost"
                href="<?= e($producto['enlace_detalle_vista']) ?>">Ver detalles
                </a>
            </article>
                <?php endforeach; ?>
        </div>
    </div>
</section>
