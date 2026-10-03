<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Nucleo\Aplicacion;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Solicitud;
use App\Soporte\Seguridad\GestorTokenCsrf;
use PHPUnit\Framework\TestCase;

final class PanelAutorizacionTest extends TestCase
{
    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testClienteNoPuedeCambiarEstadoDeSusPedidosPorPostDirecto(): void
    {
        $respuesta = $this->postComo('cliente_minorista', '/panel/pedidos/101');

        self::assertSame(302, $respuesta->estado());
    }

    public function testModuloDeActualizacionNoAceptaCreacionPorPostDirecto(): void
    {
        $respuesta = $this->postComo('ventas_minoristas', '/panel/pedidos', ['estado' => 'En proceso']);

        self::assertSame(302, $respuesta->estado());
    }

    public function testModuloSoloDeCreacionNoAceptaActualizacionPorPostDirecto(): void
    {
        $respuesta = $this->postComo('compras_logistica', '/panel/inventario/10', [
            'producto_id' => '10',
            'tipo_movimiento' => 'entrada',
            'cantidad' => '1',
            'notas' => 'Intento de actualización',
        ]);

        self::assertSame(302, $respuesta->estado());
    }

    public function testSolicitudPostConCsrfInvalidoSeRechazaAntesDelControlador(): void
    {
        $_SESSION = ['user' => ['id' => 12, 'nombre' => 'Cliente', 'rol' => 'cliente_minorista']];
        $respuesta = Aplicacion::obtener(Enrutador::class)->despachar(new Solicitud(
            'POST',
            '/panel/pedidos/101',
            [],
            ['csrf' => 'token-invalido', 'estado' => 'En proceso'],
            ['REMOTE_ADDR' => '127.0.0.1']
        ));

        self::assertSame(419, $respuesta->estado());
    }

    public function testDetalleGlobalDePedidoSoloEstaDisponibleParaAdministracion(): void
    {
        $_SESSION = ['user' => ['id' => 12, 'nombre' => 'Cliente', 'rol' => 'cliente_minorista']];
        $respuesta = Aplicacion::obtener(Enrutador::class)->despachar(new Solicitud(
            'GET',
            '/admin/orders/101',
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1']
        ));

        self::assertSame(403, $respuesta->estado());
    }

    public function testVistaDePedidosDeClienteNoInvocaElListadoOperativo(): void
    {
        $_SESSION = ['user' => ['id' => 12, 'nombre' => 'Cliente', 'rol' => 'cliente_minorista']];
        $respuesta = Aplicacion::obtener(Enrutador::class)->despachar(new Solicitud(
            'GET',
            '/panel/pedidos',
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1']
        ));

        self::assertSame(200, $respuesta->estado());
        self::assertStringContainsString('MD-2024-000158', $respuesta->contenido());
    }

    /** @param array<string, string> $datos */
    private function postComo(string $rol, string $ruta, array $datos = []): \App\Nucleo\Http\Respuesta
    {
        $_SESSION = ['user' => ['id' => 12, 'nombre' => 'Usuario de prueba', 'rol' => $rol]];
        $token = Aplicacion::obtener(GestorTokenCsrf::class)->token();

        return Aplicacion::obtener(Enrutador::class)->despachar(new Solicitud(
            'POST',
            $ruta,
            [],
            ['csrf' => $token] + $datos,
            ['REMOTE_ADDR' => '127.0.0.1']
        ));
    }
}
