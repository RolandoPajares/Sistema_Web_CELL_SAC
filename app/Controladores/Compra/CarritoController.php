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

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('modulos.compra.carrito.indice', [
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
                $this->carrito->agregar((int) $idProducto, (int)($solicitud->entrada('variante_id') ?? 0) ?: null);
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
