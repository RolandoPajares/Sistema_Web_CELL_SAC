<?php

declare(strict_types=1);

namespace App\Controladores\Productos;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Productos\ProductoServicio;

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
            return $this->vista->renderizar('errores.404', ['tituloPagina' => 'Producto no encontrado'], 'aplicacion', 404);
        }

        return $this->vista->renderizar('publico.catalogo.detalle', [
            'tituloPagina' => (string) $producto['nombre'],
            'producto' => $producto,
        ]);
    }
}
