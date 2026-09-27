<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\PedidoServicio;

final class AdministradorPedidoController
{
    public function __construct(private Vista $vista, private PedidoServicio $pedidos)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('administrador.pedidos', [
            'tituloPagina' => 'Pedidos',
            'pedidos' => $this->pedidos->todosConUsuarios(),
        ], 'administrador');
    }
}
