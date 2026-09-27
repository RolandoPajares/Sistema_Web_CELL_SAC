<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\CarritoServicio;
use App\Servicios\ComercioInteligenteServicio;

final class CarritoController
{
    public function __construct(
        private Vista $vista,
        private CarritoServicio $carrito,
        private ComercioInteligenteServicio $comercioInteligente,
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('carrito.indice', [
            'tituloPagina' => 'Carrito',
            'resumen' => $resumen = $this->carrito->resumen(),
            'sugerencias' => $this->comercioInteligente->sugerenciasCarrito($resumen['articulos']),
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

        return redirect('cart');
    }
}
