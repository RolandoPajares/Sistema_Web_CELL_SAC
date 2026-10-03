<?php

declare(strict_types=1);

namespace App\Controladores\Publico;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Productos\PresentadorTarjetaProducto;
use App\Servicios\Productos\ProductoServicio;

final class InicioController
{
    public function __construct(
        private Vista $vista,
        private ProductoServicio $productos,
        private PresentadorTarjetaProducto $presentadorTarjetaProducto,
    ) {
    }

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $tarjetasProducto = $this->presentadorTarjetaProducto->presentarColeccion(
            $this->productos->destacados(20),
            110,
        );

        return $this->vista->renderizar('publico.inicio.indice', [
            'tituloPagina' => 'Inicio',
            'tarjetasProducto' => $tarjetasProducto,
            'atributoProductosInicioGridOculto' => $tarjetasProducto !== [] ? '' : 'hidden',
            'atributoProductosInicioVacioOculto' => $tarjetasProducto === [] ? '' : 'hidden',
        ]);
    }
}
