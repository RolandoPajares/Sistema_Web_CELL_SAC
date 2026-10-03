<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Nucleo\Aplicacion;
use App\Nucleo\BaseDatos\Conexion;
use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Solicitud;
use App\Soporte\Seguridad\GestorTokenCsrf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas de integración para verificar el inicio de sesión y redireccionamiento según el rol del usuario.
 */
final class InicioSesionRolesTest extends TestCase
{
    /**
     * Devuelve los usuarios que cumplen los criterios de consulta.
     *
     * @return array<string, array{string, string}>
     */
    public static function usuarios(): array
    {
        return [
            'administrador' => ['admin@md.demo', 'admin'], 
            'compras' => ['compras@md.demo', 'panel'],
            'ventas mayoristas' => ['b2b@md.demo', 'panel'], 
            'ventas minoristas' => ['b2c@md.demo', 'panel'],
            'marketing' => ['marketing@md.demo', 'panel'], 
            'cliente minorista' => ['minorista@md.demo', 'panel'],
            'cliente mayorista' => ['mayorista@md.demo', 'panel'],
        ];
    }

    /**
     * Comprueba el comportamiento cubierto por el caso de prueba testCadaRolIniciaSesionEnSuPanel.
     */
    #[DataProvider('usuarios')]
    public function testCadaRolIniciaSesionEnSuPanel(string $correo, string $destino): void
    {
        if (Aplicacion::obtener(Conexion::class)->pdo() === null) {
            self::markTestSkipped('La prueba de inicio de sesión requiere MySQL.');
        }
        
        $_SESSION = [];
        
        $csrf = Aplicacion::obtener(GestorTokenCsrf::class)->token();
        
        $respuesta = Aplicacion::obtener(Enrutador::class)->despachar(new Solicitud(
            'POST', 
            '/login', 
            [], 
            [
                'csrf' => $csrf, 
                'email' => $correo, 
                'password' => 'Demo2026!'
            ], 
            ['REMOTE_ADDR' => '127.0.0.1']
        ));
        
        self::assertSame(302, $respuesta->estado());
        self::assertStringEndsWith('/' . $destino, $respuesta->encabezados()['Location'] ?? '');
    }

    /**
     * Libera los recursos utilizados por la prueba y restaura el estado.
     */
    protected function tearDown(): void
    {
        $_SESSION = [];
    }
}
