<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Soporte\Seguridad\LimitadorSolicitudes;

final class LimiteSolicitudesMiddleware
{
    public function __construct(private LimitadorSolicitudes $limitador)
    {
    }

    /**
     * Limita los intentos por ruta y dirección IP antes de continuar la solicitud.
     */
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        $clave = $solicitud->ruta() . '|' . $solicitud->direccionIp();

        if ($this->limitador->demasiadosIntentos($clave, 8, 900)) {
            return new Respuesta('Demasiados intentos. Espera unos minutos.', 429, ['Retry-After' => '900']);
        }

        $this->limitador->registrarIntento($clave, 900);

        return $siguiente($solicitud);
    }
}
