<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Soporte\Autorizacion\AccesoRol;

final class AdministradorMiddleware
{
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        $usuario = current_user();
        if (!$usuario) {
            return redirect('login');
        }
        if (AccesoRol::normalize((string) ($usuario['rol'] ?? '')) !== 'administrador') {
            return response('No tienes autorización para acceder a este módulo.', 403);
        }

        return $siguiente($solicitud);
    }
}
