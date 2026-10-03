<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Nucleo\BaseDatos\Conexion;
use App\DAO\Contratos\RepositorioPedidoInterfaz;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\DAO\Contratos\RepositorioInventarioInterfaz;
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
    public function testLaCompraRechazaProductoInactivoYExistenciasInsuficientes(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite es necesario para probar el rollback de compra.');
        }

        foreach ([null, ['id' => 7, 'nombre' => 'Producto', 'precio' => '10.00', 'existencias' => 1]] as $productoBloqueado) {
            $pdo = new PDO('sqlite::memory:');
            $sesion = new GestorSesion();
            $sesion->guardar('cart', [7 => 2]);
            $productos = $this->createMock(RepositorioProductoInterfaz::class);
            $productos->method('estaDisponible')->willReturn(true);
            $productos->method('buscarActivoParaActualizar')->with(7)->willReturn($productoBloqueado);
            $pedidos = $this->createMock(RepositorioPedidoInterfaz::class);
            $pedidos->method('estaDisponible')->willReturn(true);
            $inventario = $this->createMock(RepositorioInventarioInterfaz::class);
            $inventario->expects(self::never())->method('registrarMovimiento');
            $carrito = new CarritoServicio(new ProductoServicio($productos), $sesion);
            $servicio = new ProcesoCompraServicio(
                new Conexion(new RepositorioConfiguracion([]), $pdo),
                $productos,
                $pedidos,
                $carrito,
                $inventario
            );

            try {
                $servicio->procesarCompra(4);
                self::fail('La confirmación debía fallar para producto inactivo o stock insuficiente.');
            } catch (\App\Soporte\Excepciones\ExcepcionStockInsuficiente) {
                self::assertSame([7 => 2], $carrito->datosCrudos());
            }
        }
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testCheckoutRequiresCartItems`.
     */
    public function testLaCompraRequiereProductosEnElCarrito(): void
    {
        $_SESSION['cart'] = [];
        $productos = new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return false;
            }
            /**
             * Devuelve todos los registros activos del repositorio.
             */
            public function todosActivos(): array
            {
                return [];
            }
            /**
             * Devuelve productos activos con descuentos reales, ordenados según ventas y disponibilidad.
             */
            public function ofertasPopulares(): array
            {
                return [];
            }
            /**
             * Devuelve los productos de la página solicitada junto con los datos de paginación.
             */
            public function paginar(FiltroProducto $filtro): array
            {
                return ['productos' => [], 'total' => 0, 'pagina' => 1, 'por_pagina' => 12, 'ultima_pagina' => 1];
            }
            /**
             * Devuelve los registros disponibles para el panel de administración.
             */
            public function todosParaAdministrador(): array
            {
                return [];
            }
            /**
             * Busca el registro activo que coincide con el identificador o filtro indicado.
             */
            public function buscarActivo(int $idProducto): ?array
            {
                return null;
            }
            /** Devuelve arreglos vacíos porque estos casos de prueba no modelan galerías ni fichas adicionales. */
            public function imagenesRelacionadas(int $idProducto): array { return []; }
            /**
             * Recupera las características asociadas al producto solicitado.
             */
            public function caracteristicas(int $idProducto): array { return []; }
            public function buscarParaAdministrador(int $idProducto): ?array
            {
                return null;
            }
            public function buscarActivoParaActualizar(int $idProducto): ?array
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
            public function actualizar(int $idProducto, array $datos): void
            {
            }
            /**
             * Elimina el registro indicado respetando sus relaciones.
             */
            public function eliminarFisicamente(int $idProducto): bool
            {
                return true;
            }
            /**
             * Marca como inactivo el registro seleccionado, sin borrar su historial.
             */
            public function desactivar(int $idProducto): void
            {
            }
            /**
             * Descuenta del inventario la cantidad indicada para el producto correspondiente.
             */
            public function reducirStock(int $idProducto, int $cantidad): void
            {
            }
            /**
             * Cuenta los elementos relacionados con «activos».
             */
            public function contarActivos(): int
            {
                return 0;
            }
            /**
             * Suma las existencias registradas para los productos activos.
             */
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
            /**
             * Crea o guarda la información relacionada con «pedido pendiente».
             */
            public function crearPedidoPendiente(int $idUsuario, float $total): int
            {
                return 1;
            }
            /**
             * Crea o guarda la información relacionada con «detalle».
             */
            public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void
            {
            }
            /**
             * Devuelve la lista de registros relacionados con «con usuarios».
             */
            public function todosConUsuarios(): array
            {
                return [];
            }
            /**
             * Cuenta los elementos que cumplen las condiciones recibidas.
             */
            public function contar(): int
            {
                return 0;
            }
            public function buscarConDetalle(int $idPedido): ?array { return null; }
            /**
             * Actualiza la información relacionada con «estado».
             */
            public function actualizarEstado(int $idPedido, string $estado): void {}
            public function actualizarEstadoParaRolCliente(int $idPedido, string $estado, string $rolCliente): void {}
            /**
             * Cuenta los elementos relacionados con «por estado».
             */
            public function contarPorEstado(): array { return []; }
            /**
             * Cuenta los elementos relacionados con «ventas registradas».
             */
            public function contarVentasRegistradas(): int { return 0; }
            /**
             * Calcula las ventas y pedidos acumulados durante el periodo indicado.
             */
            public function totalVentasPeriodo(): float { return 0.0; }
        };
        $carrito = new CarritoServicio(new ProductoServicio($productos), new GestorSesion());
        $inventario = $this->createMock(RepositorioInventarioInterfaz::class);
        $servicio = new ProcesoCompraServicio(
            new Conexion(new RepositorioConfiguracion([])),
            $productos,
            $pedidos,
            $carrito,
            $inventario
        );

        $this->expectException(\RuntimeException::class);
        $servicio->procesarCompra(1);
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba `testCheckoutRollsBackWhenStockUpdateFails`.
     */
    public function testLaCompraRevierteSiFallaLaActualizacionDeExistencias(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite es necesario para la prueba transaccional.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE test_orders (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, total REAL)');
        $pdo->exec('CREATE TABLE test_stock (product_id INTEGER PRIMARY KEY, quantity INTEGER NOT NULL)');
        $pdo->exec('CREATE TABLE test_movements (id INTEGER PRIMARY KEY AUTOINCREMENT, product_id INTEGER, quantity INTEGER)');
        $pdo->exec('INSERT INTO test_stock(product_id, quantity) VALUES (1, 5)');
        $sesion = new GestorSesion();
        $sesion->guardar('cart', [1 => 1]);
        $productos = new class implements RepositorioProductoInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            /**
             * Devuelve todos los registros activos del repositorio.
             */
            public function todosActivos(): array
            {
                return [];
            }
            /**
             * Devuelve productos activos con descuentos reales, ordenados según ventas y disponibilidad.
             */
            public function ofertasPopulares(): array
            {
                return [];
            }
            /**
             * Devuelve los productos de la página solicitada junto con los datos de paginación.
             */
            public function paginar(FiltroProducto $filtro): array
            {
                return ['productos' => [], 'total' => 0, 'pagina' => 1, 'por_pagina' => 12, 'ultima_pagina' => 1];
            }
            /**
             * Devuelve los registros disponibles para el panel de administración.
             */
            public function todosParaAdministrador(): array
            {
                return [];
            }
            /**
             * Busca el registro activo que coincide con el identificador o filtro indicado.
             */
            public function buscarActivo(int $idProducto): ?array
            {
                if ($idProducto <= 0) {
                    return null;
                }

                return ['id' => 1, 'nombre' => 'Teléfono', 'precio' => 100, 'existencias' => 1];
            }
            /** Devuelve arreglos vacíos porque estos casos de prueba no modelan galerías ni fichas adicionales. */
            public function imagenesRelacionadas(int $idProducto): array { return []; }
            /**
             * Recupera las características asociadas al producto solicitado.
             */
            public function caracteristicas(int $idProducto): array { return []; }
            public function buscarParaAdministrador(int $idProducto): ?array
            {
                return null;
            }
            public function buscarActivoParaActualizar(int $idProducto): ?array
            {
                if ($idProducto <= 0) {
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
            public function actualizar(int $idProducto, array $datos): void
            {
            }
            /**
             * Elimina el registro indicado respetando sus relaciones.
             */
            public function eliminarFisicamente(int $idProducto): bool
            {
                return true;
            }
            /**
             * Marca como inactivo el registro seleccionado, sin borrar su historial.
             */
            public function desactivar(int $idProducto): void
            {
            }
            /**
             * Descuenta del inventario la cantidad indicada para el producto correspondiente.
             */
            public function reducirStock(int $idProducto, int $cantidad): void {}
            /**
             * Cuenta los elementos relacionados con «activos».
             */
            public function contarActivos(): int
            {
                return 1;
            }
            /**
             * Suma las existencias registradas para los productos activos.
             */
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
            /**
             * Crea o guarda la información relacionada con «pedido pendiente».
             */
            public function crearPedidoPendiente(int $idUsuario, float $total): int
            {
                $this->pdo->prepare('INSERT INTO test_orders(user_id, total) VALUES(?, ?)')->execute([$idUsuario, $total]);
                return (int) $this->pdo->lastInsertId();
            }
            /**
             * Crea o guarda la información relacionada con «detalle».
             */
            public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void
            {
            }
            /**
             * Devuelve la lista de registros relacionados con «con usuarios».
             */
            public function todosConUsuarios(): array
            {
                return [];
            }
            /**
             * Cuenta los elementos que cumplen las condiciones recibidas.
             */
            public function contar(): int
            {
                return 0;
            }
            public function buscarConDetalle(int $idPedido): ?array { return null; }
            /**
             * Actualiza la información relacionada con «estado».
             */
            public function actualizarEstado(int $idPedido, string $estado): void {}
            public function actualizarEstadoParaRolCliente(int $idPedido, string $estado, string $rolCliente): void {}
            /**
             * Cuenta los elementos relacionados con «por estado».
             */
            public function contarPorEstado(): array { return []; }
            /**
             * Cuenta los elementos relacionados con «ventas registradas».
             */
            public function contarVentasRegistradas(): int { return 0; }
            /**
             * Calcula las ventas y pedidos acumulados durante el periodo indicado.
             */
            public function totalVentasPeriodo(): float { return 0.0; }
        };
        $inventario = new class ($pdo) implements RepositorioInventarioInterfaz {
            public function __construct(private PDO $pdo)
            {
            }

            public function existencias(): array { return []; }

            public function movimientos(): array { return []; }

            public function registrarMovimiento(array $datos, int $idUsuario): int
            {
                $this->pdo->prepare('UPDATE test_stock SET quantity = quantity - ? WHERE product_id = ?')
                    ->execute([$datos['cantidad'], $datos['producto_id']]);
                $this->pdo->prepare('INSERT INTO test_movements(product_id, quantity) VALUES(?, ?)')
                    ->execute([$datos['producto_id'], $datos['cantidad']]);

                throw new \RuntimeException('forced failure');
            }
        };
        $carrito = new CarritoServicio(new ProductoServicio($productos), $sesion);
        $servicio = new ProcesoCompraServicio(
            new Conexion(new RepositorioConfiguracion([]), $pdo),
            $productos,
            $pedidos,
            $carrito,
            $inventario
        );

        try {
            $servicio->procesarCompra(1);
            self::fail('The checkout should fail.');
        } catch (\RuntimeException) {
            self::assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM test_orders')->fetchColumn());
            self::assertSame(5, (int) $pdo->query('SELECT quantity FROM test_stock WHERE product_id = 1')->fetchColumn());
            self::assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM test_movements')->fetchColumn());
            self::assertSame([1 => 1], $carrito->datosCrudos());
        }
    }
}
