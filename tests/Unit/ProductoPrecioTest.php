<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DAO\Contratos\RepositorioPedidoInterfaz;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\DAO\Contratos\RepositorioInventarioInterfaz;
use App\DAO\Productos\ProductoDAO;
use App\DTO\Productos\FiltroProducto;
use App\Modelos\Productos\Producto;
use App\Nucleo\BaseDatos\Conexion;
use App\Nucleo\Presentacion\Productos\PresentadorTarjetaProducto;
use App\Servicios\Compra\CarritoServicio;
use App\Servicios\Compra\ProcesoCompraServicio;
use App\Servicios\Productos\ProductoServicio;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\Soporte\Sesion\GestorSesion;
use PDO;
use PHPUnit\Framework\TestCase;

final class ProductoPrecioTest extends TestCase
{
    public function testUsaPrecioBaseCuandoNoHayOferta(): void
    {
        self::assertSame(12999, Producto::desdeRegistro(['precio' => '129.99'])->precioEfectivoEnCentimos());
        self::assertSame(129.99, Producto::desdeRegistro(['precio' => '129.99'])->precioEfectivo());
    }

    public function testUsaOfertaValidaYAdmiteCero(): void
    {
        self::assertSame(8999, Producto::desdeRegistro([
            'precio' => '129.99',
            'precio_oferta' => '89.99',
        ])->precioEfectivoEnCentimos());
        self::assertSame(0, Producto::desdeRegistro([
            'precio' => '129.99',
            'precio_oferta' => '0.00',
        ])->precioEfectivoEnCentimos());
    }

    public function testRechazaImportesInvalidos(): void
    {
        foreach (['no-numérico', '-1.00', INF, NAN, 100000000] as $oferta) {
            try {
                Producto::desdeRegistro(['precio' => '10.00', 'precio_oferta' => $oferta])
                    ->precioEfectivoEnCentimos();
                self::fail('La oferta inválida debía rechazarse.');
            } catch (\DomainException) {
                self::assertTrue(true);
            }
        }
    }

    public function testConfirmacionUsaPrecioVigenteYMultiplicaEnCentimos(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite es necesario para probar el límite transaccional.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE pedidos_prueba (id INTEGER PRIMARY KEY AUTOINCREMENT, usuario_id INTEGER, total NUMERIC)');
        $sesion = new GestorSesion();
        $sesion->guardar('cart', [7 => 3]);

        $productos = $this->createMock(RepositorioProductoInterfaz::class);
        $productos->method('estaDisponible')->willReturn(true);
        $productos->method('buscarActivo')->willReturn([
            'id' => 7,
            'nombre' => 'Producto de prueba',
            'precio' => '20.00',
            'precio_oferta' => '18.25',
            'existencias' => 5,
        ]);
        $productos->expects(self::once())->method('buscarActivoParaActualizar')->with(7)->willReturn([
            'id' => 7,
            'nombre' => 'Producto de prueba',
            'precio' => '22.00',
            'precio_oferta' => '19.90',
            'existencias' => 5,
        ]);
        $productos->expects(self::never())->method('reducirStock');

        $pedidos = $this->createMock(RepositorioPedidoInterfaz::class);
        $pedidos->method('estaDisponible')->willReturn(true);
        $pedidos->expects(self::once())->method('crearPedidoPendiente')
            ->with(4, 59.7)
            ->willReturnCallback(function (int $idUsuario, float $total) use ($pdo): int {
                $sentencia = $pdo->prepare('INSERT INTO pedidos_prueba(usuario_id, total) VALUES(?, ?)');
                $sentencia->execute([$idUsuario, $total]);

                return (int) $pdo->lastInsertId();
            });
        $pedidos->expects(self::once())->method('agregarDetalle')->with(1, 7, 3, 19.9);
        $inventario = $this->createMock(RepositorioInventarioInterfaz::class);
        $inventario->expects(self::once())
            ->method('registrarMovimiento')
            ->with([
                'producto_id' => 7,
                'tipo_movimiento' => 'salida',
                'cantidad' => 3,
                'notas' => 'Salida automática por pedido #1',
            ], 4)
            ->willReturn(1);

        $carrito = new CarritoServicio(new ProductoServicio($productos), $sesion);
        self::assertSame(54.75, $carrito->resumen()['total']);

        $servicio = new ProcesoCompraServicio(
            new Conexion(new RepositorioConfiguracion([]), $pdo),
            $productos,
            $pedidos,
            $carrito,
            $inventario
        );

        self::assertSame(1, $servicio->procesarCompra(4));
        self::assertSame(59.7, (float) $pdo->query('SELECT total FROM pedidos_prueba')->fetchColumn());
        self::assertSame([], $carrito->datosCrudos());
    }

    public function testPresentadorMuestraLaMismaPoliticaDeOferta(): void
    {
        $tarjeta = (new PresentadorTarjetaProducto())->presentar([
            'id' => 1,
            'precio' => '20.00',
            'precio_oferta' => '17.50',
        ]);

        self::assertSame(17.5, $tarjeta['precio_oferta']);
    }

    public function testCatalogoFiltraYOrdenaPorPrecioEfectivo(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite es necesario para probar la consulta de catálogo.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE productos (
            id INTEGER PRIMARY KEY, marca TEXT, nombre TEXT, categoria TEXT, precio NUMERIC,
            precio_original NUMERIC, descuento TEXT, precio_oferta NUMERIC, url_imagen TEXT,
            existencias INTEGER, etiqueta TEXT, almacenamiento TEXT, color TEXT,
            descripcion TEXT, activo INTEGER
        )');
        $pdo->exec("INSERT INTO productos(id, marca, nombre, categoria, precio, precio_oferta, activo)
            VALUES (1, 'Marca', 'Con oferta', 'Celular', 100, 50, 1),
                   (2, 'Marca', 'Sin oferta', 'Celular', 40, NULL, 1)");
        $productos = new ProductoDAO(new Conexion(new RepositorioConfiguracion([]), $pdo));

        $filtrados = $productos->paginar(new FiltroProducto(precioMinimo: 45));
        self::assertSame([1], array_column($filtrados['productos'], 'id'));

        $ordenados = $productos->paginar(new FiltroProducto(orden: 'price_asc'));
        self::assertSame([2, 1], array_column($ordenados['productos'], 'id'));
    }
}
