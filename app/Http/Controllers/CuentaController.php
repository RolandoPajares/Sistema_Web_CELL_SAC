<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;

final class CuentaController
{
    public function indice(Solicitud $solicitud): Respuesta
    {
        return redirect('panel');
    }
}
