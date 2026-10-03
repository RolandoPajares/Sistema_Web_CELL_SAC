<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\DAO\Pedidos\PedidoDAO;
use App\DAO\Productos\ProductoDAO;
use App\DAO\Usuarios\UsuarioDAO;
use App\DAO\Inventario\InventarioDAO;
use App\DTO\Productos\FiltroProducto;
use App\Nucleo\BaseDatos\Conexion;
use App\Nucleo\BaseDatos\EjecutorMigraciones;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\Servicios\Productos\ProductoServicio;
use PHPUnit\Framework\TestCase;

// Pruebas de integración para validar las operaciones y transacciones de los repositorios y servicios con base de datos.
final class RepositoriosPdoTest extends TestCase
{
    // Instancia de la conexión a la base de datos de pruebas.
    private ?Conexion $conexion = null;

    // Prepara la base aislada de pruebas, ejecuta sus migraciones e inicia una transacción.
    protected function setUp(): void
    {
        // Obtiene el nombre de la base de datos desde las variables de entorno de prueba.
        $nombreBaseDatos = getenv('DB_TEST_DATABASE') ?: '';
        
        // Valida que el nombre de la base de datos de prueba no esté vacío y termine obligatoriamente en '_test'.
        if ($nombreBaseDatos === '' || !str_ends_with($nombreBaseDatos, '_test')) {
            self::markTestSkipped('Configura DB_TEST_DATABASE con una base aislada cuyo nombre termine en _test.');
        }

        // Configura e instancia la conexión PDO utilizando los parámetros definidos en el entorno.
        $this->conexion = new Conexion(new RepositorioConfiguracion([
            'database' => [
                'host' => getenv('DB_TEST_HOST') ?: '127.0.0.1',
                'port' => (int) (getenv('DB_TEST_PORT') ?: 3306),
                'database' => $nombreBaseDatos,
                'username' => getenv('DB_TEST_USERNAME') ?: '',
                'password' => getenv('DB_TEST_PASSWORD') ?: '',
                'charset' => 'utf8mb4',
            ]
        ]));
        
        // Ejecuta las migraciones necesarias sobre la base de datos configurada para las pruebas.
        (new EjecutorMigraciones($this->conexion))->migrar(dirname(__DIR__, 2) . '/database/migrations');
        
        // Inicia una transacción para aislar las escrituras de cada prueba.
        $this->conexion->pdoObligatorio()->beginTransaction();
    }

    // Revierte la transacción activa y limpia los recursos de la conexión al finalizar cada prueba.
    protected function tearDown(): void
    {
        // Recupera la conexión que usa esta prueba.
        $pdo = $this->conexion?->pdo();
        
        // Si la transacción sigue activa, la revierte antes de liberar la conexión.
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    // Verifica que los DAOs y servicios pueden persistir un ciclo completo de pedidos, stock y actualización de productos.
    public function testLosRepositoriosGuardanUnPedidoCompleto(): void
    {
        // Inicializa los objetos de acceso a datos (DAO) y servicios requeridos para la prueba.
        $usuarios = new UsuarioDAO($this->conexion);
        $productos = new ProductoDAO($this->conexion);
        $inventario = new InventarioDAO($this->conexion);
        $servicioProductos = new ProductoServicio($productos);
        $pedidos = new PedidoDAO($this->conexion);
        
        // Genera un sufijo aleatorio único para evitar colisiones en los correos electrónicos de prueba.
        $sufijo = bin2hex(random_bytes(4));
        $correoPrueba = $sufijo . '@example.test';
        
        // Crea un nuevo usuario en la base de datos de pruebas y captura su ID asignado.
        $idUsuario = $usuarios->crear([
            'nombre' => 'Test', 
            'correo' => $correoPrueba, 
            'contrasena' => 'hash', 
            'rol' => 'cliente_minorista'
        ]);
        
        // Crea un producto de prueba inicial con stock disponible en el inventario.
        $idProducto = $productos->crear([
            'marca' => 'Test', 
            'nombre' => 'Product', 
            'categoria_id' => 1,
            'categoria' => 'Celular', 
            'precio' => 20, 
            'existencias' => 2, 
            'almacenamiento' => '', 
            'color' => '', 
            'etiqueta' => '', 
            'descripcion' => '',
            'url_imagen' => '',
        ]);

        $inventario->registrarMovimiento([
            'producto_id' => $idProducto,
            'tipo_movimiento' => 'entrada',
            'cantidad' => 2,
            'notas' => 'Carga inicial ficticia de prueba.',
        ], $idUsuario);
        
        // Comprueba las validaciones de existencia de marcas y nombres de productos.
        self::assertTrue($productos->existeConMarcaYNombre('Test', 'Product'));
        self::assertFalse($productos->existeConMarcaYNombre('Test', 'Product', $idProducto));
        
        // Crea un pedido pendiente asociado al usuario y agrega los detalles correspondientes al producto.
        $idPedido = $pedidos->crearPedidoPendiente($idUsuario, 20.0);
        $pedidos->agregarDetalle($idPedido, $idProducto, 1, 20.0);
        
        // Registra la salida de inventario y actualiza los datos descriptivos del producto.
        $inventario->registrarMovimiento([
            'producto_id' => $idProducto,
            'tipo_movimiento' => 'salida',
            'cantidad' => 1,
            'notas' => 'Salida ficticia por pedido de prueba.',
        ], $idUsuario);
        $productos->actualizar($idProducto, [
            'marca' => 'Test', 
            'nombre' => 'Updated', 
            'categoria_id' => 1,
            'categoria' => 'Celular', 
            'precio' => 25, 
            'existencias' => 1, 
            'almacenamiento' => '', 
            'color' => '', 
            'etiqueta' => '', 
            'descripcion' => 'Updated',
            'url_imagen' => '',
        ]);

        // Busca el usuario registrado por su correo y valida que coincida correctamente.
        $usuarioBuscado = $usuarios->buscarPorCorreo($correoPrueba);
        self::assertSame($correoPrueba, $usuarioBuscado['correo']);

        // Consulta la información del producto actualizado desde la perspectiva del administrador y valida existencias y nombre.
        $productoAdmin = $productos->buscarParaAdministrador($idProducto);
        self::assertSame(1, (int) $productoAdmin['existencias']);
        self::assertSame('Updated', $productoAdmin['nombre']);

        // Realiza una paginación filtrando por el nombre actualizado y comprueba los totales y el conteo de pedidos.
        $resultadoPaginado = $productos->paginar(new FiltroProducto(busqueda: 'Updated'));
        self::assertSame(1, $resultadoPaginado['total']);
        self::assertGreaterThanOrEqual(1, $pedidos->contar());
        self::assertSame(2, (int) $this->conexion->pdoObligatorio()->query(
            'SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = ' . (int) $idProducto
        )->fetchColumn());

        // Comprueba que no se puede eliminar físicamente un producto con referencias de pedidos activos, forzando la desactivación lógica.
        $stockAntesDeLaBaja = (int) $productoAdmin['existencias'];
        self::assertFalse($productos->eliminarFisicamente($idProducto));
        self::assertSame('deactivated', $servicioProductos->eliminarODesactivar($idProducto));
        self::assertNull($productos->buscarActivo($idProducto));

        // Valida que el estado del producto en la vista de administrador refleje correctamente que ha sido desactivado.
        $productoAdminFinal = $productos->buscarParaAdministrador($idProducto);
        self::assertSame(0, (int) $productoAdminFinal['activo']);
        self::assertSame($stockAntesDeLaBaja, (int) $productoAdminFinal['existencias']);
        self::assertSame(1, (int) $this->conexion->pdoObligatorio()->query(
            'SELECT COUNT(*) FROM detalle_pedidos WHERE producto_id = ' . (int) $idProducto
        )->fetchColumn());
        self::assertSame(2, (int) $this->conexion->pdoObligatorio()->query(
            'SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = ' . (int) $idProducto
        )->fetchColumn());
    }

    // Comprueba que un producto que no posee referencias o pedidos asociados puede ser eliminado permanentemente.
    public function testSeEliminaFisicamenteUnProductoSinPedidosAsociados(): void
    {
        $productos = new ProductoDAO($this->conexion);
        $servicio = new ProductoServicio($productos);
        $nombreProducto = 'Delete ' . bin2hex(random_bytes(4));
        
        // Crea un producto que estará completamente libre de referencias históricas en pedidos.
        $idProducto = $productos->crear([
            'marca' => 'Test',
            'nombre' => $nombreProducto,
            'categoria_id' => 1,
            'categoria' => 'Accesorio',
            'precio' => 10,
            'existencias' => 1,
            'almacenamiento' => '',
            'color' => '',
            'etiqueta' => '',
            'descripcion' => '',
            'url_imagen' => '',
        ]);

        // Verifica que el producto existe, solicita su eliminación y comprueba que se elimina por completo de la base de datos.
        self::assertNotNull($productos->buscarParaAdministrador($idProducto));
        self::assertSame('deleted', $servicio->eliminarODesactivar($idProducto));
        self::assertNull($productos->buscarParaAdministrador($idProducto));
    }

    public function testUnMovimientoSinPedidoConservaProductoStockEHistorialAlDarDeBaja(): void
    {
        $pdo = $this->conexion->pdoObligatorio();
        $usuarios = new UsuarioDAO($this->conexion);
        $productos = new ProductoDAO($this->conexion);
        $inventario = new InventarioDAO($this->conexion);
        $servicio = new ProductoServicio($productos);
        $correo = bin2hex(random_bytes(8)) . '@example.test';
        $idUsuario = $usuarios->crear([
            'nombre' => 'Usuario de prueba',
            'correo' => $correo,
            'contrasena' => 'hash',
            'rol' => 'administrador',
        ]);
        $idProducto = $productos->crear([
            'marca' => 'Prueba',
            'nombre' => 'Movimiento sin pedido ' . bin2hex(random_bytes(4)),
            'categoria_id' => 1,
            'categoria' => 'Accesorio',
            'precio' => 10,
            'existencias' => 4,
            'almacenamiento' => '',
            'color' => '',
            'etiqueta' => '',
            'descripcion' => '',
            'url_imagen' => '',
        ]);

        $inventario->registrarMovimiento([
            'producto_id' => $idProducto,
            'tipo_movimiento' => 'entrada',
            'cantidad' => 2,
            'notas' => 'Movimiento ficticio para la prueba de baja.',
        ], $idUsuario);

        $productoAntes = $productos->buscarParaAdministrador($idProducto);
        $movimientosAntes = $pdo->prepare(
            'SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = :id'
        );
        $movimientosAntes->execute([':id' => $idProducto]);
        $cantidadMovimientosAntes = (int) $movimientosAntes->fetchColumn();
        $pedidos = $pdo->prepare('SELECT COUNT(*) FROM detalle_pedidos WHERE producto_id = :id');
        $pedidos->execute([':id' => $idProducto]);
        self::assertSame(0, (int) $pedidos->fetchColumn());
        self::assertSame(1, $cantidadMovimientosAntes);

        self::assertSame('deactivated', $servicio->eliminarODesactivar($idProducto));

        $productoDespues = $productos->buscarParaAdministrador($idProducto);
        $movimientosDespues = $pdo->prepare(
            'SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = :id'
        );
        $movimientosDespues->execute([':id' => $idProducto]);

        self::assertSame(0, (int) $productoDespues['activo']);
        self::assertSame((int) $productoAntes['existencias'], (int) $productoDespues['existencias']);
        self::assertSame($cantidadMovimientosAntes, (int) $movimientosDespues->fetchColumn());
        self::assertNull($productos->buscarActivo($idProducto));
    }

    // Comprueba con dos conexiones que una compra espera el bloqueo y observa el stock actualizado.
    public function testElBloqueoDeProductoSerializaDosConexionesDuranteUnaCompra(): void
    {
        $pdoPrincipal = $this->conexion->pdoObligatorio();
        if ($pdoPrincipal->inTransaction()) {
            $pdoPrincipal->rollBack();
        }

        $contrasena = getenv('DB_TEST_PASSWORD');
        if ($contrasena === false && getenv('DB_TEST_PASSWORD_EMPTY') === '1') {
            $contrasena = '';
        }

        if ($contrasena === false) {
            self::fail('La conexión de pruebas no tiene contraseña configurada.');
        }

        $conexionSecundaria = new Conexion(new RepositorioConfiguracion([
            'database' => [
                'host' => (string) getenv('DB_TEST_HOST'),
                'port' => (int) getenv('DB_TEST_PORT'),
                'database' => (string) getenv('DB_TEST_DATABASE'),
                'username' => (string) getenv('DB_TEST_USERNAME'),
                'password' => $contrasena,
                'charset' => 'utf8mb4',
            ],
        ]));
        $pdoSecundaria = $conexionSecundaria->pdoObligatorio();
        $pdoSecundaria->exec('SET SESSION innodb_lock_wait_timeout = 1');

        $productosPrincipal = new ProductoDAO($this->conexion);
        $productosSecundaria = new ProductoDAO($conexionSecundaria);
        $inventarioPrincipal = new InventarioDAO($this->conexion);
        $nombreProducto = 'Compra concurrente ' . bin2hex(random_bytes(4));
        $idProducto = $productosPrincipal->crear([
            'marca' => 'Test',
            'nombre' => $nombreProducto,
            'categoria_id' => 1,
            'categoria' => 'Categoría ficticia de pruebas',
            'precio' => 25,
            'existencias' => 1,
            'almacenamiento' => '',
            'color' => '',
            'etiqueta' => '',
            'descripcion' => '',
            'url_imagen' => '',
        ]);
        $actualizarStock = $pdoPrincipal->prepare('UPDATE productos SET existencias = 1 WHERE id = :id');
        $actualizarStock->execute([':id' => $idProducto]);

        try {
            $pdoPrincipal->beginTransaction();
            self::assertNotNull($productosPrincipal->buscarActivoParaActualizar($idProducto));

            $pdoSecundaria->beginTransaction();
            try {
                $productosSecundaria->buscarActivoParaActualizar($idProducto);
                self::fail('La segunda conexión debía esperar el bloqueo de la primera.');
            } catch (\PDOException $excepcion) {
                self::assertStringContainsString('Lock wait timeout', $excepcion->getMessage());
            } finally {
                if ($pdoSecundaria->inTransaction()) {
                    $pdoSecundaria->rollBack();
                }
            }

            $inventarioPrincipal->registrarMovimiento([
                'producto_id' => $idProducto,
                'tipo_movimiento' => 'salida',
                'cantidad' => 1,
                'notas' => 'Prueba controlada de bloqueo concurrente.',
            ], 1);
            $pdoPrincipal->commit();

            $pdoSecundaria->beginTransaction();
            $productoActualizado = $productosSecundaria->buscarActivoParaActualizar($idProducto);
            self::assertSame(0, (int) ($productoActualizado['existencias'] ?? -1));
            $pdoSecundaria->commit();

            $sentenciaMovimientos = $pdoPrincipal->prepare(
                'SELECT COUNT(*) FROM movimientos_inventario WHERE producto_id = :id'
            );
            $sentenciaMovimientos->execute([':id' => $idProducto]);
            self::assertSame(1, (int) $sentenciaMovimientos->fetchColumn());
        } finally {
            if ($pdoPrincipal->inTransaction()) {
                $pdoPrincipal->rollBack();
            }
            if ($pdoSecundaria->inTransaction()) {
                $pdoSecundaria->rollBack();
            }

            $eliminarMovimientos = $pdoPrincipal->prepare(
                'DELETE FROM movimientos_inventario WHERE producto_id = :id'
            );
            $eliminarMovimientos->execute([':id' => $idProducto]);
            $eliminarProducto = $pdoPrincipal->prepare('DELETE FROM productos WHERE id = :id');
            $eliminarProducto->execute([':id' => $idProducto]);
        }
    }

    // Comprueba en la base aislada que una excepción provoca la reversión de las escrituras.
    public function testLaTransaccionMySQLRevierteLasEscriturasDelDAO(): void
    {
        // Revierte la transacción inicial para evaluar este caso de forma aislada.
        $this->conexion->pdoObligatorio()->rollBack();
        
        $usuarios = new UsuarioDAO($this->conexion);
        $productos = new ProductoDAO($this->conexion);
        
        $correo = bin2hex(random_bytes(8)) . '@example.test';
        $nombreProducto = 'No debe existir ' . bin2hex(random_bytes(4));
        $forzarFallo = static fn(): bool => hrtime(true) > 0;

        try {
            // Ejecuta una operación transaccional que intenta realizar inserciones y luego lanza un error simulado.
            $this->conexion->transaccion(static function () use (
                $usuarios,
                $productos,
                $correo,
                $nombreProducto,
                $forzarFallo
            ): void {
                $usuarios->crear([
                    'nombre' => 'Rollback', 
                    'correo' => $correo, 
                    'contrasena' => 'hash', 
                    'rol' => 'cliente_minorista'
                ]);
                
                // Evalúa la condición para forzar el fallo de la transacción de forma controlada.
                if ($forzarFallo()) {
                    throw new \RuntimeException('Reversión forzada');
                }
                
                $productos->crear([
                    'marca' => 'Rollback',
                    'nombre' => $nombreProducto,
                    'categoria_id' => 1,
                    'categoria' => 'Accesorio',
                    'precio' => 10,
                    'existencias' => 1,
                    'almacenamiento' => '',
                    'color' => '',
                    'etiqueta' => '',
                    'descripcion' => '',
                    'url_imagen' => '',
                ]);
            });
            
            self::fail('La transacción debe fallar.');
        } catch (\RuntimeException $excepcion) {
            // Confirma que se recibió la excepción provocada por la operación.
            self::assertSame('Reversión forzada', $excepcion->getMessage());
        }

        // Comprueba que la reversión automática descartó las escrituras de la operación fallida.
        self::assertNull($usuarios->buscarPorCorreo($correo));
        self::assertFalse($productos->existeConMarcaYNombre('Rollback', $nombreProducto));
    }
}
