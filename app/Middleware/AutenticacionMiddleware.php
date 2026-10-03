<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;

final class AutenticacionMiddleware
{
    /**
     * Exige una sesión autenticada antes de permitir el acceso a la ruta.
     */
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        if (!usuario_actual()) {
            return redirigir('login');
        }

        return $siguiente($solicitud);
    }
}
