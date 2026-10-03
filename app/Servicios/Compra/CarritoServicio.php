<?php

declare(strict_types=1);

namespace App\Servicios\Compra;

use App\Modelos\Productos\Producto;
use App\Servicios\Productos\ProductoServicio;
use App\Soporte\Sesion\GestorSesion;

final class CarritoServicio
{
    public function __construct(private ProductoServicio $productos, private GestorSesion $sesion)
    {
    }

    public function agregar(int $idProducto): void
    {
        $producto = $this->productos->buscarActivo($idProducto);

        if (!$producto || (int) $producto['existencias'] <= 0) {
            return;
        }

        $carrito = (array) $this->sesion->obtener('cart', []);
        $carrito[$idProducto] = min(
            (int) $producto['existencias'],
            (int) ($carrito[$idProducto] ?? 0) + 1
        );
        $this->sesion->guardar('cart', $carrito);
    }

    /**
     * Quita del carrito el producto indicado.
     */
    public function eliminar(int $idProducto): void
    {
        $carrito = (array) $this->sesion->obtener('cart', []);
        unset($carrito[$idProducto]);
        $this->sesion->guardar('cart', $carrito);
    }

    /**
     * Limpia el estado actual y elimina los datos temporales asociados.
     */
    public function limpiar(): void
    {
        $this->sesion->eliminar('cart');
    }

    /**
     * Calcula un resumen consolidado de la información solicitada.
     *
     * @return array{articulos:array<int,array<string,mixed>>,total:float}
     */
    public function resumen(): array
    {
        $articulos = [];
        $totalCentimos = 0;

        foreach ((array) $this->sesion->obtener('cart', []) as $idProducto => $cantidad) {
            $producto = $this->productos->buscarActivo((int) $idProducto);

            if (!$producto) {
                continue;
            }

            $cantidad = max(1, (int) $cantidad);
            $precioCentimos = Producto::desdeRegistro($producto)->precioEfectivoEnCentimos();
            $producto['precio'] = $precioCentimos / 100;
            $producto['cantidad'] = $cantidad;
            $subtotalCentimos = Producto::subtotalEnCentimos($precioCentimos, $cantidad);
            if ($totalCentimos > Producto::MAXIMO_CENTIMOS - $subtotalCentimos) {
                throw new \DomainException('El total excede el importe permitido para el pedido.');
            }
            $producto['subtotal'] = $subtotalCentimos / 100;
            $totalCentimos += $subtotalCentimos;
            $articulos[] = $producto;
        }

        return ['articulos' => $articulos, 'total' => $totalCentimos / 100];
    }

    /**
     * Obtiene los datos originales necesarios para construir la respuesta.
     *
     * @return array<int, int>
     */
    public function datosCrudos(): array
    {
        return (array) $this->sesion->obtener('cart', []);
    }
}
