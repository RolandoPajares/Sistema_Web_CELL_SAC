<?php

declare(strict_types=1);

namespace App\Servicios\Panel;

use App\Servicios\ComercioInteligente\InteligenciaNegocioServicio;

final class AsistenteAdministradorServicio
{
    public function __construct(
        private PanelAdministradorServicio $panel,
        private InteligenciaNegocioServicio $inteligencia,
    ) {
    }

    /** @return array{mensaje:string} */
    public function responder(string $consulta): array
    {
        $texto = mb_strtolower(trim($consulta));
        $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
        $estadisticas = $this->panel->estadisticas();

        if (str_contains($texto, 'stock bajo')) {
            $productos = $this->inteligencia->resumenActual()['existencias_bajas'];
            if ($productos === []) {
                return ['mensaje' => 'No hay productos activos registrados para resumir el stock.'];
            }
            $detalle = implode(', ', array_map(
                static fn (array $producto): string => $producto['marca'] . ' ' . $producto['nombre'] . ' (' . (int) $producto['existencias'] . ')',
                $productos
            ));

            return ['mensaje' => 'Hay ' . (int) $estadisticas['stock_bajo'] . ' productos con stock bajo. Los primeros por existencias son: ' . $detalle . '.'];
        }

        if (str_contains($texto, 'pedido') && (str_contains($texto, 'pendiente') || str_contains($texto, 'resumen'))) {
            return ['mensaje' => 'El sistema registra ' . (int) $estadisticas['pedidos_pendientes'] . ' pedidos pendientes. Consulta el módulo Pedidos para revisar sus estados y detalles reales.'];
        }

        if (str_contains($texto, 'venta')) {
            return ['mensaje' => 'Las ventas no canceladas del mes disponible suman ' . money($estadisticas['ventas_periodo']) . '.'];
        }

        if (str_contains($texto, 'inventario')) {
            return ['mensaje' => 'El inventario activo suma ' . (int) $estadisticas['existencias'] . ' unidades y ' . (int) $estadisticas['stock_bajo'] . ' productos requieren atención por stock bajo.'];
        }

        if (str_contains($texto, 'producto')) {
            return ['mensaje' => 'Hay ' . (int) $estadisticas['productos'] . ' productos activos de ' . (int) $estadisticas['productos_total'] . ' productos registrados.'];
        }

        if (str_contains($texto, 'reporte')) {
            return ['mensaje' => 'Están disponibles reportes reales de ventas, pedidos, inventario, productos y clientes. La exportación PDF está prevista para una siguiente iteración.'];
        }

        if (str_contains($texto, 'resumen') || str_contains($texto, 'negocio') || str_contains($texto, 'hola')) {
            return ['mensaje' => 'Resumen actual: ' . (int) $estadisticas['productos'] . ' productos activos, ' . (int) $estadisticas['pedidos'] . ' pedidos, ' . (int) $estadisticas['clientes'] . ' clientes activos y ' . money($estadisticas['ventas_periodo']) . ' en ventas del mes disponible.'];
        }

        return ['mensaje' => 'Esta consulta todavía no está disponible. Puedo resumir negocio, ventas, productos, inventario, stock bajo, pedidos pendientes y reportes.'];
    }
}
