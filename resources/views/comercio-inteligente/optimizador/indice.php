<?php
/**
 * Datos preparados por ComercioInteligenteController y PresentadorComercioInteligente.
 * @var string $presupuestoFormulario
 * @var array<int, array{valor:string,etiqueta:string,atributo_seleccionado:string}> $opcionesObjetivoVista
 * @var string $propuestaHtml
 
 */
?>
<section class="smart-page">
    <div class="container">
        <div class="smart-hero">
            <span class="eyebrow">Modo emprendedor</span>
            <h1><i class="bi bi-cash-coin" aria-hidden="true"></i> Optimizador por presupuesto</h1>
            <p>El sistema arma automáticamente una propuesta de compra respetando tu presupuesto y objetivo comercial.</p>
        </div>

        <form class="smart-form panel" method="get">
            <label>
                Presupuesto
                <input class="input" type="number" name="budget" min="500" step="100" value="<?= e($presupuestoFormulario) ?>" placeholder="Ej. 10000">
            </label>
            <label>
                Objetivo
                <select name="goal">
                <?php foreach ($opcionesObjetivoVista as $opcion): ?>
                    <option value="<?= e($opcion['valor']) ?>" <?= $opcion['atributo_seleccionado'] ?>><?= e($opcion['etiqueta']) ?></option>
                <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-primary">Generar propuesta</button>
        </form>

        <?= $propuestaHtml ?>
    </div>
</section>
