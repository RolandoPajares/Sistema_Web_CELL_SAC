<?php

declare(strict_types=1);

namespace App\Controladores\Publico;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Productos\ProductoServicio;

final class InicioController
{
    public function __construct(private Vista $vista, private ProductoServicio $productos)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('publico.inicio.indice', [
            'tituloPagina' => 'Inicio',
            'productos' => $this->productos->destacados(7),
        ]);
    }
}
