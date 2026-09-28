<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Soporte\Seguridad\GestorTokenCsrf;

final class CsrfMiddleware
{
    public function __construct(private GestorTokenCsrf $csrf)
    {
    }

    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        if (!$this->csrf->validar((string) $solicitud->entrada('csrf', ''))) {
            return new Respuesta('Solicitud inválida.', 419);
        }

        return $siguiente($solicitud);
    }
}
