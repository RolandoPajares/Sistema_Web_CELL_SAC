<?php

declare(strict_types=1);

namespace App\Controladores\Campanias;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Servicios\Campanias\CampaniaServicio;

/** Coordina el registro de vistas y clics de campañas. */
final class CampaniaController
{
    public function __construct(
        private CampaniaServicio $campanias
    ) {}

    /**
     * Registra una vista o un clic y responde en JSON con el resultado.
     */
    public function registrarEvento(Solicitud $solicitud): Respuesta
    {
        $idCampania = filter_var(
            $solicitud->parametroRuta('id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $entradaEvento = $solicitud->entrada('event', 'view');

        $evento = is_scalar($entradaEvento) ? (string) $entradaEvento : '';

        if ($idCampania === false || !in_array($evento, ['view', 'click'], true)) {
            return new Respuesta(
                '{"ok":false}',
                422,
                ['Content-Type' => 'application/json; charset=UTF-8']
            );
        }

        $this->campanias->registrarEvento((int) $idCampania, $evento);

        return new Respuesta(
            '{"ok":true}',
            200,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
