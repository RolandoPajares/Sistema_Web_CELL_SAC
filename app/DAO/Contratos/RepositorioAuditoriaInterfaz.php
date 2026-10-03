<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioAuditoriaInterfaz
{
    /**
     * @param array<string, mixed>|null $valoresAnteriores
     * @param array<string, mixed>|null $valoresNuevos
     */
    public function registrar(
        ?int $idUsuario,
        string $accion,
        string $entidad,
        ?int $idEntidad,
        ?array $valoresAnteriores,
        ?array $valoresNuevos,
        string $direccionIp
    ): void;

    /** @return array<int, array<string, mixed>> */
    public function listar(string $desde, string $hasta, string $entidad = '', int $idUsuario = 0): array;

    /** @return array{hoy:int,total:int,usuarios:int,entidades:int} */
    public function resumen(): array;

    /** @return array<int, array{entidad:string,total:int}> */
    public function actividadPorEntidad(): array;
}
