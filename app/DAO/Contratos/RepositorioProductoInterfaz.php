<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

use App\DTO\Productos\FiltroProducto;

interface RepositorioProductoInterfaz
{
    public function estaDisponible(): bool;

    /** @return array<int, array<string, mixed>> */
    public function todosActivos(): array;

    /** @return array{productos:array<int,array<string,mixed>>,total:int,pagina:int,por_pagina:int,ultima_pagina:int} */
    public function paginar(FiltroProducto $filtro): array;

    /** @return array<int, array<string, mixed>> */
    public function todosParaAdministrador(): array;

    /** @return array<string, mixed>|null */
    public function buscarActivo(int $idProducto): ?array;

    /** @return array<string, mixed>|null */
    public function buscarParaAdministrador(int $idProducto): ?array;

    /** @return array<string, mixed>|null */
    public function buscarActivoParaActualizar(int $idProducto): ?array;

    public function existeConMarcaYNombre(string $marca, string $nombre, ?int $idExcluido = null): bool;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): int;

    /** @param array<string, mixed> $datos */
    public function actualizar(int $idProducto, array $datos): void;

    public function delete(int $idProducto): bool;

    public function desactivar(int $idProducto): void;

    public function reducirStock(int $idProducto, int $cantidad): void;

    public function contarActivos(): int;

    public function stockTotal(): int;
}
