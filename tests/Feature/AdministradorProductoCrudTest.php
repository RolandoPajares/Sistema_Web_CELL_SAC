<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DAO\Productos\ProductoDAO;
use App\Nucleo\Http\Solicitud;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\BaseDatos\Conexion;
use App\Nucleo\Aplicacion;
use App\Soporte\Seguridad\GestorTokenCsrf;
use PHPUnit\Framework\TestCase;

final class AdministradorProductoCrudTest extends TestCase
{
    private Conexion $conexion;
    private ProductoDAO $productos;
    private Enrutador $enrutador;
    private string $csrf;

    protected function setUp(): void
    {
        $this->conexion = Aplicacion::obtener(Conexion::class);
        if ($this->conexion->pdo() === null) {
            self::markTestSkipped('Admin CRUD route test requires a live MySQL database.');
        }

        $this->conexion->pdoObligatorio()->beginTransaction();
        $this->productos = Aplicacion::obtener(ProductoDAO::class);
        $this->enrutador = Aplicacion::obtener(Enrutador::class);
        $_SESSION = ['user' => ['id' => 1, 'nombre' => 'Admin Test', 'rol' => 'administrador']];
        $this->csrf = Aplicacion::obtener(GestorTokenCsrf::class)->token();
    }

    protected function tearDown(): void
    {
        $pdo = $this->conexion->pdo();
        if ($pdo !== null && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION = [];
    }

    public function testAdminRoutesExecuteTheCompleteProductCrud(): void
    {
        $sufijo = bin2hex(random_bytes(4));
        $nombreCreado = 'Producto CRUD ' . $sufijo;
        $creado = $this->enrutador->despachar($this->post('/admin/products', [
            'csrf' => $this->csrf,
            'brand' => 'Marca Test',
            'name' => $nombreCreado,
            'category' => 'Accesorio',
            'price' => '49.90',
            'stock' => '5',
            'storage' => 'USB-C',
            'color' => 'Negro',
            'badge' => 'Test',
            'description' => 'Producto temporal para verificar el CRUD.',
        ]));
        self::assertSame(302, $creado->estado());

        $producto = $this->findByName($nombreCreado);
        self::assertNotNull($producto);
        $id = (int) $producto['id'];

        $paginaEdicion = $this->enrutador->despachar(
            new Solicitud('GET', '/admin/products/' . $id . '/edit', [], [], [])
        );
        self::assertSame(200, $paginaEdicion->estado());
        self::assertStringContainsString($nombreCreado, $paginaEdicion->contenido());

        $nombreActualizado = $nombreCreado . ' actualizado';
        $actualizado = $this->enrutador->despachar($this->post('/admin/products/' . $id, [
            'csrf' => $this->csrf,
            'brand' => 'Marca Test',
            'name' => $nombreActualizado,
            'category' => 'Accesorio',
            'price' => '59.90',
            'stock' => '7',
            'storage' => 'USB-C',
            'color' => 'Azul',
            'badge' => 'Actualizado',
            'description' => 'Producto actualizado por la prueba de rutas.',
        ]));
        self::assertSame(302, $actualizado->estado());
        self::assertSame($nombreActualizado, $this->productos->buscarParaAdministrador($id)['nombre']);

        $eliminado = $this->enrutador->despachar($this->post('/admin/products/' . $id . '/deactivate', [
            'csrf' => $this->csrf,
        ]));
        self::assertSame(302, $eliminado->estado());
        self::assertNull($this->productos->buscarActivo($id));
        self::assertNull($this->productos->buscarParaAdministrador($id));
    }

    /** @param array<string, mixed> $cuerpo */
    private function post(string $ruta, array $cuerpo): Solicitud
    {
        return new Solicitud('POST', $ruta, [], $cuerpo, ['REMOTE_ADDR' => '127.0.0.1']);
    }

    /** @return array<string, mixed>|null */
    private function findByName(string $nombre): ?array
    {
        foreach ($this->productos->todosParaAdministrador() as $producto) {
            if ($producto['nombre'] === $nombre) {
                return $producto;
            }
        }

        return null;
    }
}
