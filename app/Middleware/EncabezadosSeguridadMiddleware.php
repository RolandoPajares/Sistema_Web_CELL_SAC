<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;

final class EncabezadosSeguridadMiddleware
{
    public function manejar(Solicitud $solicitud, callable $siguiente): Respuesta
    {
        $respuesta = $siguiente($solicitud);
        $respuesta->conEncabezado('X-Content-Type-Options', 'nosniff');
        $respuesta->conEncabezado('Referrer-Policy', 'strict-origin-when-cross-origin');
        $respuesta->conEncabezado('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $respuesta->conEncabezado(
            'Content-Security-Policy',
            "default-src 'self'; img-src 'self' data: https://www.apple.com https://image-stgus.samsung.com https://images.samsung.com https://www.samsung.com https://i02.appmifile.com https://*.mi.com https://p4-ofp.static.pub https://commons.wikimedia.org https://upload.wikimedia.org https://content.abt.com https://images.tcdn.com.br https://www.spigen.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'"
        );

        if ($solicitud->esSegura()) {
            $respuesta->conEncabezado('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $respuesta;
    }
}
