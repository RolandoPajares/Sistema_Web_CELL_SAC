<?php

declare(strict_types=1);

namespace App\Servicios\Compra;

use App\Servicios\Productos\ProductoServicio;
use App\Soporte\Sesion\GestorSesion;

final class CarritoServicio
{
    public function __construct(private ProductoServicio $productos, private GestorSesion $sesion)
    {
    }

    public function agregar(int $idProducto, ?int $idVariante = null): void
    {
        $producto = $this->productos->buscarActivo($idProducto);

        if (!$producto || (int) $producto['existencias'] <= 0) {
            return;
        }

        $carrito = (array) $this->sesion->obtener('cart', []);
        $clave = $idVariante !== null ? $idProducto . ':' . $idVariante : (string)$idProducto;
        $limite = (int)($producto['existencias'] ?? 0);
        $carrito[$clave] = min($limite, (int)($carrito[$clave] ?? 0) + 1);
        $this->sesion->guardar('cart', $carrito);
    }

    public function eliminar(int $idProducto): void
    {
        $carrito = (array) $this->sesion->obtener('cart', []);
        unset($carrito[$idProducto]);
        $this->sesion->guardar('cart', $carrito);
    }

    public function limpiar(): void
    {
        $this->sesion->eliminar('cart');
    }

    /** @return array{articulos:array<int,array<string,mixed>>,total:float} */
    public function resumen(): array
    {
        $articulos = [];
        $total = 0.0;

        foreach ((array) $this->sesion->obtener('cart', []) as $idProducto => $cantidad) {
            $producto = $this->productos->buscarActivo((int) $idProducto);

            if (!$producto) {
                continue;
            }

            $cantidad = max(1, (int) $cantidad);
            $producto['cantidad'] = $cantidad;
            $producto['subtotal'] = (float) $producto['precio'] * $cantidad;
            $total += $producto['subtotal'];
            $articulos[] = $producto;
        }

        return ['articulos' => $articulos, 'total' => $total];
    }

    /** @return array<int, int> */
    public function datosCrudos(): array
    {
        return (array) $this->sesion->obtener('cart', []);
    }
}
