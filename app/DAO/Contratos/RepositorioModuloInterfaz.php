<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioModuloInterfaz
{
    /** @return array<int, array<string, mixed>> */
    public function listar(string $modulo): array;

    /** @param array<string, mixed> $datos */
    public function crear(string $modulo, array $datos, int $idUsuario): int;

    /** @param array<string, mixed> $datos */
    public function actualizar(string $modulo, int $idRegistro, array $datos): void;

    public function desactivar(string $modulo, int $idRegistro): void;

    /** @return array<string, int|float> */
    public function resumen(): array;

    /** @return array<int, array{id:int,etiqueta:string}> */
    public function opciones(string $tipo): array;
}
