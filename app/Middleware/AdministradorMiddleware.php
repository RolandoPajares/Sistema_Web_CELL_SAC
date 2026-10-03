<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Soporte\Autorizacion\AccesoRol;

final class AdministradorMiddleware
{
    /**
     * Restringe el acceso al módulo a usuarios con rol de administrador.
     */
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        $usuario = usuario_actual();
        if (!$usuario) {
            return redirigir('login');
        }
        if (AccesoRol::normalizarRol((string) ($usuario['rol'] ?? '')) !== 'administrador') {
            return respuesta_http('No tienes autorización para acceder a este módulo.', 403);
        }

        return $siguiente($solicitud);
    }
}
