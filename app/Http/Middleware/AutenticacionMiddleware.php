<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;

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
