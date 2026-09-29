<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\Http\Solicitud;
use App\Nucleo\Aplicacion;
use App\Soporte\Autorizacion\AccesoRol;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NavegacionRolesTest extends TestCase
{
    private Enrutador $enrutador;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->enrutador = Aplicacion::obtener(Enrutador::class);
    }

    /** @return array<string, array{0:string,1:array<int,string>,2:string}> */
    public static function perfiles(): array
    {
        return [
            'cliente minorista' => ['cliente_minorista', ['perfil', 'direcciones', 'pedidos', 'historial'], 'usuarios'],
            'cliente mayorista' => ['cliente_mayorista', ['cotizaciones', 'pedidos-mayoristas', 'historial'], 'usuarios'],
            'compras y logística' => ['compras_logistica', ['inventario', 'productos', 'proveedores', 'compras', 'movimientos-stock', 'preparacion-pedidos', 'logistica-mayorista', 'alertas-stock'], 'usuarios'],
            'ventas mayoristas' => ['ventas_mayoristas', ['productos', 'clientes-mayoristas', 'cotizaciones', 'pedidos-mayoristas', 'historial-cliente', 'seguimiento-comercial'], 'usuarios'],
            'ventas minoristas' => ['ventas_minoristas', ['productos', 'inventario', 'clientes', 'ventas', 'pedidos', 'garantias', 'devoluciones', 'reclamaciones'], 'usuarios'],
            'marketing' => ['marketing', ['publicidad', 'campanias', 'catalogo-digital', 'destacados', 'promociones', 'contenido', 'analitica', 'consultas-digitales', 'segmentacion', 'smartcommerce-analytics'], 'usuarios'],
        ];
    }

    /** @param array<int, string> $permitidos */
    #[DataProvider('perfiles')]
    public function testDashboardModulosYBloqueosPorPerfil(string $rol, array $permitidos, string $prohibido): void
    {
        $_SESSION['user'] = ['id' => 1, 'nombre' => 'Prueba', 'correo' => $rol . '@test.local', 'rol' => $rol];

        self::assertSame(200, $this->get('/panel')->estado(), $rol . ' debe entrar a su dashboard');
        foreach ($permitidos as $modulo) {
            self::assertSame(200, $this->get('/panel/' . $modulo)->estado(), $rol . ' debe acceder a ' . $modulo);
        }
        if ($prohibido !== '') {
            self::assertSame(403, $this->get('/panel/' . $prohibido)->estado(), $rol . ' no debe acceder a ' . $prohibido);
        }
    }

    public function testVisitanteSoloAccedeAlRecorridoPublico(): void
    {
        foreach (['/', '/catalog', '/products/1', '/smart/recommend', '/smart/compare', '/smart/assistant', '/about', '/contact'] as $ruta) {
            self::assertSame(200, $this->get($ruta)->estado(), 'Visitante debe acceder a ' . $ruta);
        }
        foreach (['/panel', '/cart', '/checkout', '/mayorista', '/smart/optimizer', '/smart/ads'] as $ruta) {
            self::assertSame(302, $this->get($ruta)->estado(), 'Visitante debe ser enviado al login desde ' . $ruta);
        }
    }

    public function testRutasEspecialesRespetanElPerfil(): void
    {
        $casos = [
            ['cliente_minorista', '/cart', 200],
            ['cliente_minorista', '/checkout', 200],
            ['cliente_minorista', '/mayorista', 403],
            ['cliente_mayorista', '/mayorista', 200],
            ['cliente_mayorista', '/smart/optimizer', 200],
            ['cliente_mayorista', '/cart', 403],
            ['ventas_mayoristas', '/smart/optimizer', 200],
            ['ventas_minoristas', '/smart/optimizer', 403],
            ['marketing', '/smart/ads', 200],
            ['compras_logistica', '/smart/ads', 403],
        ];

        foreach ($casos as [$rol, $ruta, $estado]) {
            $_SESSION['user'] = ['id' => 1, 'nombre' => 'Prueba', 'rol' => $rol];
            self::assertSame($estado, $this->get($ruta)->estado(), $rol . ' en ' . $ruta);
        }
    }

    /** @return array<string, array{0:string,1:string}> */
    public static function dashboards(): array
    {
        return [
            'cliente minorista' => ['cliente_minorista', 'Mi cuenta'],
            'cliente mayorista' => ['cliente_mayorista', 'Portal mayorista B2B'],
            'administrador' => ['administrador', 'Dashboard ejecutivo'],
            'compras y logística' => ['compras_logistica', 'Dashboard de operaciones'],
            'ventas mayoristas' => ['ventas_mayoristas', 'Dashboard comercial B2B'],
            'ventas minoristas' => ['ventas_minoristas', 'Punto de venta / Dashboard B2C'],
            'marketing' => ['marketing', 'Dashboard de marketing'],
        ];
    }

    #[DataProvider('dashboards')]
    public function testCadaRolLlegaAlDashboardQueLeCorresponde(string $rol, string $titulo): void
    {
        $_SESSION['user'] = ['id' => 1, 'nombre' => 'Prueba', 'correo' => $rol . '@test.local', 'rol' => $rol];

        $respuesta = $this->get('/panel');

        if ($rol === 'administrador') {
            self::assertSame(302, $respuesta->estado());
            return;
        }
        self::assertSame(200, $respuesta->estado());
        self::assertStringContainsString($titulo, $respuesta->contenido());
    }

    public function testDestinosDeNavegacionCriticos(): void
    {
        self::assertSame('catalog', AccesoRol::destino('cliente_minorista', 'catalogo'));
        self::assertSame('mayorista', AccesoRol::destino('cliente_mayorista', 'catalogo-b2b'));
        self::assertSame('smart/optimizer', AccesoRol::destino('cliente_mayorista', 'optimizador'));
        self::assertSame('admin/campaigns', AccesoRol::destino('administrador', 'publicidad'));
        self::assertSame('admin/categories', AccesoRol::destino('administrador', 'categorias'));
        self::assertSame('panel/movimientos-stock', AccesoRol::destino('compras_logistica', 'movimientos-stock'));
    }

    private function get(string $ruta): \App\Nucleo\Http\Respuesta
    {
        return $this->enrutador->despachar(new Solicitud('GET', $ruta, [], [], []));
    }
}
