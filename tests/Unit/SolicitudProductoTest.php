<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Nucleo\Http\Solicitud;
use App\Validacion\Productos\SolicitudProducto;
use App\Soporte\Excepciones\ExcepcionValidacion;
use PHPUnit\Framework\TestCase;

final class SolicitudProductoTest extends TestCase
{
    public function testAccessoryIsAcceptedAndNegativeStockIsRejected(): void
    {
        $campos = [
            'brand' => 'Test',
            'name' => 'Cable',
            'category' => 'Accesorio',
            'price' => '19.90',
            'stock' => '3',
        ];

        $datosValidos = SolicitudProducto::validar(new Solicitud('POST', '/admin/products', [], $campos, []));
        self::assertSame('Accesorio', $datosValidos['categoria']);
        self::assertSame(3, $datosValidos['existencias']);

        $campos['stock'] = '-2';
        try {
            SolicitudProducto::validar(new Solicitud('POST', '/admin/products', [], $campos, []));
            self::fail('Negative stock must be rejected.');
        } catch (ExcepcionValidacion $excepcion) {
            self::assertArrayHasKey('existencias', $excepcion->errores());
        }
    }
}
