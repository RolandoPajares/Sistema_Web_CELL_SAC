<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Soporte\Autorizacion\AccesoRol;
use PHPUnit\Framework\TestCase;

// Pruebas unitarias para validar la normalización de roles y las reglas de control de acceso por módulos.
final class AccesoRolTest extends TestCase
{
    // Comprueba que los roles oficiales del sistema se mantienen intactos al ser normalizados.
    public function testSeConservanLosRolesOficiales(): void
    {
        self::assertSame('administrador', AccesoRol::normalizarRol('administrador'));
        self::assertSame('cliente_minorista', AccesoRol::normalizarRol('cliente_minorista'));
    }

    // Comprueba que los roles internos de la organización solo poseen acceso exclusivo a sus módulos asignados.
    public function testLosRolesInternosSoloAccedenASusModulos(): void
    {
        self::assertTrue(AccesoRol::puedeAcceder('compras_logistica', 'inventario'));
        self::assertFalse(AccesoRol::puedeAcceder('compras_logistica', 'usuarios'));
        self::assertTrue(AccesoRol::puedeAcceder('marketing', 'campanias'));
        self::assertFalse(AccesoRol::puedeAcceder('marketing', 'compras'));
    }

    // Comprueba que el rol de administrador cuenta con permisos de acceso global para todos los módulos registrados.
    public function testElAdministradorAccedeATodosLosModulosRegistrados(): void
    {
        self::assertTrue(AccesoRol::puedeAcceder('administrador', 'auditoria'));
        self::assertTrue(AccesoRol::puedeAcceder('administrador', 'categorias'));
    }

    public function testLaVisibilidadDeUnModuloNoAutorizaEscrituras(): void
    {
        self::assertTrue(AccesoRol::puedeAcceder('cliente_minorista', 'pedidos'));
        self::assertFalse(AccesoRol::puedeOperar('cliente_minorista', 'pedidos', 'cambiar_estado'));
        self::assertTrue(AccesoRol::puedeOperar('compras_logistica', 'inventario', 'crear'));
        self::assertFalse(AccesoRol::puedeOperar('compras_logistica', 'inventario', 'editar'));
    }

    public function testLosPermisosOperativosCorrespondenAlModuloYLaAccion(): void
    {
        self::assertTrue(AccesoRol::puedeOperar('ventas_mayoristas', 'cotizaciones', 'crear'));
        self::assertTrue(AccesoRol::puedeOperar('ventas_mayoristas', 'pedidos-mayoristas', 'cambiar_estado'));
        self::assertFalse(AccesoRol::puedeOperar('ventas_mayoristas', 'pedidos-mayoristas', 'desactivar'));
        self::assertTrue(AccesoRol::puedeAcceder('marketing', 'campanias'));
        self::assertFalse(AccesoRol::puedeOperar('marketing', 'campanias', 'crear'));
        self::assertTrue(AccesoRol::puedeOperar('administrador', 'auditoria', 'desactivar'));
    }
}
