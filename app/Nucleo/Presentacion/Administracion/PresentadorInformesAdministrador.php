<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Administracion;

/** Prepara gráficos, tablas y detalles de reportes y auditoría administrativa. */
final class PresentadorInformesAdministrador
{
    /**
     * Prepara la serie y el detalle visual de un reporte ya consultado.
     *
     * @param array<string, mixed> $reporte
     * @return array<string, mixed>
     */
    public static function presentarReporte(array $reporte): array
    {
        $maximo = max(array_merge([1.0], array_map(
            static fn (array $fila): float => (float) $fila['total'],
            $reporte['serie']
        )));
        $reporte['serie'] = array_map(static function (array $fila) use ($maximo): array {
            $fila['altura_vista'] = max(2, (int) round(((float) $fila['total'] / $maximo) * 100));
            $fila['fecha_vista'] = date('d/m/Y', strtotime((string) $fila['periodo']));
            $fila['fecha_corta_vista'] = date('d/m', strtotime((string) $fila['periodo']));

            return $fila;
        }, $reporte['serie']);
        $tiposReporte = [
            'ventas' => 'Ventas',
            'pedidos' => 'Pedidos',
            'inventario' => 'Inventario',
            'productos' => 'Productos',
            'clientes' => 'Clientes',
        ];
        $columnas = match ($reporte['tipo']) {
            'pedidos' => ['id' => 'Pedido', 'cliente' => 'Cliente', 'fecha' => 'Fecha', 'total' => 'Total', 'estado' => 'Estado'],
            'inventario' => ['producto' => 'Producto', 'categoria' => 'Categoría', 'stock' => 'Stock', 'estado' => 'Estado'],
            'productos' => ['producto' => 'Producto', 'categoria' => 'Categoría', 'precio' => 'Precio', 'stock' => 'Stock', 'estado' => 'Estado'],
            'clientes' => ['cliente' => 'Cliente', 'documento' => 'Documento', 'tipo' => 'Tipo', 'correo' => 'Correo', 'telefono' => 'Teléfono', 'estado' => 'Estado'],
            default => ['producto' => 'Producto', 'categoria' => 'Categoría', 'cantidad' => 'Cantidad', 'total' => 'Total'],
        };

        $reporte['desde_vista'] = date('d/m/Y', strtotime((string) $reporte['desde']));
        $reporte['hasta_vista'] = date('d/m/Y', strtotime((string) $reporte['hasta']));
        $esReporteVentas = $reporte['tipo'] === 'ventas';
        $haySerieVentas = $esReporteVentas && $reporte['serie'] !== [];
        $reporte['titulo_grafico_vista'] = $esReporteVentas ? 'Ventas por período' : 'Tendencia temporal';
        $reporte['mensaje_serie_vista'] = $esReporteVentas
            ? 'No hay ventas en el rango seleccionado.'
            : 'La serie temporal no está disponible para este tipo de reporte.';
        $reporte['eje_vista'] = [];
        for ($tick = 4; $tick >= 0; $tick--) {
            $reporte['eje_vista'][] = formatear_dinero($maximo * $tick / 4);
        }
        $reporte['detalle'] = array_map(static function (array $fila) use ($columnas): array {
            $instante = isset($fila['fecha']) && is_scalar($fila['fecha'])
                ? strtotime((string) $fila['fecha'])
                : false;
            $fila['fecha_vista'] = $instante !== false ? date('d/m/Y H:i', $instante) : '—';
            $fila['total_vista'] = formatear_dinero($fila['total'] ?? 0);
            $fila['precio_vista'] = formatear_dinero($fila['precio'] ?? 0);
            if (array_key_exists('estado', $fila) && is_numeric($fila['estado'])) {
                $fila['estado_es_booleano_vista'] = true;
                $fila['estado_texto_vista'] = (int) $fila['estado'] === 1 ? 'Activo' : 'Inactivo';
                $fila['estado_clase_vista'] = (int) $fila['estado'] === 1 ? '' : 'admin-status--muted';
            } else {
                $fila['estado_es_booleano_vista'] = false;
            }
            $fila['celdas_vista'] = [];
            foreach ($columnas as $clave => $etiqueta) {
                $esEstado = $clave === 'estado' && $fila['estado_es_booleano_vista'];
                $valor = match (true) {
                    $clave === 'total' || $clave === 'precio' => (string) $fila[$clave . '_vista'],
                    $esEstado => (string) $fila['estado_texto_vista'],
                    $clave === 'fecha' => (string) $fila['fecha_vista'],
                    default => (string) ($fila[$clave] ?? '—'),
                };
                $fila['celdas_vista'][] = [
                    'valor' => $valor,
                    'es_estado' => $esEstado,
                    'clase_estado' => $esEstado ? (string) $fila['estado_clase_vista'] : '',
                ];
            }

            return $fila;
        }, $reporte['detalle']);

        return [
            'reporte' => $reporte,
            'tarjetasKpi' => $reporte['indicadores'],
            'maximo' => $maximo,
            'tiposReporte' => $tiposReporte,
            'tipoReporte' => $tiposReporte[$reporte['tipo']] ?? $tiposReporte['ventas'],
            'tipoReporteMinuscula' => mb_strtolower($tiposReporte[$reporte['tipo']] ?? $tiposReporte['ventas']),
            'columnas' => $columnas,
            'atributoNotaRangoOculta' => in_array($reporte['tipo'], ['inventario', 'productos', 'clientes'], true) ? '' : 'hidden',
            'atributoMensajeSerieOculto' => $haySerieVentas ? 'hidden' : '',
            'atributoGraficoSerieOculto' => $haySerieVentas ? '' : 'hidden',
            'atributoDetalleReporteVacioOculto' => $reporte['detalle'] === [] ? '' : 'hidden',
        ];
    }

    /**
     * Filtra datos sensibles y prepara filas y opciones de auditoría para la vista.
     *
     * @param array<string, mixed> $consulta
     * @return array<string, mixed>
     */
    public static function presentarAuditoria(array $consulta): array
    {
        $consulta['registros'] = array_map(static function (array $registro): array {
            $anteriores = json_decode((string) ($registro['valores_anteriores'] ?? ''), true);
            $nuevos = json_decode((string) ($registro['valores_nuevos'] ?? ''), true);
            $anteriores = is_array($anteriores) ? $anteriores : [];
            $nuevos = is_array($nuevos) ? $nuevos : [];
            unset(
                $anteriores['contrasena'],
                $anteriores['token'],
                $anteriores['csrf'],
                $nuevos['contrasena'],
                $nuevos['token'],
                $nuevos['csrf']
            );

            $detalle = $nuevos !== []
                ? implode(', ', array_keys($nuevos))
                : ($anteriores !== [] ? implode(', ', array_keys($anteriores)) : 'Sin detalle adicional');
            $instante = strtotime((string) $registro['creado_en']);
            $registro['fecha_registro_vista'] = $instante !== false ? date('d/m/Y H:i:s', $instante) : '—';
            $registro['entidad_vista'] = (string) $registro['entidad']
                . (!empty($registro['entidad_id']) ? ' #' . (int) $registro['entidad_id'] : '');
            $registro['detalle_vista'] = $detalle;
            $registro['json_anterior_vista'] = json_encode(
                $anteriores,
                JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
            ) ?: '{}';
            $registro['json_nuevo_vista'] = json_encode(
                $nuevos,
                JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
            ) ?: '{}';

            return $registro;
        }, $consulta['registros']);
        $consulta['actividad'] = array_map(static function (array $fila): array {
            $fila['entidad_vista'] = ucfirst((string) $fila['entidad']);

            return $fila;
        }, $consulta['actividad']);
        $entidades = array_values(array_unique(array_map(
            static fn (array $fila): string => (string) $fila['entidad'],
            $consulta['actividad']
        )));
        sort($entidades);
        $entidadesVista = array_map(static fn (string $entidad): array => [
            'valor' => $entidad,
            'etiqueta' => ucfirst($entidad),
        ], $entidades);
        $resumen = $consulta['resumen'];

        return [
            'consultaAuditoria' => $consulta,
            'atributoRegistrosAuditoriaVaciosOculto' => $consulta['registros'] === [] ? '' : 'hidden',
            'atributoActividadAuditoriaVaciaOculto' => $consulta['actividad'] === [] ? '' : 'hidden',
            'atributoListaActividadAuditoriaOculta' => $consulta['actividad'] === [] ? 'hidden' : '',
            'tarjetasKpi' => [
                ['etiqueta' => 'Registros hoy', 'valor' => (string) $resumen['hoy'], 'detalle' => 'Eventos almacenados hoy', 'icono' => 'bi-file-earmark-text', 'tono' => 'azul'],
                ['etiqueta' => 'Registros totales', 'valor' => (string) $resumen['total'], 'detalle' => 'Historial disponible', 'icono' => 'bi-database-check', 'tono' => 'verde'],
                ['etiqueta' => 'Usuarios registrados', 'valor' => (string) $resumen['usuarios'], 'detalle' => 'Usuarios con eventos', 'icono' => 'bi-people', 'tono' => 'violeta'],
                ['etiqueta' => 'Entidades auditadas', 'valor' => (string) $resumen['entidades'], 'detalle' => 'Módulos con eventos', 'icono' => 'bi-shield-check', 'tono' => 'rojo'],
            ],
            'entidades' => $entidades,
            'entidadesVista' => $entidadesVista,
        ];
    }
}
