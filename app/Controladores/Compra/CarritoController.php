<?php

declare(strict_types=1);

namespace App\Controladores\Compra;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Compra\CarritoServicio;
use App\Servicios\ComercioInteligente\ComercioInteligenteServicio;

final class CarritoController
{
    public function __construct(
        private Vista $vista,
        private CarritoServicio $carrito,
        private ComercioInteligenteServicio $comercioInteligente,
    ) {
    }

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $resumen = $this->carrito->resumen();
        $sugerencias = $this->comercioInteligente->sugerenciasCarrito($resumen['articulos']);
        $resumen['articulos'] = array_map(static function (array $producto): array {
            $producto['imagen_carrito_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['icono_carrito_vista'] = icono_categoria_producto((string) ($producto['categoria'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_carrito_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_carrito_vista'] === '' ? '' : 'hidden';

            return $producto;
        }, $resumen['articulos']);
        $sugerencias = array_map(static function (array $producto): array {
            $producto['imagen_sugerencia_vista'] = url_imagen_producto((string) ($producto['url_imagen'] ?? ''));
            $producto['icono_sugerencia_vista'] = icono_categoria_producto((string) ($producto['categoria'] ?? ''));
            $producto['texto_alternativo_vista'] = trim((string) ($producto['marca'] ?? '') . ' ' . (string) ($producto['nombre'] ?? ''));
            $producto['atributoImagenOculta'] = $producto['imagen_sugerencia_vista'] !== '' ? '' : 'hidden';
            $producto['atributoIconoOculto'] = $producto['imagen_sugerencia_vista'] === '' ? '' : 'hidden';

            return $producto;
        }, $sugerencias);

        return $this->vista->renderizar('modulos.compra.carrito.indice', [
            'tituloPagina' => 'Carrito',
            'resumen' => $resumen,
            'sugerencias' => $sugerencias,
            'atributoCarritoVacioOculto' => $resumen['articulos'] === [] ? '' : 'hidden',
            'atributoCarritoConArticulosOculto' => $resumen['articulos'] !== [] ? '' : 'hidden',
            'atributoSugerenciasOculto' => $sugerencias !== [] ? '' : 'hidden',
        ]);
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        if ($solicitud->entrada('add') !== null) {
            $idProducto = filter_var(
                $solicitud->entrada('add'),
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
            if ($idProducto !== false) {
                $this->carrito->agregar((int) $idProducto);
            }
        }

        if ($solicitud->entrada('remove') !== null) {
            $idProducto = filter_var(
                $solicitud->entrada('remove'),
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
            if ($idProducto !== false) {
                $this->carrito->eliminar((int) $idProducto);
            }
        }

        if ($solicitud->entrada('clear') !== null) {
            $this->carrito->limpiar();
        }

        return redirigir('cart');
    }
}
