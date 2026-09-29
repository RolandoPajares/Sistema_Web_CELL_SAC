<?php

declare(strict_types=1);

namespace App\DAO\Panel;

use App\Nucleo\BaseDatos\Conexion;
use PDO;

final class ModuloDAO
{
    public function __construct(private Conexion $conexion)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listar(string $modulo): array
    {
        $sql = match ($modulo) {
            'categorias' => 'SELECT id, nombre, descripcion, activo, creado_en FROM categorias ORDER BY id DESC',
            'proveedores' => 'SELECT id, nombre, ruc, correo, telefono, ciudad, activo FROM proveedores ORDER BY id DESC',
            'clientes', 'clientes-mayoristas' => "SELECT id, tipo, documento, razon_social AS empresa, nombre_contacto AS contacto, correo, telefono, ciudad, activo FROM clientes WHERE (:tipo = '' OR tipo = :tipo2) ORDER BY id DESC",
            'cotizaciones' => 'SELECT co.id, co.cliente_id, COALESCE(cl.razon_social, cl.nombre_contacto) AS cliente, co.total, co.estado, co.notas, co.activo, co.creado_en FROM cotizaciones co JOIN clientes cl ON cl.id = co.cliente_id ORDER BY co.id DESC',
            'compras' => 'SELECT c.id, c.proveedor_id, p.nombre AS proveedor, c.total, c.estado, c.fecha_compra AS fecha, c.activo FROM compras c JOIN proveedores p ON p.id = c.proveedor_id ORDER BY c.id DESC',
            'inventario', 'alertas-stock' => 'SELECT p.id, p.marca, p.nombre AS producto, p.categoria, p.existencias, CASE WHEN p.existencias <= 3 THEN \'Crítico\' WHEN p.existencias <= 8 THEN \'Bajo\' ELSE \'Disponible\' END AS estado FROM productos p WHERE p.activo = 1 ORDER BY p.existencias ASC, p.nombre',
            'pedidos', 'pedidos-mayoristas' => 'SELECT p.id, u.nombre AS cliente, p.total, p.estado, p.creado_en FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.id DESC',
            'usuarios' => 'SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY id DESC',
            'publicidad', 'campanias' => 'SELECT id, nombre, ubicacion, titulo, activo, vistas, clics FROM campanas_publicitarias ORDER BY id DESC',
            default => '',
        };

        if ($sql === '') {
            return [];
        }

        $sentencia = $this->pdo()->prepare($sql);
        if (in_array($modulo, ['clientes', 'clientes-mayoristas'], true)) {
            $tipo = $modulo === 'clientes-mayoristas' ? 'mayorista' : '';
            $sentencia->bindValue(':tipo', $tipo);
            $sentencia->bindValue(':tipo2', $tipo);
        }
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    /** @param array<string, mixed> $datos */
    public function crear(string $modulo, array $datos, int $usuarioId): int
    {
        return match ($modulo) {
            'categorias' => $this->insertar(
                'INSERT INTO categorias(nombre, descripcion) VALUES(:nombre, :descripcion)',
                [':nombre' => $datos['nombre'], ':descripcion' => $datos['descripcion']]
            ),
            'proveedores' => $this->insertar(
                'INSERT INTO proveedores(nombre, ruc, correo, telefono, ciudad) VALUES(:nombre, :ruc, :correo, :telefono, :ciudad)',
                [':nombre' => $datos['nombre'], ':ruc' => $datos['ruc'], ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']]
            ),
            'clientes', 'clientes-mayoristas' => $this->insertar(
                'INSERT INTO clientes(tipo, documento, razon_social, nombre_contacto, correo, telefono, ciudad) VALUES(:tipo, :documento, :empresa, :contacto, :correo, :telefono, :ciudad)',
                [
                    ':tipo' => $modulo === 'clientes-mayoristas' ? 'mayorista' : $datos['tipo'],
                    ':documento' => $datos['documento'], ':empresa' => $datos['empresa'], ':contacto' => $datos['contacto'],
                    ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad'],
                ]
            ),
            'cotizaciones' => $this->insertar(
                'INSERT INTO cotizaciones(cliente_id, creado_por, total, estado, notas) VALUES(:cliente, :usuario, :total, :estado, :notas)',
                [':cliente' => (int) $datos['cliente_id'], ':usuario' => $usuarioId, ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':notas' => $datos['notas']]
            ),
            'compras' => $this->insertar(
                'INSERT INTO compras(proveedor_id, creado_por, total, estado, fecha_compra) VALUES(:proveedor, :usuario, :total, :estado, :fecha)',
                [':proveedor' => (int) $datos['proveedor_id'], ':usuario' => $usuarioId, ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':fecha' => $datos['fecha']]
            ),
            'inventario' => $this->registrarMovimiento($datos, $usuarioId),
            default => throw new \InvalidArgumentException('El módulo no admite creación de registros.'),
        };
    }

    /** @param array<string, mixed> $datos */
    public function actualizar(string $modulo, int $id, array $datos): void
    {
        [$sql, $parametros] = match ($modulo) {
            'categorias' => ['UPDATE categorias SET nombre=:nombre, descripcion=:descripcion WHERE id=:id', [':nombre' => $datos['nombre'], ':descripcion' => $datos['descripcion']]],
            'proveedores' => ['UPDATE proveedores SET nombre=:nombre, ruc=:ruc, correo=:correo, telefono=:telefono, ciudad=:ciudad WHERE id=:id', [':nombre' => $datos['nombre'], ':ruc' => $datos['ruc'], ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']]],
            'clientes', 'clientes-mayoristas' => ['UPDATE clientes SET tipo=:tipo, documento=:documento, razon_social=:empresa, nombre_contacto=:contacto, correo=:correo, telefono=:telefono, ciudad=:ciudad WHERE id=:id', [':tipo' => $modulo === 'clientes-mayoristas' ? 'mayorista' : $datos['tipo'], ':documento' => $datos['documento'], ':empresa' => $datos['empresa'], ':contacto' => $datos['contacto'], ':correo' => $datos['correo'], ':telefono' => $datos['telefono'], ':ciudad' => $datos['ciudad']]],
            'cotizaciones' => ['UPDATE cotizaciones SET cliente_id=:cliente, total=:total, estado=:estado, notas=:notas WHERE id=:id', [':cliente' => (int) $datos['cliente_id'], ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':notas' => $datos['notas']]],
            'compras' => ['UPDATE compras SET proveedor_id=:proveedor, total=:total, estado=:estado, fecha_compra=:fecha WHERE id=:id', [':proveedor' => (int) $datos['proveedor_id'], ':total' => (float) $datos['total'], ':estado' => $datos['estado'], ':fecha' => $datos['fecha']]],
            'pedidos', 'pedidos-mayoristas' => ['UPDATE pedidos SET estado=:estado WHERE id=:id', [':estado' => $datos['estado']]],
            default => throw new \InvalidArgumentException('El módulo no admite actualización.'),
        };
        $parametros[':id'] = $id;
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->execute($parametros);
        if ($sentencia->rowCount() === 0 && !$this->existe($this->tabla($modulo), $id)) {
            throw new \RuntimeException('El registro solicitado no existe.');
        }
    }

    public function desactivar(string $modulo, int $id): void
    {
        $tabla = $this->tabla($modulo);
        if (!in_array($tabla, ['categorias', 'proveedores', 'clientes', 'cotizaciones', 'compras'], true)) {
            throw new \InvalidArgumentException('El módulo no admite desactivación.');
        }
        $sentencia = $this->pdo()->prepare("UPDATE {$tabla} SET activo = 0 WHERE id = :id");
        $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
        $sentencia->execute();
        if ($sentencia->rowCount() === 0) {
            throw new \RuntimeException('El registro solicitado no existe o ya está inactivo.');
        }
    }

    /** @return array<string, int|float> */
    public function resumen(): array
    {
        $pdo = $this->pdo();

        return [
            'productos' => (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE activo=1')->fetchColumn(),
            'existencias' => (int) $pdo->query('SELECT COALESCE(SUM(existencias),0) FROM productos WHERE activo=1')->fetchColumn(),
            'stock_bajo' => (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE activo=1 AND existencias<=8')->fetchColumn(),
            'pedidos' => (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
            'clientes' => (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol IN ('cliente_minorista','cliente_mayorista')")->fetchColumn(),
            'ventas' => (float) $pdo->query('SELECT COALESCE(SUM(total),0) FROM pedidos')->fetchColumn(),
        ];
    }

    /** @return array<int, array{id:int,etiqueta:string}> */
    public function opciones(string $tipo): array
    {
        $sql = match ($tipo) {
            'productos' => "SELECT id, CONCAT(marca, ' ', nombre, ' · stock ', existencias) AS etiqueta FROM productos WHERE activo=1 ORDER BY nombre",
            'proveedores' => "SELECT id, nombre AS etiqueta FROM proveedores WHERE activo=1 ORDER BY nombre",
            'clientes' => "SELECT id, COALESCE(NULLIF(razon_social,''), nombre_contacto) AS etiqueta FROM clientes WHERE activo=1 ORDER BY etiqueta",
            default => throw new \InvalidArgumentException('Tipo de opción no permitido.'),
        };

        return $this->pdo()->query($sql)->fetchAll();
    }

    /** @param array<string, mixed> $parametros */
    private function insertar(string $sql, array $parametros): int
    {
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->execute($parametros);

        return (int) $this->pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $datos */
    private function registrarMovimiento(array $datos, int $usuarioId): int
    {
        return (int) $this->conexion->transaccion(function () use ($datos, $usuarioId): int {
            $pdo = $this->pdo();
            $productoId = (int) $datos['producto_id'];
            $cantidad = (int) $datos['cantidad'];
            $tipo = (string) $datos['tipo_movimiento'];
            $consulta = $pdo->prepare('SELECT existencias FROM productos WHERE id=:id AND activo=1 FOR UPDATE');
            $consulta->execute([':id' => $productoId]);
            $existenciasActuales = $consulta->fetchColumn();
            if ($existenciasActuales === false) {
                throw new \RuntimeException('El producto seleccionado no existe.');
            }
            $nuevoStock = match ($tipo) {
                'entrada' => (int) $existenciasActuales + $cantidad,
                'salida' => (int) $existenciasActuales - $cantidad,
                'ajuste' => $cantidad,
                default => throw new \InvalidArgumentException('Tipo de movimiento inválido.'),
            };
            if ($nuevoStock < 0) {
                throw new \RuntimeException('El movimiento dejaría el stock en un valor negativo.');
            }
            $movimiento = $pdo->prepare('INSERT INTO movimientos_inventario(producto_id,usuario_id,tipo_movimiento,cantidad,notas) VALUES(:producto,:usuario,:tipo,:cantidad,:notas)');
            $movimiento->execute([':producto' => $productoId, ':usuario' => $usuarioId, ':tipo' => $tipo, ':cantidad' => $cantidad, ':notas' => $datos['notas']]);
            $actualizacion = $pdo->prepare('UPDATE productos SET existencias=:existencias WHERE id=:id');
            $actualizacion->execute([':existencias' => $nuevoStock, ':id' => $productoId]);

            return (int) $pdo->lastInsertId();
        });
    }

    private function existe(string $tabla, int $id): bool
    {
        $permitidas = ['categorias', 'proveedores', 'clientes', 'cotizaciones', 'compras', 'pedidos'];
        if (!in_array($tabla, $permitidas, true)) {
            return false;
        }
        $sentencia = $this->pdo()->prepare("SELECT COUNT(*) FROM {$tabla} WHERE id=:id");
        $sentencia->execute([':id' => $id]);

        return (int) $sentencia->fetchColumn() > 0;
    }

    private function tabla(string $modulo): string
    {
        return match ($modulo) {
            'categorias' => 'categorias',
            'proveedores' => 'proveedores',
            'clientes', 'clientes-mayoristas' => 'clientes',
            'cotizaciones' => 'cotizaciones',
            'compras' => 'compras',
            'pedidos', 'pedidos-mayoristas' => 'pedidos',
            default => '',
        };
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
