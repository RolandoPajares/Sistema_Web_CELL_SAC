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

/**
 * Pruebas de integración de extremo a extremo para los módulos CRUD, inventario y pedidos del administrador.
 */
final class AdministradorModulosCrudTest extends TestCase
{
    private Conexion $conexion;
    private PDO $pdo;
    private Enrutador $enrutador;
    private string $csrf;

    /**
     * Prepara el estado y los recursos necesarios para ejecutar la prueba.
     */
    protected function setUp(): void
    {
        $this->conexion = Aplicacion::obtener(Conexion::class);
        
        if ($this->conexion->pdo() === null) {
            self::markTestSkipped('La prueba de módulos administrativos requiere MySQL.');
        }
        
        $this->pdo = $this->conexion->pdoObligatorio();
        $this->pdo->beginTransaction();
        
        $_SESSION = [
            'user' => [
                'id' => 1, 
                'nombre' => 'Admin Test', 
                'rol' => 'administrador'
            ]
        ];
        
        $this->csrf = Aplicacion::obtener(GestorTokenCsrf::class)->token();
        $this->enrutador = Aplicacion::obtener(Enrutador::class);
    }

    /**
     * Libera los recursos utilizados por la prueba y restaura el estado.
     */
    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        
        $_SESSION = [];
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba testCrudEInventarioYPedidosFuncionanDeExtremoAExtremo.
     */
    public function testCrudEInventarioYPedidosFuncionanDeExtremoAExtremo(): void
    {
        $sufijo = bin2hex(random_bytes(4));

        // Gestión de categorías
        $this->enviarPost('/admin/categories', [
            'nombre' => 'Tablets ' . $sufijo, 
            'descripcion' => 'Categoría de prueba'
        ]);
        
        $categoria = $this->fila('SELECT * FROM categorias WHERE nombre = :valor', 'Tablets ' . $sufijo);
        self::assertNotNull($categoria);
        
        $idCategoria = (int) $categoria['id'];
        
        $this->enviarPost('/admin/categories/' . $idCategoria, [
            'nombre' => 'Tablets Pro ' . $sufijo, 
            'descripcion' => 'Actualizada'
        ]);

        // Gestión de productos
        $this->enviarPost('/admin/products', [
            'brand' => 'Samsung',
            'name' => 'Tablet ' . $sufijo, 
            'category_id' => (string) $idCategoria,
            'price' => '799.90', 
            'storage' => '128 GB', 
            'color' => 'Negro', 
            'badge' => '', 
            'description' => 'Prueba',
        ]);
        
        $producto = $this->fila('SELECT * FROM productos WHERE nombre = :valor', 'Tablet ' . $sufijo);
        self::assertNotNull($producto);
        self::assertSame($idCategoria, (int) $producto['categoria_id']);
        self::assertSame('Tablets Pro ' . $sufijo, $producto['categoria']);
        self::assertSame(0, (int) $producto['existencias']);
        
        $idProducto = (int) $producto['id'];

        // Movimientos de inventario
        $this->enviarPost('/admin/inventory', [
            'producto_id' => (string) $idProducto, 
            'tipo_movimiento' => 'entrada', 
            'cantidad' => '10', 
            'notas' => 'Ingreso de prueba'
        ]);
        
        $this->enviarPost('/admin/inventory', [
            'producto_id' => (string) $idProducto, 
            'tipo_movimiento' => 'salida', 
            'cantidad' => '3', 
            'notas' => 'Salida de prueba'
        ]);
        
        $this->enviarPost('/admin/inventory', [
            'producto_id' => (string) $idProducto, 
            'tipo_movimiento' => 'ajuste', 
            'cantidad' => '4', 
            'notas' => 'Ajuste de prueba'
        ]);
        
        self::assertSame(4, (int) $this->valor('SELECT existencias FROM productos WHERE id = :id', $idProducto));
        self::assertSame(3, (int) $this->valor('SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = :id', $idProducto));

        // Gestión de proveedores
        $ruc = '20' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        
        $this->enviarPost('/admin/suppliers', [
            'nombre' => 'Proveedor ' . $sufijo, 
            'ruc' => $ruc, 
            'correo' => $sufijo . '@proveedor.test', 
            'telefono' => '987654321', 
            'ciudad' => 'Lima'
        ]);
        
        $proveedor = $this->fila('SELECT * FROM proveedores WHERE ruc = :valor', $ruc);
        self::assertNotNull($proveedor);
        
        $this->enviarPost('/admin/suppliers/' . $proveedor['id'], [
            'nombre' => 'Proveedor actualizado ' . $sufijo, 
            'ruc' => $ruc, 
            'correo' => $sufijo . '@proveedor.test', 
            'telefono' => '987654320', 
            'ciudad' => 'Bagua'
        ]);
        
        $this->enviarPost('/admin/suppliers/' . $proveedor['id'] . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM proveedores WHERE id = :id', (int) $proveedor['id']));

        // Gestión de clientes
        $documento = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        $this->enviarPost('/admin/customers', [
            'tipo' => 'minorista', 
            'documento' => $documento, 
            'empresa' => '', 
            'contacto' => 'Cliente ' . $sufijo, 
            'correo' => $sufijo . '@cliente.test', 
            'telefono' => '912345678', 
            'ciudad' => 'Bagua'
        ]);
        
        $cliente = $this->fila('SELECT * FROM clientes WHERE documento = :valor', $documento);
        self::assertNotNull($cliente);
        
        $this->enviarPost('/admin/customers/' . $cliente['id'], [
            'tipo' => 'mayorista', 
            'documento' => $documento, 
            'empresa' => 'Empresa ' . $sufijo, 
            'contacto' => 'Cliente ' . $sufijo, 
            'correo' => $sufijo . '@cliente.test', 
            'telefono' => '912345678', 
            'ciudad' => 'Lima'
        ]);
        
        $this->enviarPost('/admin/customers/' . $cliente['id'] . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM clientes WHERE id = :id', (int) $cliente['id']));

        // Procesamiento de pedidos y estados
        $_SESSION['cart'] = [$idProducto => 1];
        
        $idPedido = Aplicacion::obtener(ProcesoCompraServicio::class)->procesarCompra(6);
        self::assertSame(3, (int) $this->valor('SELECT existencias FROM productos WHERE id = :id', $idProducto));
        self::assertSame(4, (int) $this->valor('SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = :id', $idProducto));
        
        $detalle = $this->enrutador->despachar(new Solicitud('GET', '/admin/orders/' . $idPedido, [], [], []));
        self::assertSame(200, $detalle->estado());
        self::assertStringContainsString('Tablet ' . $sufijo, $detalle->contenido());
        
        $this->enviarPost('/admin/orders/' . $idPedido . '/status', [
            'estado' => 'En proceso'
        ]);
        
        self::assertSame('En proceso', $this->valor('SELECT estado FROM pedidos WHERE id = :id', $idPedido));

        // Desactivación final de producto y categoría
        $this->enviarPost('/admin/products/' . $idProducto . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM productos WHERE id = :id', $idProducto));
        
        $this->enviarPost('/admin/categories/' . $idCategoria . '/deactivate');
        self::assertSame(0, (int) $this->valor('SELECT activo FROM categorias WHERE id = :id', $idCategoria));
    }

    // Envía una petición POST simulada a la ruta indicada.
    private function enviarPost(string $ruta, array $datos = []): void
    {
        $respuesta = $this->enrutador->despachar(new Solicitud(
            'POST', 
            $ruta, 
            [], 
            ['csrf' => $this->csrf] + $datos, 
            ['REMOTE_ADDR' => '127.0.0.1']
        ));
        
        self::assertSame(302, $respuesta->estado(), $ruta);
    }

    // Obtiene un registro mediante la consulta SQL y el valor indicados.
    private function fila(string $sql, string $valor): ?array
    {
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->execute([':valor' => $valor]);
        
        return $sentencia->fetch() ?: null;
    }

    // Obtiene un valor escalar mediante la consulta SQL indicada.
    private function valor(string $sql, int $idRegistro): mixed
    {
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->execute([':id' => $idRegistro]);
        
        return $sentencia->fetchColumn();
    }
}
