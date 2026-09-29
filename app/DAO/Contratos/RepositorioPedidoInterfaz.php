<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioPedidoInterfaz
{
    public function estaDisponible(): bool;

    public function crearPedidoPendiente(int $idUsuario, float $total): int;

    public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void;

    /** @return array<int, array<string, mixed>> */
    public function todosConUsuarios(): array;

    public function contar(): int;

    /** @return array<string, mixed>|null */
    public function buscarConDetalle(int $idPedido): ?array;

    public function actualizarEstado(int $idPedido, string $estado): void;

    /** @return array<string, int> */
    public function contarPorEstado(): array;

    public function contarVentasRegistradas(): int;

    public function totalVentasPeriodo(): float;
}
