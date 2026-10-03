<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Soporte\Autorizacion\AccesoRol;

final class AccesoRutaRolMiddleware
{
    /**
     * Autoriza la solicitud según el rol del usuario y la ruta solicitada.
     */
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        $usuario = usuario_actual();
        if (!$usuario) {
            return redirigir('login');
        }

        $rol = AccesoRol::normalizarRol((string) ($usuario['rol'] ?? ''));
        $ruta = $solicitud->ruta();
        $permitidos = match (true) {
            $ruta === '/cart', $ruta === '/checkout' => ['cliente_minorista'],
            $ruta === '/mayorista' => ['cliente_mayorista'],
            $ruta === '/smart/optimizer' => ['cliente_mayorista', 'ventas_mayoristas'],
            $ruta === '/smart/ads' => ['marketing'],
            default => [],
        };

        if ($rol !== 'administrador' && !in_array($rol, $permitidos, true)) {
            return respuesta_http('No tienes autorización para acceder a esta ruta.', 403);
        }

        return $siguiente($solicitud);
    }
}
