<?php

declare(strict_types=1);

namespace App\Repositorios\Contratos;

interface RepositorioPedidoInterfaz
{
    public function estaDisponible(): bool;

    public function crearPedidoPendiente(int $idUsuario, float $total): int;

    public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void;

    /** @return array<int, array<string, mixed>> */
    public function todosConUsuarios(): array;

    public function contar(): int;
}
