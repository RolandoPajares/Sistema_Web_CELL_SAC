<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\ProductoServicio;

final class InicioController
{
    public function __construct(private Vista $vista, private ProductoServicio $productos)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('inicio.indice', [
            'tituloPagina' => 'Inicio',
            'productos' => $this->productos->destacados(7),
        ]);
    }
}
