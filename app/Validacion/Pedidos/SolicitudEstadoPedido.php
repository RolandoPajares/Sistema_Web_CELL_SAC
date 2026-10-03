<?php

declare(strict_types=1);

namespace App\Validacion\Pedidos;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudEstadoPedido
{
    public const ESTADOS = ['Pendiente', 'En proceso', 'Enviado', 'Entregado', 'Cancelado'];

    /**
     * Comprueba que los datos cumplan las reglas antes de continuar.
     */
    public static function validar(Solicitud $solicitud): string
    {
        $estado = trim((string) $solicitud->entrada('estado', ''));
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new ExcepcionValidacion(['estado' => 'El estado seleccionado no es válido.']);
        }

        return $estado;
    }
}
