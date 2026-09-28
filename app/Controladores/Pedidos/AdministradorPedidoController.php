<?php

declare(strict_types=1);

namespace App\Controladores\Pedidos;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Pedidos\PedidoServicio;

final class AdministradorPedidoController
{
    public function __construct(private Vista $vista, private PedidoServicio $pedidos)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.pedidos.indice', [
            'tituloPagina' => 'Pedidos',
            'pedidos' => $this->pedidos->todosConUsuarios(),
        ], 'interno');
    }
}
