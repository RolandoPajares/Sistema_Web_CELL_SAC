<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Administracion;

/** Prepara los datos compartidos por los listados administrativos de catálogos. */
final class PresentadorCrudAdministrativo
{
    /**
     * Prepara el listado, los filtros, el resumen y las marcas del formulario de productos.
     *
     * @param array<int, array<string, mixed>> $productos
     * @param array<int, array<string, mixed>> $categorias
     * @param array<string, mixed> $resumen
     * @param array<string, mixed>|null $edicion
     * @param array<int, string> $marcasPermitidas
     * @return array<string, mixed>
     */
    public static function presentarProductos(
        array $productos,
        array $categorias,
        array $resumen,
        ?array $edicion,
        array $marcasPermitidas
    ): array {
        $productos = array_map(static function (array $producto): array {
            $producto['imagen_admin_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['icono_admin_vista'] = icono_categoria_producto((string) ($producto['categoria'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_admin_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_admin_vista'] === '' ? '' : 'hidden';
            $producto['estado_vista'] = (int) ($producto['activo'] ?? 0) === 1 ? 'Activo' : 'Inactivo';
            $producto['clase_estado_vista'] = (int) ($producto['activo'] ?? 0) === 1 ? '' : 'admin-status--muted';
            $producto['atributoDesactivarOculto'] = (int) ($producto['activo'] ?? 0) === 1 ? '' : 'hidden';
            $producto['codigo_admin_vista'] = str_pad((string) $producto['id'], 5, '0', STR_PAD_LEFT);
            $producto['fecha_creacion_vista'] = date('d/m/Y', strtotime((string) $producto['creado_en']));

            return $producto;
        }, $productos);
        $categoriasFiltro = array_values(array_unique(array_filter(array_merge(
            array_map(static fn (array $categoria): string => (string) $categoria['nombre'], $categorias),
            array_map(static fn (array $producto): string => (string) $producto['categoria'], $productos)
        ), static fn (string $nombre): bool => trim($nombre) !== '')));
        sort($categoriasFiltro, SORT_NATURAL | SORT_FLAG_CASE);

        $marcaEdicionNormalizada = mb_strtolower((string) ($edicion['marca'] ?? ''));
        $marcasPermitidasVista = array_map(static function (string $marca) use ($marcaEdicionNormalizada): array {
            return [
                'valor' => $marca,
                'seleccionada' => $marcaEdicionNormalizada === mb_strtolower($marca),
            ];
        }, $marcasPermitidas);

        return [
            'productos' => $productos,
            'categoriasFiltro' => $categoriasFiltro,
            'marcasPermitidasVista' => $marcasPermitidasVista,
            'tarjetasKpi' => [
                [
                    'etiqueta' => 'Total de productos',
                    'valor' => (string) $resumen['total'],
                    'detalle' => 'Registros en MySQL',
                    'icono' => 'bi-box-seam',
                    'tono' => 'violeta',
                ],
                [
                    'etiqueta' => 'Productos activos',
                    'valor' => (string) $resumen['activos'],
                    'detalle' => 'Visibles en catálogo',
                    'icono' => 'bi-file-earmark-check',
                    'tono' => 'verde',
                ],
                [
                    'etiqueta' => 'Stock bajo',
                    'valor' => (string) $resumen['stock_bajo'],
                    'detalle' => '8 unidades o menos',
                    'icono' => 'bi-exclamation-triangle',
                    'tono' => 'rojo',
                ],
                [
                    'etiqueta' => 'Productos inactivos',
                    'valor' => (string) $resumen['inactivos'],
                    'detalle' => 'No visibles en catálogo',
                    'icono' => 'bi-eye-slash',
                    'tono' => 'azul',
                ],
            ],
        ];
    }

    /**
     * Prepara la tabla, resumen y formulario de categorías.
     *
     * @param array<int, array<string, mixed>> $registros
     * @param array<string, mixed> $resumen
     * @param array<string, mixed>|null $edicion
     * @return array<string, mixed>
     */
    public static function presentarCategorias(array $registros, array $resumen, ?array $edicion): array
    {
        $columnas = [
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'productos_asociados' => 'Productos asociados',
            'creado_en' => 'Fecha de creación',
            'activo' => 'Estado',
        ];
        $camposCategoria = [
            'nombre' => ['label' => 'Nombre', 'required' => true, 'max' => 120],
            'descripcion' => ['label' => 'Descripción', 'type' => 'textarea', 'max' => 500],
        ];
        $camposFormularioVista = self::prepararCamposFormularioVista($camposCategoria, $edicion);
        $registros = array_map(static function (array $categoria): array {
            $fecha = strtotime((string) $categoria['creado_en']);
            $categoria['creado_en_iso_vista'] = date('Y-m-d', $fecha === false ? 0 : $fecha);
            $categoria['creado_en_vista'] = date('d/m/Y', $fecha === false ? 0 : $fecha);
            $categoria['url_editar_vista'] = url_interna('admin/categories/' . (int) $categoria['id'] . '/edit');
            $categoria['url_desactivar_vista'] = url_interna('admin/categories/' . (int) $categoria['id'] . '/deactivate');
            $categoria['atributoDesactivarOculto'] = (int) ($categoria['activo'] ?? 0) === 1 ? '' : 'hidden';

            return $categoria;
        }, $registros);
        $registros = array_map(static function (array $categoria) use ($columnas): array {
            $activo = (int) ($categoria['activo'] ?? 0) === 1;
            $categoria['estado_vista'] = $activo ? 'Activo' : 'Inactivo';
            $categoria['estado_filtro_vista'] = $activo ? 'active' : 'inactive';
            $categoria['estado_clase_vista'] = $activo ? '' : 'admin-status--muted';
            $categoria['mostrar_desactivar_vista'] = $activo;
            $categoria['celdas_vista'] = [];

            foreach ($columnas as $clave => $etiqueta) {
                $valor = match ($clave) {
                    'activo' => $categoria['estado_vista'],
                    'creado_en' => (string) $categoria['creado_en_vista'],
                    default => (string) (($categoria[$clave] ?? '') !== '' ? $categoria[$clave] : '—'),
                };
                $categoria['celdas_vista'][] = [
                    'valor' => $valor,
                    'es_estado' => $clave === 'activo',
                    'clase_estado' => $clave === 'activo' ? $categoria['estado_clase_vista'] : '',
                    'clase_html_vista' => $clave === 'activo'
                        ? 'admin-status ' . $categoria['estado_clase_vista']
                        : '',
                ];
            }

            return $categoria;
        }, $registros);
        $categoriasConProductos = array_values(array_filter(
            $registros,
            static fn (array $categoria): bool => (int) $categoria['productos_asociados'] > 0
        ));
        $categoriasSinProductos = array_values(array_filter(
            $registros,
            static fn (array $categoria): bool => (int) $categoria['productos_asociados'] === 0
        ));
        usort($categoriasConProductos, static fn (array $a, array $b): int => (int) $b['productos_asociados'] <=> (int) $a['productos_asociados']);
        $totalProductosAsociados = array_sum(array_map(
            static fn (array $categoria): int => (int) $categoria['productos_asociados'],
            $registros
        ));
        $categoriasConProductos = array_map(static function (array $categoria) use ($totalProductosAsociados): array {
            $cantidad = (int) $categoria['productos_asociados'];
            $categoria['cantidad_vista'] = $cantidad;
            $categoria['porcentaje_vista'] = $totalProductosAsociados > 0
                ? (int) round($cantidad * 100 / $totalProductosAsociados)
                : 0;

            return $categoria;
        }, $categoriasConProductos);

        return [
            'tituloModuloMinuscula' => mb_strtolower('Categorías'),
            'urlFormularioCrud' => $edicion !== null
                ? url_interna('admin/categories/' . (int) $edicion['id'])
                : url_interna('admin/categories'),
            'registros' => $registros,
            'atributoRegistrosVaciosOculto' => $registros === [] ? '' : 'hidden',
            'atributoDistribucionVaciaOculto' => $categoriasConProductos === [] ? '' : 'hidden',
            'atributoDistribucionListaOculto' => $categoriasConProductos !== [] ? '' : 'hidden',
            'atributoSinProductosVacioOculto' => $categoriasSinProductos === [] ? '' : 'hidden',
            'atributoSinProductosListaOculto' => $categoriasSinProductos !== [] ? '' : 'hidden',
            'resumen' => $resumen,
            'tarjetasKpi' => [
                ['etiqueta' => 'Total de categorías', 'valor' => (string) $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-tags', 'tono' => 'azul'],
                ['etiqueta' => 'Activas', 'valor' => (string) $resumen['activas'], 'detalle' => 'Disponibles para productos', 'icono' => 'bi-check-circle', 'tono' => 'verde'],
                ['etiqueta' => 'Con productos', 'valor' => (string) $resumen['con_productos'], 'detalle' => 'Asociaciones activas', 'icono' => 'bi-box-seam', 'tono' => 'violeta'],
                ['etiqueta' => 'Sin productos', 'valor' => (string) $resumen['sin_productos'], 'detalle' => 'Sin asociaciones activas', 'icono' => 'bi-slash-square', 'tono' => 'rojo'],
            ],
            'categoriasConProductos' => $categoriasConProductos,
            'categoriasSinProductos' => $categoriasSinProductos,
            'totalProductosAsociados' => $totalProductosAsociados,
            'columnas' => $columnas,
            'campos' => $camposCategoria,
            'camposFormularioVista' => $camposFormularioVista,
        ];
    }

    /**
     * Prepara la tabla, resumen y formulario de clientes.
     *
     * @param array<int, array<string, mixed>> $registros
     * @param array<string, mixed> $resumen
     * @param array<string, mixed>|null $edicion
     * @return array<string, mixed>
     */
    public static function presentarClientes(array $registros, array $resumen, ?array $edicion): array
    {
        $registros = array_map(static function (array $cliente): array {
            $fecha = strtotime((string) ($cliente['creado_en'] ?? ''));
            $activo = (int) ($cliente['activo'] ?? 0) === 1;
            $tipo = (string) ($cliente['tipo'] ?? '');
            $cliente['creado_en_vista'] = date('d/m/Y', $fecha === false ? 0 : $fecha);
            $cliente['tipo_vista'] = ucfirst($tipo);
            $cliente['tipo_clase_vista'] = $tipo === 'mayorista' ? 'mayorista' : 'minorista';
            $cliente['telefono_vista'] = (string) (($cliente['telefono'] ?? '') ?: '—');
            $cliente['estado_vista'] = $activo ? 'Activo' : 'Inactivo';
            $cliente['estado_clase_vista'] = $activo ? '' : 'admin-status--danger';
            $cliente['estado_filtro_vista'] = $activo ? 'active' : 'inactive';
            $cliente['mostrar_desactivar_vista'] = $activo;
            $cliente['atributoDesactivarOculto'] = $activo ? '' : 'hidden';
            $cliente['atributoEmpresaOculta'] = trim((string) ($cliente['empresa'] ?? '')) !== '' ? '' : 'hidden';
            $cliente['url_editar_vista'] = url_interna('admin/customers/' . (int) $cliente['id'] . '/edit');
            $cliente['url_desactivar_vista'] = url_interna('admin/customers/' . (int) $cliente['id'] . '/deactivate');

            return $cliente;
        }, $registros);
        $clientesConPedidos = array_values(array_filter(
            $registros,
            static fn (array $cliente): bool => (int) $cliente['pedidos'] > 0
        ));
        usort($clientesConPedidos, static fn (array $a, array $b): int => (int) $b['pedidos'] <=> (int) $a['pedidos']);
        $clientesConPedidosDestacados = array_map(
            static function (array $cliente, int $indice): array {
                $cliente['puesto_vista'] = $indice + 1;
                $cliente['nombre_ranking_vista'] = (string) (($cliente['empresa'] ?? '') ?: ($cliente['contacto'] ?? ''));
                $cantidadPedidos = (int) ($cliente['pedidos'] ?? 0);
                $cliente['cantidad_pedidos_vista'] = $cantidadPedidos . ($cantidadPedidos === 1 ? ' pedido' : ' pedidos');

                return $cliente;
            },
            array_slice($clientesConPedidos, 0, 5),
            array_keys(array_slice($clientesConPedidos, 0, 5))
        );

        $camposCliente = [
            'tipo' => ['label' => 'Tipo', 'type' => 'select', 'required' => true, 'options' => ['minorista' => 'Minorista', 'mayorista' => 'Mayorista']],
            'documento' => ['label' => 'DNI o RUC', 'required' => true, 'max' => 11],
            'empresa' => ['label' => 'Empresa', 'max' => 160],
            'contacto' => ['label' => 'Contacto', 'required' => true, 'max' => 160],
            'correo' => ['label' => 'Correo', 'type' => 'email', 'required' => true, 'max' => 160],
            'telefono' => ['label' => 'Teléfono', 'max' => 20],
            'ciudad' => ['label' => 'Ciudad', 'max' => 80],
        ];
        $camposFormularioVista = self::prepararCamposFormularioVista($camposCliente, $edicion);

        return [
            'tituloModuloMinuscula' => mb_strtolower('Clientes'),
            'urlFormularioCrud' => $edicion !== null
                ? url_interna('admin/customers/' . (int) $edicion['id'])
                : url_interna('admin/customers'),
            'registros' => $registros,
            'atributoRegistrosVaciosOculto' => $registros === [] ? '' : 'hidden',
            'atributoRankingVacioOculto' => $clientesConPedidos === [] ? '' : 'hidden',
            'atributoRankingListaOculto' => $clientesConPedidos !== [] ? '' : 'hidden',
            'resumen' => $resumen,
            'tarjetasKpi' => [
                ['etiqueta' => 'Total de clientes', 'valor' => (string) $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-people', 'tono' => 'azul'],
                ['etiqueta' => 'Minoristas', 'valor' => (string) $resumen['minoristas'], 'detalle' => 'Clientes activos', 'icono' => 'bi-bag-check', 'tono' => 'verde'],
                ['etiqueta' => 'Mayoristas', 'valor' => (string) $resumen['mayoristas'], 'detalle' => 'Clientes activos', 'icono' => 'bi-buildings', 'tono' => 'violeta'],
                ['etiqueta' => 'Inactivos', 'valor' => (string) $resumen['inactivos'], 'detalle' => 'Registros conservados', 'icono' => 'bi-person-x', 'tono' => 'rojo'],
            ],
            'clientesConPedidos' => $clientesConPedidos,
            'clientesConPedidosDestacados' => $clientesConPedidosDestacados,
            'camposFormularioVista' => $camposFormularioVista,
            'columnas' => [
                'contacto' => 'Cliente',
                'documento' => 'Documento',
                'tipo' => 'Tipo',
                'telefono' => 'Teléfono',
                'correo' => 'Correo',
                'pedidos' => 'Pedidos',
                'activo' => 'Estado',
            ],
            'campos' => $camposCliente,
        ];
    }

    /**
     * Prepara filas, filtros geográficos y métricas de proveedores.
     *
     * @param array<int, array<string, mixed>> $registros
     * @param array<string, mixed> $resumen
     * @param array<string, mixed>|null $edicion
     * @return array<string, mixed>
     */
    public static function presentarProveedores(array $registros, array $resumen, ?array $edicion): array
    {
        $registros = array_map(static function (array $proveedor): array {
            $fecha = strtotime((string) ($proveedor['creado_en'] ?? ''));
            $ciudad = trim((string) ($proveedor['ciudad'] ?? ''));
            $proveedor['fecha_registro_iso_vista'] = date('Y-m-d', $fecha === false ? 0 : $fecha);
            $proveedor['fecha_registro_vista'] = date('d/m/Y', $fecha === false ? 0 : $fecha);
            $proveedor['creado_en_vista'] = $proveedor['fecha_registro_vista'];
            $proveedor['inicial_vista'] = mb_strtoupper(mb_substr((string) $proveedor['nombre'], 0, 1));
            $proveedor['ciudad_vista'] = $ciudad !== '' ? $ciudad : '—';
            $proveedor['ciudad_filtro_vista'] = mb_strtolower($ciudad);
            $activo = (int) ($proveedor['activo'] ?? 0) === 1;
            $proveedor['estado_vista'] = $activo ? 'Activo' : 'Inactivo';
            $proveedor['estado_clase_vista'] = $activo ? '' : 'admin-status--muted';
            $proveedor['estado_filtro_vista'] = $activo ? 'active' : 'inactive';
            $proveedor['contacto_vista'] = (string) (($proveedor['contacto'] ?? '') ?: '—');
            $proveedor['telefono_vista'] = (string) (($proveedor['telefono'] ?? '') ?: '—');
            $proveedor['url_editar_vista'] = url_interna('admin/suppliers/' . (int) $proveedor['id'] . '/edit');
            $proveedor['url_desactivar_vista'] = url_interna('admin/suppliers/' . (int) $proveedor['id'] . '/deactivate');
            $proveedor['atributoDesactivarOculto'] = (int) ($proveedor['activo'] ?? 0) === 1 ? '' : 'hidden';

            return $proveedor;
        }, $registros);
        $proveedoresDelMes = array_values(array_filter($registros, static function (array $proveedor): bool {
            $fecha = strtotime((string) ($proveedor['creado_en'] ?? ''));

            return $fecha !== false && date('Y-m', $fecha) === date('Y-m');
        }));
        $proveedoresRecientes = $registros;
        usort($proveedoresRecientes, static fn (array $a, array $b): int => strcmp((string) ($b['creado_en'] ?? ''), (string) ($a['creado_en'] ?? '')));
        $proveedoresPorCiudad = [];
        foreach ($registros as $proveedor) {
            $ciudad = trim((string) ($proveedor['ciudad'] ?? ''));
            if ($ciudad !== '') {
                $proveedoresPorCiudad[$ciudad] = ($proveedoresPorCiudad[$ciudad] ?? 0) + 1;
            }
        }

        arsort($proveedoresPorCiudad);
        $ciudadMaxima = max($proveedoresPorCiudad ?: [0]);
        $ciudadesProveedor = array_keys($proveedoresPorCiudad);
        $opcionesCiudadProveedor = array_map(static fn (string $ciudad): array => [
            'valor' => mb_strtolower($ciudad),
            'etiqueta' => $ciudad,
        ], $ciudadesProveedor);
        $porcentajesCiudad = [];
        foreach ($proveedoresPorCiudad as $ciudad => $cantidad) {
            $porcentajesCiudad[$ciudad] = $ciudadMaxima > 0
                ? round($cantidad / $ciudadMaxima * 100)
                : 0;
        }
        $ciudadesDestacadasVista = [];
        foreach (array_slice($proveedoresPorCiudad, 0, 8, true) as $ciudad => $cantidad) {
            $ciudadesDestacadasVista[] = [
                'nombre' => (string) $ciudad,
                'cantidad' => (int) $cantidad,
                'porcentaje' => (int) ($porcentajesCiudad[$ciudad] ?? 0),
            ];
        }
        $camposProveedor = [
            'nombre' => ['label' => 'Nombre', 'required' => true, 'max' => 160],
            'ruc' => ['label' => 'RUC', 'required' => true, 'max' => 11],
            'correo' => ['label' => 'Correo', 'type' => 'email', 'required' => true, 'max' => 160],
            'telefono' => ['label' => 'Teléfono', 'max' => 20],
            'ciudad' => ['label' => 'Ciudad', 'max' => 80],
        ];
        $camposFormularioVista = self::prepararCamposFormularioVista($camposProveedor, $edicion);

        return [
            'tituloModuloMinuscula' => mb_strtolower('Proveedores'),
            'urlFormularioCrud' => $edicion !== null
                ? url_interna('admin/suppliers/' . (int) $edicion['id'])
                : url_interna('admin/suppliers'),
            'registros' => $registros,
            'atributoRegistrosVaciosOculto' => $registros === [] ? '' : 'hidden',
            'atributoProveedoresRecientesVacioOculto' => $proveedoresRecientes === [] ? '' : 'hidden',
            'atributoProveedoresRecientesListaOculto' => $proveedoresRecientes !== [] ? '' : 'hidden',
            'atributoCiudadesVacioOculto' => $proveedoresPorCiudad === [] ? '' : 'hidden',
            'atributoCiudadesListaOculto' => $proveedoresPorCiudad !== [] ? '' : 'hidden',
            'resumen' => $resumen,
            'proveedoresDelMes' => $proveedoresDelMes,
            'proveedoresRecientes' => $proveedoresRecientes,
            'proveedoresPorCiudad' => $proveedoresPorCiudad,
            'ciudadMaxima' => $ciudadMaxima,
            'porcentajesCiudad' => $porcentajesCiudad,
            'ciudadesProveedor' => $ciudadesProveedor,
            'opcionesCiudadProveedor' => $opcionesCiudadProveedor,
            'proveedoresRecientesDestacados' => array_slice($proveedoresRecientes, 0, 5),
            'ciudadesDestacadasVista' => $ciudadesDestacadasVista,
            'camposFormularioVista' => $camposFormularioVista,
            'tarjetasKpi' => [
                ['etiqueta' => 'Total proveedores', 'valor' => (string) $resumen['total'], 'detalle' => 'Registros en MySQL', 'icono' => 'bi-truck', 'tono' => 'azul'],
                ['etiqueta' => 'Activos', 'valor' => (string) $resumen['activos'], 'detalle' => 'Proveedores disponibles', 'icono' => 'bi-people', 'tono' => 'verde'],
                ['etiqueta' => 'Nuevos este mes', 'valor' => (string) count($proveedoresDelMes), 'detalle' => 'Según fecha de registro', 'icono' => 'bi-person-plus', 'tono' => 'violeta'],
                ['etiqueta' => 'Ciudades', 'valor' => (string) $resumen['ciudades'], 'detalle' => 'Con proveedores registrados', 'icono' => 'bi-geo-alt', 'tono' => 'rojo'],
            ],
            'columnas' => [
                'nombre' => 'Proveedor',
                'ruc' => 'RUC',
                'telefono' => 'Teléfono',
                'correo' => 'Correo',
                'ciudad' => 'Ciudad',
                'creado_en' => 'Registro',
                'activo' => 'Estado',
            ],
            'campos' => $camposProveedor,
        ];
    }

    /**
     * Prepara valores y atributos de los campos de un formulario administrativo.
     *
     * @param array<string, array<string, mixed>> $campos
     * @param array<string, mixed>|null $edicion
     * @return array<int, array<string, mixed>>
     */
    private static function prepararCamposFormularioVista(array $campos, ?array $edicion): array
    {
        $camposVista = [];

        foreach ($campos as $nombre => $campo) {
            $tipo = (string) ($campo['type'] ?? 'text');
            $valor = (string) ($edicion[$nombre] ?? '');
            $opciones = [];
            foreach ((array) ($campo['options'] ?? []) as $clave => $etiqueta) {
                $opciones[] = [
                    'valor' => (string) $clave,
                    'etiqueta' => (string) $etiqueta,
                    'seleccionada' => $valor === (string) $clave,
                    'atributoSeleccionada' => $valor === (string) $clave ? 'selected' : '',
                ];
            }

            $esRequerido = !empty($campo['required']);
            $camposVista[] = [
                'nombre' => (string) $nombre,
                'etiqueta' => (string) ($campo['label'] ?? $nombre),
                'tipo' => $tipo,
                'valor' => $valor,
                'clase' => $tipo === 'textarea' ? ' is-full' : '',
                'maximo' => (int) ($campo['max'] ?? ($tipo === 'textarea' ? 500 : 160)),
                'filas' => (int) ($campo['rows'] ?? 4),
                'marcadorRequerido' => $esRequerido ? ' *' : '',
                'atributoRequerido' => $esRequerido ? 'required' : '',
                'opciones' => $opciones,
            ];
        }

        return $camposVista;
    }

    /**
     * Renderiza los controles específicos de cada tipo de campo como fragmentos reutilizables.
     *
     * @param array<int, array<string, mixed>> $campos
     * @param callable(string, array<string, mixed>): string $renderizar
     * @return array<int, array<string, mixed>>
     */
    public static function presentarControlesFormulario(array $campos, callable $renderizar): array
    {
        foreach ($campos as &$campo) {
            $vistaControl = match ($campo['tipo'] ?? 'text') {
                'textarea' => 'roles.internos.administrador.crud.controles.textarea',
                'select' => 'roles.internos.administrador.crud.controles.select',
                default => 'roles.internos.administrador.crud.controles.texto',
            };
            $campo['controlHtml'] = $renderizar($vistaControl, ['campo' => $campo]);
        }
        unset($campo);

        return $campos;
    }
}
