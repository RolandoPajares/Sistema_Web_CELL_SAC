<?php

declare(strict_types=1);

namespace App\Validacion\Productos;

use App\DTO\Productos\FiltroProducto;
use App\Nucleo\Http\Solicitud;

/** Normaliza los filtros GET del catálogo y prepara el DTO para el servicio. */
final class SolicitudFiltroCatalogo
{
    /**
     * @param array<int, string> $marcasDisponibles
     * @param array<int, string> $categoriasDisponibles
     * @return array{filtros:array<string,string>,filtro:FiltroProducto}
     */
    public function validar(Solicitud $solicitud, array $marcasDisponibles, array $categoriasDisponibles): array
    {
        $texto = static function (mixed $valor, string $predeterminado = ''): string {
            return is_scalar($valor) ? trim((string) $valor) : $predeterminado;
        };

        $marca = $texto($solicitud->consulta('brand', $solicitud->consulta('marca')));
        $categoriaSolicitada = $solicitud->consulta('cat', $solicitud->consulta('category'));
        $categoria = $texto($categoriaSolicitada);

        if ($categoriaSolicitada === null) {
            foreach ($categoriasDisponibles as $categoriaActiva) {
                if (preg_match('/^celular(?:es)?$/iu', trim($categoriaActiva)) === 1) {
                    $categoria = $categoriaActiva;
                    break;
                }
            }
        }

        $orden = $texto($solicitud->consulta('sort'), 'newest');
        $filtros = [
            'q' => mb_substr($texto($solicitud->consulta('q')), 0, 160),
            'marca' => in_array($marca, $marcasDisponibles, true) ? $marca : '',
            'cat' => in_array($categoria, $categoriasDisponibles, true) ? $categoria : '',
            'min_price' => $texto($solicitud->consulta('min_price')),
            'max_price' => $texto($solicitud->consulta('max_price')),
            'sort' => in_array($orden, ['newest', 'price_asc', 'price_desc', 'name'], true) ? $orden : 'newest',
        ];

        $precioMinimo = is_numeric($filtros['min_price']) && (float) $filtros['min_price'] >= 0
            ? (float) $filtros['min_price']
            : null;
        $precioMaximo = is_numeric($filtros['max_price']) && (float) $filtros['max_price'] >= 0
            ? (float) $filtros['max_price']
            : null;

        if ($precioMinimo !== null && $precioMaximo !== null && $precioMaximo < $precioMinimo) {
            [$precioMinimo, $precioMaximo] = [$precioMaximo, $precioMinimo];
            [$filtros['min_price'], $filtros['max_price']] = [$filtros['max_price'], $filtros['min_price']];
        }

        return [
            'filtros' => $filtros,
            'filtro' => new FiltroProducto(
                busqueda: $filtros['q'],
                marca: $filtros['marca'],
                categoria: $filtros['cat'],
                precioMinimo: $precioMinimo,
                precioMaximo: $precioMaximo,
                pagina: max(1, (int) $solicitud->consulta('page', 1)),
                porPagina: 25,
                orden: $filtros['sort'],
            ),
        ];
    }
}
