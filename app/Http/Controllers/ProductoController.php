<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\ProductoServicio;

final class ProductoController
{
    public function __construct(private Vista $vista, private ProductoServicio $productos)
    {
    }

    public function detalle(Solicitud $solicitud): Respuesta
    {
        $idProducto = (int) ($solicitud->parametroRuta('id') ?? $solicitud->consulta('id', 0));
        $producto = $this->productos->buscarActivo($idProducto);

        if (!$producto) {
            return $this->vista->renderizar('paginas.no_encontrado', ['tituloPagina' => 'Producto no encontrado'], 'aplicacion', 404);
        }

        return $this->vista->renderizar('productos.detalle', [
            'tituloPagina' => (string) $producto['nombre'],
            'producto' => $producto,
        ]);
    }
}
