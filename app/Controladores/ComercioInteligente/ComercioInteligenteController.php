<?php

declare(strict_types=1);

namespace App\Controladores\ComercioInteligente;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\ComercioInteligente\PresentadorComercioInteligente;
use App\Servicios\Productos\ProductoServicio;
use App\Servicios\ComercioInteligente\ComercioInteligenteServicio;
use App\Servicios\Campanias\CampaniaServicio;

final class ComercioInteligenteController
{
    public function __construct(
        private Vista $vista,
        private ComercioInteligenteServicio $comercioInteligente,
        private ProductoServicio $productos,
        private CampaniaServicio $campanias,
    ) {
    }

    /**
     * Genera recomendaciones de productos a partir de las preferencias recibidas.
     */
    public function recomendador(Solicitud $solicitud): Respuesta
    {
        $presupuesto = $this->numeroPositivo($solicitud->consulta('budget'));
        $uso = $this->valorPermitido($solicitud->consulta('use'), ['study', 'gaming', 'camera', 'work', 'social'], 'study');
        $prioridad = $this->valorPermitido(
            $solicitud->consulta('priority'),
            ['valor', 'rendimiento', 'bateria', 'camara'],
            'valor'
        );
        $resultados = $presupuesto > 0
            ? $this->comercioInteligente->recomendar($presupuesto, $uso, $prioridad)
            : [];
        $resultados = PresentadorComercioInteligente::presentarRecomendaciones($resultados);

        return $this->vista->renderizar('comercio-inteligente.recomendador.indice', [
            'tituloPagina' => 'Recomendador inteligente',
            'resultados' => $resultados,
            'atributoResultadosOculto' => $resultados !== [] ? '' : 'hidden',
            'atributoSinResultadosOculto' => $presupuesto > 0 && $resultados === [] ? '' : 'hidden',
            'presupuesto' => $presupuesto,
            'uso' => $uso,
            'prioridad' => $prioridad,
        ]);
    }

    /**
     * Prepara la comparación de los productos seleccionados y sus indicadores.
     */
    public function comparar(Solicitud $solicitud): Respuesta
    {
        $idsCrudos = $solicitud->consulta('ids', '');
        $idsProductos = is_scalar($idsCrudos)
            ? array_values(array_filter(
                array_map('intval', explode(',', (string) $idsCrudos)),
                static fn (int $idProducto): bool => $idProducto > 0
            ))
            : [];
        $productos = $this->comercioInteligente->comparar($idsProductos);
        $productos = PresentadorComercioInteligente::presentarComparacion($productos);
        $todosLosProductos = PresentadorComercioInteligente::filtrarProductosComparador(
            $this->productos->todosActivos()
        );

        return $this->vista->renderizar('comercio-inteligente.comparador.indice', [
            'tituloPagina' => 'Comparador inteligente',
            'productos' => $productos,
            'etiquetasPuntajeInteligente' => [
                'rendimiento' => 'Rendimiento',
                'camara' => 'Cámara',
                'bateria' => 'Batería',
                'valor' => 'Calidad/precio',
            ],
            'atributoComparacionOculta' => $productos !== [] ? '' : 'hidden',
            'ranurasComparador' => PresentadorComercioInteligente::prepararRanurasComparador(
                $idsProductos,
                $todosLosProductos
            ),
        ]);
    }

    /**
     * Calcula una propuesta optimizada con los datos recibidos.
     */
    public function optimizador(Solicitud $solicitud): Respuesta
    {
        $presupuesto = $this->numeroPositivo($solicitud->consulta('budget'));
        $objetivo = $this->valorPermitido(
            $solicitud->consulta('goal'),
            ['variety', 'units', 'margin', 'premium'],
            'variety'
        );
        $propuesta = $presupuesto > 0
            ? $this->comercioInteligente->optimizar($presupuesto, $objetivo)
            : null;
        $propuestaHtml = '';

        if ($propuesta !== null) {
            $propuestaPreparada = PresentadorComercioInteligente::presentarPropuestaOptimizador($propuesta);
            $propuestaHtml = $this->vista->renderizar(
                'comercio-inteligente.optimizador._propuesta',
                ['propuestaVista' => $propuestaPreparada],
                '',
            )->contenido();
        }

        return $this->vista->renderizar('comercio-inteligente.optimizador.indice', [
            'tituloPagina' => 'Optimizador de compra',
            'presupuestoFormulario' => $presupuesto > 0 ? (string) $presupuesto : '',
            'opcionesObjetivoVista' => PresentadorComercioInteligente::presentarObjetivosOptimizador($objetivo),
            'propuestaHtml' => $propuestaHtml,
        ]);
    }

    /**
     * Genera la respuesta del asistente inteligente para la consulta recibida.
     */
    public function asistente(Solicitud $solicitud): Respuesta
    {
        if (rol_usuario_actual() === 'ventas_mayoristas') {
            $opcionesLaptop = $this->opcionesLaptopDesdeCatalogo();
            $recomendacionLaptop = PresentadorComercioInteligente::presentarRecomendacionLaptop($opcionesLaptop);

            return $this->vista->renderizar(
                'comercio-inteligente.asistente-ia.interno',
                [
                    'tituloPagina' => 'MD Assistant B2B',
                    'conversacionesDemo' => PresentadorComercioInteligente::presentarConversacionesAsistente([

                        ['titulo' => 'Recomendación laptops empresa', 'resumen' => 'Necesito laptops para una empresa...'],
                        ['titulo' => 'Cotización celulares corporativos', 'resumen' => 'Hola, necesito una cotización de 50...'],
                        ['titulo' => 'Stock iPhone 15 para distribuidor', 'resumen' => '¿Tienen stock del iPhone 15 en volumen?'],
                        ['titulo' => 'Propuesta para licitación estatal', 'resumen' => 'Revisa estas especificaciones...'],
                        ['titulo' => 'Auriculares para call center', 'resumen' => '¿Qué opciones tienen con micrófono?'],
                        ['titulo' => 'Simulador de margen', 'resumen' => 'Ayúdame a calcular un margen...'],
                    ]),
                    'promptsRapidos' => [
                        'Buscar productos por volumen',
                        'Armar una cotización',
                        'Comparar productos',
                        'Ver stock disponible',
                        'Sugerir productos por rubro',
                        'Redactar mensaje para cliente',
                    ],
                    'opcionesLaptop' => $opcionesLaptop,
                    'recomendacionLaptop' => $recomendacionLaptop,
                    'atributoSinPortatilesOculto' => $recomendacionLaptop['disponible'] ? 'hidden' : '',
                    'atributoComparativaOculta' => $recomendacionLaptop['disponible'] ? '' : 'hidden',
                    'atributoOpcionCatalogoOculta' => $recomendacionLaptop['disponible'] ? '' : 'hidden',
                    'filasComparativaLaptop' => $this->filasComparativaLaptop($opcionesLaptop),
                    'mensajeProductosCatalogo' => !$recomendacionLaptop['disponible']
                        ? 'No hay equipos portátiles activos registrados en el catálogo.'
                        : 'Los productos, características, precios y existencias mostrados provienen del catálogo.',
                ],
                'interno'
            );
        }
        return $this->vista->renderizar('comercio-inteligente.asistente-ia.indice', ['tituloPagina' => 'MD Assistant']);
    }

    /**
     * Prepara las opciones de equipos portátiles con los datos del catálogo.
     *
     * @return array<int, array{nombre:string,descripcion:string,existencias:int,precio:float,caracteristicas:array<int,array{nombre:string,valor:string}>}>
     */
    private function opcionesLaptopDesdeCatalogo(): array
    {
        $productosPortatiles = PresentadorComercioInteligente::seleccionarPortatiles(
            $this->productos->todosActivos()
        );

        foreach ($productosPortatiles as &$producto) {
            $producto['caracteristicas'] = $this->productos->caracteristicas((int) $producto['id']);
        }
        unset($producto);

        return PresentadorComercioInteligente::presentarOpcionesPortatiles($productosPortatiles);
    }

    /**
     * Organiza los datos del catálogo para presentar la comparativa de productos.
     *
     * @param array<int, array{nombre:string,descripcion:string,existencias:int,precio:float,caracteristicas:array<int,array{nombre:string,valor:string}>}> $opcionesLaptop
     * @return array<int, array{etiqueta:string,valores:array<int,string>}>
     */
    private function filasComparativaLaptop(array $opcionesLaptop): array
    {
        return PresentadorComercioInteligente::presentarFilasComparativa($opcionesLaptop);
    }

    /**
     * Prepara los datos necesarios para el flujo de compra mayorista.
     */
    public function mayorista(Solicitud $solicitud): Respuesta
    {
        $productos = PresentadorComercioInteligente::presentarProductosMayoristas(
            $this->productos->todosActivos()
        );

        return $this->vista->renderizar('roles.externos.cliente-mayorista.portal-mayorista.indice', [
            'tituloPagina' => 'Portal mayorista',
            'productos' => $productos,
            'beneficiosMayoristas' => PresentadorComercioInteligente::beneficiosMayoristas(),
        ]);
    }

    /**
     * Prepara una propuesta de publicidad a partir de la solicitud recibida.
     */
    public function publicidadInteligente(Solicitud $solicitud): Respuesta
    {
        $resumen = $this->campanias->resumen();
        $campanias = $this->campanias->todosParaAdministrador();
        $presentacionPublicidad = PresentadorComercioInteligente::presentarPublicidad($resumen, $campanias);

        return $this->vista->renderizar('comercio-inteligente.publicidad.indice', [
            'tituloPagina' => 'MD Ads inteligente',
            'resumen' => $resumen,
            'metricasPublicidad' => $presentacionPublicidad['metricasPublicidad'],
            'campanias' => $campanias,
            'campaniasDestacadas' => $presentacionPublicidad['campaniasDestacadas'],
        ]);
    }

    /**
     * Construye la respuesta del asistente a partir del resultado de la consulta.
     */
    public function respuestaAsistente(Solicitud $solicitud): Respuesta
    {
        $entradaMensaje = $solicitud->consulta('message', '');
        $mensaje = is_scalar($entradaMensaje) ? trim((string) $entradaMensaje) : '';
        if ($mensaje === '') {
            return new Respuesta(json_encode(['ok' => false, 'mensaje' => 'Escribe una consulta para poder ayudarte.'], JSON_UNESCAPED_UNICODE), 422, ['Content-Type' => 'application/json; charset=utf-8']);
        }
        if (mb_strlen($mensaje) > 500) {
            return new Respuesta(json_encode(['ok' => false, 'mensaje' => 'La consulta admite hasta 500 caracteres.'], JSON_UNESCAPED_UNICODE), 422, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        $respuesta = $this->comercioInteligente->responder($mensaje);

        return new Respuesta(json_encode(['ok' => true, 'respuesta' => $respuesta], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * @param array<int, string> $permitidos
     */
    private function valorPermitido(mixed $valor, array $permitidos, string $predeterminado): string
    {
        $valor = is_scalar($valor) ? (string) $valor : '';

        return in_array($valor, $permitidos, true) ? $valor : $predeterminado;
    }

    /**
     * Valida y devuelve el número positivo recibido.
     */
    private function numeroPositivo(mixed $valor): float
    {
        if (!is_scalar($valor) || !is_numeric($valor)) {
            return 0.0;
        }

        $numero = (float) $valor;

        return is_finite($numero) && $numero > 0 ? min($numero, 99999999.99) : 0.0;
    }
}
