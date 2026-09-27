<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Repositorios\Contratos\RepositorioPedidoInterfaz;

final class PedidoServicio
{
    public function __construct(private RepositorioPedidoInterfaz $pedidos)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function todosConUsuarios(): array
    {
        return $this->pedidos->todosConUsuarios();
    }

    public function contar(): int
    {
        return $this->pedidos->contar();
    }
}
