<?php

declare(strict_types=1);

namespace App\Validacion\Productos;

use App\Nucleo\Http\Solicitud;
use App\Soporte\Excepciones\ExcepcionValidacion;

final class SolicitudProducto
{
    /** @return array<string, mixed> */
    public static function validar(Solicitud $solicitud): array
    {
        $texto = static function (string $campo) use ($solicitud): string {
            $valor = $solicitud->entrada($campo, '');
            return is_scalar($valor) ? trim((string) $valor) : '';
        };

        $datos = [
            'marca' => $texto('brand'),
            'nombre' => $texto('name'),
            'categoria' => $texto('category'),
            'categoria_id' => 0,
            'precio' => 0.0,
            'almacenamiento' => $texto('storage'),
            'color' => $texto('color'),
            'etiqueta' => $texto('badge'),
            'descripcion' => $texto('description'),
        ];

        $errores = [];

        if ($datos['marca'] === '' || mb_strlen($datos['marca']) > 80) {
            $errores['marca'] = 'La marca es obligatoria y admite hasta 80 caracteres.';
        }

        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 160) {
            $errores['nombre'] = 'El nombre es obligatorio y admite hasta 160 caracteres.';
        }

        $categoriaId = filter_var($solicitud->entrada('category_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($categoriaId === false && $datos['categoria'] === '') {
            $errores['categoria'] = 'Selecciona una categoría válida.';
        } elseif ($categoriaId !== false) {
            $datos['categoria_id'] = (int) $categoriaId;
        }

        $precio = $solicitud->entrada('price');
        if (!is_scalar($precio) || !is_numeric($precio) || !is_finite((float) $precio) || (float) $precio <= 0 || (float) $precio > 99999999.99) {
            $errores['precio'] = 'El precio debe ser mayor que cero.';
        } else {
            $datos['precio'] = (float) $precio;
        }

        foreach (['almacenamiento' => 80, 'color' => 80, 'etiqueta' => 80] as $campo => $limite) {
            if (mb_strlen($datos[$campo]) > $limite) {
                $errores[$campo] = 'El campo ' . $campo . ' admite hasta ' . $limite . ' caracteres.';
            }
        }

        if (strlen($datos['descripcion']) > 65535) {
            $errores['descripcion'] = 'La descripción es demasiado larga.';
        }

        if ($errores) {
            throw new ExcepcionValidacion($errores);
        }

        return $datos;
    }
}
