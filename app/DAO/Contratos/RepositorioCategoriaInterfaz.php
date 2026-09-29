<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioCategoriaInterfaz
{
    /** @return array<int, array<string, mixed>> */
    public function todas(): array;

    /** @return array<int, array<string, mixed>> */
    public function activas(): array;

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array;

    /** @return array<string, mixed>|null */
    public function buscarActiva(int $id): ?array;

    /** @return array<string, mixed>|null */
    public function buscarPorNombre(string $nombre): ?array;

    public function existeNombre(string $nombre, ?int $idExcluido = null): bool;

    /** @param array{nombre:string,descripcion:string} $datos */
    public function crear(array $datos): int;

    /** @param array{nombre:string,descripcion:string} $datos */
    public function actualizar(int $id, array $datos): void;

    public function desactivar(int $id): void;
}
