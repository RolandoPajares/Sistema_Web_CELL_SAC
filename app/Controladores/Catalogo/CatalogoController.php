<?php

declare(strict_types=1);

namespace App\Controladores\Catalogo; // actualiza el espacio de nombres para reflejar la ubicación del controlador dentro del módulo de catálogo

use App\Nucleo\Http\Solicitud; // Representa la solicitud HTTP entrante y proporciona métodos para acceder a sus datos.
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Productos\PresentadorTarjetaProducto;
use App\Nucleo\Presentacion\Productos\PresentadorDetalleProducto;
use App\Nucleo\Presentacion\Catalogo\PresentadorCatalogo;
use App\Validacion\Productos\SolicitudFiltroCatalogo;
use App\Servicios\Productos\ProductoServicio;
use App\Servicios\Categorias\CategoriaServicio;

// Controlador para gestionar las operaciones del catálogo de productos
final class CatalogoController //               
{
// se inyectan dependencias a través del constructor para facilitar la prueba y el mantenimiento del código
public function __construct(
        private Vista $vista,  
        private ProductoServicio $productos,
        private CategoriaServicio $categorias,
        private SolicitudFiltroCatalogo $filtrosCatalogo,
        private PresentadorTarjetaProducto $presentadorTarjetaProducto,
        private PresentadorDetalleProducto $presentadorDetalleProducto,
    ) {
    }

    // Declaro un método público llamado indice, que recibe una solicitud y devuelve una respuesta.
    public function indice(Solicitud $solicitud): Respuesta  // Maneja la solicitud para mostrar el catálogo de productos y devuelve la respuesta HTTP correspondiente
    {
        // Obtiene todos los productos activos y prepara las opciones de filtro para la vista
        $todosLosProductos = $this->productos->todosActivos();
        $opcionesFiltros = PresentadorCatalogo::presentarOpcionesFiltros(
            $todosLosProductos,
            $this->categorias->activas()
        );

        // Extrae las marcas y categorías disponibles para los filtros de la vista
        $categorias = $opcionesFiltros['categorias'];
        $marcas = $opcionesFiltros['marcas'];

        // Valida los filtros de la solicitud y obtiene los productos paginados según los criterios aplicados
        $resultadoFiltros = $this->filtrosCatalogo->validar($solicitud, $marcas, $categorias);
        $filtros = $resultadoFiltros['filtros'];

        // Obtiene la paginación de productos según los filtros aplicados y prepara los enlaces para la vista
        $paginacion = $this->productos->paginar($resultadoFiltros['filtro']);
        $enlacesVista = PresentadorCatalogo::presentarEnlaces($filtros, $paginacion);

        // El detalle se consulta en el servidor con el repositorio existente; no se envía información del producto en el HTML de las tarjetas.
        $idProductoDetalle = filter_var($solicitud->consulta('producto'), FILTER_VALIDATE_INT); // Obtiene el ID del producto seleccionado para mostrar su detalle, si se proporciona en la consulta
        $productoDetalle = is_int($idProductoDetalle) && $idProductoDetalle > 0
            ? $this->productos->buscarActivoConRelaciones($idProductoDetalle)
            : null;

            // Prepara los datos del producto seleccionado para el diálogo de detalle, si existe; de lo contrario, prepara un producto vacío con precio cero
        $detalleProducto = $productoDetalle !== null
            ? $this->presentadorDetalleProducto->presentar($productoDetalle)
            : $this->presentadorDetalleProducto->presentar(['precio' => 0]);
            // Prepara los datos de las tarjetas de producto para la vista, utilizando el presentador correspondiente
        $tarjetasProducto = $this->presentadorTarjetaProducto->presentarColeccion($paginacion['productos']); // Prepara los datos de las tarjetas de producto para la vista, utilizando el presentador correspondiente

        // Renderiza la vista del catálogo con los datos preparados y devuelve la respuesta HTTP
        return $this->vista->renderizar('publico.catalogo.indice', [
            'tituloPagina' => 'Catálogo',
            'tarjetasProducto' => $tarjetasProducto,
            'atributoProductosCatalogoGridOculto' => $tarjetasProducto !== [] ? '' : 'hidden', // Oculta el grid de productos si no hay productos para mostrar
            'atributoProductosCatalogoVacioOculto' => $tarjetasProducto === [] ? '' : 'hidden',
            'atributoPaginacionOculto' => $paginacion['ultima_pagina'] > 1 ? '' : 'hidden',
            'atributoDialogoDetalleAbierto' => $productoDetalle !== null ? 'open' : '', // Abre el diálogo de detalle si se ha seleccionado un producto
            'paginacion' => $paginacion,
            'filtros' => $filtros,
            'marcas' => $marcas,
            'categorias' => $categorias,
            'enlaceConFiltros' => $enlacesVista['enlaceConFiltros'], // Enlace base para la paginación y filtrado, sin parámetros de consulta adicionales
            'enlacesPaginacion' => $enlacesVista['enlacesPaginacion'],
            'detalleProducto' => $detalleProducto,
        ]);
    }
}
