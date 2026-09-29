<?php
namespace App\Soporte\Productos;

class ValidadorVarianteProducto
{
    public function tieneStock(int $stockDisponible, int $cantidad): bool
    {
        return $cantidad > 0 && $stockDisponible >= $cantidad;
    }

    public function validarCantidad(int $cantidad): bool
    {
        return $cantidad > 0;
    }
}
