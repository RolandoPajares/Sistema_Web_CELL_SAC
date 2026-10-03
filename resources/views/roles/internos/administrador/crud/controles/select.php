<?php
/** @var array<string, mixed> $campo 
 */
?>
<select name="<?= e($campo['nombre']) ?>" <?= e($campo['atributoRequerido']) ?>>
    <?php foreach ($campo['opciones'] as $opcion): ?>
    <option value="<?= e($opcion['valor']) ?>" <?= $opcion['atributoSeleccionada'] ?>><?= e($opcion['etiqueta']) ?></option>
    <?php endforeach; ?>
</select>
