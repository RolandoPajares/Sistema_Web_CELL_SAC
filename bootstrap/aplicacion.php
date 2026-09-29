<?php

declare(strict_types=1);

use App\Nucleo\Enrutamiento\Enrutador;
use App\Nucleo\BaseDatos\GestorTransaccionesInterfaz;
use App\Nucleo\BaseDatos\Conexion;
use App\Soporte\Registros\RegistradorArchivo;
use App\DAO\Pedidos\PedidoDAO;
use App\DAO\Productos\ProductoDAO;
use App\DAO\Usuarios\UsuarioDAO;
use App\DAO\Contratos\RepositorioPedidoInterfaz;
use App\DAO\Contratos\RepositorioProductoInterfaz;
use App\DAO\Contratos\RepositorioUsuarioInterfaz;
use App\DAO\Contratos\RepositorioCategoriaInterfaz;
use App\DAO\Contratos\RepositorioProveedorInterfaz;
use App\DAO\Contratos\RepositorioClienteInterfaz;
use App\DAO\Contratos\RepositorioInventarioInterfaz;
use App\DAO\Categorias\CategoriaDAO;
use App\DAO\Proveedores\ProveedorDAO;
use App\DAO\Clientes\ClienteDAO;
use App\DAO\Inventario\InventarioDAO;
use App\Nucleo\Aplicacion;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\Nucleo\Contenedor;
use App\Nucleo\Entorno;
use App\Soporte\Seguridad\GestorTokenCsrf;
use App\Soporte\Seguridad\LimitadorSolicitudes;
use App\Soporte\Sesion\GestorSesion;
use App\Soporte\GeneradorUrl;

$rutaBase = dirname(__DIR__);

if (is_file($rutaBase . '/vendor/autoload.php')) {
    require_once $rutaBase . '/vendor/autoload.php';
}

spl_autoload_register(static function (string $clase) use ($rutaBase): void {
    $prefijo = 'App\\';

    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    $claseRelativa = substr($clase, strlen($prefijo));
    $archivo = $rutaBase . '/app/' . str_replace('\\', '/', $claseRelativa) . '.php';

    if (is_file($archivo)) {
        require_once $archivo;
    }
});

Entorno::load($rutaBase . '/.env');

$configuracion = new RepositorioConfiguracion([
    'app' => require $rutaBase . '/config/aplicacion.php',
    'database' => require $rutaBase . '/config/base_datos.php',
    'session' => require $rutaBase . '/config/sesion.php',
    'paths' => [
        'base' => $rutaBase,
        'views' => $rutaBase . '/resources/views',
        'storage' => $rutaBase . '/storage',
        'logs' => $rutaBase . '/storage/logs',
    ],
]);

date_default_timezone_set((string) $configuracion->obtener('app.timezone', 'America/Lima'));
GestorSesion::start((array) $configuracion->obtener('session', []));

$contenedor = new Contenedor();
$contenedor->registrarInstancia(RepositorioConfiguracion::class, $configuracion);
$contenedor->registrarInstancia(GestorSesion::class, new GestorSesion());
$contenedor->definir(RegistradorArchivo::class, fn () => new RegistradorArchivo((string) $configuracion->obtener('paths.logs') . '/app.log'));
$contenedor->definir(LimitadorSolicitudes::class, fn () => new LimitadorSolicitudes((string) $configuracion->obtener('paths.storage') . '/cache'));
$contenedor->definir(Conexion::class, fn (Contenedor $contenedor) => new Conexion($contenedor->obtener(RepositorioConfiguracion::class)));
$contenedor->definir(GestorTransaccionesInterfaz::class, Conexion::class);
$contenedor->definir(RepositorioProductoInterfaz::class, ProductoDAO::class);
$contenedor->definir(RepositorioUsuarioInterfaz::class, UsuarioDAO::class);
$contenedor->definir(RepositorioPedidoInterfaz::class, PedidoDAO::class);
$contenedor->definir(RepositorioCategoriaInterfaz::class, CategoriaDAO::class);
$contenedor->definir(RepositorioProveedorInterfaz::class, ProveedorDAO::class);
$contenedor->definir(RepositorioClienteInterfaz::class, ClienteDAO::class);
$contenedor->definir(RepositorioInventarioInterfaz::class, InventarioDAO::class);
$contenedor->definir(GestorTokenCsrf::class, fn (Contenedor $contenedor) => new GestorTokenCsrf($contenedor->obtener(GestorSesion::class)));
$contenedor->definir(GeneradorUrl::class, fn () => new GeneradorUrl());

Aplicacion::establecerContenedor($contenedor);

require_once $rutaBase . '/bootstrap/funciones.php';

$enrutador = new Enrutador($contenedor);
$contenedor->registrarInstancia(Enrutador::class, $enrutador);

(require $rutaBase . '/routes/publicas.php')($enrutador);
(require $rutaBase . '/routes/administrador.php')($enrutador);
(require $rutaBase . '/routes/roles.php')($enrutador);
(require $rutaBase . '/routes/api.php')($enrutador);

return $contenedor;
