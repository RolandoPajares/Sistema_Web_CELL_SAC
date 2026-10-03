<?php

declare(strict_types=1); 

namespace App\DTO\Productos; // Define el espacio de nombres para la clase FiltroProducto, indicando que pertenece al módulo de productos dentro de la aplicación   

final class FiltroProducto
{
    public function __construct(
        public string $busqueda = '',
        public string $marca = '',
        public string $categoria = '',
        public ?float $precioMinimo = null,
        public ?float $precioMaximo = null,
        public int $pagina = 1,
        public int $porPagina = 12,
        public string $orden = 'newest',
    ) {
    }
}
