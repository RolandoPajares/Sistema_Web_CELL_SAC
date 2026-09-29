<?php

declare(strict_types=1);

namespace App\Servicios\Panel;

use App\Servicios\Pedidos\PedidoServicio;
use App\Servicios\Productos\ProductoServicio;
use App\Servicios\Usuarios\UsuarioServicio;
use App\Servicios\Clientes\ClienteServicio;

final class PanelAdministradorServicio
{
    public function __construct(
        private ProductoServicio $productos,
        private PedidoServicio $pedidos,
        private UsuarioServicio $usuarios,
        private ClienteServicio $clientes,
    ) {
    }

    /** @return array<string, int|float> */
    public function estadisticas(): array
    {
        $productos = $this->productos->todosParaAdministrador();
        $clientes = $this->clientes->todos();
        $pedidosPorEstado = $this->pedidos->contarPorEstado();

        return [
            'productos' => $this->productos->contarActivos(),
            'productos_total' => count($productos),
            'existencias' => $this->productos->stockTotal(),
            'stock_bajo' => count(array_filter($productos, static fn (array $producto): bool =>
                (int) $producto['activo'] === 1 && (int) $producto['existencias'] <= 8)),
            'pedidos' => $this->pedidos->contar(),
            'pedidos_pendientes' => $pedidosPorEstado['Pendiente'] ?? 0,
            'clientes' => count(array_filter($clientes, static fn (array $cliente): bool => (int) $cliente['activo'] === 1)),
            'ventas_registradas' => $this->pedidos->contarVentasRegistradas(),
            'ventas_periodo' => $this->pedidos->totalVentasPeriodo(),
            'usuarios' => $this->usuarios->contar(),
        ];
    }
}
