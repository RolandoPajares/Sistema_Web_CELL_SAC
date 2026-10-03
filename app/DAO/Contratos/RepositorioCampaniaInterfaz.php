<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioCampaniaInterfaz
{
    public function estaDisponible(): bool;

    /** @return array<int, array<string, mixed>> */
    public function todosParaAdministrador(): array;

    /** @return array<string, mixed>|null */
    public function buscar(int $idCampania): ?array;

    /** @return array<int, array<string, mixed>> */
    public function ubicacionesActivas(): array;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): int;

    /** @param array<string, mixed> $datos */
    public function actualizar(int $idCampania, array $datos): void;

    public function desactivar(int $idCampania): void;

    public function registrarEvento(int $idCampania, string $evento): void;
}
