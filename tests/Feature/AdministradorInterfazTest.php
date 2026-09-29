<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Nucleo\Aplicacion;
use App\Nucleo\BaseDatos\Conexion;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Solicitud;
use App\Soporte\Seguridad\GestorTokenCsrf;
use PHPUnit\Framework\TestCase;

final class AdministradorInterfazTest extends TestCase
{
    private Enrutador $enrutador;

    protected function setUp(): void
    {
        if (Aplicacion::obtener(Conexion::class)->pdo() === null) {
            self::markTestSkipped('La interfaz administrativa requiere MySQL.');
        }
        $_SESSION = ['user' => ['id' => 1, 'nombre' => 'Administrador Test', 'rol' => 'administrador']];
        $this->enrutador = Aplicacion::obtener(Enrutador::class);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testTodasLasRutasAdministrativasUsanLaPlantillaExclusiva(): void
    {
        $rutas = [
            '/admin', '/admin/products', '/admin/categories', '/admin/inventory', '/admin/orders',
            '/admin/customers', '/admin/suppliers', '/admin/users', '/admin/campaigns',
            '/admin/reports', '/admin/audit',
        ];

        foreach ($rutas as $ruta) {
            $respuesta = $this->enrutador->despachar(new Solicitud('GET', $ruta, [], [], []));
            self::assertSame(200, $respuesta->estado(), $ruta);
            self::assertStringContainsString('data-admin-app', $respuesta->contenido(), $ruta);
            self::assertStringContainsString('data-admin-assistant', $respuesta->contenido(), $ruta);
        }
    }

    public function testAsistenteAdministrativoRespondeSoloConsultasDeLectura(): void
    {
        $csrf = Aplicacion::obtener(GestorTokenCsrf::class)->token();
        $respuesta = $this->enrutador->despachar(new Solicitud(
            'POST',
            '/admin/assistant',
            [],
            ['csrf' => $csrf, 'consulta' => 'Resumen del negocio'],
            []
        ));

        self::assertSame(200, $respuesta->estado());
        self::assertStringContainsString('"ok":true', $respuesta->contenido());
        self::assertStringContainsString('productos activos', $respuesta->contenido());
    }
}
