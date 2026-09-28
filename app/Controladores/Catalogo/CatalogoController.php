<?php

declare(strict_types=1);

namespace App\Controladores\Catalogo;

use App\DTO\Productos\FiltroProducto;
use App\Soporte\Productos\CategoriasProducto;
use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Productos\ProductoServicio;

final class CatalogoController
{
    public function __construct(private Vista $vista, private ProductoServicio $productos)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        $texto = static function (mixed $valor, string $predeterminado = ''): string {
            return is_scalar($valor) ? trim((string) $valor) : $predeterminado;
        };
        $todosLosProductos = $this->productos->todosActivos();
        $marcas = array_values(array_unique(array_map('strval', array_column($todosLosProductos, 'marca'))));
        sort($marcas, SORT_STRING);

        $marca = $texto($solicitud->consulta('brand', $solicitud->consulta('marca')));
        $categoria = $texto($solicitud->consulta('cat', $solicitud->consulta('category')));
        $orden = $texto($solicitud->consulta('sort'), 'newest');
        $filtros = [
            'q' => mb_substr($texto($solicitud->consulta('q')), 0, 160),
            'marca' => in_array($marca, $marcas, true) ? $marca : '',
            'cat' => in_array($categoria, CategoriasProducto::ALL, true) ? $categoria : '',
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
        $paginacion = $this->productos->paginar(new FiltroProducto(
            busqueda: $filtros['q'],
            marca: $filtros['marca'],
            categoria: $filtros['cat'],
            precioMinimo: $precioMinimo,
            precioMaximo: $precioMaximo,
            pagina: max(1, (int) $solicitud->consulta('page', 1)),
            porPagina: 12,
            orden: $filtros['sort'],
        ));

        return $this->vista->renderizar('publico.catalogo.indice', [
            'tituloPagina' => 'Catálogo',
            'productos' => $paginacion['productos'],
            'paginacion' => $paginacion,
            'filtros' => $filtros,
            'marcas' => $marcas,
            'categorias' => CategoriasProducto::ALL,
        ]);
    }
}
