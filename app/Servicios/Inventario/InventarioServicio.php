<?php

declare(strict_types=1);

namespace App\Servicios\Inventario;

use App\DAO\Contratos\RepositorioInventarioInterfaz;

final class InventarioServicio
{
    public function __construct(private RepositorioInventarioInterfaz $inventario)
    {
    }

    /**
     * Obtiene la cantidad disponible para el producto o variante solicitada.
     */
    public function existencias(): array
    {
        return $this->inventario->existencias();
    }

    /**
     * Devuelve los movimientos de inventario registrados para el periodo solicitado.
     */
    public function movimientos(): array
    {
        return $this->inventario->movimientos();
    }

    /**
     * Calcula un resumen consolidado de la información solicitada.
     *
     * @return array{stock_total:int,stock_bajo:int,sin_stock:int,movimientos_hoy:int}
     */
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

    public function registrar(array $datos, int $idUsuario): int
    {
        if ($idUsuario <= 0) {
            throw new \DomainException('No se pudo identificar al usuario responsable.');
        }

        $idProducto = filter_var($datos['producto_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $cantidad = filter_var($datos['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $tipo = $datos['tipo_movimiento'] ?? null;
        $notas = $datos['notas'] ?? null;
        if ($idProducto === false || $cantidad === false
            || !is_string($tipo) || !in_array($tipo, ['entrada', 'salida', 'ajuste'], true)
            || !is_string($notas) || trim($notas) === '' || mb_strlen(trim($notas)) > 500
        ) {
            throw new \DomainException('Los datos del movimiento de inventario no son válidos.');
        }

        $movimiento = [
            'producto_id' => (int) $idProducto,
            'tipo_movimiento' => $tipo,
            'cantidad' => (int) $cantidad,
            'notas' => trim($notas),
        ];

        return $this->inventario->registrarMovimiento($movimiento, $idUsuario);
    }
}
