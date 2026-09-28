<?php

declare(strict_types=1);

namespace App\Nucleo\Http;

final class RespuestaRedireccion extends Respuesta
{
    public function __construct(string $ubicacion, int $estado = 302)
    {
        parent::__construct('', $estado, ['Location' => $ubicacion]);
    }
}
