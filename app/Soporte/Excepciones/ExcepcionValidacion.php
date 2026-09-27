<?php

declare(strict_types=1);

namespace App\Soporte\Excepciones;

final class ExcepcionValidacion extends \RuntimeException
{
    /** @param array<string, string> $errores */
    public function __construct(private array $errores)
    {
        parent::__construct('Los datos proporcionados no son válidos.');
    }

    /** @return array<string, string> */
    public function errores(): array
    {
        return $this->errores;
    }
}
