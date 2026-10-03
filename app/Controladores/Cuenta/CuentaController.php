<?php

declare(strict_types=1);

namespace App\Controladores\Cuenta;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;

final class CuentaController
{
    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        return redirigir('panel');
    }
}
