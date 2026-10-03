<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Nucleo\Aplicacion;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Solicitud;
use App\Servicios\Productos\ProductoServicio;
use PHPUnit\Framework\TestCase;

// Pruebas de integración para verificar el funcionamiento de las rutas públicas de la aplicación.
final class RutasPublicasTest extends TestCase
{
    // Comprueba que las rutas del catálogo y de productos se renderizan correctamente.
    public function testLasRutasDeCatalogoYProductoSeRenderizan(): void
    {
        if (!Aplicacion::obtener(ProductoServicio::class)->conexionDisponible()) {
            self::markTestSkipped('Las rutas del catálogo público requieren una base MySQL de pruebas.');
        }

        $enrutador = Aplicacion::obtener(Enrutador::class);
        $catalogo = $enrutador->despachar(new Solicitud('GET', '/catalog', [], [], []));
        $producto = $enrutador->despachar(new Solicitud('GET', '/products/1', [], [], []));

        self::assertSame(200, $catalogo->estado());
        self::assertStringContainsString('<h1>Catálogo</h1>', $catalogo->contenido());
        self::assertSame(200, $producto->estado());
    }

    // Comprueba que una ruta desconocida o inexistente devuelve el código de estado 404.
    public function testUnaRutaDesconocidaDevuelve404(): void
    {
        $respuesta = Aplicacion::obtener(Enrutador::class)->despachar(new Solicitud('GET', '/does-not-exist', [], [], []));

        self::assertSame(404, $respuesta->estado());
    }
}
