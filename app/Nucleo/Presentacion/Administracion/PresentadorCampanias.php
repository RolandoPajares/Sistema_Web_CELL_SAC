<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Administracion;

/** Prepara filas, filtros, selección y métricas visuales de campañas. */
final class PresentadorCampanias
{
    /**
     * Prepara la pantalla administrativa a partir de campañas y resumen del servicio.
     *
     * @param array<int, array<string, mixed>> $campanias
     * @param array<string, mixed> $resumen
     * @param array<string, mixed>|null $edicion
     * @return array<string, mixed>
     */
    public static function presentar(array $campanias, array $resumen, ?array $edicion): array
    {
        $ahora = time();
        foreach ($campanias as &$campania) {
            $campania['estado_actual_vista'] = self::estadoActual($campania, $ahora);
            $campania['inicio_timestamp_vista'] = strtotime((string) $campania['inicia_en']);
            $campania['fin_timestamp_vista'] = strtotime((string) $campania['finaliza_en']);
            $campania['inicio_fecha_vista'] = $campania['inicio_timestamp_vista'] !== false
                ? date('d/m/Y', $campania['inicio_timestamp_vista'])
                : '—';
            $campania['fin_fecha_vista'] = $campania['fin_timestamp_vista'] !== false
                ? date('d/m/Y', $campania['fin_timestamp_vista'])
                : '—';
            $campania['inicio_fecha_iso_vista'] = $campania['inicio_timestamp_vista'] !== false
                ? date('Y-m-d', $campania['inicio_timestamp_vista'])
                : '';
            $campania['fin_fecha_iso_vista'] = $campania['fin_timestamp_vista'] !== false
                ? date('Y-m-d', $campania['fin_timestamp_vista'])
                : '';
            $campania['ubicacion_valor_vista'] = mb_strtolower((string) $campania['ubicacion']);
            $campania['ubicacion_etiqueta_vista'] = ucfirst(str_replace('_', ' ', (string) $campania['ubicacion']));
            $campania['imagen_fila_vista'] = self::resolverImagen((string) ($campania['url_imagen'] ?? ''));
            $campania['atributoImagenOculta'] = $campania['imagen_fila_vista'] !== '' ? '' : 'hidden';
            $campania['atributoImagenMarcadorOculto'] = $campania['imagen_fila_vista'] === '' ? '' : 'hidden';
            $campania['atributoDesactivarOculto'] = (int) ($campania['activo'] ?? 0) === 1 ? '' : 'hidden';
        }
        unset($campania);

        $programadas = count(array_filter(
            $campanias,
            static fn (array $campania): bool => $campania['estado_actual_vista'] === 'scheduled'
        ));
        $finalizadas = count(array_filter(
            $campanias,
            static fn (array $campania): bool => $campania['estado_actual_vista'] === 'finished'
        ));
        $estadosCampania = [
            'active' => 'Activa',
            'scheduled' => 'Programada',
            'finished' => 'Finalizada',
            'inactive' => 'Inactiva',
        ];
        $campaniaSeleccionada = $campanias[0] ?? [];
        $hayCampaniaSeleccionada = $campaniaSeleccionada !== [];
        $imagenCampania = '';
        if (is_array($campaniaSeleccionada) && is_string($campaniaSeleccionada['url_imagen'] ?? null)) {
            $imagenCampania = self::resolverImagen($campaniaSeleccionada['url_imagen']);
        }
        $fechaInicioSeleccion = $hayCampaniaSeleccionada
            ? strtotime((string) $campaniaSeleccionada['inicia_en'])
            : false;
        $fechaFinSeleccion = $hayCampaniaSeleccionada
            ? strtotime((string) $campaniaSeleccionada['finaliza_en'])
            : false;
        $ubicaciones = array_values(array_unique(array_map(
            static fn (array $campania): string => (string) $campania['ubicacion'],
            $campanias
        )));
        $ubicacionesCampania = array_map(static fn (string $ubicacion): array => [
            'valor' => mb_strtolower($ubicacion),
            'etiqueta' => ucfirst(str_replace('_', ' ', $ubicacion)),
        ], $ubicaciones);
        $estadoSeleccionado = is_array($campaniaSeleccionada)
            ? (string) ($campaniaSeleccionada['estado_actual_vista'] ?? '')
            : '';
        $fechaInicioFormulario = date('Y-m-d\\TH:i');
        $fechaFinFormulario = date('Y-m-d\\TH:i', strtotime('+30 days'));
        if ($edicion !== null) {
            if (isset($edicion['inicia_en'])) {
                $fechaInicioFormulario = date('Y-m-d\\TH:i', strtotime((string) $edicion['inicia_en']));
            }
            if (isset($edicion['finaliza_en'])) {
                $fechaFinFormulario = date('Y-m-d\\TH:i', strtotime((string) $edicion['finaliza_en']));
            }
        }

        return [
            'campanias' => $campanias,
            'programadas' => $programadas,
            'finalizadas' => $finalizadas,
            'estadosCampania' => $estadosCampania,
            'campaniaSeleccionada' => $campaniaSeleccionada,
            'atributoEditarCampaniaSeleccionadaOculto' => $hayCampaniaSeleccionada ? '' : 'hidden',
            'atributoVistaPreviaVaciaOculto' => $hayCampaniaSeleccionada ? 'hidden' : '',
            'atributoVistaPreviaDetallesOculto' => $hayCampaniaSeleccionada ? '' : 'hidden',
            'atributoCampaniasVaciasOculto' => $campanias === [] ? '' : 'hidden',
            'imagenCampania' => $imagenCampania,
            'atributoImagenPreviaOculto' => $imagenCampania !== '' ? '' : 'hidden',
            'atributoMarcadorImagenPreviaOculto' => $imagenCampania === '' ? '' : 'hidden',
            'fechaInicioSeleccion' => $fechaInicioSeleccion,
            'fechaFinSeleccion' => $fechaFinSeleccion,
            'fechaInicioSeleccionIso' => $fechaInicioSeleccion !== false ? date('Y-m-d', $fechaInicioSeleccion) : '',
            'fechaInicioSeleccionVista' => $fechaInicioSeleccion !== false ? date('d/m/Y', $fechaInicioSeleccion) : '—',
            'fechaFinSeleccionIso' => $fechaFinSeleccion !== false ? date('Y-m-d', $fechaFinSeleccion) : '',
            'fechaFinSeleccionVista' => $fechaFinSeleccion !== false ? date('d/m/Y', $fechaFinSeleccion) : '—',
            'estadoSeleccionado' => $estadoSeleccionado,
            'etiquetaEstadoSeleccionado' => $estadosCampania[$estadoSeleccionado] ?? '',
            'opcionesUbicacionFormulario' => [
                'emergente' => 'Popup al ingresar',
                'lateral' => 'Banner lateral',
                'barra_superior' => 'Barra superior',
            ],
            'imagenCampaniaAlt' => is_array($campaniaSeleccionada) && $imagenCampania !== ''
                ? 'Imagen de ' . (string) $campaniaSeleccionada['titulo']
                : '',
            'ubicacionesCampania' => $ubicacionesCampania,
            'tarjetasKpi' => self::tarjetasKpi($resumen, $programadas, $finalizadas),
            'edicion' => $edicion,
            'fechaInicioFormulario' => $fechaInicioFormulario,
            'fechaFinFormulario' => $fechaFinFormulario,
        ];
    }

    /** Determina el estado temporal que ya utiliza la pantalla administrativa. */
    private static function estadoActual(array $campania, int $ahora): string
    {
        if ((int) $campania['activo'] !== 1) {
            return 'inactive';
        }
        if (strtotime((string) $campania['inicia_en']) > $ahora) {
            return 'scheduled';
        }
        if (strtotime((string) $campania['finaliza_en']) < $ahora) {
            return 'finished';
        }

        return 'active';
    }

    /** Normaliza una URL de imagen conservando las rutas públicas admitidas. */
    private static function resolverImagen(string $fuente): string
    {
        $fuente = trim($fuente);
        $esquema = strtolower((string) parse_url($fuente, PHP_URL_SCHEME));

        if (filter_var($fuente, FILTER_VALIDATE_URL) && in_array($esquema, ['http', 'https'], true)) {
            return $fuente;
        }
        if ($fuente !== '' && preg_match('/^(?!.*\.\.)[A-Za-z0-9_\/. -]+$/D', $fuente)) {
            return url_interna(ltrim($fuente, '/'));
        }

        return '';
    }

    /** @return array<int, array<string, string>> */
    private static function tarjetasKpi(array $resumen, int $programadas, int $finalizadas): array
    {
        return [
            [
                'etiqueta' => 'Campañas activas',
                'valor' => (string) $resumen['activas'],
                'detalle' => 'Habilitadas en el sistema',
                'icono' => 'bi-megaphone',
                'tono' => 'azul',
            ],
            [
                'etiqueta' => 'Programadas',
                'valor' => (string) $programadas,
                'detalle' => 'Con inicio futuro y habilitadas',
                'icono' => 'bi-calendar-event',
                'tono' => 'verde',
            ],
            [
                'etiqueta' => 'Finalizadas',
                'valor' => (string) $finalizadas,
                'detalle' => 'Con fecha de fin vencida',
                'icono' => 'bi-check-circle',
                'tono' => 'violeta',
            ],
            [
                'etiqueta' => 'Clics totales',
                'valor' => number_format((int) $resumen['clics']),
                'detalle' => number_format((int) $resumen['vistas']) . ' vistas registradas',
                'icono' => 'bi-mouse',
                'tono' => 'rojo',
            ],
        ];
    }
}
