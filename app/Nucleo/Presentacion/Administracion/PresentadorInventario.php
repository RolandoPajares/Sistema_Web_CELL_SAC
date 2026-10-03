<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Administracion;

/** Prepara existencias y movimientos para las tablas administrativas. */
final class PresentadorInventario
{
    /**
     * Prepara datos de presentación sin alterar los registros del inventario.
     *
     * @param array<int, array<string, mixed>> $existencias
     * @param array<int, array<string, mixed>> $movimientos
     * @param array<string, mixed> $resumen
     * @return array<string, mixed>
     */
    public static function presentar(array $existencias, array $movimientos, array $resumen): array
    {
        $fechaHoyVista = date('d/m/Y');
        $categoriasInventario = array_values(array_unique(array_map(
            static fn (array $producto): string => (string) $producto['categoria'],
            $existencias
        )));
        sort($categoriasInventario, SORT_NATURAL | SORT_FLAG_CASE);

        $existenciasInventario = [];
        foreach ($existencias as $producto) {
            $cantidad = (int) $producto['existencias'];
            $estadoInventario = $cantidad === 0 ? 'out' : ($cantidad <= 8 ? 'low' : 'available');
            $etiquetaInventario = $estadoInventario === 'out'
                ? 'Sin stock'
                : ($estadoInventario === 'low' ? 'Stock bajo' : 'En stock');
            $producto['cantidad_inventario_vista'] = $cantidad;
            $producto['estado_inventario_vista'] = $estadoInventario;
            $producto['etiqueta_inventario_vista'] = $etiquetaInventario;
            $producto['imagen_inventario_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_inventario_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_inventario_vista'] === '' ? '' : 'hidden';
            $producto['icono_inventario_vista'] = icono_categoria_producto((string) $producto['categoria']);
            $producto['clase_inventario_vista'] = $estadoInventario === 'out'
                ? 'admin-status--danger'
                : ($estadoInventario === 'low' ? 'admin-status--warning' : '');
            $producto['ultimo_movimiento_vista'] = $producto['ultimo_movimiento']
                ? date('d/m/Y H:i', strtotime((string) $producto['ultimo_movimiento']))
                : '—';
            $existenciasInventario[] = $producto;
        }

        $movimientos = array_map(static function (array $movimiento): array {
            $movimiento['fecha_timestamp_vista'] = (int) strtotime((string) $movimiento['fecha']);
            $movimiento['fecha_vista'] = date('d/m/Y H:i', strtotime((string) $movimiento['fecha']));
            $movimiento['etiqueta_tipo_vista'] = ucfirst((string) $movimiento['tipo_movimiento']);
            $movimiento['clase_tipo_vista'] = $movimiento['tipo_movimiento'] === 'salida'
                ? 'admin-status--danger'
                : ($movimiento['tipo_movimiento'] === 'ajuste' ? 'admin-status--info' : '');

            return $movimiento;
        }, $movimientos);

        $tarjetasKpi = [
            [
                'etiqueta' => 'Stock total',
                'valor' => (string) $resumen['stock_total'],
                'detalle' => 'Unidades en productos activos',
                'icono' => 'bi-box-seam',
                'tono' => 'violeta',
            ],
            [
                'etiqueta' => 'Stock bajo',
                'valor' => (string) $resumen['stock_bajo'],
                'detalle' => 'Entre 1 y 8 unidades',
                'icono' => 'bi-exclamation-triangle',
                'tono' => 'rojo',
            ],
            [
                'etiqueta' => 'Sin stock',
                'valor' => (string) $resumen['sin_stock'],
                'detalle' => 'Productos con 0 unidades',
                'icono' => 'bi-box-seam-fill',
                'tono' => 'rojo',
            ],
            [
                'etiqueta' => 'Movimientos del día',
                'valor' => (string) $resumen['movimientos_hoy'],
                'detalle' => 'Entradas, salidas y ajustes',
                'icono' => 'bi-arrow-repeat',
                'tono' => 'azul',
            ],
        ];

        return [
            'fechaHoyVista' => $fechaHoyVista,
            'categoriasInventario' => $categoriasInventario,
            'existenciasInventario' => $existenciasInventario,
            'movimientos' => $movimientos,
            'tarjetasKpi' => $tarjetasKpi,
        ];
    }
}
