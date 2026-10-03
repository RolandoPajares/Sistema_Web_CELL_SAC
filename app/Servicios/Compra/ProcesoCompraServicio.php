<?php

declare(strict_types=1);

namespace App\Servicios\Compra;

use App\Nucleo\BaseDatos\GestorTransaccionesInterfaz;
use App\DAO\Contratos\RepositorioPedidoInterfaz;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\DAO\Contratos\RepositorioInventarioInterfaz;
use App\Modelos\Productos\Producto;
use App\Soporte\Excepciones\ExcepcionStockInsuficiente;

final class ProcesoCompraServicio
{
    public function __construct(
        private GestorTransaccionesInterfaz $transacciones,
        private RepositorioProductoInterfaz $productos,
        private RepositorioPedidoInterfaz $pedidos,
        private CarritoServicio $carrito,
        private RepositorioInventarioInterfaz $inventario,
    ) {
    }

    /**
     * Procesa «compra» y devuelve el resultado correspondiente.
     */
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
            $totalCentimos = 0;

            foreach ($carrito as $idProducto => $cantidad) {
                $cantidad = max(1, (int) $cantidad);
                $producto = $this->productos->buscarActivoParaActualizar((int) $idProducto);

                if (!$producto) {
                    throw new ExcepcionStockInsuficiente('Un producto ya no está disponible.');
                }

                if ((int) $producto['existencias'] < $cantidad) {
                    throw new ExcepcionStockInsuficiente('Stock insuficiente para ' . $producto['nombre'] . '.');
                }

                $precioCentimos = Producto::desdeRegistro($producto)->precioEfectivoEnCentimos();
                $subtotalCentimos = Producto::subtotalEnCentimos($precioCentimos, $cantidad);
                if ($totalCentimos > Producto::MAXIMO_CENTIMOS - $subtotalCentimos) {
                    throw new \DomainException('El total excede el importe permitido para el pedido.');
                }
                $totalCentimos += $subtotalCentimos;
                $articulos[] = [$producto, $cantidad, $precioCentimos];
            }

            $idPedido = $this->pedidos->crearPedidoPendiente($idUsuario, $totalCentimos / 100);

            foreach ($articulos as [$producto, $cantidad, $precioCentimos]) {
                $this->pedidos->agregarDetalle(
                    $idPedido,
                    (int) $producto['id'],
                    $cantidad,
                    $precioCentimos / 100
                );
                $this->inventario->registrarMovimiento([
                    'producto_id' => (int) $producto['id'],
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $cantidad,
                    'notas' => 'Salida automática por pedido #' . $idPedido,
                ], $idUsuario);
            }

            return $idPedido;
        });

        $this->carrito->limpiar();

        return $idPedido;
    }
}
