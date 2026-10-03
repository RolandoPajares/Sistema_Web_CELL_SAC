<?php
/** @var array<string, mixed> $campo 
 */
?>
<textarea name="<?= e($campo['nombre']) ?>" maxlength="<?= (int) $campo['maximo'] ?>" rows="<?= (int) $campo['filas'] ?>" <?= e($campo['atributoRequerido']) ?>><?= e($campo['valor']) ?></textarea>
