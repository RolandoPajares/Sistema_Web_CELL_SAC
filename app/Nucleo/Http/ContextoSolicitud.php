<?php

declare(strict_types=1);

namespace App\Nucleo\Http;

/** Mantiene la solicitud activa para los componentes compartidos de la aplicación. */
final class ContextoSolicitud
{
    private ?Solicitud $solicitud = null;

    public function establecer(Solicitud $solicitud): void
    {
        $this->solicitud = $solicitud;
    }

    public function actual(): Solicitud
    {
        return $this->solicitud ?? new Solicitud('GET', '/', [], [], []);
    }
}
