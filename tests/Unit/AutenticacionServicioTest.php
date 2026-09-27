<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositorios\Contratos\RepositorioUsuarioInterfaz;
use App\Servicios\AutenticacionServicio;
use App\Soporte\Excepciones\ExcepcionAutenticacion;
use App\Soporte\Sesion\GestorSesion;
use PHPUnit\Framework\TestCase;

final class AutenticacionServicioTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testLoginRejectsInvalidPassword(): void
    {
        $servicio = new AutenticacionServicio(new class implements RepositorioUsuarioInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            public function buscarPorCorreo(string $correo): ?array
            {
                if ($correo === '') {
                    return null;
                }

                return [
                'id' => 1,
                'nombre' => 'Admin',
                'correo' => $correo,
                'contrasena' => password_hash('secret', PASSWORD_DEFAULT),
                'rol' => 'administrador',
                ];
            }
            public function crear(array $datos): int
            {
                return 1;
            }
            public function todos(): array
            {
                return [];
            }
            public function contar(): int
            {
                return 0;
            }
        }, new GestorSesion());

        $this->expectException(ExcepcionAutenticacion::class);
        $servicio->iniciarSesion('admin@example.com', 'bad-password');
    }

    public function testLoginRegeneratesAndStoresSafeUserData(): void
    {
        $repositorio = new class implements RepositorioUsuarioInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            public function buscarPorCorreo(string $correo): ?array
            {
                if ($correo === '') {
                    return null;
                }

                return [
                    'id' => 7,
                    'nombre' => 'Cliente',
                    'correo' => $correo,
                    'contrasena' => password_hash('valid-password', PASSWORD_DEFAULT),
                    'rol' => 'cliente_minorista',
                ];
            }
            public function crear(array $datos): int
            {
                return 1;
            }
            public function todos(): array
            {
                return [];
            }
            public function contar(): int
            {
                return 1;
            }
        };
        $sesion = new GestorSesion();
        $usuario = (new AutenticacionServicio($repositorio, $sesion))->iniciarSesion('customer@example.com', 'valid-password');

        self::assertSame(7, $usuario['id']);
        self::assertArrayNotHasKey('contrasena', $usuario);
        self::assertSame($usuario, $sesion->obtener('user'));
    }

    public function testRegisterHashesPasswordAndForcesCustomerRole(): void
    {
        $repositorio = new class implements RepositorioUsuarioInterfaz {
            /** @var array<string, mixed> */
            public array $creado = [];
            public function estaDisponible(): bool
            {
                return true;
            }
            public function buscarPorCorreo(string $correo): ?array
            {
                return null;
            }
            public function crear(array $datos): int
            {
                $this->creado = $datos;
                return 9;
            }
            public function todos(): array
            {
                return [];
            }
            public function contar(): int
            {
                return 0;
            }
        };
        $servicio = new AutenticacionServicio($repositorio, new GestorSesion());
        $servicio->registrar(['nombre' => 'Nuevo usuario', 'correo' => 'new@example.com', 'contrasena' => 'plain-password']);

        self::assertSame('cliente_minorista', $repositorio->creado['rol']);
        self::assertNotSame('plain-password', $repositorio->creado['contrasena']);
        self::assertTrue(password_verify('plain-password', (string) $repositorio->creado['contrasena']));
    }

    public function testLogoutDestroysSessionData(): void
    {
        $sesion = new GestorSesion();
        $sesion->guardar('user', ['id' => 1]);
        $repositorio = new class implements RepositorioUsuarioInterfaz {
            public function estaDisponible(): bool
            {
                return true;
            }
            public function buscarPorCorreo(string $correo): ?array
            {
                return null;
            }
            public function crear(array $datos): int
            {
                return 1;
            }
            public function todos(): array
            {
                return [];
            }
            public function contar(): int
            {
                return 0;
            }
        };

        (new AutenticacionServicio($repositorio, $sesion))->cerrarSesion();

        self::assertFalse($sesion->tiene('user'));
    }
}
