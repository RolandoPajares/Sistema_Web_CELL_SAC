<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Middleware\AdministradorMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Soporte\Seguridad\GestorTokenCsrf;
use App\Soporte\Sesion\GestorSesion;
use PHPUnit\Framework\TestCase;

final class CsrfYAutorizacionTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testCsrfRejectsMissingToken(): void
    {
        $intermediario = new CsrfMiddleware(new GestorTokenCsrf(new GestorSesion()));
        $respuesta = $intermediario->manejar(
            new Solicitud('POST', '/cart', [], [], []),
            static fn(): Respuesta => new Respuesta('ok')
        );

        self::assertSame(419, $respuesta->estado());
    }

    public function testCustomerCannotAccessAdmin(): void
    {
        $_SESSION['user'] = ['id' => 1, 'rol' => 'cliente_minorista'];
        $respuesta = (new AdministradorMiddleware())->manejar(
            new Solicitud('GET', '/admin', [], [], []),
            static fn(): Respuesta => new Respuesta('admin')
        );

        self::assertSame(403, $respuesta->estado());
    }
}
