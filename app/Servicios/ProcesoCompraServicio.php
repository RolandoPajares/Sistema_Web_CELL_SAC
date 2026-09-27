<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Contratos\GestorTransaccionesInterfaz;
use App\Repositorios\Contratos\RepositorioPedidoInterfaz;
use App\Repositorios\Contratos\RepositorioProductoInterfaz;
use App\Soporte\Excepciones\ExcepcionStockInsuficiente;

final class ProcesoCompraServicio
{
    public function __construct(
        private GestorTransaccionesInterfaz $transacciones,
        private RepositorioProductoInterfaz $productos,
        private RepositorioPedidoInterfaz $pedidos,
        private CarritoServicio $carrito,
    ) {
    }

    public function procesarCompra(int $idUsuario): int
    {
        $carrito = $this->carrito->datosCrudos();

        if ($carrito === []) {
            throw new \RuntimeException('No hay productos pendientes.');
        }

        if (!$this->productos->estaDisponible() || !$this->pedidos->estaDisponible()) {
            throw new \RuntimeException('La base de datos debe estar instalada.');
        }

        $idPedido = (int) $this->transacciones->transaccion(function () use ($carrito, $idUsuario): int {
            $articulos = [];
            $total = 0.0;

            foreach ($carrito as $idProducto => $cantidad) {
                $cantidad = max(1, (int) $cantidad);
                $producto = $this->productos->buscarActivoParaActualizar((int) $idProducto);

                if (!$producto) {
                    throw new ExcepcionStockInsuficiente('Un producto ya no está disponible.');
                }

                if ((int) $producto['existencias'] < $cantidad) {
                    throw new ExcepcionStockInsuficiente('Stock insuficiente para ' . $producto['nombre'] . '.');
                }

                $total += (float) $producto['precio'] * $cantidad;
                $articulos[] = [$producto, $cantidad];
            }

            $idPedido = $this->pedidos->crearPedidoPendiente($idUsuario, $total);

            foreach ($articulos as [$producto, $cantidad]) {
                $this->pedidos->agregarDetalle(
                    $idPedido,
                    (int) $producto['id'],
                    $cantidad,
                    (float) $producto['precio']
                );
                $this->productos->reducirStock((int) $producto['id'], $cantidad);
            }

            return $idPedido;
        });

        $this->carrito->limpiar();

        return $idPedido;
    }
}
