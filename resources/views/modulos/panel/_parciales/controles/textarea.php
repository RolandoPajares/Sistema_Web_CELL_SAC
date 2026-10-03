<?php

/**
 * @var string $nombre
 * @var array<array-key, mixed> $configuracion
 * @var string $atributoRequerido
 * @var mixed $valor
 */ ?><textarea name="<?= e($nombre) ?>" maxlength="<?= (int) ($configuracion['max'] ?? 500) ?>" <?= $atributoRequerido ?>><?= e($valor) ?></textarea>