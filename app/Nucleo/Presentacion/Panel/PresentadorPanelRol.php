<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Panel;

/** Prepara tablas, formularios y gráficos compartidos por los paneles de rol. */
final class PresentadorPanelRol
{
    /**
     * Reúne los datos visuales de la página de administración de un módulo.
     *
     * @param array<string, mixed> $configuracion
     * @param array<string, mixed> $interfaz
     * @param array<string, mixed> $datosDemostracion
     * @param array<int, array<string, mixed>> $registros
     * @param array<string, mixed>|null $edicion
     * @param array<string, array<int, array<string, mixed>>> $opciones
     * @param array<string, mixed> $resumen
     * @return array<string, mixed>
     */
    public static function prepararIndiceModulo(
        string $modulo,
        array $configuracion,
        array $interfaz,
        array $datosDemostracion,
        array $registros,
        ?array $edicion,
        array $opciones,
        array $resumen,
        bool $puedeCrear,
        bool $puedeActualizar,
        bool $puedeDesactivar,
        bool $usarInterfazDavid
    ): array {
        $soloCrear = $puedeCrear && !$puedeActualizar;
        $soloActualizar = !$puedeCrear && $puedeActualizar;
        $editando = is_array($edicion) && $puedeActualizar;
        $columnasVisibles = match ($modulo) {
            'preparacion-pedidos' => ['pedido', 'cliente', 'tipo_cliente', 'items', 'total', 'estado', 'fecha'],
            'logistica-mayorista' => ['codigo', 'exportador', 'consignatario', 'factura', 'orden_compra', 'pais_origen', 'lugar_carga', 'puerto_descarga', 'entrega', 'mercancia', 'bultos', 'peso_neto_kg', 'peso_bruto_kg', 'estado'],
            default => null,
        };
        $tiposInterfazPermitidos = ['kanban', 'analitica', 'contenido', 'tarjetas', 'perfil', 'historial'];
        $tipoInterfazVista = in_array($interfaz['tipo'] ?? '', $tiposInterfazPermitidos, true)
            ? $interfaz['tipo']
            : 'tabla';
        $fechaActual = date('Y-m-d');
        $clavesOpcionesCampos = [
            'producto_id' => 'productos',
            'proveedor_id' => 'proveedores',
            'cliente_id' => 'clientes',
        ];
        $campos = (array) ($configuracion['campos'] ?? []);
        $camposFormulario = self::prepararCamposFormulario(
            $campos,
            $edicion,
            $opciones,
            $fechaActual,
            $clavesOpcionesCampos
        );
        $rutasModulo = self::prepararRutasModulo($modulo, $registros, $edicion, $editando);
        $columnasGenerales = self::prepararColumnasModulo($registros, $columnasVisibles);
        $desactivables = $puedeDesactivar ? [$modulo] : [];
        $esCrud = $puedeCrear || $puedeActualizar || $puedeDesactivar;

        return [
            'tituloPagina' => (string) ($configuracion['titulo'] ?? ''),
            'configuracionModulo' => $configuracion,
            'datosDemostracion' => $datosDemostracion,
            'registroEdicion' => $edicion,
            'campos' => $campos,
            'camposFormulario' => $camposFormulario,
            'esCrud' => $esCrud,
            'atributoCrudOculto' => $esCrud ? '' : 'hidden',
            'atributoVistaModuloOculta' => $esCrud ? 'hidden' : '',
            'atributoEditorOculto' => $soloActualizar && !$editando ? 'hidden' : '',
            'atributoAccionNuevoOculta' => $esCrud && !$soloActualizar ? '' : 'hidden',
            'atributoAccionGestionOculta' => $esCrud && !$soloActualizar ? 'hidden' : '',
            'atributoCerrarEditorOculto' => $editando ? '' : 'hidden',
            'tituloEditorVista' => $editando ? 'Actualizar registro' : 'Crear registro',
            'etiquetaEditorVista' => $editando ? 'Edición' : 'Nuevo',
            'textoGuardarEditorVista' => $editando ? 'Guardar cambios' : 'Registrar',
            'soloCrear' => $soloCrear,
            'soloActualizar' => $soloActualizar,
            'editando' => $editando,
            'desactivables' => $desactivables,
            'columnasVisibles' => $columnasVisibles,
            'columnasGenerales' => $columnasGenerales,
            'rutasRegistrosPanel' => $rutasModulo['rutasRegistrosPanel'],
            'urlModuloPanel' => $rutasModulo['urlModuloPanel'],
            'urlFormularioPanel' => $rutasModulo['urlFormularioPanel'],
            'usarInterfazDavid' => $usarInterfazDavid,
            'tipoInterfazVista' => $tipoInterfazVista,
            'tablaDavid' => self::prepararTablaCompras($interfaz, $registros),
            'tablaModulo' => self::prepararTablaModulo(
                $registros,
                $columnasGenerales,
                $rutasModulo['rutasRegistrosPanel'],
                $modulo,
                $soloCrear,
                $desactivables,
                !$usarInterfazDavid || $modulo !== 'inventario'
            ),
            'fechaActual' => $fechaActual,
            'clavesOpcionesCampos' => $clavesOpcionesCampos,
            'resumen' => $resumen,
            'opciones' => $opciones,
        ];
    }

    /**
     * Convierte registros demostrativos de pedidos en datos con nombres claros para la vista.
     *
     * @param array<int, array<string, mixed>> $pedidos
     * @return array<int, array<string, string>>
     */
    public static function presentarPedidosCuenta(array $pedidos): array
    {
        $iconosPorTipo = [
            'telefono' => 'bi-phone',
            'audio_portatil' => 'bi-speaker',
            'audifonos' => 'bi-headphones',
        ];

        return array_map(static function (array $pedido) use ($iconosPorTipo): array {
            $estado = (string) ($pedido['estado'] ?? '');

            return [
                'codigo' => (string) ($pedido['codigo'] ?? ''),
                'fecha' => (string) ($pedido['fecha'] ?? ''),
                'producto' => (string) ($pedido['producto'] ?? ''),
                'cantidad_vista' => isset($pedido['cantidad']) && (int) $pedido['cantidad'] > 0
                    ? (int) $pedido['cantidad'] . ((int) $pedido['cantidad'] === 1 ? ' unidad' : ' unidades')
                    : '',
                'total' => (string) ($pedido['total'] ?? ''),
                'estado' => $estado,
                'icono' => $iconosPorTipo[(string) ($pedido['tipo_producto'] ?? '')] ?? 'bi-box-seam',
                'clase_estado' => mb_strtolower($estado) === 'preparando' ? 'preparando' : '',
                'url_detalle' => url_interna('panel/historial'),
                'texto_accion' => (string) ($pedido['texto_accion'] ?? 'Ver detalle'),
            ];
        }, $pedidos);
    }

    /**
     * Prepara el nombre y el tipo de cuenta usados por el área privada del cliente.
     *
     * @param array<string, mixed> $usuario
     * @return array{usuarioCuenta:array<string,mixed>,nombreCuenta:string,nombreCortoCuenta:string,esCuentaMayorista:bool,tituloCuenta:string,tipoCuentaVista:string,descripcionCuentaVista:string,destinoPedidosCuenta:string,iconoBannerCuenta:string,accionesCabeceraCuenta:array<int,array{clase:string,destino:string,icono:string,etiqueta:string}>}
     */
    public static function prepararDatosCuenta(array $usuario, string $rol): array
    {
        $nombreCuenta = (string) ($usuario['nombre'] ?? 'Cliente');
        $esCuentaMayorista = $rol === 'cliente_mayorista';

        return [
            'usuarioCuenta' => $usuario,
            'nombreCuenta' => $nombreCuenta,
            'nombreCortoCuenta' => explode(' ', $nombreCuenta)[0],
            'esCuentaMayorista' => $esCuentaMayorista,
            'tituloCuenta' => $esCuentaMayorista ? 'Portal mayorista B2B' : 'Mi cuenta',
            'tipoCuentaVista' => $esCuentaMayorista ? 'mayorista' : 'minorista',
            'descripcionCuentaVista' => $esCuentaMayorista
                ? 'Gestiona tus cotizaciones, pedidos y compras por volumen.'
                : 'Gestiona tus compras, datos y preferencias.',
            'destinoPedidosCuenta' => $esCuentaMayorista ? 'panel/pedidos-mayoristas' : 'panel/pedidos',
            'iconoBannerCuenta' => $esCuentaMayorista ? 'bi-buildings' : 'bi-bag-heart',
            'accionesCabeceraCuenta' => $esCuentaMayorista
                ? [
                    ['clase' => 'cuenta-btn-blanco', 'destino' => 'mayorista', 'icono' => 'bi-grid', 'etiqueta' => 'Catálogo B2B'],
                    ['clase' => 'cuenta-btn-borde', 'destino' => 'panel/cotizaciones', 'icono' => 'bi-file-earmark-text', 'etiqueta' => 'Mis cotizaciones'],
                ]
                : [
                    ['clase' => 'cuenta-btn-blanco', 'destino' => 'catalog', 'icono' => 'bi-phone', 'etiqueta' => 'Ver catálogo'],
                    ['clase' => 'cuenta-btn-borde', 'destino' => 'panel/pedidos', 'icono' => 'bi-box-seam', 'etiqueta' => 'Mis pedidos'],
                ],
            'accesosRapidosCuenta' => $esCuentaMayorista
                ? [
                    ['destino' => 'mayorista', 'icono' => 'bi-grid', 'etiqueta' => 'Catálogo B2B'],
                    ['destino' => 'panel/cotizaciones', 'icono' => 'bi-file-earmark-text', 'etiqueta' => 'Cotizaciones'],
                    ['destino' => 'panel/pedidos-mayoristas', 'icono' => 'bi-truck', 'etiqueta' => 'Mis pedidos'],
                    ['destino' => 'panel/historial', 'icono' => 'bi-clock-history', 'etiqueta' => 'Historial'],
                ]
                : [
                    ['destino' => 'panel/perfil', 'icono' => 'bi-person', 'etiqueta' => 'Mis datos'],
                    ['destino' => 'panel/direcciones', 'icono' => 'bi-geo-alt', 'etiqueta' => 'Direcciones'],
                    ['destino' => 'panel/pedidos', 'icono' => 'bi-box-seam', 'etiqueta' => 'Mis pedidos'],
                    ['destino' => 'panel/historial', 'icono' => 'bi-clock-history', 'etiqueta' => 'Historial'],
                ],
        ];
    }

    public static function vistaContenidoCuenta(string $modulo): string
    {
        return match ($modulo) {
            'dashboard' => 'modulos.cuenta.contenido.dashboard',
            'pedidos', 'pedidos-mayoristas' => 'modulos.cuenta.contenido.pedidos',
            'historial' => 'modulos.cuenta.contenido.historial',
            'perfil' => 'modulos.cuenta.contenido.perfil',
            'direcciones' => 'modulos.cuenta.contenido.direcciones',
            'favoritos' => 'modulos.cuenta.contenido.favoritos',
            default => 'modulos.cuenta.contenido.vacio',
        };
    }

    /**
     * Prepara el gráfico, actividad y proveedores del tablero de compras.
     *
     * @param array<string, mixed> $datosPagina
     * @return array<string, mixed>
     */
    public static function prepararDatosTableroCompras(array $datosPagina, string $rolActual): array
    {
        $datosCompras = is_array($datosPagina['dashboardCompras'] ?? null)
            ? $datosPagina['dashboardCompras']
            : ['tendencia' => [], 'proveedores' => []];
        $tendenciaCompras = $datosCompras['tendencia'] ?? [];
        $proveedoresDashboard = $datosCompras['proveedores'] ?? [];
        $compraMaxima = 0.0;

        foreach ($tendenciaCompras as $punto) {
            $compraMaxima = max($compraMaxima, (float) ($punto['total'] ?? 0));
        }

        $totalProveedores = array_sum(array_map(
            static fn ($proveedor) => (float) ($proveedor['total'] ?? 0),
            $proveedoresDashboard
        ));
        $coloresDonut = ['#1687ff', '#22c58b', '#ffb72f', '#8b4fe8'];
        $acumulado = 0.0;
        $segmentos = [];

        foreach ($proveedoresDashboard as $indice => $proveedor) {
            $porcentaje = $totalProveedores > 0
                ? ((float) $proveedor['total'] / $totalProveedores * 100)
                : 0;
            $proveedor['porcentaje_vista'] = $totalProveedores > 0
                ? round((float) $proveedor['total'] / $totalProveedores * 100)
                : 0;
            $proveedor['color_vista'] = $coloresDonut[$indice % 4];
            $proveedoresDashboard[$indice] = $proveedor;
            $segmentos[] = $coloresDonut[$indice % 4] . ' ' . $acumulado . '% ' . ($acumulado + $porcentaje) . '%';
            $acumulado += $porcentaje;
        }

        foreach ($tendenciaCompras as $indice => $punto) {
            $punto['altura_vista'] = $compraMaxima > 0
                ? max(12, (int) (((float) $punto['total'] / $compraMaxima) * 145))
                : 12;
            $fecha = \DateTime::createFromFormat('Y-m', (string) $punto['periodo']);
            $punto['fecha_vista'] = $fecha
                ? [
                    'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
                    'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
                    'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic',
                ][$fecha->format('M')]
                : (string) $punto['periodo'];
            $punto['total_vista'] = number_format((float) $punto['total'], 2, '.', ',');
            $tendenciaCompras[$indice] = $punto;
        }

        $actividadCompras = $rolActual === 'compras_logistica' && is_array($datosPagina['actividadCompras'] ?? null)
            ? $datosPagina['actividadCompras']
            : [];
        $actividadCompras = array_map(static function (array $fila): array {
            $total = (float) ($fila['total'] ?? 0);
            $fila['total_vista'] = $total > 0 ? 'S/ ' . number_format($total, 2, '.', ',') : '—';
            $fila['estado_vista'] = ucfirst((string) ($fila['estado'] ?? 'Registrado'));

            return $fila;
        }, $actividadCompras);
        $prioridadesCompras = $rolActual === 'compras_logistica' && is_array($datosPagina['prioridadesCompras'] ?? null)
            ? $datosPagina['prioridadesCompras']
            : [];

        return [
            'datosCompras' => $datosCompras,
            'tendenciaCompras' => $tendenciaCompras,
            'atributoTendenciaVaciaOculto' => $tendenciaCompras === [] ? '' : 'hidden',
            'atributoGraficoTendenciaOculto' => $tendenciaCompras === [] ? 'hidden' : '',
            'proveedoresDashboard' => $proveedoresDashboard,
            'atributoProveedoresVaciosOculto' => $proveedoresDashboard === [] ? '' : 'hidden',
            'atributoGraficosProveedoresOculto' => $proveedoresDashboard === [] ? 'hidden' : '',
            'compraMaxima' => $compraMaxima,
            'totalProveedores' => $totalProveedores,
            'coloresDonut' => $coloresDonut,
            'segmentos' => $segmentos,
            'actividadCompras' => $actividadCompras,
            'atributoActividadComprasVaciaOculto' => $actividadCompras === [] ? '' : 'hidden',
            'atributoTablaActividadComprasOculta' => $actividadCompras === [] ? 'hidden' : '',
            'prioridadesCompras' => $prioridadesCompras,
            'atributoPrioridadesComprasVaciasOculto' => $prioridadesCompras === [] ? '' : 'hidden',
            'atributoListaPrioridadesComprasOculta' => $prioridadesCompras === [] ? 'hidden' : '',
            'textoPeriodoDashboard' => $rolActual === 'marketing'
                ? 'Rendimiento por período pendiente de conectar'
                : 'Compras por período pendientes de conectar',
            'textoDistribucionDashboard' => $rolActual === 'marketing'
                ? 'Distribución por canales pendiente de conectar'
                : 'Participación por monto comprado',
            'textoDistribucionVaciaDashboard' => $rolActual === 'marketing'
                ? 'Distribución no disponible'
                : 'Distribución de proveedores no disponible',
            'opcionesPeriodoDashboard' => [
                3 => 'Últimos 3 meses',
                6 => 'Últimos 6 meses',
                12 => 'Últimos 12 meses',
            ],
        ];
    }

    /**
     * Prepara valores, clases y opciones para los campos de un formulario de módulo.
     *
     * @param array<string, array<string, mixed>> $campos
     * @param array<string, mixed>|null $edicion
     * @param array<string, array<int, array<string, mixed>>> $opciones
     * @param array<string, string> $clavesOpcionesCampos
     * @return array<int, array<string, mixed>>
     */
    public static function prepararCamposFormulario(
        array $campos,
        ?array $edicion,
        array $opciones,
        string $fechaActual,
        array $clavesOpcionesCampos
    ): array {
        $camposVista = [];

        foreach ($campos as $nombre => $campo) {
            $tipo = (string) ($campo['type'] ?? 'text');
            $valor = (string) ($edicion[$nombre] ?? '');
            if ($tipo === 'date' && $valor === '') {
                $valor = $fechaActual;
            }
            $claveOpciones = $clavesOpcionesCampos[$nombre] ?? '';
            $opcionesCampo = $tipo === 'select-data'
                ? (array) ($opciones[$claveOpciones] ?? [])
                : (array) ($campo['options'] ?? []);
            $opcionesVista = [];
            foreach ($opcionesCampo as $claveOpcion => $opcion) {
                $valorOpcion = $tipo === 'select-data'
                    ? (string) ($opcion['id'] ?? '')
                    : (string) $claveOpcion;
                $etiquetaOpcion = $tipo === 'select-data'
                    ? (string) ($opcion['etiqueta'] ?? '')
                    : (string) $opcion;
                $opcionesVista[] = [
                    'valor' => $valorOpcion,
                    'etiqueta' => $etiquetaOpcion,
                    'atributoSeleccionada' => $valor === $valorOpcion ? 'selected' : '',
                ];
            }
            $camposVista[] = [
                'nombre' => $nombre,
                'configuracion' => $campo,
                'tipo' => $tipo,
                'valor' => $valor,
                'clase' => $tipo === 'textarea' ? ' is-full' : '',
                'etiqueta_vista' => (string) ($campo['label'] ?? $nombre),
                'marcaRequeridoVista' => !empty($campo['required']) ? ' *' : '',
                'atributoRequerido' => !empty($campo['required']) ? 'required' : '',
                'atributoMinimo' => isset($campo['min']) ? 'min="' . e($campo['min']) . '"' : '',
                'atributoMaximo' => isset($campo['max']) ? 'maxlength="' . (int) $campo['max'] . '"' : '',
                'opcionesVista' => $opcionesVista,
            ];
        }

        return $camposVista;
    }

    /**
     * Prepara las filas y columnas de la tabla especializada de compras y marketing.
     *
     * @param array<string, mixed> $interfaz
     * @param array<int, array<string, mixed>> $registros
     * @return array<string, mixed>
     */
    public static function prepararTablaCompras(array $interfaz, array $registros): array
    {
        $clave = (string) ($interfaz['clave'] ?? '');
        $esProductos = $clave === 'productos';
        $esMovimientos = $clave === 'movimientos-stock';
        $esAlertas = $clave === 'alertas-stock';
        $urlModuloInventario = url_interna('panel/inventario');

        if ($esProductos) {
            $columnas = ['sku' => 'SKU', 'producto' => 'Producto', 'categoria' => 'Categoría', 'precio' => 'Precio', 'stock' => 'Stock', 'estado' => 'Estado'];
        } elseif ($esMovimientos) {
            $columnas = ['codigo' => 'Código', 'producto' => 'Producto', 'movimiento' => 'Movimiento', 'cantidad' => 'Cantidad', 'responsable' => 'Responsable', 'motivo' => 'Motivo', 'fecha' => 'Fecha'];
        } elseif ($esAlertas) {
            $columnas = ['sku' => 'SKU', 'producto' => 'Producto', 'categoria' => 'Categoría', 'stock_actual' => 'Stock actual', 'stock_minimo' => 'Stock mínimo'];
        } else {
            $columnas = [];
            if ($registros !== []) {
                foreach (array_keys($registros[0]) as $columna) {
                    if ($columna !== 'id') {
                        $columnas[$columna] = ucfirst(str_replace('_', ' ', $columna));
                    }
                }
            }
        }

        $filas = [];
        foreach ($registros as $registro) {
            $celdas = [];
            foreach ($columnas as $claveColumna => $etiqueta) {
                $valor = $registro[$claveColumna]
                    ?? ($claveColumna === 'stock_actual' ? ($registro['existencias'] ?? '—') : '—');
                $esEstado = in_array($claveColumna, ['estado', 'movimiento'], true);
                $claseEstado = '';
                if ($esEstado) {
                    $normalizado = mb_strtolower((string) $valor);
                    $claseEstado = in_array($normalizado, ['publicado', 'entrada'], true)
                        ? 'estado--verde'
                        : 'estado--ambar';
                }
                $celdas[] = [
                    'valor' => (string) $valor,
                    'valor_precio' => $claveColumna === 'precio' ? formatear_dinero($valor) : '',
                    'es_estado' => $esEstado,
                    'clase_estado' => $claseEstado,
                    'atributoEstadoOculto' => $esEstado ? '' : 'hidden',
                    'atributoPrecioOculto' => $claveColumna === 'precio' ? '' : 'hidden',
                    'atributoValorOculto' => $esEstado || $claveColumna === 'precio' ? 'hidden' : '',
                ];
            }
            $filas[] = [
                'json' => json_encode($registro, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'celdas' => $celdas,
            ];
        }

        return [
            'es_productos' => $esProductos,
            'es_movimientos' => $esMovimientos,
            'es_alertas' => $esAlertas,
            'titulo_vista' => match (true) {
                $esProductos => 'Gestión de productos',
                $esMovimientos => 'Movimientos reales de stock',
                $esAlertas => 'Estado de existencias',
                default => 'Listado',
            },
            'atributoFiltrosProductoOculto' => $esProductos ? '' : 'hidden',
            'atributoColumnasAlertaOcultas' => $esAlertas ? '' : 'hidden',
            'atributoAccionesAlertaOcultas' => $esAlertas ? '' : 'hidden',
            'atributoPanelProveedorOculto' => $esAlertas ? '' : 'hidden',
            'atributoFilasTablaVaciasOculto' => $filas === [] ? '' : 'hidden',
            'urlModuloInventario' => $urlModuloInventario,
            'columnas' => $columnas,
            'filas' => $filas,
            'colspan' => max(1, count($columnas) + ($esAlertas ? 2 : 0)),
        ];
    }

    /**
     * Prepara columnas y etiquetas para las tablas genéricas de módulos.
     *
     * @param array<int, array<string, mixed>> $registros
     * @param array<int, string>|null $columnasVisibles
     * @return array<int, array{clave: string, etiqueta: string}>
     */
    public static function prepararColumnasModulo(array $registros, ?array $columnasVisibles): array
    {
        if ($registros === []) {
            return [];
        }

        $columnas = [];
        foreach (array_keys($registros[0]) as $columna) {
            $columna = (string) $columna;
            if (str_ends_with($columna, '_id')) {
                continue;
            }
            if ($columnasVisibles !== null && !in_array($columna, $columnasVisibles, true)) {
                continue;
            }

            $columnas[] = [
                'clave' => $columna,
                'etiqueta' => ucfirst(str_replace('_', ' ', $columna)),
            ];
        }

        return $columnas;
    }

    /**
     * Prepara las celdas y acciones de las tablas CRUD genéricas del panel.
     *
     * @param array<int, array<string, mixed>> $registros
     * @param array<int, array{clave:string,etiqueta:string}> $columnas
     * @param array<int, array{editar:string,desactivar:string}> $rutasRegistros
     * @param array<int, string> $modulosDesactivables
     * @return array<string, mixed>
     */
    public static function prepararTablaModulo(
        array $registros,
        array $columnas,
        array $rutasRegistros,
        string $modulo,
        bool $soloCrear,
        array $modulosDesactivables,
        bool $mostrarAcciones
    ): array {
        $filas = [];

        foreach ($registros as $registro) {
            $celdas = [];
            foreach ($columnas as $columna) {
                $clave = $columna['clave'];
                $valor = $registro[$clave] ?? '';
                $celdas[] = [
                    'valor' => (string) $valor,
                    'es_estado' => in_array($clave, ['activo', 'estado'], true),
                    'valor_precio' => $clave === 'total' ? formatear_dinero($valor) : '',
                    'clase_estado' => is_numeric($valor) && (int) $valor !== 1 ? 'admin-status--muted' : '',
                    'atributoEstadoOculto' => in_array($clave, ['activo', 'estado'], true) ? '' : 'hidden',
                    'atributoPrecioOculto' => $clave === 'total' ? '' : 'hidden',
                    'atributoValorOculto' => in_array($clave, ['activo', 'estado'], true) || $clave === 'total' ? 'hidden' : '',
                ];
            }

            $idRegistro = (int) ($registro['id'] ?? 0);
            $rutas = $rutasRegistros[$idRegistro] ?? ['editar' => '', 'desactivar' => ''];
            $filas[] = [
                'celdas' => $celdas,
                'mostrar_editar' => !$soloCrear,
                'atributoEditarOculto' => !$soloCrear ? '' : 'hidden',
                'url_editar' => $rutas['editar'],
                'mostrar_desactivar' => in_array($modulo, $modulosDesactivables, true),
                'atributoDesactivarOculto' => in_array($modulo, $modulosDesactivables, true) ? '' : 'hidden',
                'url_desactivar' => $rutas['desactivar'],
            ];
        }

        return [
            'columnas' => $columnas,
            'filas' => $filas,
            'hay_registros' => $registros !== [],
            'atributoColumnaInformacionOculta' => $registros === [] ? '' : 'hidden',
            'atributoTablaVaciaOculto' => $registros === [] ? '' : 'hidden',
            'atributoColumnaAccionesOculta' => $mostrarAcciones ? '' : 'hidden',
            'colspan_vacio' => max(1, count($columnas)) + ($mostrarAcciones ? 1 : 0),
            'mostrar_acciones' => $mostrarAcciones,
            'cantidad_registros' => count($registros),
        ];
    }

    /**
     * Prepara las direcciones usadas por las tablas y formularios del módulo.
     *
     * @param array<int, array<string, mixed>> $registros
     * @param array<string, mixed>|null $edicion
     * @return array<string, mixed>
     */
    public static function prepararRutasModulo(
        string $modulo,
        array $registros,
        ?array $edicion,
        bool $editando
    ): array {
        $urlModuloPanel = url_interna('panel/' . $modulo);
        $urlFormularioPanel = $editando
            ? url_interna('panel/' . $modulo . '/' . (int) $edicion['id'])
            : $urlModuloPanel;
        $rutasRegistrosPanel = [];

        foreach ($registros as $registro) {
            $idRegistro = (int) $registro['id'];
            $rutasRegistrosPanel[$idRegistro] = [
                'editar' => $urlModuloPanel . '?edit=' . $idRegistro,
                'desactivar' => url_interna('panel/' . $modulo . '/' . $idRegistro . '/deactivate'),
            ];
        }

        return [
            'urlModuloPanel' => $urlModuloPanel,
            'urlFormularioPanel' => $urlFormularioPanel,
            'rutasRegistrosPanel' => $rutasRegistrosPanel,
        ];
    }
}
