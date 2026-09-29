<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioInventarioInterfaz
{
    /** @return array<int, array<string, mixed>> */
    public function existencias(): array;

    /** @return array<int, array<string, mixed>> */
    public function movimientos(): array;

    /** @param array{producto_id:int,tipo_movimiento:string,cantidad:int,notas:string} $datos */
    public function registrarMovimiento(array $datos, int $usuarioId): int;
}
