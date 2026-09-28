<?php

declare(strict_types=1);

namespace App\Controladores\Panel;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Panel\PanelAdministradorServicio;
use App\Servicios\ComercioInteligente\InteligenciaNegocioServicio;

final class PanelAdministradorController
{
    public function __construct(private Vista $vista, private PanelAdministradorServicio $panel, private InteligenciaNegocioServicio $inteligencia)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.tablero', [
            'tituloPagina' => 'Panel administrativo',
            'estadisticas' => $this->panel->estadisticas(),
            'inteligencia' => $this->inteligencia->resumenActual(),
        ], 'interno');
    }
}
