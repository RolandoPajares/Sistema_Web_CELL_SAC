<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Nucleo\BaseDatos\Conexion;
use App\DAO\Contratos\RepositorioPedidoInterfaz;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\Servicios\Compra\CarritoServicio;
use App\Servicios\Compra\ProcesoCompraServicio;
use App\Servicios\Productos\ProductoServicio;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\DTO\Productos\FiltroProducto;
use App\Soporte\Sesion\GestorSesion;
use PHPUnit\Framework\TestCase;
use PDO;

final class ProcesoCompraServicioTest extends TestCase
{
    public function testCheckoutRequiresCartItems(): void
    {
        $_SESSION['cart'] = [];
        $productos = new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return false;
            }
            public function todosActivos(): array
            {
                return [];
            }
            public function paginar(FiltroProducto $filtro): array
            {
                return ['productos' => [], 'total' => 0, 'pagina' => 1, 'por_pagina' => 12, 'ultima_pagina' => 1];
            }
            public function todosParaAdministrador(): array
            {
                return [];
            }
            public function buscarActivo(int $id): ?array
            {
                return null;
            }
            public function buscarParaAdministrador(int $id): ?array
            {
                return null;
            }
            public function buscarActivoParaActualizar(int $id): ?array
            {
                return null;
            }
            public function existeConMarcaYNombre(string $marca, string $nombre, ?int $idExcluido = null): bool
            {
                return false;
            }
            public function crear(array $datos): int
            {
                return 1;
            }
            public function actualizar(int $id, array $datos): void
            {
            }
            public function delete(int $id): bool
            {
                return true;
            }
            public function desactivar(int $id): void
            {
            }
            public function reducirStock(int $id, int $cantidad): void
            {
            }
            public function contarActivos(): int
            {
                return 0;
            }
            public function stockTotal(): int
            {
                return 0;
            }
        };
        $pedidos = new class implements RepositorioPedidoInterfaz {
            public function estaDisponible(): bool
            {
                return false;
            }
            public function crearPedidoPendiente(int $idUsuario, float $total): int
            {
                return 1;
            }
            public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void
            {
            }
            public function todosConUsuarios(): array
            {
                return [];
            }
            public function contar(): int
            {
                return 0;
            }
        };
        $carrito = new CarritoServicio(new ProductoServicio($productos), new GestorSesion());
        $servicio = new ProcesoCompraServicio(new Conexion(new RepositorioConfiguracion([])), $productos, $pedidos, $carrito);

        $this->expectException(\RuntimeException::class);
        $servicio->procesarCompra(1);
    }

    public function testCheckoutRollsBackWhenStockUpdateFails(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite is required for the transaction test.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE test_orders (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, total REAL)');
        $sesion = new GestorSesion();
        $sesion->guardar('cart', [1 => 1]);
        $productos = new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            public function todosActivos(): array
            {
                return [];
            }
            public function paginar(FiltroProducto $filtro): array
            {
                return ['productos' => [], 'total' => 0, 'pagina' => 1, 'por_pagina' => 12, 'ultima_pagina' => 1];
            }
            public function todosParaAdministrador(): array
            {
                return [];
            }
            public function buscarActivo(int $id): ?array
            {
                if ($id <= 0) {
                    return null;
                }

                return ['id' => 1, 'nombre' => 'Teléfono', 'precio' => 100, 'existencias' => 1];
            }
            public function buscarParaAdministrador(int $id): ?array
            {
                return null;
            }
            public function buscarActivoParaActualizar(int $id): ?array
            {
                if ($id <= 0) {
                    return null;
                }

                return ['id' => 1, 'nombre' => 'Teléfono', 'precio' => 100, 'existencias' => 1];
            }
            public function existeConMarcaYNombre(string $marca, string $nombre, ?int $idExcluido = null): bool
            {
                return false;
            }
            public function crear(array $datos): int
            {
                return 1;
            }
            public function actualizar(int $id, array $datos): void
            {
            }
            public function delete(int $id): bool
            {
                return true;
            }
            public function desactivar(int $id): void
            {
            }
            public function reducirStock(int $id, int $cantidad): void
            {
                throw new \RuntimeException('forced failure');
            }
            public function contarActivos(): int
            {
                return 1;
            }
            public function stockTotal(): int
            {
                return 1;
            }
        };
        $pedidos = new class ($pdo) implements RepositorioPedidoInterfaz {
            public function __construct(private PDO $pdo)
            {
            }
            public function estaDisponible(): bool
            {
                return true;
            }
            public function crearPedidoPendiente(int $idUsuario, float $total): int
            {
                $this->pdo->prepare('INSERT INTO test_orders(user_id, total) VALUES(?, ?)')->execute([$idUsuario, $total]);
                return (int) $this->pdo->lastInsertId();
            }
            public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void
            {
            }
            public function todosConUsuarios(): array
            {
                return [];
            }
            public function contar(): int
            {
                return 0;
            }
        };
        $carrito = new CarritoServicio(new ProductoServicio($productos), $sesion);
        $servicio = new ProcesoCompraServicio(new Conexion(new RepositorioConfiguracion([]), $pdo), $productos, $pedidos, $carrito);

        try {
            $servicio->procesarCompra(1);
            self::fail('The checkout should fail.');
        } catch (\RuntimeException) {
            self::assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM test_orders')->fetchColumn());
            self::assertSame([1 => 1], $carrito->datosCrudos());
        }
    }
}
