<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DAO\Contratos\RepositorioCategoriaInterfaz;
use App\DAO\Contratos\RepositorioClienteInterfaz;
use App\DAO\Contratos\RepositorioInventarioInterfaz;
use App\DAO\Contratos\RepositorioModuloInterfaz;
use App\DAO\Contratos\RepositorioPedidoInterfaz;
use App\DAO\Contratos\RepositorioProveedorInterfaz;
use App\Servicios\Categorias\CategoriaServicio;
use App\Servicios\Clientes\ClienteServicio;
use App\Servicios\Inventario\InventarioServicio;
use App\Servicios\Panel\PanelModuloServicio;
use App\Servicios\Pedidos\PedidoServicio;
use App\Servicios\Proveedores\ProveedorServicio;
use App\Soporte\Registros\RegistradorArchivo;
use PHPUnit\Framework\TestCase;

final class PanelClienteSegmentoTest extends TestCase
{
    public function testPermisosDeEscrituraSeSeparanPorSegmentoYElAdministradorMantieneAmbos(): void
    {
        $panel = $this->crearPanel(new RepositorioClienteEnMemoria());

        foreach (['crear', 'editar', 'desactivar'] as $operacion) {
            self::assertTrue($panel->puedeOperar('ventas_minoristas', 'clientes', $operacion));
            self::assertFalse($panel->puedeOperar('ventas_minoristas', 'clientes-mayoristas', $operacion));
            self::assertTrue($panel->puedeOperar('ventas_mayoristas', 'clientes-mayoristas', $operacion));
            self::assertFalse($panel->puedeOperar('ventas_mayoristas', 'clientes', $operacion));
            self::assertTrue($panel->puedeOperar('administrador', 'clientes', $operacion));
            self::assertTrue($panel->puedeOperar('administrador', 'clientes-mayoristas', $operacion));
        }
    }

    public function testElPanelFuerzaElSegmentoAlCrearAunqueManipulenElTipo(): void
    {
        $repositorio = new RepositorioClienteEnMemoria();
        $panel = $this->crearPanel($repositorio);

        $idMinorista = $panel->crear('clientes', $this->datosCliente('mayorista'), 1);
        $idMayorista = $panel->crear('clientes-mayoristas', $this->datosCliente('minorista'), 1);

        self::assertSame('minorista', $repositorio->registros[$idMinorista]['tipo']);
        self::assertSame('mayorista', $repositorio->registros[$idMayorista]['tipo']);
        self::assertSame(
            ['minorista' => 'Minorista'],
            $panel->configuracion('clientes')['campos']['tipo']['options']
        );
        self::assertArrayNotHasKey('tipo', $panel->configuracion('clientes-mayoristas')['campos']);
    }

    public function testPermiteActualizarYDesactivarClientesDeSuPropioSegmento(): void
    {
        $repositorio = new RepositorioClienteEnMemoria([
            10 => ['id' => 10, 'tipo' => 'minorista', 'documento' => '11111111', 'contacto' => 'Minorista', 'activo' => 1],
            20 => ['id' => 20, 'tipo' => 'mayorista', 'documento' => '20111111111', 'contacto' => 'Mayorista', 'activo' => 1],
        ]);
        $panel = $this->crearPanel($repositorio);

        $panel->actualizar('clientes', 10, $this->datosCliente('mayorista', 'Minorista editado'));
        $panel->actualizar('clientes-mayoristas', 20, $this->datosCliente('minorista', 'Mayorista editado'));
        $panel->desactivar('clientes', 10);
        $panel->desactivar('clientes-mayoristas', 20);

        self::assertSame('minorista', $repositorio->registros[10]['tipo']);
        self::assertSame('mayorista', $repositorio->registros[20]['tipo']);
        self::assertSame('Minorista editado', $repositorio->registros[10]['contacto']);
        self::assertSame('Mayorista editado', $repositorio->registros[20]['contacto']);
        self::assertSame(0, $repositorio->registros[10]['activo']);
        self::assertSame(0, $repositorio->registros[20]['activo']);
    }

    public function testNoPermiteEditarNiDesactivarUnIdDelOtroSegmento(): void
    {
        $repositorio = new RepositorioClienteEnMemoria([
            10 => ['id' => 10, 'tipo' => 'minorista', 'documento' => '11111111', 'contacto' => 'Minorista', 'activo' => 1],
            20 => ['id' => 20, 'tipo' => 'mayorista', 'documento' => '20111111111', 'contacto' => 'Mayorista', 'activo' => 1],
        ]);
        $panel = $this->crearPanel($repositorio);

        try {
            $panel->actualizar('clientes', 20, $this->datosCliente('minorista', 'Intento de edición'));
            self::fail('El panel minorista no debe editar un cliente mayorista.');
        } catch (\DomainException $excepcion) {
            self::assertSame('El cliente no pertenece al segmento autorizado.', $excepcion->getMessage());
        }

        try {
            $panel->desactivar('clientes-mayoristas', 10);
            self::fail('El panel mayorista no debe desactivar un cliente minorista.');
        } catch (\DomainException $excepcion) {
            self::assertSame('El cliente no pertenece al segmento autorizado.', $excepcion->getMessage());
        }

        self::assertSame('Mayorista', $repositorio->registros[20]['contacto']);
        self::assertSame('Minorista', $repositorio->registros[10]['contacto']);
        self::assertSame(1, $repositorio->registros[10]['activo']);
        self::assertSame(1, $repositorio->registros[20]['activo']);
    }

    public function testNoPermiteEditarNiDesactivarIdentificadoresInexistentes(): void
    {
        $panel = $this->crearPanel(new RepositorioClienteEnMemoria());

        try {
            $panel->actualizar('clientes', 99, $this->datosCliente('minorista'));
            self::fail('No se debe actualizar un cliente inexistente.');
        } catch (\DomainException $excepcion) {
            self::assertSame('El cliente no existe.', $excepcion->getMessage());
        }

        try {
            $panel->desactivar('clientes-mayoristas', 99);
            self::fail('No se debe desactivar un cliente inexistente.');
        } catch (\DomainException $excepcion) {
            self::assertSame('El cliente no existe.', $excepcion->getMessage());
        }
    }

    public function testElServicioAdministrativoPuedeGestionarAmbosSegmentos(): void
    {
        $repositorio = new RepositorioClienteEnMemoria([
            10 => ['id' => 10, 'tipo' => 'minorista', 'documento' => '11111111', 'contacto' => 'Minorista', 'activo' => 1],
            20 => ['id' => 20, 'tipo' => 'mayorista', 'documento' => '20111111111', 'contacto' => 'Mayorista', 'activo' => 1],
        ]);
        $clientes = new ClienteServicio($repositorio);

        $idMinorista = $clientes->guardar($this->datosCliente('minorista', 'Alta administrativa minorista'));
        $idMayorista = $clientes->guardar($this->datosCliente('mayorista', 'Alta administrativa mayorista'));

        $datosMayorista = $this->datosCliente('mayorista', 'Convertido por administración');
        $datosMayorista['documento'] = '20111111112';
        $clientes->guardar($datosMayorista, 10);

        $datosMinorista = $this->datosCliente('minorista', 'Convertido por administración');
        $datosMinorista['documento'] = '12345679';
        $clientes->guardar($datosMinorista, 20);
        $clientes->desactivar(10);
        $clientes->desactivar(20);

        self::assertSame('mayorista', $repositorio->registros[10]['tipo']);
        self::assertSame('minorista', $repositorio->registros[20]['tipo']);
        self::assertSame('minorista', $repositorio->registros[$idMinorista]['tipo']);
        self::assertSame('mayorista', $repositorio->registros[$idMayorista]['tipo']);
        self::assertSame(0, $repositorio->registros[10]['activo']);
        self::assertSame(0, $repositorio->registros[20]['activo']);
    }

    /** @return array<string, string> */
    private function datosCliente(string $tipo, string $contacto = 'Cliente de prueba'): array
    {
        return [
            'tipo' => $tipo,
            'documento' => $tipo === 'mayorista' ? '20123456789' : '12345678',
            'empresa' => '',
            'contacto' => $contacto,
            'correo' => strtolower(str_replace(' ', '.', $contacto)) . '@example.test',
            'telefono' => '',
            'ciudad' => 'Bagua',
        ];
    }

    private function crearPanel(RepositorioClienteInterfaz $clientes): PanelModuloServicio
    {
        return new PanelModuloServicio(
            $this->createMock(RepositorioModuloInterfaz::class),
            new CategoriaServicio($this->createMock(RepositorioCategoriaInterfaz::class)),
            new ClienteServicio($clientes),
            new ProveedorServicio($this->createMock(RepositorioProveedorInterfaz::class)),
            new InventarioServicio($this->createMock(RepositorioInventarioInterfaz::class)),
            new PedidoServicio($this->createMock(RepositorioPedidoInterfaz::class)),
            new RegistradorArchivo(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'panel-clientes-segmento.log')
        );
    }
}

final class RepositorioClienteEnMemoria implements RepositorioClienteInterfaz
{
    /** @var array<int, array<string, mixed>> */
    public array $registros;

    private int $siguienteId;

    /** @param array<int, array<string, mixed>> $registros */
    public function __construct(array $registros = [])
    {
        $this->registros = $registros;
        $this->siguienteId = $registros === [] ? 1 : max(array_keys($registros)) + 1;
    }

    public function todos(): array
    {
        return array_values($this->registros);
    }

    public function buscar(int $idCliente): ?array
    {
        return $this->registros[$idCliente] ?? null;
    }

    public function existeDocumento(string $documento, ?int $idExcluido = null): bool
    {
        foreach ($this->registros as $cliente) {
            if (($cliente['documento'] ?? null) === $documento && (int) $cliente['id'] !== $idExcluido) {
                return true;
            }
        }

        return false;
    }

    public function crear(array $datos): int
    {
        $idCliente = $this->siguienteId++;
        $this->registros[$idCliente] = $datos + ['id' => $idCliente, 'activo' => 1];

        return $idCliente;
    }

    public function actualizar(int $idCliente, array $datos, ?string $segmentoPermitido = null): void
    {
        $cliente = $this->registros[$idCliente] ?? null;
        if ($cliente === null) {
            throw new \DomainException('El cliente no existe.');
        }
        if ($segmentoPermitido !== null && $cliente['tipo'] !== $segmentoPermitido) {
            throw new \DomainException('El cliente no pertenece al segmento autorizado.');
        }

        $this->registros[$idCliente] = array_replace($cliente, $datos);
    }

    public function desactivar(int $idCliente, ?string $segmentoPermitido = null): void
    {
        $cliente = $this->registros[$idCliente] ?? null;
        if ($cliente === null || (int) $cliente['activo'] !== 1) {
            throw new \DomainException('El cliente no existe o ya está inactivo.');
        }
        if ($segmentoPermitido !== null && $cliente['tipo'] !== $segmentoPermitido) {
            throw new \DomainException('El cliente no pertenece al segmento autorizado.');
        }

        $this->registros[$idCliente]['activo'] = 0;
    }
}
