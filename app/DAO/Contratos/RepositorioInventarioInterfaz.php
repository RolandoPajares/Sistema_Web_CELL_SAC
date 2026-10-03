<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioInventarioInterfaz
{
    /**
     * Obtiene la cantidad disponible para el producto o variante solicitada.
     *
     * @return array<int, array<string, mixed>>
     */
    public function existencias(): array;

    /**
     * Devuelve los movimientos de inventario registrados para el periodo solicitado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function movimientos(): array;

    /**
     * Crea o guarda la información relacionada con «movimiento».
     * @param array{producto_id:int,tipo_movimiento:string,cantidad:int,notas:string} $datos
     */
    public function registrarMovimiento(array $datos, int $idUsuario): int;
}
