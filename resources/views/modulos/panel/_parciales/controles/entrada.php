<?php

/**
 * @var string $tipo
 * @var string $nombre
 * @var mixed $valor
 * @var string $atributoRequerido
 * @var string $atributoMinimo
 * @var string $atributoMaximo
 */ ?>
 <input class="input" type="<?= e($tipo) ?>" name="<?= e($nombre) ?>" value="<?= e($valor) ?>" <?= $atributoRequerido ?> <?= $atributoMinimo ?> <?= $atributoMaximo ?>>