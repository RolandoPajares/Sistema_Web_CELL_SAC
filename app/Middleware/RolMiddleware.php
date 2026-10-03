<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Soporte\Autorizacion\AccesoRol;

final class RolMiddleware
{
    /**
     * Comprueba los permisos del usuario para acceder al módulo solicitado.
     */
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        $usuario = usuario_actual();
        if (!$usuario) {
            return redirigir('login');
        }

        if (AccesoRol::normalizarRol((string) ($usuario['rol'] ?? '')) === 'administrador') {
            return redirigir('admin');
        }

        $modulo = (string) ($solicitud->parametroRuta('module') ?? 'dashboard');
        if (!AccesoRol::puedeAcceder((string) ($usuario['rol'] ?? ''), $modulo)) {
            return respuesta_http('No tienes autorización para acceder a este módulo.', 403);
        }

        return $siguiente($solicitud);
    }
}
