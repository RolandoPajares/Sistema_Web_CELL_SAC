<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Soporte\Autorizacion\AccesoRol;

final class RolMiddleware
{
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        $usuario = current_user();
        if (!$usuario) {
            return redirect('login');
        }

        $modulo = (string) ($solicitud->parametroRuta('module') ?? 'dashboard');
        if (!AccesoRol::can((string) ($usuario['rol'] ?? ''), $modulo)) {
            return response('No tienes autorización para acceder a este módulo.', 403);
        }

        return $siguiente($solicitud);
    }
}
