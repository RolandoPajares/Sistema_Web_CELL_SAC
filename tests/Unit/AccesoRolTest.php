<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Soporte\Autorizacion\AccesoRol;
use PHPUnit\Framework\TestCase;

final class AccesoRolTest extends TestCase
{
    public function testOfficialRolesArePreserved(): void
    {
        self::assertSame('administrador', AccesoRol::normalize('administrador'));
        self::assertSame('cliente_minorista', AccesoRol::normalize('cliente_minorista'));
    }

    public function testInternalRolesOnlyAccessTheirModules(): void
    {
        self::assertTrue(AccesoRol::can('compras_logistica', 'inventario'));
        self::assertFalse(AccesoRol::can('compras_logistica', 'usuarios'));
        self::assertTrue(AccesoRol::can('marketing', 'campanias'));
        self::assertFalse(AccesoRol::can('marketing', 'compras'));
    }

    public function testAdministratorCanAccessEveryRegisteredModule(): void
    {
        self::assertTrue(AccesoRol::can('administrador', 'auditoria'));
        self::assertTrue(AccesoRol::can('administrador', 'categorias'));
    }
}
