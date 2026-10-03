<?php

declare(strict_types=1);

namespace App\DAO\ComercioInteligente;

use App\DAO\Contratos\RepositorioInteligenciaNegocioInterfaz;
use App\Nucleo\BaseDatos\Conexion;

final class InteligenciaNegocioDAO implements RepositorioInteligenciaNegocioInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * Calcula un resumen de la actividad correspondiente al periodo actual.
     *
     * @return array{ingresos:float,destacados:array<int,array<string,mixed>>,existencias_bajas:array<int,array<string,mixed>>,diario:array<int,array<string,mixed>>}
     */
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
                'SELECT id, marca, nombre, categoria, url_imagen, existencias FROM productos
                 WHERE activo = 1 AND existencias <= 8 ORDER BY existencias ASC LIMIT 5'
            )->fetchAll(),
            'diario' => $pdo->query(
                'SELECT DATE(p.creado_en) dia, COALESCE(SUM(dp.cantidad), 0) unidades
                 FROM pedidos p JOIN detalle_pedidos dp ON dp.pedido_id = p.id
                 WHERE p.creado_en >= CURRENT_DATE - INTERVAL 30 DAY
                 GROUP BY DATE(p.creado_en) ORDER BY dia'
            )->fetchAll(),
        ];
    }

    /**
     * Agrupa las ventas por mes para generar el reporte solicitado.
     *
     * @return array<int, array{periodo:string,total:float,pedidos:int}>
     */
    public function ventasMensuales(int $meses = 12): array
    {
        $meses = min(24, max(1, $meses));
        $sentencia = $this->conexion->pdoObligatorio()->prepare(
            'SELECT DATE_FORMAT(creado_en, "%Y-%m") AS periodo,
                    COALESCE(SUM(total), 0) AS total, COUNT(*) AS pedidos
             FROM pedidos
             WHERE estado <> :cancelado AND creado_en >= DATE_SUB(CURRENT_DATE, INTERVAL ' . $meses . ' MONTH)
             GROUP BY DATE_FORMAT(creado_en, "%Y-%m")
             ORDER BY periodo'
        );
        $sentencia->bindValue(':cancelado', 'Cancelado');
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    /**
     * Devuelve los pedidos más recientes para mostrarlos en el panel.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pedidosRecientes(int $limite = 6): array
    {
        $sentencia = $this->conexion->pdoObligatorio()->prepare(
            'SELECT p.id, p.total, p.estado, p.creado_en, u.nombre
             FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
             ORDER BY p.creado_en DESC, p.id DESC LIMIT :limite'
        );
        $sentencia->bindValue(':limite', min(20, max(1, $limite)), \PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    /**
     * Calcula los indicadores del periodo solicitado.
     *
     * @return array{ventas:float,pedidos:int,productos_vendidos:int,clientes:int}
     */
    public function resumenPeriodo(string $desde, string $hasta): array
    {
        $pdo = $this->conexion->pdoObligatorio();
        $sentencia = $pdo->prepare(
            'SELECT COALESCE(SUM(total), 0) AS ventas, COUNT(*) AS pedidos,
                    COUNT(DISTINCT usuario_id) AS clientes
             FROM pedidos
             WHERE estado <> :cancelado AND DATE(creado_en) BETWEEN :desde AND :hasta'
        );
        $sentencia->execute([':cancelado' => 'Cancelado', ':desde' => $desde, ':hasta' => $hasta]);
        $fila = $sentencia->fetch() ?: [];
        $productos = $pdo->prepare(
            'SELECT COALESCE(SUM(dp.cantidad), 0)
             FROM detalle_pedidos dp JOIN pedidos p ON p.id = dp.pedido_id
             WHERE p.estado <> :cancelado AND DATE(p.creado_en) BETWEEN :desde AND :hasta'
        );
        $productos->execute([':cancelado' => 'Cancelado', ':desde' => $desde, ':hasta' => $hasta]);

        return [
            'ventas' => (float) ($fila['ventas'] ?? 0),
            'pedidos' => (int) ($fila['pedidos'] ?? 0),
            'productos_vendidos' => (int) $productos->fetchColumn(),
            'clientes' => (int) ($fila['clientes'] ?? 0),
        ];
    }

    /**
     * Agrupa las ventas por día para el periodo del reporte.
     *
     * @return array<int, array{periodo:string,total:float,pedidos:int}>
     */
    public function ventasDiarias(string $desde, string $hasta): array
    {
        $sentencia = $this->conexion->pdoObligatorio()->prepare(
            'SELECT DATE(creado_en) AS periodo, COALESCE(SUM(total), 0) AS total, COUNT(*) AS pedidos
             FROM pedidos
             WHERE estado <> :cancelado AND DATE(creado_en) BETWEEN :desde AND :hasta
             GROUP BY DATE(creado_en) ORDER BY periodo'
        );
        $sentencia->execute([':cancelado' => 'Cancelado', ':desde' => $desde, ':hasta' => $hasta]);

        return $sentencia->fetchAll();
    }

    /**
     * Devuelve las filas de detalle que corresponden al reporte solicitado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function detalleReporte(string $tipo, string $desde, string $hasta): array
    {
        $pdo = $this->conexion->pdoObligatorio();
        $consulta = match ($tipo) {
            'pedidos' => 'SELECT p.id, u.nombre AS cliente, p.creado_en AS fecha, p.total, p.estado
                          FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
                          WHERE DATE(p.creado_en) BETWEEN :desde AND :hasta ORDER BY p.creado_en DESC',
            'inventario' => 'SELECT CONCAT(p.marca, " ", p.nombre) AS producto, p.categoria,
                             p.existencias AS stock, p.activo AS estado
                             FROM productos p ORDER BY p.existencias, p.nombre',
            'productos' => 'SELECT CONCAT(p.marca, " ", p.nombre) AS producto, c.nombre AS categoria,
                            p.precio, p.existencias AS stock, p.activo AS estado
                            FROM productos p JOIN categorias c ON c.id = p.categoria_id ORDER BY p.nombre',
            'clientes' => 'SELECT nombre_contacto AS cliente, documento, tipo, correo, telefono, activo AS estado
                           FROM clientes ORDER BY activo DESC, nombre_contacto',
            default => 'SELECT CONCAT(pr.marca, " ", pr.nombre) AS producto, c.nombre AS categoria,
                        COALESCE(SUM(dp.cantidad), 0) AS cantidad,
                        COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) AS total
                        FROM detalle_pedidos dp
                        JOIN pedidos p ON p.id = dp.pedido_id
                        JOIN productos pr ON pr.id = dp.producto_id
                        JOIN categorias c ON c.id = pr.categoria_id
                        WHERE p.estado <> :cancelado AND DATE(p.creado_en) BETWEEN :desde AND :hasta
                        GROUP BY pr.id, pr.marca, pr.nombre, c.nombre ORDER BY total DESC',
        };
        $sentencia = $pdo->prepare($consulta);
        if (in_array($tipo, ['inventario', 'productos', 'clientes'], true)) {
            $sentencia->execute();
        } else {
            $parametros = [':desde' => $desde, ':hasta' => $hasta];
            if ($tipo === 'ventas') {
                $parametros[':cancelado'] = 'Cancelado';
            }
            $sentencia->execute($parametros);
        }

        return $sentencia->fetchAll();
    }
}
