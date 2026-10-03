<?php

declare(strict_types=1);

namespace App\Controladores\Productos;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Productos\PresentadorDetalleProducto;
use App\Servicios\Productos\ProductoServicio;

final class ProductoController
{
    public function __construct(
        private Vista $vista,
        private ProductoServicio $productos,
        private PresentadorDetalleProducto $presentadorDetalleProducto,
    ) {
    }

    /**
     * Obtiene los datos de detalle del registro solicitado.
     */
    public function detalle(Solicitud $solicitud): Respuesta
    {
        $idProducto = (int) ($solicitud->parametroRuta('id') ?? $solicitud->consulta('id', 0));
        $producto = $this->productos->buscarActivoConRelaciones($idProducto);

        if (!$producto) {
            return $this->vista->renderizar('errores.404', ['tituloPagina' => 'Producto no encontrado'], 'aplicacion', 404);
        }

        $presentacionProducto = $this->presentadorDetalleProducto->presentar($producto);

        return $this->vista->renderizar('publico.catalogo.detalle', [
            'tituloPagina' => (string) $producto['nombre'],
            'esDetalleProducto' => true,
            'producto' => $producto,
            'marca' => $presentacionProducto['marca'],
            'nombre' => $presentacionProducto['nombre'],
            'categoria' => $presentacionProducto['categoria'],
            'atributoCategoriaOculto' => $presentacionProducto['atributoCategoriaOculto'],
            'enlaceCategoria' => $presentacionProducto['enlace_categoria'],
            'iconoProducto' => $presentacionProducto['icono_imagen'],
            'visualMarca' => $presentacionProducto['visual_marca'],
            'textoAlternativoProducto' => $presentacionProducto['texto_alternativo'],
            'tipoVisualProducto' => $presentacionProducto['tipo_imagen'],
            'precioActual' => $presentacionProducto['precio_oferta'],
            'precioOriginal' => $presentacionProducto['precio_original'],
            'tieneDescuento' => $presentacionProducto['tiene_descuento'],
            'descuento' => $presentacionProducto['descuento'],
            'imagenesProductoDetalle' => $presentacionProducto['imagenes'],
            'imagenesProductoDetalleVista' => $presentacionProducto['imagenes_vista'],
            'imagen' => $presentacionProducto['imagen_principal'],
            'atributoImagenOculta' => $presentacionProducto['atributoImagenOculta'],
            'atributoPlaceholderOculto' => $presentacionProducto['atributoPlaceholderOculto'],
            'atributoMarcaOculto' => $presentacionProducto['atributoMarcaOculto'],
            'atributoEtiquetaOculto' => $presentacionProducto['atributoEtiquetaOculto'],
            'atributoPrecioAnteriorOculto' => $presentacionProducto['atributoPrecioAnteriorOculto'],
            'atributoDescuentoOculto' => $presentacionProducto['atributoDescuentoOculto'],
            'atributoTextoMarcaVisualOculto' => $presentacionProducto['atributoTextoMarcaVisualOculto'],
            'atributoIconoVisualOculto' => $presentacionProducto['atributoIconoVisualOculto'],
            'stock' => $presentacionProducto['stock'],
            'disponible' => $presentacionProducto['disponible'],
            'claseStock' => $presentacionProducto['clase_stock'],
            'iconoStock' => $presentacionProducto['icono_stock'],
            'textoStock' => $presentacionProducto['texto_stock'],
            'etiquetaProducto' => $presentacionProducto['etiqueta'],
            'atributos' => $presentacionProducto['atributos'],
            'descripcion' => $presentacionProducto['descripcion'],
            'atributoDescripcionOculto' => $presentacionProducto['atributoDescripcionOculto'],
            'atributoAtributosOculto' => $presentacionProducto['atributoAtributosOculto'],
        ]);
    }
}
