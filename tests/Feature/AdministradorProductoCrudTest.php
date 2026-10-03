<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DAO\Categorias\CategoriaDAO;
use App\DAO\Productos\ProductoDAO;
use App\Nucleo\Aplicacion;
use App\Nucleo\BaseDatos\Conexion;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Solicitud;
use App\Soporte\Seguridad\GestorTokenCsrf;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas de integración para las rutas y el funcionamiento completo del CRUD de productos de administración.
 */
final class AdministradorProductoCrudTest extends TestCase
{
    private Conexion $conexion;
    private ProductoDAO $productos;
    private Enrutador $enrutador;
    private string $csrf;
    private int $categoriaId;

    /**
     * Prepara el estado y los recursos necesarios para ejecutar la prueba.
     */
    protected function setUp(): void
    {
        $this->conexion = Aplicacion::obtener(Conexion::class);
        
        if ($this->conexion->pdo() === null) {
            self::markTestSkipped('La prueba de rutas CRUD administrativas requiere una base MySQL de pruebas.');
        }

        $this->conexion->pdoObligatorio()->beginTransaction();
        $this->productos = Aplicacion::obtener(ProductoDAO::class);
        $this->enrutador = Aplicacion::obtener(Enrutador::class);
        
        $_SESSION = [
            'user' => [
                'id' => 1, 
                'nombre' => 'Admin Test', 
                'rol' => 'administrador'
            ]
        ];
        
        $this->csrf = Aplicacion::obtener(GestorTokenCsrf::class)->token();
        $this->categoriaId = (int) Aplicacion::obtener(CategoriaDAO::class)->activas()[0]['id'];
    }

    /**
     * Libera los recursos utilizados por la prueba y restaura el estado.
     */
    protected function tearDown(): void
    {
        $pdo = $this->conexion->pdo();
        
        if ($pdo !== null && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        $_SESSION = [];
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba testAdminRoutesExecuteTheCompleteProductCrud.
     */
    public function testLasRutasAdministrativasEjecutanElCrudCompletoDeProductos(): void
    {
        $sufijo = bin2hex(random_bytes(4));
        $nombreCreado = 'Producto CRUD ' . $sufijo;

        $creado = $this->enrutador->despachar($this->enviarPost('/admin/products', [
            'csrf' => $this->csrf,
            'brand' => 'Samsung',
            'name' => $nombreCreado,
            'category_id' => (string) $this->categoriaId,
            'price' => '49.90',
            'stock' => '5',
            'storage' => 'USB-C',
            'color' => 'Negro',
            'badge' => 'Test',
            'description' => 'Producto temporal para verificar el CRUD.',
        ]));
        
        self::assertSame(302, $creado->estado());

        $producto = $this->buscarPorNombre($nombreCreado);
        self::assertNotNull($producto);
        
        $idProducto = (int) $producto['id'];

        $paginaEdicion = $this->enrutador->despachar(
            new Solicitud('GET', '/admin/products/' . $idProducto . '/edit', [], [], [])
        );
        
        self::assertSame(200, $paginaEdicion->estado());
        self::assertStringContainsString($nombreCreado, $paginaEdicion->contenido());

        $nombreActualizado = $nombreCreado . ' actualizado';
        
        $actualizado = $this->enrutador->despachar($this->enviarPost('/admin/products/' . $idProducto, [
            'csrf' => $this->csrf,
            'brand' => 'Samsung',
            'name' => $nombreActualizado,
            'category_id' => (string) $this->categoriaId,
            'price' => '59.90',
            'stock' => '7',
            'storage' => 'USB-C',
            'color' => 'Azul',
            'badge' => 'Actualizado',
            'description' => 'Producto actualizado por la prueba de rutas.',
        ]));
        
        self::assertSame(302, $actualizado->estado());
        self::assertSame($nombreActualizado, $this->productos->buscarParaAdministrador($idProducto)['nombre']);

        $eliminado = $this->enrutador->despachar($this->enviarPost('/admin/products/' . $idProducto . '/deactivate', [
            'csrf' => $this->csrf,
        ]));
        
        self::assertSame(302, $eliminado->estado());
        self::assertNull($this->productos->buscarActivo($idProducto));
        self::assertNull($this->productos->buscarParaAdministrador($idProducto));
    }

    // Envía una petición POST simulada a la ruta indicada.
    private function enviarPost(string $ruta, array $cuerpo): Solicitud
    {
        return new Solicitud('POST', $ruta, [], $cuerpo, ['REMOTE_ADDR' => '127.0.0.1']);
    }

    // Busca un producto por su nombre entre los productos disponibles para administración.
    private function buscarPorNombre(string $nombre): ?array
    {
        foreach ($this->productos->todosParaAdministrador() as $producto) {
            if ($producto['nombre'] === $nombre) {
                return $producto;
            }
        }

        return null;
    }
}
