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

    /**
     * Indica si el repositorio puede consultar la base de datos.
     */
    public function estaDisponible(): bool
    {
        return $this->conexion->pdo() !== null;
    }

    /**
     * Crea o guarda la información relacionada con «pedido pendiente».
     */
    public function crearPedidoPendiente(int $idUsuario, float $total): int
    {
        $sentencia = $this->pdo()->prepare('INSERT INTO pedidos(usuario_id, total, estado) VALUES(?, ?, ?)');
        $sentencia->execute([$idUsuario, $total, 'Pendiente']);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * Crea o guarda la información relacionada con «detalle».
     */
    public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO detalle_pedidos(pedido_id, producto_id, cantidad, precio_unitario) VALUES(?, ?, ?, ?)'
        );
        $sentencia->execute([$idPedido, $idProducto, $cantidad, $precio]);
    }

    /**
     * Devuelve la lista de registros relacionados con «con usuarios».
     */
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

    /**
     * Cuenta los elementos que cumplen las condiciones recibidas.
     */
    public function contar(): int
    {
        $sentencia = $this->pdo()->prepare('SELECT COUNT(*) FROM pedidos');
        $sentencia->execute();
        return (int) $sentencia->fetchColumn();
    }

    /**
     * Obtiene el pedido por su identificador e incluye sus líneas de detalle.
     */
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
            'SELECT dp.producto_id, p.marca, p.nombre, p.categoria, p.url_imagen, dp.cantidad, dp.precio_unitario,
                    dp.cantidad * dp.precio_unitario AS subtotal
             FROM detalle_pedidos dp JOIN productos p ON p.id = dp.producto_id
             WHERE dp.pedido_id = :pedido ORDER BY dp.id'
        );
        $detalle->execute([':pedido' => $idPedido]);
        $pedido['detalle'] = $detalle->fetchAll();

        return $pedido;
    }

    /**
     * Actualiza la información relacionada con «estado».
     */
    public function actualizarEstado(int $idPedido, string $estado): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE pedidos SET estado = :estado WHERE id = :id');
        $sentencia->execute([':estado' => $estado, ':id' => $idPedido]);
        if ($sentencia->rowCount() === 0 && $this->buscarConDetalle($idPedido) === null) {
            throw new \DomainException('El pedido no existe.');
        }
    }

    /** Actualiza el pedido dentro del segmento de clientes asignado al módulo. */
    public function actualizarEstadoParaRolCliente(int $idPedido, string $estado, string $rolCliente): void
    {
        if (!in_array($rolCliente, ['cliente_minorista', 'cliente_mayorista'], true)) {
            throw new \DomainException('El segmento de pedidos no es válido.');
        }

        $sentencia = $this->pdo()->prepare(
            'UPDATE pedidos SET estado = :estado
             WHERE id = :id
               AND usuario_id IN (SELECT id FROM usuarios WHERE rol = :rol)'
        );
        $sentencia->execute([':estado' => $estado, ':id' => $idPedido, ':rol' => $rolCliente]);
        if ($sentencia->rowCount() === 0) {
            $comprobacion = $this->pdo()->prepare(
                'SELECT COUNT(*) FROM pedidos p
                 JOIN usuarios u ON u.id = p.usuario_id
                 WHERE p.id = :id AND u.rol = :rol'
            );
            $comprobacion->execute([':id' => $idPedido, ':rol' => $rolCliente]);
            if ((int) $comprobacion->fetchColumn() === 0) {
                throw new \DomainException('El pedido no existe o no pertenece a este módulo.');
            }
        }
    }

    /**
     * Cuenta los elementos relacionados con «por estado».
     */
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

    /**
     * Cuenta los elementos relacionados con «ventas registradas».
     */
    public function contarVentasRegistradas(): int
    {
        $sentencia = $this->pdo()->prepare('SELECT COUNT(*) FROM pedidos WHERE estado <> :cancelado');
        $sentencia->execute([':cancelado' => 'Cancelado']);
        return (int) $sentencia->fetchColumn();
    }

    /**
     * Calcula las ventas y pedidos acumulados durante el periodo indicado.
     */
    public function totalVentasPeriodo(): float
    {
        $sentencia = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(total), 0) FROM pedidos
             WHERE estado <> :cancelado AND creado_en >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')"
        );
        $sentencia->execute([':cancelado' => 'Cancelado']);
        return (float) $sentencia->fetchColumn();
    }

    /**
     * Obtiene la conexión PDO y detiene la operación si no está disponible.
     */
    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
