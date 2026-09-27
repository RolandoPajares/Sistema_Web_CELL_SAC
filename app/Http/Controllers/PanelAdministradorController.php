<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\PanelAdministradorServicio;
use App\Servicios\InteligenciaNegocioServicio;

final class PanelAdministradorController
{
    public function __construct(private Vista $vista, private PanelAdministradorServicio $panel, private InteligenciaNegocioServicio $inteligencia)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('administrador.tablero', [
            'tituloPagina' => 'Panel administrativo',
            'estadisticas' => $this->panel->estadisticas(),
            'inteligencia' => $this->inteligencia->resumenActual(),
        ], 'administrador');
    }
}
