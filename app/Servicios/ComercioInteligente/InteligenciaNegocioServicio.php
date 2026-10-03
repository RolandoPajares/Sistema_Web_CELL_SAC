<?php

declare(strict_types=1);

namespace App\Servicios\ComercioInteligente;

use App\DAO\Contratos\RepositorioInteligenciaNegocioInterfaz;

final class InteligenciaNegocioServicio
{
    public function __construct(private RepositorioInteligenciaNegocioInterfaz $inteligencia)
    {
    }

    /**
     * Calcula un resumen de la actividad correspondiente al periodo actual.
     *
     * @return array<string,mixed>
     */
    public function resumenActual(): array
    {
        $resumen = $this->inteligencia->resumenActual();
        $datosDiarios = $resumen['diario'];
        $destacados = $resumen['destacados'];
        $promedio = $datosDiarios === [] ? 0.0 : array_sum(array_map(
            static fn (array $fila): float => (float) $fila['unidades'],
            $datosDiarios
        )) / count($datosDiarios);

        foreach ($resumen['existencias_bajas'] as &$producto) {
            $producto['dias_restantes'] = $promedio > 0
                ? max(1, (int) round((float) $producto['existencias'] / $promedio * max(1, count($destacados))))
                : null;
        }
        unset($producto);

        return $resumen + ['promedio_unidades_diarias' => $promedio];
    }

    /**
     * Agrupa las ventas por mes para generar el reporte solicitado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ventasMensuales(int $meses = 12): array
    {
        return $this->inteligencia->ventasMensuales($meses);
    }

    /**
     * Devuelve los pedidos más recientes para mostrarlos en el panel.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pedidosRecientes(int $limite = 6): array
    {
        return $this->inteligencia->pedidosRecientes($limite);
    }

    /**
     * Prepara el contenido del reporte solicitado.
     *
     * @return array{tipo:string,desde:string,hasta:string,indicadores:array<int,array<string,string>>,serie:array<int,array<string,mixed>>,detalle:array<int,array<string,mixed>>}
     */
    public function reporte(string $tipo, string $desde, string $hasta): array
    {
        $tipos = ['ventas', 'pedidos', 'inventario', 'productos', 'clientes'];
        $tipo = in_array($tipo, $tipos, true) ? $tipo : 'ventas';
        $fechaHasta = $this->fechaValida($hasta) ?? new \DateTimeImmutable('today');
        $fechaDesde = $this->fechaValida($desde) ?? $fechaHasta->modify('-29 days');
        if ($fechaDesde > $fechaHasta) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }
        if ($fechaDesde < $fechaHasta->modify('-1 year')) {
            $fechaDesde = $fechaHasta->modify('-1 year');
        }
        $desdeNormalizado = $fechaDesde->format('Y-m-d');
        $hastaNormalizado = $fechaHasta->format('Y-m-d');

        $detalle = $this->inteligencia->detalleReporte($tipo, $desdeNormalizado, $hastaNormalizado);
        $indicadores = $this->indicadoresReporte($tipo, $detalle, $desdeNormalizado, $hastaNormalizado);

        return [
            'tipo' => $tipo,
            'desde' => $desdeNormalizado,
            'hasta' => $hastaNormalizado,
            'indicadores' => $indicadores,
            'serie' => $tipo === 'ventas' ? $this->inteligencia->ventasDiarias($desdeNormalizado, $hastaNormalizado) : [],
            'detalle' => $detalle,
        ];
    }

    /**
     * Calcula los indicadores resumidos que acompañan al reporte.
     *
     * @param array<int, array<string, mixed>> $detalle
     * @return array<int, array<string, string>>
     */
    private function indicadoresReporte(string $tipo, array $detalle, string $desde, string $hasta): array
    {
        if ($tipo === 'ventas') {
            $resumen = $this->inteligencia->resumenPeriodo($desde, $hasta);

            return [
                ['etiqueta' => 'Total de ventas', 'valor' => formatear_dinero($resumen['ventas']), 'detalle' => 'Pedidos no cancelados del rango', 'icono' => 'bi-cart3', 'tono' => 'azul'],
                ['etiqueta' => 'Total de pedidos', 'valor' => (string) $resumen['pedidos'], 'detalle' => 'Pedidos no cancelados', 'icono' => 'bi-file-earmark-text', 'tono' => 'verde'],
                ['etiqueta' => 'Productos vendidos', 'valor' => (string) $resumen['productos_vendidos'], 'detalle' => 'Unidades del rango', 'icono' => 'bi-box-seam', 'tono' => 'violeta'],
                ['etiqueta' => 'Clientes atendidos', 'valor' => (string) $resumen['clientes'], 'detalle' => 'Usuarios distintos con pedido', 'icono' => 'bi-people', 'tono' => 'rojo'],
            ];
        }

        if ($tipo === 'pedidos') {
            $clientes = array_unique(array_map(static fn (array $fila): string => (string) $fila['cliente'], $detalle));

            return [
                ['etiqueta' => 'Pedidos', 'valor' => (string) count($detalle), 'detalle' => 'Pedidos del rango seleccionado', 'icono' => 'bi-file-earmark-text', 'tono' => 'azul'],
                ['etiqueta' => 'Monto registrado', 'valor' => formatear_dinero(array_sum(array_map(static fn (array $fila): float => (float) $fila['total'], $detalle))), 'detalle' => 'Incluye los estados registrados', 'icono' => 'bi-cash-stack', 'tono' => 'verde'],
                ['etiqueta' => 'Clientes', 'valor' => (string) count($clientes), 'detalle' => 'Clientes con pedido en el rango', 'icono' => 'bi-people', 'tono' => 'violeta'],
                ['etiqueta' => 'Pendientes', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => $fila['estado'] === 'Pendiente')), 'detalle' => 'Estado actual de los pedidos', 'icono' => 'bi-hourglass-split', 'tono' => 'rojo'],
            ];
        }

        if ($tipo === 'inventario') {
            return [
                ['etiqueta' => 'Productos listados', 'valor' => (string) count($detalle), 'detalle' => 'Existencias actuales', 'icono' => 'bi-box-seam', 'tono' => 'azul'],
                ['etiqueta' => 'Unidades en stock', 'valor' => (string) array_sum(array_map(static fn (array $fila): int => (int) $fila['stock'], $detalle)), 'detalle' => 'Suma de existencias actuales', 'icono' => 'bi-boxes', 'tono' => 'verde'],
                ['etiqueta' => 'Sin stock', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => (int) $fila['stock'] === 0)), 'detalle' => 'Productos con cero unidades', 'icono' => 'bi-exclamation-triangle', 'tono' => 'rojo'],
                ['etiqueta' => 'Activos', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => (int) $fila['estado'] === 1)), 'detalle' => 'Productos activos', 'icono' => 'bi-check-circle', 'tono' => 'violeta'],
            ];
        }

        if ($tipo === 'productos') {
            return [
                ['etiqueta' => 'Productos', 'valor' => (string) count($detalle), 'detalle' => 'Registros de catálogo', 'icono' => 'bi-box-seam', 'tono' => 'azul'],
                ['etiqueta' => 'Activos', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => (int) $fila['estado'] === 1)), 'detalle' => 'Disponibles en el catálogo', 'icono' => 'bi-check-circle', 'tono' => 'verde'],
                ['etiqueta' => 'Inactivos', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => (int) $fila['estado'] === 0)), 'detalle' => 'No visibles en el catálogo', 'icono' => 'bi-eye-slash', 'tono' => 'rojo'],
                ['etiqueta' => 'Unidades en stock', 'valor' => (string) array_sum(array_map(static fn (array $fila): int => (int) $fila['stock'], $detalle)), 'detalle' => 'Suma de existencias actuales', 'icono' => 'bi-boxes', 'tono' => 'violeta'],
            ];
        }

        return [
            ['etiqueta' => 'Clientes', 'valor' => (string) count($detalle), 'detalle' => 'Registros de clientes', 'icono' => 'bi-people', 'tono' => 'azul'],
            ['etiqueta' => 'Minoristas', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => $fila['tipo'] === 'minorista')), 'detalle' => 'Tipo registrado en MySQL', 'icono' => 'bi-person', 'tono' => 'verde'],
            ['etiqueta' => 'Mayoristas', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => $fila['tipo'] === 'mayorista')), 'detalle' => 'Tipo registrado en MySQL', 'icono' => 'bi-building', 'tono' => 'violeta'],
            ['etiqueta' => 'Activos', 'valor' => (string) count(array_filter($detalle, static fn (array $fila): bool => (int) $fila['estado'] === 1)), 'detalle' => 'Clientes habilitados', 'icono' => 'bi-check-circle', 'tono' => 'rojo'],
        ];
    }

    /**
     * Comprueba que la fecha recibida tenga un formato válido.
     */
    private function fechaValida(string $fecha): ?\DateTimeImmutable
    {
        $valor = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);

        return $valor && $valor->format('Y-m-d') === $fecha ? $valor : null;
    }
}
