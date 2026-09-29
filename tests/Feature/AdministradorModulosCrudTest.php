<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Nucleo\Aplicacion;
use App\Nucleo\BaseDatos\Conexion;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Solicitud;
use App\Soporte\Seguridad\GestorTokenCsrf;
use App\Servicios\Compra\ProcesoCompraServicio;
use PDO;
use PHPUnit\Framework\TestCase;

final class AdministradorModulosCrudTest extends TestCase
{
    private Conexion $conexion;
    private PDO $pdo;
    private Enrutador $enrutador;
    private string $csrf;

    protected function setUp(): void
    {
        $this->conexion = Aplicacion::obtener(Conexion::class);
        if ($this->conexion->pdo() === null) {
            self::markTestSkipped('La prueba de módulos administrativos requiere MySQL.');
        }
        $this->pdo = $this->conexion->pdoObligatorio();
        $this->pdo->beginTransaction();
        $_SESSION = ['user' => ['id' => 1, 'nombre' => 'Admin Test', 'rol' => 'administrador']];
        $this->csrf = Aplicacion::obtener(GestorTokenCsrf::class)->token();
        $this->enrutador = Aplicacion::obtener(Enrutador::class);
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        $_SESSION = [];
    }

    public function testCrudEInventarioYPedidosFuncionanDeExtremoAExtremo(): void
    {
        $sufijo = bin2hex(random_bytes(4));

        $this->post('/admin/categories', ['nombre' => 'Tablets ' . $sufijo, 'descripcion' => 'Categoría de prueba']);
        $categoria = $this->fila('SELECT * FROM categorias WHERE nombre = :valor', 'Tablets ' . $sufijo);
        self::assertNotNull($categoria);
        $categoriaId = (int) $categoria['id'];
        $this->post('/admin/categories/' . $categoriaId, ['nombre' => 'Tablets Pro ' . $sufijo, 'descripcion' => 'Actualizada']);

        $this->post('/admin/products', [
            'brand' => 'Marca Test', 'name' => 'Tablet ' . $sufijo, 'category_id' => (string) $categoriaId,
            'price' => '799.90', 'storage' => '128 GB', 'color' => 'Negro', 'badge' => '', 'description' => 'Prueba',
        ]);
        $producto = $this->fila('SELECT * FROM productos WHERE nombre = :valor', 'Tablet ' . $sufijo);
        self::assertNotNull($producto);
        self::assertSame($categoriaId, (int) $producto['categoria_id']);
        self::assertSame('Tablets Pro ' . $sufijo, $producto['categoria']);
        self::assertSame(0, (int) $producto['existencias']);
        $productoId = (int) $producto['id'];

        $this->post('/admin/inventory', ['producto_id' => (string) $productoId, 'tipo_movimiento' => 'entrada', 'cantidad' => '10', 'notas' => 'Ingreso de prueba']);
        $this->post('/admin/inventory', ['producto_id' => (string) $productoId, 'tipo_movimiento' => 'salida', 'cantidad' => '3', 'notas' => 'Salida de prueba']);
        $this->post('/admin/inventory', ['producto_id' => (string) $productoId, 'tipo_movimiento' => 'ajuste', 'cantidad' => '4', 'notas' => 'Ajuste de prueba']);
        self::assertSame(4, (int) $this->valor('SELECT existencias FROM productos WHERE id = :id', $productoId));
        self::assertSame(3, (int) $this->valor('SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = :id', $productoId));

        $ruc = '20' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $this->post('/admin/suppliers', ['nombre' => 'Proveedor ' . $sufijo, 'ruc' => $ruc, 'correo' => $sufijo . '@proveedor.test', 'telefono' => '987654321', 'ciudad' => 'Lima']);
        $proveedor = $this->fila('SELECT * FROM proveedores WHERE ruc = :valor', $ruc);
        self::assertNotNull($proveedor);
        $this->post('/admin/suppliers/' . $proveedor['id'], ['nombre' => 'Proveedor actualizado ' . $sufijo, 'ruc' => $ruc, 'correo' => $sufijo . '@proveedor.test', 'telefono' => '987654320', 'ciudad' => 'Bagua']);
        $this->post('/admin/suppliers/' . $proveedor['id'] . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM proveedores WHERE id = :id', (int) $proveedor['id']));

        $documento = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $this->post('/admin/customers', ['tipo' => 'minorista', 'documento' => $documento, 'empresa' => '', 'contacto' => 'Cliente ' . $sufijo, 'correo' => $sufijo . '@cliente.test', 'telefono' => '912345678', 'ciudad' => 'Bagua']);
        $cliente = $this->fila('SELECT * FROM clientes WHERE documento = :valor', $documento);
        self::assertNotNull($cliente);
        $this->post('/admin/customers/' . $cliente['id'], ['tipo' => 'mayorista', 'documento' => $documento, 'empresa' => 'Empresa ' . $sufijo, 'contacto' => 'Cliente ' . $sufijo, 'correo' => $sufijo . '@cliente.test', 'telefono' => '912345678', 'ciudad' => 'Lima']);
        $this->post('/admin/customers/' . $cliente['id'] . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM clientes WHERE id = :id', (int) $cliente['id']));

        $_SESSION['cart'] = [$productoId => 1];
        $pedidoId = Aplicacion::obtener(ProcesoCompraServicio::class)->procesarCompra(6);
        self::assertSame(3, (int) $this->valor('SELECT existencias FROM productos WHERE id = :id', $productoId));
        self::assertSame(4, (int) $this->valor('SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = :id', $productoId));
        $detalle = $this->enrutador->despachar(new Solicitud('GET', '/admin/orders/' . $pedidoId, [], [], []));
        self::assertSame(200, $detalle->estado());
        self::assertStringContainsString('Tablet ' . $sufijo, $detalle->contenido());
        $this->post('/admin/orders/' . $pedidoId . '/status', ['estado' => 'En proceso']);
        self::assertSame('En proceso', $this->valor('SELECT estado FROM pedidos WHERE id = :id', $pedidoId));

        $this->post('/admin/products/' . $productoId . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM productos WHERE id = :id', $productoId));
        $this->post('/admin/categories/' . $categoriaId . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM categorias WHERE id = :id', $categoriaId));
    }

    private function post(string $ruta, array $datos = []): void
    {
        $respuesta = $this->enrutador->despachar(new Solicitud('POST', $ruta, [], ['csrf' => $this->csrf] + $datos, ['REMOTE_ADDR' => '127.0.0.1']));
        self::assertSame(302, $respuesta->estado(), $ruta);
    }

    private function fila(string $sql, string $valor): ?array
    {
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->execute([':valor' => $valor]);
        return $sentencia->fetch() ?: null;
    }

    private function valor(string $sql, int $id): mixed
    {
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->execute([':id' => $id]);
        return $sentencia->fetchColumn();
    }
}
