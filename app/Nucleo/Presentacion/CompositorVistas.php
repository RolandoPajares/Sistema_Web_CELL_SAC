<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion;

use App\Nucleo\Http\ContextoSolicitud;
use App\Nucleo\Presentacion\Panel\PresentadorPanelRol;
use App\Servicios\Campanias\CampaniaServicio;
use App\Soporte\Autorizacion\AccesoRol;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\Soporte\GeneradorUrl;
use App\Soporte\Presentacion\CatalogoEstilos;
use App\Soporte\Registros\RegistradorArchivo;
use App\Soporte\Seguridad\GestorTokenCsrf;
use App\Soporte\Sesion\GestorSesion;

/** Prepara los datos compartidos por las plantillas y los componentes visuales. */
final class CompositorVistas
{
    public function __construct(
        private ContextoSolicitud $contextoSolicitud,
        private GestorSesion $sesion,
        private GestorTokenCsrf $csrf,
        private CampaniaServicio $campanias,
        private RegistradorArchivo $registrador,
        private RepositorioConfiguracion $configuracion,
        private GeneradorUrl $generadorUrl,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function datos(string $plantilla, array $datosPagina = []): array
    {
        $solicitud = $this->contextoSolicitud->actual();
        $usuario = $this->sesion->obtener('user');
        $usuario = is_array($usuario) ? $usuario : null;
        $rolActual = AccesoRol::normalizarRol((string) ($usuario['rol'] ?? 'visitante'));
        $campanias = $plantilla === 'aplicacion' ? $this->campaniasActivas() : [];
        $consulta = $solicitud->consulta('q', '');
        $categoria = $solicitud->consulta('category', $solicitud->consulta('categoria', 'todos'));
        $moduloAccionTablero = $this->moduloAccionTablero($rolActual);
        $rutaActual = trim($solicitud->ruta(), '/') ?: 'admin';
        $rutaActualInterna = trim($solicitud->ruta(), '/');
        $rutaCampanias = $rolActual === 'administrador' ? 'admin/campaigns' : 'panel/campanias';
        $direccionEmpresa = (string) $this->configuracion->obtener('app.address', '');
        $navegacionInterna = $this->navegacionInterna($rolActual, $rutaActualInterna);
        $slugsNavegacionInterna = array_column($navegacionInterna, 'slug');
        $navegacionPublica = $this->navegacionPublica($solicitud->ruta());

        $datosComunes = [
            'usuario' => $usuario,
            'usuarioActual' => $usuario ?? [],
            'inicialesUsuarioVista' => mb_strtoupper(mb_substr((string) ($usuario['nombre'] ?? 'U'), 0, 2)),
            'nombreUsuarioCortoVista' => explode(' ', (string) ($usuario['nombre'] ?? 'Usuario'))[0],
            'fechaHoyEtiqueta' => date('d M Y'),
            'fechaHoyIso' => date('Y-m-d'),
            'rolActual' => $rolActual,
            'etiquetaRol' => AccesoRol::etiqueta($rolActual),
            'esAdministrador' => $rolActual === 'administrador',
            'atributoEnlaceAdministradorOculto' => $rolActual === 'administrador' ? '' : 'hidden',
            'etiquetaCuentaEncabezado' => $rolActual === 'administrador' ? 'Panel' : 'Mi cuenta',
            'atributoCarritoOculto' => $usuario !== null && $rolActual !== 'cliente_minorista' ? 'hidden' : '',
            'atributoCuentaOculto' => $usuario !== null ? '' : 'hidden',
            'atributoInvitadoOculto' => $usuario === null ? '' : 'hidden',
            'rutaSolicitud' => $solicitud->ruta(),
            'consultaEncabezado' => is_scalar($consulta) ? trim((string) $consulta) : '',
            'categoriaPublicidad' => is_scalar($categoria) ? (string) $categoria : 'todos',
            'carritoUnidades' => array_sum((array) $this->sesion->obtener('cart', [])),
            'navegacion' => $navegacionInterna,
            'navegacionSecundaria' => array_slice($navegacionInterna, 1, 6),
            'destinoInicioPanel' => $rolActual === 'administrador' ? 'admin' : 'panel',
            'mostrarEnlaceRecomendador' => !in_array('recomendador', $slugsNavegacionInterna, true),
            'mostrarEnlaceComparador' => !in_array('comparador', $slugsNavegacionInterna, true),
            'mostrarEnlaceAsistente' => !in_array('asistente', $slugsNavegacionInterna, true),
            'navegacionAdministrativa' => $this->navegacionAdministrativa($rutaActual),
            'cuentaAdministrativaActiva' => $rutaActual === 'admin/account',
            'enlacesEncabezado' => $navegacionPublica,
            'busquedaEncabezado' => is_scalar($consulta) ? trim((string) $consulta) : '',
            'rolEncabezado' => $rolActual,
            'consultasRapidas' => [
                'Resumen del negocio',
                'Stock bajo',
                'Ventas',
                'Pedidos pendientes',
                'Reportes disponibles',
            ],
            'moduloAccionTablero' => $moduloAccionTablero,
            'destinoModuloAccion' => AccesoRol::destino($rolActual, $moduloAccionTablero),
            'rutasAlertasTablero' => $this->rutasAlertasTablero($rolActual, $moduloAccionTablero),
            'campanias' => $campanias,
            'campaniaEmergente' => $campanias['emergente'] ?? null,
            'tokenCsrf' => $this->csrf->token(),
            'nombreAplicacion' => (string) $this->configuracion->obtener('app.name', 'MD Technology'),
            'direccionEmpresa' => $direccionEmpresa,
            'direccion' => $direccionEmpresa,
            'comoLlegar' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($direccionEmpresa),
            'versionesEstilosContextuales' => [
                'assets/css/publico/inicio.css' => '20260929-3',
                'assets/css/estructura/sitio.css' => '20260928-1',
                'assets/css/publico/catalogo.css' => '20260929-4',
                'assets/css/publico/producto.css' => '20260929-2',
            ],
            'rutaCampanias' => $rutaCampanias,
            'opcionesMenuPublicidadInteligente' => $this->opcionesMenuPublicidadInteligente($rutaCampanias),
            'enlacesComercioInteligente' => array_values(array_filter([
                [
                    'destino' => 'smart/recommend',
                    'icono' => 'bi-lightbulb',
                    'etiqueta' => 'Recomendador',
                    'mostrar' => !in_array('recomendador', $slugsNavegacionInterna, true),
                ],
                [
                    'destino' => 'smart/compare',
                    'icono' => 'bi-columns-gap',
                    'etiqueta' => 'Comparador',
                    'mostrar' => !in_array('comparador', $slugsNavegacionInterna, true),
                ],
                [
                    'destino' => 'smart/assistant',
                    'icono' => 'bi-robot',
                    'etiqueta' => 'MD Assistant',
                    'mostrar' => !in_array('asistente', $slugsNavegacionInterna, true),
                ],
            ], static fn (array $enlace): bool => $enlace['mostrar'])),
            'scriptsFinalesVista' => $this->scriptsFinalesVista($plantilla, $datosPagina, $rolActual),
        ];

        $datosComunes['atributoErrorOculto'] = empty($datosPagina['error']) ? 'hidden' : '';
        $datosComunes['atributoExitoOculto'] = empty($datosPagina['exito']) ? 'hidden' : '';
        $datosComunes['atributoMensajeOculto'] = empty($datosPagina['mensaje']) ? 'hidden' : '';

        if ($plantilla === 'aplicacion') {
            $datosComunes = array_merge(
                $datosComunes,
                $this->datosPublicidad(
                    $solicitud->ruta(),
                    $usuario,
                    $datosComunes['campaniaEmergente'],
                    $datosPagina,
                    $datosComunes['categoriaPublicidad'],
                )
            );
        }

        if (in_array($plantilla, ['interno', 'administrador'], true)) {
            $contextoEstilos = $plantilla === 'administrador' ? 'administrador' : 'interno';
            $rutaEstilos = '/' . ($plantilla === 'administrador' ? $rutaActual : $rutaActualInterna);
            $moduloEstilos = (string) ($datosPagina['modulo'] ?? '');

            $datosComunes['clasesCuerpo'] = $plantilla === 'administrador'
                ? CatalogoEstilos::clasesCuerpo($rutaEstilos, $rolActual)
                : CatalogoEstilos::clasesCuerpo($rutaEstilos, $rolActual, $moduloEstilos);
            $datosComunes['estilosContextuales'] = $plantilla === 'administrador'
                ? CatalogoEstilos::para($rutaEstilos, $contextoEstilos, $rolActual)
                : CatalogoEstilos::para($rutaEstilos, $contextoEstilos, $rolActual, $moduloEstilos);
        }

        if ($plantilla === 'aplicacion') {
            $moduloEstilos = (string) ($datosPagina['modulo'] ?? '');
            $datosComunes['clasesCuerpo'] = CatalogoEstilos::clasesCuerpo(
                $solicitud->ruta(),
                $rolActual,
                $moduloEstilos
            );
            $datosComunes['estilosContextuales'] = CatalogoEstilos::para(
                $solicitud->ruta(),
                'aplicacion',
                $rolActual,
                $moduloEstilos
            );
        }

        if ($plantilla === 'interno') {
            $datosComunes = array_merge(
                $datosComunes,
                PresentadorPanelRol::prepararDatosTableroCompras($datosPagina, $rolActual)
            );
        }

        $versionesEstilos = (array) $datosComunes['versionesEstilosContextuales'];
        $datosComunes['estilosContextualesVista'] = array_map(
            fn (string $archivo): string => $this->generadorUrl->urlRecursoEstatico(
                $archivo . '?v=' . ($versionesEstilos[$archivo] ?? '20260927-8')
            ),
            (array) ($datosComunes['estilosContextuales'] ?? [])
        );
        unset($datosComunes['versionesEstilosContextuales']);

        return $datosComunes;
    }

    /**
     * @return array<int, array{slug:string,label:string,icon:string,destino:string}>
     */
    private function navegacionConDestinos(string $rol): array
    {
        $navegacion = AccesoRol::navegacion($rol);
        foreach ($navegacion as &$elemento) {
            $elemento['destino'] = AccesoRol::destino($rol, $elemento['slug']);
        }
        unset($elemento);

        return $navegacion;
    }

    /**
     * @return array<int, array{slug:string,label:string,icon:string,destino:string,activo:bool,etiqueta:string}>
     */
    private function navegacionAdministrativa(string $rutaActual): array
    {
        $navegacion = $this->navegacionConDestinos('administrador');

        foreach ($navegacion as &$elemento) {
            $destino = $elemento['destino'];
            $elemento['activo'] = $destino === 'admin'
                ? $rutaActual === 'admin'
                : str_starts_with($rutaActual, $destino);
            $elemento['etiqueta'] = match ($elemento['slug']) {
                'dashboard' => 'Dashboard',
                'publicidad' => 'Campañas',
                default => $elemento['label'],
            };
        }
        unset($elemento);

        return $navegacion;
    }

    /**
     * @return array<int, array{slug:string,label:string,icon:string,destino:string,activo:bool}>
     */
    private function navegacionInterna(string $rol, string $rutaActual): array
    {
        $navegacion = $this->navegacionConDestinos($rol);

        foreach ($navegacion as &$elemento) {
            $elemento['activo'] = str_contains('/' . $rutaActual, '/' . $elemento['destino']);
        }
        unset($elemento);

        return $navegacion;
    }

    /**
     * @return array<int, array{etiqueta:string,destino:string,activo:bool}>
     */
    private function navegacionPublica(string $ruta): array
    {
        $enlaces = [
            [
                'etiqueta' => 'Inicio',
                'destino' => '',
                'activo' => $ruta === '/' || str_ends_with($ruta, '/public/'),
            ],
            [
                'etiqueta' => 'Catálogo',
                'destino' => 'catalog',
                'activo' => str_contains($ruta, '/catalog') || str_contains($ruta, '/products/'),
            ],
            [
                'etiqueta' => 'SmartMatch',
                'destino' => 'smart/recommend',
                'activo' => str_contains($ruta, '/smart/recommend'),
            ],
            [
                'etiqueta' => 'Nosotros',
                'destino' => 'about',
                'activo' => str_contains($ruta, '/about'),
            ],
            [
                'etiqueta' => 'Contacto',
                'destino' => 'contact',
                'activo' => str_contains($ruta, '/contact'),
            ],
        ];

        if (str_ends_with(rtrim($ruta, '/'), '/smart/ads')) {
            $enlaces[] = [
                'etiqueta' => 'MD Ads',
                'destino' => 'smart/ads',
                'activo' => true,
            ];
        }

        return $enlaces;
    }

    /**
     * @return array<int, array{icono:string,etiqueta:string,destino:string,activo:bool}>
     */
    private function opcionesMenuPublicidadInteligente(string $rutaCampanias): array
    {
        return [
            ['icono' => 'bi-grid', 'etiqueta' => 'Vista general', 'destino' => '#vista-general', 'activo' => true],
            ['icono' => 'bi-clock-history', 'etiqueta' => 'Mis campañas', 'destino' => url_interna($rutaCampanias), 'activo' => false],
            ['icono' => 'bi-bar-chart', 'etiqueta' => 'Analítica de ventas', 'destino' => '#funciones-pendientes', 'activo' => false],
            ['icono' => 'bi-people', 'etiqueta' => 'Audiencia', 'destino' => '#funciones-pendientes', 'activo' => false],
            ['icono' => 'bi-box-seam', 'etiqueta' => 'Productos', 'destino' => '#funciones-pendientes', 'activo' => false],
            ['icono' => 'bi-stars', 'etiqueta' => 'Recomendaciones IA', 'destino' => '#recomendaciones', 'activo' => false],
            ['icono' => 'bi-file-earmark-bar-graph', 'etiqueta' => 'Reportes', 'destino' => '#reportes', 'activo' => false],
            ['icono' => 'bi-plugin', 'etiqueta' => 'Integraciones', 'destino' => '#funciones-pendientes', 'activo' => false],
            ['icono' => 'bi-gear', 'etiqueta' => 'Configuración', 'destino' => '#funciones-pendientes', 'activo' => false],
        ];
    }

    private function moduloAccionTablero(string $rol): string
    {
        return match ($rol) {
            'compras_logistica' => 'compras',
            'ventas_mayoristas' => 'cotizaciones',
            'ventas_minoristas' => 'ventas',
            'marketing' => 'campanias',
            default => 'productos',
        };
    }

    /**
     * @param array<string, mixed> $datosPagina
     * @return array<int, string>
     */
    private function scriptsFinalesVista(string $plantilla, array $datosPagina, string $rol): array
    {
        return match ($plantilla) {
            'aplicacion' => array_values(array_filter([
                url_recurso_estatico('assets/js/aplicacion.js?v=20261001-2'),
                !empty($datosPagina['esDetalleProducto'])
                    ? url_recurso_estatico('assets/js/david/producto.js?v=20260929-4')
                    : null,
            ])),
            'interno' => array_values(array_filter([
                url_recurso_estatico('assets/js/aplicacion.js?v=20261001-2'),
                in_array($rol, ['compras_logistica', 'marketing'], true)
                    ? url_recurso_estatico('assets/js/david/panel.js')
                    : null,
            ])),
            default => [],
        };
    }

    /** @return array<string, string> */
    private function rutasAlertasTablero(string $rol, string $moduloAccion): array
    {
        $rutas = [];
        foreach (['inventario', 'cotizaciones', 'pedidos', 'clientes', 'proveedores', 'compras', 'campanias', 'productos', 'reportes', 'auditoria'] as $modulo) {
            $destino = AccesoRol::puedeAcceder($rol, $modulo) ? $modulo : $moduloAccion;
            $rutas[$modulo] = AccesoRol::destino($rol, $destino);
        }

        return $rutas;
    }

    /**
     * @param array<string, mixed>|null $usuario
     * @param array<string, mixed>|null $campaniaEmergente
     * @param array<string, mixed> $datosPagina
     * @return array<string, mixed>
     */
    private function datosPublicidad(
        string $ruta,
        ?array $usuario,
        ?array $campaniaEmergente,
        array $datosPagina,
        string $categoriaPublicidad,
    ): array {
        $rol = (string) ($usuario['rol'] ?? 'visitante');
        $perfil = $rol === 'cliente_mayorista'
            ? 'mayorista'
            : ($rol === 'cliente_minorista' ? 'minorista' : ($usuario ? 'registrado' : 'visitante'));

        $contexto = match (true) {
            str_contains($ruta, '/products/') => 'producto',
            str_contains($ruta, '/catalog') => 'catalogo',
            str_contains($ruta, '/cart') => 'carrito',
            str_contains($ruta, '/checkout') && !empty($datosPagina['mensaje'] ?? null) => 'postcompra',
            str_contains($ruta, '/checkout') => 'checkout',
            str_contains($ruta, '/panel') || str_contains($ruta, '/account') => 'cuenta',
            str_contains($ruta, '/mayorista') || str_contains($ruta, '/smart/optimizer') => 'mayorista',
            str_contains($ruta, '/register') => 'registro',
            str_contains($ruta, '/login') => 'ingreso',
            str_contains($ruta, '/smart/') => 'inteligente',
            str_contains($ruta, '/about') => 'nosotros',
            str_contains($ruta, '/contact') => 'contacto',
            default => 'inicio',
        };

        $claveFrecuencia = $contexto;
        if ($contexto === 'catalogo') {
            $claveFrecuencia .= '-' . strtolower((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $categoriaPublicidad));
        }

        $ofertasContextuales = [
            'inicio' => ['titulo' => 'Oferta inteligente para ti', 'descripcion' => 'Explora equipos originales y encuentra una opción dentro de tu presupuesto.', 'texto_boton' => 'Ver ofertas', 'destino' => 'catalog', 'icono' => 'bi-lightning-charge-fill', 'etiqueta' => 'OFERTA DE HOY'],
            'catalogo' => ['titulo' => 'Ahorra en tu próxima compra', 'descripcion' => 'Usa SmartMatch para filtrar por presupuesto y descubre accesorios compatibles.', 'texto_boton' => 'Encontrar mi equipo', 'destino' => 'smart/recommend', 'icono' => 'bi-stars', 'etiqueta' => 'RECOMENDACIÓN IA'],
            'producto' => ['titulo' => 'Completa tu equipo', 'descripcion' => 'Añade cargador, funda o audífonos compatibles y arma un combo más conveniente.', 'texto_boton' => 'Ver accesorios', 'destino' => 'catalog?category=Accesorio', 'icono' => 'bi-bag-plus', 'etiqueta' => 'COMBO SUGERIDO'],
            'carrito' => ['titulo' => 'Tu compra puede rendir más', 'descripcion' => 'Revisa accesorios relacionados antes de finalizar y evita pagar otro envío.', 'texto_boton' => 'Agregar complemento', 'destino' => 'catalog?category=Accesorio', 'icono' => 'bi-cart-plus', 'etiqueta' => 'ÚLTIMA OPORTUNIDAD'],
            'checkout' => ['titulo' => 'Beneficio por comprar hoy', 'descripcion' => 'Finaliza tu pedido y recibe recomendaciones para cuidar mejor tu equipo.', 'texto_boton' => 'Continuar compra', 'destino' => 'checkout', 'icono' => 'bi-shield-check', 'etiqueta' => 'COMPRA SEGURA'],
            'postcompra' => ['titulo' => 'Gracias por tu compra', 'descripcion' => 'Conserva un beneficio especial para tu siguiente pedido en MD Technology Cell.', 'texto_boton' => 'Ver recomendados', 'destino' => 'smart/recommend', 'icono' => 'bi-gift-fill', 'etiqueta' => 'CUPÓN DE FIDELIDAD'],
            'cuenta' => ['titulo' => 'Una oferta exclusiva te espera', 'descripcion' => 'Vuelve a comprar tus favoritos y descubre sugerencias según tu historial.', 'texto_boton' => 'Ver mis recomendados', 'destino' => 'smart/recommend', 'icono' => 'bi-heart-fill', 'etiqueta' => 'SOLO PARA CLIENTES'],
            'mayorista' => ['titulo' => 'Más unidades, mejor precio', 'descripcion' => 'Cotiza por lote, revisa productos de alta rotación y optimiza tu inversión.', 'texto_boton' => 'Solicitar cotización', 'destino' => 'smart/optimizer', 'icono' => 'bi-box-seam-fill', 'etiqueta' => 'BENEFICIO B2B'],
            'registro' => ['titulo' => 'Regístrate y compra mejor', 'descripcion' => 'Guarda favoritos, consulta pedidos y recibe beneficios exclusivos.', 'texto_boton' => 'Crear mi cuenta', 'destino' => 'register', 'icono' => 'bi-person-plus-fill', 'etiqueta' => 'BIENVENIDA'],
            'ingreso' => ['titulo' => 'Tus beneficios están guardados', 'descripcion' => 'Ingresa para ver ofertas, pedidos y recomendaciones personalizadas.', 'texto_boton' => 'Ingresar', 'destino' => 'login', 'icono' => 'bi-person-check-fill', 'etiqueta' => 'CLIENTE MD'],
            'inteligente' => ['titulo' => 'Compra con más información', 'descripcion' => 'Compara opciones y recibe una recomendación clara antes de decidir.', 'texto_boton' => 'Explorar catálogo', 'destino' => 'catalog', 'icono' => 'bi-cpu-fill', 'etiqueta' => 'SMARTCOMMERCE'],
            'nosotros' => ['titulo' => 'Tecnología con atención cercana', 'descripcion' => 'Conoce nuestro catálogo y encuentra productos originales con soporte local.', 'texto_boton' => 'Ver productos', 'destino' => 'catalog', 'icono' => 'bi-patch-check-fill', 'etiqueta' => 'COMPRA CON CONFIANZA'],
            'contacto' => ['titulo' => '¿Necesitas ayuda para elegir?', 'descripcion' => 'Cuéntanos qué buscas o deja que SmartMatch encuentre una opción para ti.', 'texto_boton' => 'Probar SmartMatch', 'destino' => 'smart/recommend', 'icono' => 'bi-chat-heart-fill', 'etiqueta' => 'TE AYUDAMOS'],
        ];

        if ($perfil === 'mayorista') {
            $ofertaEntrada = ['titulo' => 'Precio especial por volumen', 'descripcion' => 'Accede a descuentos por lote, stock destacado y productos de alta rotación.', 'texto_boton' => 'Cotizar mi pedido', 'destino' => 'smart/optimizer', 'icono' => 'bi-boxes', 'etiqueta' => 'CLIENTE MAYORISTA'];
        } elseif ($perfil === 'minorista') {
            $ofertaEntrada = ['titulo' => 'Beneficio exclusivo para ti', 'descripcion' => 'Descubre descuentos por recompra, accesorios y productos según tus intereses.', 'texto_boton' => 'Ver mis ofertas', 'destino' => 'smart/recommend', 'icono' => 'bi-gift-fill', 'etiqueta' => 'CLIENTE FRECUENTE'];
        } elseif ($perfil === 'registrado') {
            $ofertaEntrada = ['titulo' => 'Tenemos una oferta para tu cuenta', 'descripcion' => 'Explora productos y recomendaciones seleccionadas para una compra más conveniente.', 'texto_boton' => 'Descubrir ahora', 'destino' => 'catalog', 'icono' => 'bi-stars', 'etiqueta' => 'OFERTA PERSONALIZADA'];
        } else {
            $ofertaEntrada = ['titulo' => '¡Bienvenido a MD Technology Cell!', 'descripcion' => 'Regístrate para guardar favoritos, consultar pedidos y recibir ofertas exclusivas.', 'texto_boton' => 'Quiero registrarme', 'destino' => 'register', 'icono' => 'bi-gift-fill', 'etiqueta' => 'BENEFICIO DE BIENVENIDA'];
        }

        $ofertaContextual = $ofertasContextuales[$contexto];
        if ($perfil === 'mayorista' && !in_array($contexto, ['carrito', 'checkout', 'postcompra'], true)) {
            $ofertaContextual = $ofertasContextuales['mayorista'];
        }

        $campaniaPrincipal = $perfil === 'visitante' ? $campaniaEmergente : null;
        $idCampania = (int) ($campaniaPrincipal['id'] ?? 0);
        $precioOferta = $campaniaPrincipal['precio_oferta'] ?? null;
        $precioAnterior = $campaniaPrincipal['precio_anterior'] ?? null;
        $urlAccionPrincipal = $campaniaPrincipal
            ? url_campania((string) $campaniaPrincipal['url_boton'])
            : url_interna($ofertaEntrada['destino']);

        return [
            'contextoPublicidad' => $contexto,
            'claveFrecuenciaPublicidad' => $claveFrecuencia,
            'perfilPublicidad' => $perfil,
            'ofertaEntrada' => $ofertaEntrada,
            'ofertaContextual' => $ofertaContextual,
            'campaniaPrincipal' => $campaniaPrincipal,
            'precioOfertaPublicidadVista' => $precioOferta !== null ? formatear_dinero((float) $precioOferta) : '',
            'precioAnteriorPublicidadVista' => $precioAnterior !== null ? formatear_dinero((float) $precioAnterior) : '',
            'atributoPrecioOfertaOculto' => $precioOferta === null ? 'hidden' : '',
            'atributoPrecioAnteriorOculto' => $precioAnterior === null ? 'hidden' : '',
            'atributoBeneficiosOculto' => $precioOferta !== null ? 'hidden' : '',
            'urlAccionPrincipal' => $urlAccionPrincipal,
            'urlAccionContextual' => url_interna($ofertaContextual['destino']),
            'urlSeguimiento' => $idCampania > 0 ? $this->generadorUrl->generar('campaigns/' . $idCampania . '/track') : '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function campaniasActivas(): array
    {
        try {
            return $this->campanias->ubicacionesActivas();
        } catch (\Throwable $excepcion) {
            $this->registrador->error('Falló la consulta de campañas.', [
                'message' => $excepcion->getMessage(),
            ]);

            return [];
        }
    }
}
