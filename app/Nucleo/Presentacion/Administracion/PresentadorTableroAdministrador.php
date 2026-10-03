<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Administracion;

/** Prepara métricas y filas visuales del tablero administrativo. */
final class PresentadorTableroAdministrador
{
    /**
     * Prepara datos de presentación usando los resultados de negocio recibidos.
     *
     * @param array<string, mixed> $estadisticas
     * @param array<int, array<string, mixed>> $ventasMensuales
     * @param array<int, array<string, mixed>> $pedidosRecientes
     * @param array<string, mixed> $inteligencia
     * @return array<string, mixed>
     */
    public static function presentar(
        array $estadisticas,
        array $ventasMensuales,
        array $pedidosRecientes,
        array $inteligencia
    ): array {
        $fechaHoyVista = date('d/m/Y');
        $tarjetasKpi = [
            ['etiqueta' => 'Ventas del mes', 'valor' => formatear_dinero($estadisticas['ventas_periodo']), 'detalle' => 'Pedidos no cancelados', 'icono' => 'bi-cart3', 'tono' => 'azul'],
            ['etiqueta' => 'Pedidos', 'valor' => (string) $estadisticas['pedidos'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-file-earmark-text', 'tono' => 'verde'],
            ['etiqueta' => 'Productos activos', 'valor' => (string) $estadisticas['productos'], 'detalle' => $estadisticas['productos_total'] . ' productos totales', 'icono' => 'bi-box-seam', 'tono' => 'violeta'],
            ['etiqueta' => 'Stock bajo', 'valor' => (string) $estadisticas['stock_bajo'], 'detalle' => 'Productos activos con 8 unidades o menos', 'icono' => 'bi-exclamation-triangle', 'tono' => 'rojo'],
        ];
        $maximoVentas = max(array_merge([1.0], array_map(
            static fn (array $fila): float => (float) $fila['total'],
            $ventasMensuales
        )));
        $ventasMensuales = array_map(static function (array $fila) use ($maximoVentas): array {
            $fila['altura_vista'] = max(2, (int) round(((float) $fila['total'] / $maximoVentas) * 100));
            $fila['periodo_etiqueta_vista'] = substr((string) $fila['periodo'], 5, 2)
                . '/'
                . substr((string) $fila['periodo'], 2, 2);

            return $fila;
        }, $ventasMensuales);
        $pedidosRecientes = array_map(static function (array $pedido): array {
            $pedido['clase_estado_vista'] = PresentadorPedidosAdministrador::claseEstado((string) $pedido['estado']);
            $pedido['clase_estado_tablero_vista'] = $pedido['estado'] === 'Cancelado'
                ? 'admin-status--danger'
                : ($pedido['estado'] === 'Pendiente' ? 'admin-status--warning' : 'admin-status--info');
            $pedido['codigo_pedido_vista'] = str_pad((string) $pedido['id'], 6, '0', STR_PAD_LEFT);
            $pedido['fecha_creacion_vista'] = date('d/m/Y', strtotime((string) $pedido['creado_en']));

            return $pedido;
        }, $pedidosRecientes);
        $inteligencia['existencias_bajas'] = array_map(static function (array $producto): array {
            $producto['imagen_tablero_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['icono_tablero_vista'] = icono_categoria_producto((string) ($producto['categoria'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_tablero_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_tablero_vista'] === '' ? '' : 'hidden';
            $producto['clase_stock_bajo_vista'] = (int) $producto['existencias'] <= 3
                ? 'admin-status admin-status--danger'
                : 'admin-status admin-status--warning';

            return $producto;
        }, $inteligencia['existencias_bajas']);

        return [
            'fechaHoyVista' => $fechaHoyVista,
            'tarjetasKpi' => $tarjetasKpi,
            'maximoVentas' => $maximoVentas,
            'ventasMensuales' => $ventasMensuales,
            'atributoVentasVaciasOculto' => $ventasMensuales === [] ? '' : 'hidden',
            'atributoGraficoVentasOculto' => $ventasMensuales === [] ? 'hidden' : '',
            'pedidosRecientes' => $pedidosRecientes,
            'atributoPedidosRecientesVaciosOculto' => $pedidosRecientes === [] ? '' : 'hidden',
            'inteligencia' => $inteligencia,
            'atributoExistenciasBajasVaciasOculto' => $inteligencia['existencias_bajas'] === [] ? '' : 'hidden',
            'atributoExistenciasBajasListaOculto' => $inteligencia['existencias_bajas'] === [] ? 'hidden' : '',
        ];
    }
}
