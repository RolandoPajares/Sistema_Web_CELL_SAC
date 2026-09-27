<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Solicitud;
use App\Http\Enrutamiento\Enrutador;
use App\Soporte\Aplicacion;
use App\Servicios\ProductoServicio;
use PHPUnit\Framework\TestCase;

final class RutasPublicasTest extends TestCase
{
    public function testCatalogAndProductRoutesRender(): void
    {
        if (!Aplicacion::obtener(ProductoServicio::class)->conexionDisponible()) {
            self::markTestSkipped('Public catalog routes require a live MySQL database.');
        }
        $enrutador = Aplicacion::obtener(Enrutador::class);
        $catalogo = $enrutador->despachar(new Solicitud('GET', '/catalog', [], [], []));
        $producto = $enrutador->despachar(new Solicitud('GET', '/products/1', [], [], []));

        self::assertSame(200, $catalogo->estado());
        self::assertStringContainsString('Catálogo de productos', $catalogo->contenido());
        self::assertSame(200, $producto->estado());
    }

    public function testUnknownRouteReturns404(): void
    {
        $respuesta = Aplicacion::obtener(Enrutador::class)->despachar(new Solicitud('GET', '/does-not-exist', [], [], []));

        self::assertSame(404, $respuesta->estado());
    }
}
