<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\ComercioInteligente;

use App\Modelos\Productos\Producto;

/** Prepara productos y métricas visuales de los módulos de comercio inteligente. */
final class PresentadorComercioInteligente
{
    /**
     * Prepara las opciones del objetivo para el formulario del optimizador.
     *
     * @return array<int, array{valor:string,etiqueta:string,atributo_seleccionado:string}>
     */
    public static function presentarObjetivosOptimizador(string $objetivoSeleccionado): array
    {
        $opciones = [
            ['valor' => 'variety', 'etiqueta' => 'Más variedad'],
            ['valor' => 'units', 'etiqueta' => 'Más unidades'],
            ['valor' => 'margin', 'etiqueta' => 'Mayor margen estimado'],
            ['valor' => 'premium', 'etiqueta' => 'Equipos premium'],
        ];

        foreach ($opciones as &$opcion) {
            $opcion['atributo_seleccionado'] = $opcion['valor'] === $objetivoSeleccionado
                ? 'selected'
                : '';
        }
        unset($opcion);

        return $opciones;
    }

    /**
     * Prepara importes y nombres para la propuesta del optimizador.
     *
     * @param array<string, mixed> $propuesta
     * @return array{
     *     metricas:array<int, array{etiqueta:string,valor:string}>,
     *     articulos:array<int, array{producto:string,cantidad:int,precio:string,subtotal:string}>
     * }
     */
    public static function presentarPropuestaOptimizador(array $propuesta): array
    {
        $articulos = [];
        foreach ((array) ($propuesta['articulos'] ?? []) as $linea) {
            if (!is_array($linea)) {
                continue;
            }

            $producto = is_array($linea['producto'] ?? null) ? $linea['producto'] : [];
            $articulos[] = [
                'producto' => trim((string) ($producto['marca'] ?? '') . ' ' . (string) ($producto['nombre'] ?? '')),
                'cantidad' => (int) ($linea['cantidad'] ?? 0),
                'precio' => formatear_dinero((float) ($producto['precio'] ?? 0)),
                'subtotal' => formatear_dinero((float) ($linea['subtotal'] ?? 0)),
            ];
        }

        return [
            'metricas' => [
                ['etiqueta' => 'Inversión', 'valor' => formatear_dinero((float) ($propuesta['invertido'] ?? 0))],
                ['etiqueta' => 'Saldo', 'valor' => formatear_dinero((float) ($propuesta['saldo'] ?? 0))],
                ['etiqueta' => 'Margen estimado', 'valor' => formatear_dinero((float) ($propuesta['margen_estimado'] ?? 0))],
            ],
            'articulos' => $articulos,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @return array<int, array<string, mixed>>
     */
    public static function presentarRecomendaciones(array $productos): array
    {
        return array_map(static function (array $producto, int $indice): array {
            $producto['precio'] = Producto::desdeRegistro($producto)->precioEfectivo();
            $producto['imagen_recomendada_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_recomendada_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_recomendada_vista'] === '' ? '' : 'hidden';
            $producto['icono_recomendado_vista'] = icono_categoria_producto((string) ($producto['categoria'] ?? ''));
            $producto['texto_alternativo_vista'] = trim(
                (string) ($producto['marca'] ?? '') . ' ' . (string) ($producto['nombre'] ?? '')
            );
            $producto['puesto_vista'] = $indice + 1;

            return $producto;
        }, $productos, array_keys($productos));
    }

    /**
     * Prepara las tres opciones del selector del comparador.
     *
     * @param array<int, int> $idsProductos
     * @param array<int, array<string, mixed>> $productos
     * @return array<int, array{numero:int,opcional:bool,atributosOpcionVacia:string,opciones:array<int,array<string,mixed>>}>
     */
    public static function prepararRanurasComparador(array $idsProductos, array $productos = []): array
    {
        $ranuras = [];
        for ($indice = 0; $indice < 3; $indice++) {
            $idSeleccionado = $idsProductos[$indice] ?? 0;
            $opciones = array_map(static function (array $producto) use ($idSeleccionado): array {
                $producto['atributoSeleccionado'] = (int) $producto['id'] === $idSeleccionado ? 'selected' : '';

                return $producto;
            }, $productos);
            $ranuras[] = [
                'numero' => $indice + 1,
                'opcional' => $indice > 0,
                'atributosOpcionVacia' => $indice > 0 ? '' : 'disabled hidden',
                'opciones' => $opciones,
            ];
        }

        return $ranuras;
    }

    /**
     * Marca la conversación de demostración que aparece seleccionada al abrir el asistente.
     *
     * @param array<int, array{titulo:string,resumen:string}> $conversaciones
     * @return array<int, array{titulo:string,resumen:string,clase_vista:string}>
     */
    public static function presentarConversacionesAsistente(array $conversaciones): array
    {
        $conversacionesVista = [];
        foreach ($conversaciones as $indice => $conversacion) {
            $conversacionesVista[] = [
                'titulo' => $conversacion['titulo'],
                'resumen' => $conversacion['resumen'],
                'clase_vista' => $indice === 0 ? 'activo' : '',
            ];
        }

        return $conversacionesVista;
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @return array<int, array<string, mixed>>
     */
    public static function presentarComparacion(array $productos): array
    {
        return array_map(static function (array $producto): array {
            $producto['precio'] = Producto::desdeRegistro($producto)->precioEfectivo();
            $producto['enlace_detalle_vista'] = url_interna('catalog?' . http_build_query([
                'producto' => (int) $producto['id'],
                'cat' => (string) ($producto['categoria'] ?? ''),
            ]));
            $producto['imagen_comparacion_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_comparacion_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_comparacion_vista'] === '' ? '' : 'hidden';
            $producto['icono_comparacion_vista'] = icono_categoria_producto((string) ($producto['categoria'] ?? ''));
            $producto['texto_alternativo_vista'] = trim(
                (string) ($producto['marca'] ?? '') . ' ' . (string) ($producto['nombre'] ?? '')
            );

            return $producto;
        }, $productos);
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @return array<int, array<string, mixed>>
     */
    public static function filtrarProductosComparador(array $productos): array
    {
        return array_values(array_filter(
            $productos,
            static fn (array $producto): bool => stripos((string) $producto['categoria'], 'cel') !== false
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @return array<int, array<string, mixed>>
     */
    public static function seleccionarPortatiles(array $productos): array
    {
        $productosPortatiles = array_values(array_filter(
            $productos,
            static function (array $producto): bool {
                $categoria = mb_strtolower((string) ($producto['categoria'] ?? ''));
                $nombre = mb_strtolower((string) ($producto['nombre'] ?? ''));
                $marca = mb_strtolower((string) ($producto['marca'] ?? ''));
                $textoProducto = $categoria . ' ' . $marca . ' ' . $nombre;
                $terminosPortatiles = [
                    'laptop',
                    'notebook',
                    'computador',
                    'portatil',
                    'portÃ¡til',
                    'macbook',
                ];

                foreach ($terminosPortatiles as $termino) {
                    if (mb_strpos($textoProducto, $termino) !== false) {
                        return true;
                    }
                }

                return false;
            }
        ));

        return array_slice($productosPortatiles, 0, 3);
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @return array<int, array{nombre:string, descripcion:string, existencias:int, precio:float, caracteristicas:array<int, array{nombre:string, valor:string}>}>
     */
    public static function presentarOpcionesPortatiles(array $productos): array
    {
        return array_map(static function (array $producto): array {
            $caracteristicas = $producto['caracteristicas'] ?? [];
            $almacenamiento = trim((string) ($producto['almacenamiento'] ?? ''));
            $tieneAlmacenamiento = false;

            foreach ($caracteristicas as $caracteristica) {
                $nombreCaracteristica = (string) ($caracteristica['nombre'] ?? '');
                if (mb_stripos($nombreCaracteristica, 'almacenamiento') !== false) {
                    $tieneAlmacenamiento = true;
                    break;
                }
            }

            if ($almacenamiento !== '' && !$tieneAlmacenamiento) {
                $caracteristicas[] = [
                    'nombre' => 'Almacenamiento',
                    'valor' => $almacenamiento,
                ];
            }

            return [
                'nombre' => trim(implode(' ', array_filter([
                    trim((string) ($producto['marca'] ?? '')),
                    trim((string) ($producto['nombre'] ?? '')),
                ]))),
                'descripcion' => trim((string) ($producto['descripcion'] ?? '')),
                'existencias' => (int) ($producto['existencias'] ?? 0),
                'precio' => Producto::desdeRegistro($producto)->precioEfectivo(),
                'caracteristicas' => $caracteristicas,
                'atributoDescripcionOculta' => trim((string) ($producto['descripcion'] ?? '')) !== '' ? '' : 'hidden',
                'atributoCaracteristicasOculto' => $caracteristicas !== [] ? '' : 'hidden',
            ];
        }, $productos);
    }

    /**
     * Prepara la recomendación principal del asistente, si el catálogo tiene opciones.
     *
     * @param array<int, array<string, mixed>> $opciones
     * @return array{disponible:bool,nombre:string}
     */
    public static function presentarRecomendacionLaptop(array $opciones): array
    {
        $opcionPrincipal = $opciones[0] ?? [];

        return [
            'disponible' => $opcionPrincipal !== [],
            'nombre' => (string) ($opcionPrincipal['nombre'] ?? ''),
        ];
    }

    /**
     * @return array<int, array{icono:string,etiqueta:string}>
     */
    public static function beneficiosMayoristas(): array
    {
        return [
            ['icono' => 'bi-cash-stack', 'etiqueta' => 'Precios por volumen'],
            ['icono' => 'bi-box-seam', 'etiqueta' => 'Stock garantizado'],
            ['icono' => 'bi-headset', 'etiqueta' => 'Atención personalizada'],
            ['icono' => 'bi-truck', 'etiqueta' => 'Envíos seguros'],
            ['icono' => 'bi-shield-check', 'etiqueta' => 'Productos originales'],
        ];
    }

    /**
     * @param array<int, array{nombre:string, descripcion:string, existencias:int, precio:float, caracteristicas:array<int, array{nombre:string, valor:string}>}> $opcionesLaptop
     * @return array<int, array{etiqueta:string, valores:array<int, string>}>
     */
    public static function presentarFilasComparativa(array $opcionesLaptop): array
    {
        $caracteristicas = [];

        foreach ($opcionesLaptop as $indiceProducto => $opcion) {
            foreach ($opcion['caracteristicas'] as $caracteristica) {
                $etiqueta = trim($caracteristica['nombre']);
                $clave = mb_strtolower($etiqueta);

                if ($etiqueta === '') {
                    continue;
                }

                $caracteristicas[$clave] ??= [
                    'etiqueta' => $etiqueta,
                    'valores' => array_fill(0, count($opcionesLaptop), 'â€”'),
                ];
                $caracteristicas[$clave]['valores'][$indiceProducto] = $caracteristica['valor'];
            }
        }

        $filas = array_values($caracteristicas);
        $filas[] = [
            'etiqueta' => 'Precio registrado',
            'valores' => array_map(
                static fn (array $opcion): string => formatear_dinero($opcion['precio']),
                $opcionesLaptop
            ),
        ];
        $filas[] = [
            'etiqueta' => 'Existencias',
            'valores' => array_map(
                static fn (array $opcion): string => (string) $opcion['existencias'],
                $opcionesLaptop
            ),
        ];

        return $filas;
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @return array<int, array<string, mixed>>
     */
    public static function presentarProductosMayoristas(array $productos): array
    {
        return array_map(static function (array $producto): array {
            $producto['imagen_mayorista_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_mayorista_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_mayorista_vista'] === '' ? '' : 'hidden';
            $producto['icono_mayorista_vista'] = icono_categoria_producto((string) ($producto['categoria'] ?? ''));
            $precioEfectivo = Producto::desdeRegistro($producto)->precioEfectivo();
            $producto['precio'] = $precioEfectivo;
            $producto['precio_mayorista_10_vista'] = round($precioEfectivo * .95, 2);
            $producto['precio_mayorista_50_vista'] = round($precioEfectivo * .9, 2);

            return $producto;
        }, array_slice($productos, 0, 6));
    }

    /**
     * @param array<string, mixed> $resumen
     * @param array<int, array<string, mixed>> $campanias
     * @return array{metricasPublicidad:array<int, array{icono:string,etiqueta:string,valor:string,detalle:string}>, campaniasDestacadas:array<int, array<string, mixed>>}
     */
    public static function presentarPublicidad(array $resumen, array $campanias): array
    {
        return [
            'metricasPublicidad' => [
                ['icono' => 'bi-megaphone', 'etiqueta' => 'CampaÃ±as activas', 'valor' => (string) $resumen['activas'], 'detalle' => 'Datos reales'],
                ['icono' => 'bi-eye', 'etiqueta' => 'Impresiones', 'valor' => number_format((int) $resumen['vistas']), 'detalle' => 'Datos reales'],
                ['icono' => 'bi-mouse', 'etiqueta' => 'Clics', 'valor' => number_format((int) $resumen['clics']), 'detalle' => 'Datos reales'],
                ['icono' => 'bi-bar-chart', 'etiqueta' => 'CTR', 'valor' => number_format((float) $resumen['ctr'], 1) . '%', 'detalle' => 'Calculado'],
            ],
            'campaniasDestacadas' => array_slice($campanias, 0, 4),
        ];
    }
}
