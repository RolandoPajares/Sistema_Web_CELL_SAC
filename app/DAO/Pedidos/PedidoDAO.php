<?php

declare(strict_types=1);

namespace App\DAO\Pedidos;

use App\Nucleo\BaseDatos\Conexion;
use App\DAO\Contratos\RepositorioPedidoInterfaz;
use PDO;

final class PedidoDAO implements RepositorioPedidoInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    public function estaDisponible(): bool
    {
        return $this->conexion->pdo() !== null;
    }

    public function crearPedidoPendiente(int $idUsuario, float $total): int
    {
        $sentencia = $this->pdo()->prepare('INSERT INTO pedidos(usuario_id, total, estado) VALUES(?, ?, ?)');
        $sentencia->execute([$idUsuario, $total, 'Pendiente']);

        return (int) $this->pdo()->lastInsertId();
    }

    public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO detalle_pedidos(pedido_id, producto_id, cantidad, precio_unitario) VALUES(?, ?, ?, ?)'
        );
        $sentencia->execute([$idPedido, $idProducto, $cantidad, $precio]);
    }

    public function todosConUsuarios(): array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT p.*, u.nombre, u.correo
             FROM pedidos p
             JOIN usuarios u ON u.id = p.usuario_id
             ORDER BY p.id DESC'
        );
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    public function contar(): int
    {
        $sentencia = $this->pdo()->prepare('SELECT COUNT(*) FROM pedidos');
        $sentencia->execute();
        return (int) $sentencia->fetchColumn();
    }

    public function buscarConDetalle(int $idPedido): ?array
    {
        $cabecera = $this->pdo()->prepare(
            'SELECT p.id, p.usuario_id, p.total, p.estado, p.creado_en, u.nombre, u.correo
             FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id WHERE p.id = :id LIMIT 1'
        );
        $cabecera->execute([':id' => $idPedido]);
        $pedido = $cabecera->fetch();
        if (!$pedido) {
            return null;
        }
        $detalle = $this->pdo()->prepare(
            'SELECT dp.producto_id, p.marca, p.nombre, dp.cantidad, dp.precio_unitario,
                    dp.cantidad * dp.precio_unitario AS subtotal
             FROM detalle_pedidos dp JOIN productos p ON p.id = dp.producto_id
             WHERE dp.pedido_id = :pedido ORDER BY dp.id'
        );
        $detalle->execute([':pedido' => $idPedido]);
        $pedido['detalle'] = $detalle->fetchAll();

        return $pedido;
    }

    public function actualizarEstado(int $idPedido, string $estado): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE pedidos SET estado = :estado WHERE id = :id');
        $sentencia->execute([':estado' => $estado, ':id' => $idPedido]);
        if ($sentencia->rowCount() === 0 && $this->buscarConDetalle($idPedido) === null) {
            throw new \DomainException('El pedido no existe.');
        }
    }

    public function contarPorEstado(): array
    {
        $sentencia = $this->pdo()->prepare('SELECT estado, COUNT(*) AS total FROM pedidos GROUP BY estado');
        $sentencia->execute();
        $resultado = ['Pendiente' => 0, 'En proceso' => 0, 'Enviado' => 0, 'Entregado' => 0, 'Cancelado' => 0];
        foreach ($sentencia->fetchAll() as $fila) {
            if (array_key_exists((string) $fila['estado'], $resultado)) {
                $resultado[(string) $fila['estado']] = (int) $fila['total'];
            }
        }
        return $resultado;
    }

    public function contarVentasRegistradas(): int
    {
        $sentencia = $this->pdo()->prepare('SELECT COUNT(*) FROM pedidos WHERE estado <> :cancelado');
        $sentencia->execute([':cancelado' => 'Cancelado']);
        return (int) $sentencia->fetchColumn();
    }

    public function totalVentasPeriodo(): float
    {
        $sentencia = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(total), 0) FROM pedidos
             WHERE estado <> :cancelado AND creado_en >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')"
        );
        $sentencia->execute([':cancelado' => 'Cancelado']);
        return (float) $sentencia->fetchColumn();
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
