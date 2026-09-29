<?php

declare(strict_types=1);

namespace App\Servicios\Inventario;

use App\DAO\Contratos\RepositorioInventarioInterfaz;

final class InventarioServicio
{
    public function __construct(private RepositorioInventarioInterfaz $inventario)
    {
    }

    public function existencias(): array
    {
        return $this->inventario->existencias();
    }

    public function movimientos(): array
    {
        return $this->inventario->movimientos();
    }

    /** @return array{stock_total:int,stock_bajo:int,sin_stock:int,movimientos_hoy:int} */
    public function resumen(): array
    {
        $existencias = $this->existencias();
        $hoy = date('Y-m-d');

        return [
            'stock_total' => array_sum(array_map(static fn (array $producto): int => (int) $producto['existencias'], $existencias)),
            'stock_bajo' => count(array_filter($existencias, static fn (array $producto): bool => (int) $producto['existencias'] > 0 && (int) $producto['existencias'] <= 8)),
            'sin_stock' => count(array_filter($existencias, static fn (array $producto): bool => (int) $producto['existencias'] === 0)),
            'movimientos_hoy' => count(array_filter($this->movimientos(), static fn (array $movimiento): bool => str_starts_with((string) $movimiento['fecha'], $hoy))),
        ];
    }

    public function registrar(array $datos, int $usuarioId): int
    {
        if ($usuarioId <= 0) {
            throw new \DomainException('No se pudo identificar al usuario responsable.');
        }

        return $this->inventario->registrarMovimiento($datos, $usuarioId);
    }
}
