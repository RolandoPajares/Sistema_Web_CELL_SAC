<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;

final class AutenticacionMiddleware
{
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        if (!current_user()) {
            return redirect('login');
        }

        return $siguiente($solicitud);
    }
}
