<?php

declare(strict_types=1);

namespace App\DAO\Inventario;

use App\DAO\Contratos\RepositorioInventarioInterfaz;
use App\Nucleo\BaseDatos\Conexion;
use PDO;

final class InventarioDAO implements RepositorioInventarioInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    public function existencias(): array
    {
        return $this->pdo()->query(
            "SELECT p.id, p.marca, p.nombre AS producto, p.categoria, p.existencias,
                    MAX(m.creado_en) AS ultimo_movimiento,
                    CASE WHEN p.existencias <= 3 THEN 'Crítico' WHEN p.existencias <= 8 THEN 'Bajo' ELSE 'Disponible' END AS estado
             FROM productos p
             LEFT JOIN movimientos_inventario m ON m.producto_id = p.id
             WHERE p.activo = 1
             GROUP BY p.id, p.marca, p.nombre, p.categoria, p.existencias
             ORDER BY p.existencias, p.nombre"
        )->fetchAll();
    }

    public function movimientos(): array
    {
        return $this->pdo()->query(
            'SELECT m.id, m.creado_en AS fecha, CONCAT(p.marca, " ", p.nombre) AS producto,
                    m.tipo_movimiento, m.cantidad, m.notas, COALESCE(u.nombre, "—") AS responsable
             FROM movimientos_inventario m
             JOIN productos p ON p.id = m.producto_id
             LEFT JOIN usuarios u ON u.id = m.usuario_id
             ORDER BY m.id DESC LIMIT 200'
        )->fetchAll();
    }

    public function registrarMovimiento(array $datos, int $usuarioId): int
    {
        return (int) $this->conexion->transaccion(function () use ($datos, $usuarioId): int {
            $pdo = $this->pdo();
            $consulta = $pdo->prepare('SELECT existencias FROM productos WHERE id = :id AND activo = 1 FOR UPDATE');
            $consulta->execute([':id' => $datos['producto_id']]);
            $actual = $consulta->fetchColumn();
            if ($actual === false) {
                throw new \DomainException('El producto seleccionado no existe o está inactivo.');
            }

            $nuevo = match ($datos['tipo_movimiento']) {
                'entrada' => (int) $actual + $datos['cantidad'],
                'salida' => (int) $actual - $datos['cantidad'],
                'ajuste' => $datos['cantidad'],
                default => throw new \DomainException('El tipo de movimiento no es válido.'),
            };
            if ($nuevo < 0) {
                throw new \DomainException('La salida supera las existencias disponibles.');
            }

            $movimiento = $pdo->prepare(
                'INSERT INTO movimientos_inventario(producto_id, usuario_id, tipo_movimiento, cantidad, notas)
                 VALUES(:producto, :usuario, :tipo, :cantidad, :notas)'
            );
            $movimiento->execute([':producto' => $datos['producto_id'], ':usuario' => $usuarioId,
                ':tipo' => $datos['tipo_movimiento'], ':cantidad' => $datos['cantidad'], ':notas' => $datos['notas']]);
            $idMovimiento = (int) $pdo->lastInsertId();

            $actualizacion = $pdo->prepare('UPDATE productos SET existencias = :existencias WHERE id = :id');
            $actualizacion->execute([':existencias' => $nuevo, ':id' => $datos['producto_id']]);

            return $idMovimiento;
        });
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
