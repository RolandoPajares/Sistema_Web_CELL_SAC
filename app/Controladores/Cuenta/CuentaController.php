<?php

declare(strict_types=1);

namespace App\Controladores\Cuenta;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;

final class CuentaController
{
    public function indice(Solicitud $solicitud): Respuesta
    {
        return redirect('panel');
    }
}
