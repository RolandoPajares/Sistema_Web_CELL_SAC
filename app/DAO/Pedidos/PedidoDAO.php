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
        $sentencia = $this->pdo()->query(
            'SELECT p.*, u.nombre, u.correo
             FROM pedidos p
             JOIN usuarios u ON u.id = p.usuario_id
             ORDER BY p.id DESC'
        );

        return $sentencia->fetchAll();
    }

    public function contar(): int
    {
        return (int) $this->pdo()->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
