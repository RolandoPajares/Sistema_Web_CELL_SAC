<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Administracion;

/** Prepara estados, fechas y datos visuales de pedidos administrativos. */
final class PresentadorPedidosAdministrador
{
    /** @param array<int, array<string, mixed>> $pedidos */
    public static function presentarLista(array $pedidos, array $conteos, int $pedidoSeleccionado = 0): array
    {
        $pedidos = array_map(static function (array $pedido) use ($pedidoSeleccionado): array {
            $pedido['clase_estado_vista'] = self::claseEstado((string) $pedido['estado']);
            $pedido['codigo_pedido_vista'] = str_pad((string) $pedido['id'], 6, '0', STR_PAD_LEFT);
            $pedido['fecha_creacion_timestamp_vista'] = (int) strtotime((string) $pedido['creado_en']);
            $pedido['fecha_creacion_vista'] = date('d/m/Y H:i', strtotime((string) $pedido['creado_en']));
            $pedido['clase_fila_vista'] = (int) $pedido['id'] === $pedidoSeleccionado ? 'admin-order-row--selected' : '';
            $pedido['atributoFilaSeleccionadaOculta'] = (int) $pedido['id'] === $pedidoSeleccionado ? '' : 'hidden';

            return $pedido;
        }, $pedidos);

        return [
            'pedidos' => $pedidos,
            'tarjetasKpi' => self::tarjetasKpi($conteos),
        ];
    }

    /** @param array<string, mixed> $detalle */
    public static function presentarDetalle(array $detalle): array
    {
        $detalle['id'] = (int) ($detalle['id'] ?? 0);
        $detalle['estado'] = (string) ($detalle['estado'] ?? '');
        $detalle['hay_detalle_vista'] = $detalle['id'] > 0;
        $detalle['clase_estado_vista'] = self::claseEstado($detalle['estado']);
        $detalle['codigo_pedido_vista'] = str_pad((string) $detalle['id'], 6, '0', STR_PAD_LEFT);
        $detalle['fecha_creacion_vista'] = isset($detalle['creado_en'])
            ? date('d/m/Y H:i', strtotime((string) $detalle['creado_en']))
            : '';
        $detalle['nombre'] = (string) ($detalle['nombre'] ?? '');
        $detalle['correo'] = (string) ($detalle['correo'] ?? '');
        $detalle['total'] = (float) ($detalle['total'] ?? 0);
        $detalle['titulo_detalle_vista'] = $detalle['hay_detalle_vista']
            ? '#' . $detalle['codigo_pedido_vista']
            : 'Selecciona un pedido';
        $detalle['url_actualizar_estado_vista'] = url_interna('admin/orders/' . $detalle['id'] . '/status');
        $detalle['detalle'] = array_map(static function (array $articulo): array {
            $articulo['imagen_pedido_vista'] = url_imagen_producto((string) ($articulo['url_imagen'] ?? ''));
            $articulo['icono_pedido_vista'] = icono_categoria_producto((string) ($articulo['categoria'] ?? ''));
            $articulo['atributoImagenOculta'] = $articulo['imagen_pedido_vista'] !== '' ? '' : 'hidden';
            $articulo['atributoIconoOculto'] = $articulo['imagen_pedido_vista'] === '' ? '' : 'hidden';

            return $articulo;
        }, (array) ($detalle['detalle'] ?? []));
        $detalle['estados_vista'] = array_map(static fn (string $estado): array => [
            'valor' => $estado,
            'atributoSeleccionada' => $detalle['estado'] === $estado ? 'selected' : '',
        ], \App\Validacion\Pedidos\SolicitudEstadoPedido::ESTADOS);

        return $detalle;
    }

    /** Devuelve la clase visual que ya se asigna a cada estado de pedido. */
    public static function claseEstado(string $estado): string
    {
        return match ($estado) {
            'Pendiente' => 'admin-status--warning',
            'En proceso', 'Enviado' => 'admin-status--info',
            'Entregado' => '',
            'Cancelado' => 'admin-status--danger',
            default => 'admin-status--muted',
        };
    }

    /** @param array<string, mixed> $conteos @return array<int, array<string, string>> */
    public static function tarjetasKpi(array $conteos): array
    {
        return [
            ['etiqueta' => 'Pendientes', 'valor' => (string) ($conteos['Pendiente'] ?? 0), 'detalle' => 'Estado actual', 'icono' => 'bi-file-earmark-text', 'tono' => 'verde'],
            ['etiqueta' => 'En proceso', 'valor' => (string) ($conteos['En proceso'] ?? 0), 'detalle' => 'Estado actual', 'icono' => 'bi-gear', 'tono' => 'azul'],
            ['etiqueta' => 'Entregados', 'valor' => (string) ($conteos['Entregado'] ?? 0), 'detalle' => 'Estado actual', 'icono' => 'bi-check-circle', 'tono' => 'violeta'],
            ['etiqueta' => 'Cancelados', 'valor' => (string) ($conteos['Cancelado'] ?? 0), 'detalle' => 'Estado actual', 'icono' => 'bi-x-square', 'tono' => 'rojo'],
        ];
    }
}
