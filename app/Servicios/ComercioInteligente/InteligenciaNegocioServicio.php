<?php

declare(strict_types=1);

namespace App\Servicios\ComercioInteligente;

use App\DAO\ComercioInteligente\InteligenciaNegocioDAO;

final class InteligenciaNegocioServicio
{
    public function __construct(private InteligenciaNegocioDAO $inteligencia)
    {
    }

    /** @return array<string,mixed> */
    public function resumenActual(): array
    {
        $resumen = $this->inteligencia->resumenActual();
        $datosDiarios = $resumen['diario'];
        $destacados = $resumen['destacados'];
        $promedio = $datosDiarios === [] ? 0.0 : array_sum(array_map(
            static fn (array $fila): float => (float) $fila['unidades'],
            $datosDiarios
        )) / count($datosDiarios);

        foreach ($resumen['existencias_bajas'] as &$producto) {
            $producto['dias_restantes'] = $promedio > 0
                ? max(1, (int) round((float) $producto['existencias'] / $promedio * max(1, count($destacados))))
                : null;
        }
        unset($producto);

        return $resumen + ['promedio_unidades_diarias' => $promedio];
    }
}
