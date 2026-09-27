<?php

declare(strict_types=1);

namespace App\Servicios;

final class PanelAdministradorServicio
{
    public function __construct(
        private ProductoServicio $productos,
        private PedidoServicio $pedidos,
        private UsuarioServicio $usuarios,
    ) {
    }

    /** @return array{productos:int,existencias:int,pedidos:int,usuarios:int} */
    public function estadisticas(): array
    {
        return [
            'productos' => $this->productos->contarActivos(),
            'existencias' => $this->productos->stockTotal(),
            'pedidos' => $this->pedidos->contar(),
            'usuarios' => $this->usuarios->contar(),
        ];
    }
}
