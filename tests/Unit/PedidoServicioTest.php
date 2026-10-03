<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DAO\Pedidos\PedidoDAO;
use App\DAO\Panel\ModuloDAO;
use App\Nucleo\BaseDatos\Conexion;
use App\Servicios\Pedidos\PedidoServicio;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use PDO;
use PHPUnit\Framework\TestCase;

final class PedidoServicioTest extends TestCase
{
    public function testUnModuloNoPuedeCambiarPedidosDelOtroSegmento(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite es necesario para probar el alcance del pedido.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombre TEXT NOT NULL, rol TEXT NOT NULL)');
        $pdo->exec('CREATE TABLE pedidos (id INTEGER PRIMARY KEY, usuario_id INTEGER NOT NULL, total NUMERIC NOT NULL, estado TEXT NOT NULL, creado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
        $pdo->exec("INSERT INTO usuarios(id, nombre, rol) VALUES (10, 'Minorista de prueba', 'cliente_minorista'), (20, 'Mayorista de prueba', 'cliente_mayorista')");
        $pdo->exec("INSERT INTO pedidos(id, usuario_id, total, estado) VALUES (101, 10, 50, 'Pendiente'), (202, 20, 75, 'Pendiente')");

        $servicio = new PedidoServicio(new PedidoDAO(new Conexion(new RepositorioConfiguracion([]), $pdo)));
        $modulos = new ModuloDAO(new Conexion(new RepositorioConfiguracion([]), $pdo));
        self::assertSame([101], array_column($modulos->listar('pedidos'), 'id'));
        self::assertSame([202], array_column($modulos->listar('pedidos-mayoristas'), 'id'));
        $servicio->actualizarEstadoParaRolCliente(101, 'En proceso', 'cliente_minorista');

        try {
            $servicio->actualizarEstadoParaRolCliente(202, 'En proceso', 'cliente_minorista');
            self::fail('El módulo minorista no debe cambiar el pedido mayorista.');
        } catch (\DomainException) {
            self::assertSame('En proceso', $pdo->query('SELECT estado FROM pedidos WHERE id = 101')->fetchColumn());
            self::assertSame('Pendiente', $pdo->query('SELECT estado FROM pedidos WHERE id = 202')->fetchColumn());
        }
    }
}
