<?php

declare(strict_types=1);

namespace App\Servicios\Pedidos;

use App\DAO\Contratos\RepositorioPedidoInterfaz;

final class PedidoServicio
{
    public function __construct(private RepositorioPedidoInterfaz $pedidos)
    {
    }

    /**
     * Devuelve la lista de registros relacionados con «con usuarios».
     * @return array<int, array<string, mixed>>
     */
    public function todosConUsuarios(): array
    {
        return $this->pedidos->todosConUsuarios();
    }

    /**
     * Cuenta los elementos que cumplen las condiciones recibidas.
     */
    public function contar(): int
    {
        return $this->pedidos->contar();
    }

    /**
     * Obtiene el pedido por su identificador e incluye sus líneas de detalle.
     */
    public function buscarConDetalle(int $idPedido): ?array
    {
        return $this->pedidos->buscarConDetalle($idPedido);
    }

    /**
     * Actualiza la información relacionada con «estado».
     */
    public function actualizarEstado(int $idPedido, string $estado): void
    {
        if ($idPedido <= 0) {
            throw new \DomainException('El identificador del pedido no es válido.');
        }
        $this->pedidos->actualizarEstado($idPedido, $estado);
    }

    /** Actualiza un pedido comprobando primero el segmento de cuenta del módulo. */
    public function actualizarEstadoParaRolCliente(int $idPedido, string $estado, string $rolCliente): void
    {
        if ($idPedido <= 0) {
            throw new \DomainException('El identificador del pedido no es válido.');
        }
        if (!in_array($rolCliente, ['cliente_minorista', 'cliente_mayorista'], true)) {
            throw new \DomainException('El segmento de pedidos no es válido.');
        }

        $this->pedidos->actualizarEstadoParaRolCliente($idPedido, $estado, $rolCliente);
    }

    /**
     * Cuenta los elementos relacionados con «por estado».
     */
    public function contarPorEstado(): array
    {
        return $this->pedidos->contarPorEstado();
    }

    /**
     * Cuenta los elementos relacionados con «ventas registradas».
     */
    public function contarVentasRegistradas(): int
    {
        return $this->pedidos->contarVentasRegistradas();
    }

    /**
     * Calcula las ventas y pedidos acumulados durante el periodo indicado.
     */
    public function totalVentasPeriodo(): float
    {
        return $this->pedidos->totalVentasPeriodo();
    }
}
