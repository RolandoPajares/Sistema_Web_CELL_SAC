<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Infraestructura\BaseDatos\Conexion;
use App\Infraestructura\BaseDatos\EjecutorMigraciones;
use App\DAO\PedidoDAO;
use App\DAO\ProductoDAO;
use App\DAO\UsuarioDAO;
use App\Soporte\RepositorioConfiguracion;
use App\Dominio\Productos\FiltroProducto;
use App\Servicios\ProductoServicio;
use PHPUnit\Framework\TestCase;

final class RepositoriosPdoTest extends TestCase
{
    private ?Conexion $conexion = null;

    protected function setUp(): void
    {
        $nombreBaseDatos = getenv('DB_TEST_DATABASE') ?: '';
        if ($nombreBaseDatos === '' || !str_ends_with($nombreBaseDatos, '_test')) {
            self::markTestSkipped('Set a dedicated DB_TEST_DATABASE ending in _test.');
        }

        $this->conexion = new Conexion(new RepositorioConfiguracion(['database' => [
            'host' => getenv('DB_TEST_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('DB_TEST_PORT') ?: 3306),
            'database' => $nombreBaseDatos,
            'username' => getenv('DB_TEST_USERNAME') ?: '',
            'password' => getenv('DB_TEST_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
        ]]));
        (new EjecutorMigraciones($this->conexion))->migrar(dirname(__DIR__, 2) . '/database/migrations');
        $this->conexion->pdoObligatorio()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $pdo = $this->conexion?->pdo();
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    public function testRepositoriesPersistACompleteOrder(): void
    {
        $usuarios = new UsuarioDAO($this->conexion);
        $productos = new ProductoDAO($this->conexion);
        $servicioProductos = new ProductoServicio($productos);
        $pedidos = new PedidoDAO($this->conexion);
        $sufijo = bin2hex(random_bytes(4));
        $idUsuario = $usuarios->crear(['nombre' => 'Test', 'correo' => $sufijo . '@example.test', 'contrasena' => 'hash', 'rol' => 'cliente_minorista']);
        $idProducto = $productos->crear(['marca' => 'Test', 'nombre' => 'Product', 'categoria' => 'Celular', 'precio' => 20, 'existencias' => 2, 'almacenamiento' => '', 'color' => '', 'etiqueta' => '', 'descripcion' => '']);
        self::assertTrue($productos->existeConMarcaYNombre('Test', 'Product'));
        self::assertFalse($productos->existeConMarcaYNombre('Test', 'Product', $idProducto));
        $idPedido = $pedidos->crearPedidoPendiente($idUsuario, 20.0);
        $pedidos->agregarDetalle($idPedido, $idProducto, 1, 20.0);
        $productos->reducirStock($idProducto, 1);
        $productos->actualizar($idProducto, ['marca' => 'Test', 'nombre' => 'Updated', 'categoria' => 'Celular', 'precio' => 25, 'existencias' => 1, 'almacenamiento' => '', 'color' => '', 'etiqueta' => '', 'descripcion' => 'Updated']);

        self::assertSame($sufijo . '@example.test', $usuarios->buscarPorCorreo($sufijo . '@example.test')['correo']);
        self::assertSame(1, (int) $productos->buscarParaAdministrador($idProducto)['existencias']);
        self::assertSame('Updated', $productos->buscarParaAdministrador($idProducto)['nombre']);
        self::assertSame(1, $productos->paginar(new FiltroProducto(busqueda: 'Updated'))['total']);
        self::assertGreaterThanOrEqual(1, $pedidos->contar());

        self::assertFalse($productos->delete($idProducto));
        self::assertSame('deactivated', $servicioProductos->eliminarODesactivar($idProducto));
        self::assertNull($productos->buscarActivo($idProducto));
        self::assertSame(0, (int) $productos->buscarParaAdministrador($idProducto)['activo']);
    }

    public function testUnreferencedProductCanBePhysicallyDeleted(): void
    {
        $productos = new ProductoDAO($this->conexion);
        $servicio = new ProductoServicio($productos);
        $nombre = 'Delete ' . bin2hex(random_bytes(4));
        $idProducto = $productos->crear([
            'marca' => 'Test',
            'nombre' => $nombre,
            'categoria' => 'Accesorio',
            'precio' => 10,
            'existencias' => 1,
            'almacenamiento' => '',
            'color' => '',
            'etiqueta' => '',
            'descripcion' => '',
        ]);

        self::assertNotNull($productos->buscarParaAdministrador($idProducto));
        self::assertSame('deleted', $servicio->eliminarODesactivar($idProducto));
        self::assertNull($productos->buscarParaAdministrador($idProducto));
    }

    public function testMysqlTransactionRollsBackDaoWrites(): void
    {
        $this->conexion->pdoObligatorio()->rollBack();
        $usuarios = new UsuarioDAO($this->conexion);
        $productos = new ProductoDAO($this->conexion);
        $correo = bin2hex(random_bytes(8)) . '@example.test';
        $nombreProducto = 'Should not exist ' . bin2hex(random_bytes(4));
        $forzarFallo = static fn (): bool => hrtime(true) > 0;

        try {
            $this->conexion->transaccion(static function () use (
                $usuarios,
                $productos,
                $correo,
                $nombreProducto,
                $forzarFallo
            ): void {
                $usuarios->crear(['nombre' => 'Rollback', 'correo' => $correo, 'contrasena' => 'hash', 'rol' => 'cliente_minorista']);
                if ($forzarFallo()) {
                    throw new \RuntimeException('Forced rollback');
                }
                $productos->crear([
                    'marca' => 'Rollback',
                    'nombre' => $nombreProducto,
                    'categoria' => 'Accesorio',
                    'precio' => 10,
                    'existencias' => 1,
                    'almacenamiento' => '',
                    'color' => '',
                    'etiqueta' => '',
                    'descripcion' => '',
                ]);
            });
            self::fail('The transaction should fail.');
        } catch (\RuntimeException $excepcion) {
            self::assertSame('Forced rollback', $excepcion->getMessage());
        }

        self::assertNull($usuarios->buscarPorCorreo($correo));
        self::assertFalse($productos->existeConMarcaYNombre('Rollback', $nombreProducto));
    }
}
