<?php

declare(strict_types=1);

namespace App\Servicios\Pedidos;

use App\DAO\Contratos\RepositorioPedidoInterfaz;

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

    public function buscarConDetalle(int $idPedido): ?array
    {
        return $this->pedidos->buscarConDetalle($idPedido);
    }

    public function actualizarEstado(int $idPedido, string $estado): void
    {
        if ($idPedido <= 0) {
            throw new \DomainException('El identificador del pedido no es válido.');
        }
        $this->pedidos->actualizarEstado($idPedido, $estado);
    }

    public function contarPorEstado(): array
    {
        return $this->pedidos->contarPorEstado();
    }

    public function contarVentasRegistradas(): int
    {
        return $this->pedidos->contarVentasRegistradas();
    }

    public function totalVentasPeriodo(): float
    {
        return $this->pedidos->totalVentasPeriodo();
    }
}
