<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioClienteInterfaz
{
    /** @return array<int, array<string, mixed>> */
    public function todos(): array;

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array;

    public function existeDocumento(string $documento, ?int $idExcluido = null): bool;

    /** @param array<string, string> $datos */
    public function crear(array $datos): int;

    /** @param array<string, string> $datos */
    public function actualizar(int $id, array $datos): void;

    public function desactivar(int $id): void;
}
