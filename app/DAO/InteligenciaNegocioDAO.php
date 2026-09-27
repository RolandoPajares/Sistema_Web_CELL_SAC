<?php

declare(strict_types=1);

namespace App\DAO;

use App\Infraestructura\BaseDatos\Conexion;

final class InteligenciaNegocioDAO
{
    public function __construct(private Conexion $conexion)
    {
    }

    /** @return array{ingresos:float,destacados:array<int,array<string,mixed>>,existencias_bajas:array<int,array<string,mixed>>,diario:array<int,array<string,mixed>>} */
    public function resumenActual(): array
    {
        $pdo = $this->conexion->pdoObligatorio();

        return [
            'ingresos' => (float) $pdo->query(
                "SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE creado_en >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')"
            )->fetchColumn(),
            'destacados' => $pdo->query(
                'SELECT p.nombre, p.marca, SUM(dp.cantidad) unidades, SUM(dp.cantidad * dp.precio_unitario) ingresos
                 FROM detalle_pedidos dp JOIN productos p ON p.id = dp.producto_id
                 GROUP BY p.id ORDER BY unidades DESC LIMIT 5'
            )->fetchAll(),
            'existencias_bajas' => $pdo->query(
                'SELECT id, marca, nombre, existencias FROM productos WHERE activo = 1 ORDER BY existencias ASC LIMIT 5'
            )->fetchAll(),
            'diario' => $pdo->query(
                'SELECT DATE(p.creado_en) dia, COALESCE(SUM(dp.cantidad), 0) unidades
                 FROM pedidos p JOIN detalle_pedidos dp ON dp.pedido_id = p.id
                 WHERE p.creado_en >= CURRENT_DATE - INTERVAL 30 DAY
                 GROUP BY DATE(p.creado_en) ORDER BY dia'
            )->fetchAll(),
        ];
    }
}
