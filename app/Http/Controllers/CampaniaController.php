<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Servicios\CampaniaServicio;

final class CampaniaController
{
    public function __construct(private CampaniaServicio $campanias)
    {
    }

    public function registrarEvento(Solicitud $solicitud): Respuesta
    {
        $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $entradaEvento = $solicitud->entrada('event', 'view');
        $evento = is_scalar($entradaEvento) ? (string) $entradaEvento : '';
        if ($id === false || !in_array($evento, ['view', 'click'], true)) {
            return new Respuesta('{"ok":false}', 422, ['Content-Type' => 'application/json; charset=UTF-8']);
        }
        $this->campanias->registrarEvento((int) $id, $evento);

        return new Respuesta('{"ok":true}', 200, ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}
