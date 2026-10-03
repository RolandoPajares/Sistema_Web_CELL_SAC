<?php

declare(strict_types=1);

namespace App\Nucleo\Presentacion\Catalogo;

/** Prepara las opciones de filtros y los enlaces del listado del catálogo. */
final class PresentadorCatalogo
{
    /**
     * @param array<int, array<string, mixed>> $productos
     * @param array<int, array<string, mixed>> $categorias
     * @return array{marcas:array<int, string>, categorias:array<int, string>}
     */
    public static function presentarOpcionesFiltros(array $productos, array $categorias): array
    {
        $categorias = array_map('strval', array_column($categorias, 'nombre'));
        $marcas = array_values(array_unique(array_map('strval', array_column($productos, 'marca'))));
        sort($marcas, SORT_STRING);

        return [
            'marcas' => $marcas,
            'categorias' => $categorias,
        ];
    }

    /**
     * @param array<string, mixed> $filtros
     * @param array<string, mixed> $paginacion
     * @return array{enlaceConFiltros:callable, enlacesPaginacion:array<int, array{pagina:int, url:string}>}
     */
    // Prepara los enlaces de filtros y paginación para el listado del catálogo.
    public static function presentarEnlaces(array $filtros, array $paginacion): array
    {
        $enlaceConFiltros = static function (array $ajustes = []) use ($filtros): string {
            $consulta = [
                'q' => $filtros['q'],
                'brand' => $filtros['marca'],
                'cat' => $filtros['cat'],
                'min_price' => $filtros['min_price'],
                'max_price' => $filtros['max_price'],
                'sort' => $filtros['sort'],
            ];
            $consulta = array_merge($consulta, $ajustes);
            unset($consulta['page']);

            return url_interna('catalog?' . http_build_query($consulta));
        };
        $enlacesPaginacion = [];
// Genera los enlaces de paginación para cada página disponible en el listado del catálogo.
        for ($pagina = 1; $pagina <= $paginacion['ultima_pagina']; $pagina++) {
            $consulta = array_merge($filtros, ['page' => $pagina]);
            $enlacesPaginacion[] = [
                'pagina' => $pagina,
                'url' => url_interna('catalog?' . http_build_query($consulta)),
            ];
        }
// Devuelve un array con los enlaces de filtros y paginación para el listado del catálogo.
        return [
            'enlaceConFiltros' => $enlaceConFiltros,
            'enlacesPaginacion' => $enlacesPaginacion,
        ];
    }
}
