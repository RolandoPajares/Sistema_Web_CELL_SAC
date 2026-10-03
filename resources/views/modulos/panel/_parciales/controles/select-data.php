<?php

/**
 * @var string $nombre
 * @var string $atributoRequerido
 * @var array<array-key, mixed> $opcionesVista
 */ ?><select name="<?= e($nombre) ?>" <?= $atributoRequerido ?>>
    <option value="">Selecciona una opción</option>
    <?php foreach ($opcionesVista as $opcion): ?>
        <option value="<?= e($opcion['valor']) ?>" <?= $opcion['atributoSeleccionada'] ?>><?= e($opcion['etiqueta']) ?></option>
    <?php endforeach; ?>
</select>