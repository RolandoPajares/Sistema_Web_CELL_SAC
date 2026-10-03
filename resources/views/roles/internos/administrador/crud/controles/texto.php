<?php
/** @var array<string, mixed> $campo 
 */
?>
<input type="<?= e($campo['tipo']) ?>" name="<?= e($campo['nombre']) ?>" value="<?= e($campo['valor']) ?>" maxlength="<?= (int) $campo['maximo'] ?>" <?= e($campo['atributoRequerido']) ?>>
