<?php

declare(strict_types=1);

namespace App\Validacion\Inventario;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudMovimientoInventario
{
    public static function validar(Solicitud $solicitud): array
    {
        $productoId = filter_var($solicitud->entrada('producto_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $tipo = trim((string) $solicitud->entrada('tipo_movimiento', ''));
        $cantidad = filter_var($solicitud->entrada('cantidad'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $notas = trim((string) $solicitud->entrada('notas', ''));
        $errores = [];
        if ($productoId === false) {
            $errores['producto_id'] = 'Selecciona un producto válido.';
        }
        if (!in_array($tipo, ['entrada', 'salida', 'ajuste'], true)) {
            $errores['tipo_movimiento'] = 'Selecciona un tipo de movimiento válido.';
        }
        if ($cantidad === false) {
            $errores['cantidad'] = 'La cantidad debe ser un entero mayor que cero.';
        }
        if ($notas === '' || mb_strlen($notas) > 500) {
            $errores['notas'] = 'El motivo es obligatorio y admite hasta 500 caracteres.';
        }
        if ($errores !== []) {
            throw new ExcepcionValidacion($errores);
        }

        return ['producto_id' => (int) $productoId, 'tipo_movimiento' => $tipo,
            'cantidad' => (int) $cantidad, 'notas' => $notas];
    }
}
