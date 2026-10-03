<?php

declare(strict_types=1);

/**
 * Archivo de arranque y configuración del contenedor de dependencias (Bootstrap)
 */

// Importa las clases e interfaces necesarias para inicializar la aplicación
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
use App\DAO\Contratos\RepositorioContactoInterfaz;
use App\DAO\Contratos\RepositorioModuloInterfaz;
use App\DAO\Contratos\RepositorioAuditoriaInterfaz;
use App\DAO\Contratos\RepositorioCampaniaInterfaz;
use App\DAO\Contratos\RepositorioInteligenciaNegocioInterfaz;
use App\DAO\Contratos\RepositorioCuentaAdministradorInterfaz;
use App\DAO\Categorias\CategoriaDAO;
use App\DAO\Proveedores\ProveedorDAO;
use App\DAO\Clientes\ClienteDAO;
use App\DAO\Inventario\InventarioDAO;
use App\DAO\Contacto\ContactoDAO;
use App\DAO\Panel\ModuloDAO;
use App\DAO\Auditoria\RegistroAuditoriaDAO;
use App\DAO\Campanias\CampaniaDAO;
use App\DAO\ComercioInteligente\InteligenciaNegocioDAO;
use App\Nucleo\Aplicacion;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\Nucleo\Contenedor;
use App\Nucleo\Entorno;
use App\Soporte\Seguridad\GestorTokenCsrf;
use App\Soporte\Seguridad\LimitadorSolicitudes;
use App\Soporte\Sesion\GestorSesion;
use App\Soporte\GeneradorUrl;

// Obtiene la ruta absoluta del directorio raíz del proyecto
$rutaBase = dirname(__DIR__);

// Carga el autoloader de Composer para gestionar automáticamente las dependencias, si está disponible
if (is_file($rutaBase . '/vendor/autoload.php')) {
    require_once $rutaBase . '/vendor/autoload.php';
}

// Registra un autoloader personalizado para cargar dinámicamente las clases del espacio de nombres App\
spl_autoload_register(static function (string $clase) use ($rutaBase): void {
    $prefijo = 'App\\';

    // Ignora las clases que no pertenecen al espacio de nombres App\
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    // Convierte el nombre completo de la clase en la ruta correspondiente dentro del directorio /app/
    $claseRelativa = substr($clase, strlen($prefijo));
    $archivo = $rutaBase . '/app/' . str_replace('\\', '/', $claseRelativa) . '.php';

    // Carga el archivo correspondiente únicamente si existe
    if (is_file($archivo)) {
        require_once $archivo; // Incluye el archivo que contiene la definición de la clase solicitada
    }
});

// Carga las variables de entorno definidas en el archivo .env
Entorno::cargarDesdeArchivo($rutaBase . '/.env');

// Centraliza la configuración de la aplicación, base de datos, sesión y rutas principales del sistema
$configuracion = new RepositorioConfiguracion([
    'app'      => require $rutaBase . '/config/aplicacion.php',
    'database' => require $rutaBase . '/config/base_datos.php',
    'session'  => require $rutaBase . '/config/sesion.php',
    'paths'    => [
        'base'    => $rutaBase,
        'views'   => $rutaBase . '/resources/views',
        'storage' => $rutaBase . '/storage',
        'logs'    => $rutaBase . '/storage/logs',
    ],
]);

// Establece la zona horaria predeterminada de la aplicación utilizando la configuración definida
date_default_timezone_set((string) $configuracion->obtener('app.timezone', 'America/Lima'));

// Configura e inicia la sesión utilizando los parámetros definidos en la configuración del sistema
GestorSesion::iniciar((array) $configuracion->obtener('session', []));

// Inicializa el contenedor encargado de administrar las dependencias de la aplicación
$contenedor = new Contenedor();

// Registra las instancias principales que serán reutilizadas mediante el contenedor de dependencias
$contenedor->registrarInstancia(
    RepositorioConfiguracion::class, 
    $configuracion
);

$contenedor->registrarInstancia(
    GestorSesion::class, 
    new GestorSesion()
);

// Define los servicios principales y especifica cómo deben construirse sus dependencias
$contenedor->definir(
    RegistradorArchivo::class, 
    fn (): RegistradorArchivo => new RegistradorArchivo(
        (string) $configuracion->obtener('paths.logs') . '/app.log'
    )
);

$contenedor->definir(
    LimitadorSolicitudes::class, 
    fn (): LimitadorSolicitudes => new LimitadorSolicitudes(
        (string) $configuracion->obtener('paths.storage') . '/cache'
    )
);

$contenedor->definir(
    Conexion::class, 
    fn (Contenedor $contenedor): Conexion => new Conexion(
        $contenedor->obtener(RepositorioConfiguracion::class)
    )
);

$contenedor->definir(
    GestorTransaccionesInterfaz::class, 
    Conexion::class
);

// Vincula cada interfaz de repositorio con su implementación concreta mediante clases DAO
$contenedor->definir(
    RepositorioProductoInterfaz::class, 
    ProductoDAO::class
);

$contenedor->definir(
    RepositorioUsuarioInterfaz::class, 
    UsuarioDAO::class
);

$contenedor->definir(RepositorioCuentaAdministradorInterfaz::class, UsuarioDAO::class);

$contenedor->definir(
    RepositorioPedidoInterfaz::class, 
    PedidoDAO::class
);

$contenedor->definir(
    RepositorioCategoriaInterfaz::class, 
    CategoriaDAO::class
);

$contenedor->definir(
    RepositorioProveedorInterfaz::class, 
    ProveedorDAO::class
);

$contenedor->definir(
    RepositorioClienteInterfaz::class, 
    ClienteDAO::class
);

// Vincula la interfaz de inventario con su implementación concreta mediante la clase DAO correspondiente
$contenedor->definir(
    RepositorioInventarioInterfaz::class, 
    InventarioDAO::class
);

// Vincula las interfaces restantes de repositorio con sus implementaciones concretas
$contenedor->definir(RepositorioContactoInterfaz::class, ContactoDAO::class);
$contenedor->definir(RepositorioModuloInterfaz::class, ModuloDAO::class);
$contenedor->definir(RepositorioAuditoriaInterfaz::class, RegistroAuditoriaDAO::class);
$contenedor->definir(RepositorioCampaniaInterfaz::class, CampaniaDAO::class);
$contenedor->definir(RepositorioInteligenciaNegocioInterfaz::class, InteligenciaNegocioDAO::class);

// Registra los servicios auxiliares relacionados con seguridad y generación de URLs
$contenedor->definir(
    GestorTokenCsrf::class, 
    fn (Contenedor $contenedor): GestorTokenCsrf => new GestorTokenCsrf(
        $contenedor->obtener(GestorSesion::class)
    )
);

$contenedor->definir(
    GeneradorUrl::class, 
    fn (): GeneradorUrl => new GeneradorUrl()
);

// Establece el contenedor de dependencias para que pueda ser utilizado por la aplicación
Aplicacion::establecerContenedor($contenedor);

// Carga las funciones auxiliares globales utilizadas por la aplicación
require_once $rutaBase . '/bootstrap/funciones.php';

// Inicializa el enrutador y registra su instancia dentro del contenedor de dependencias
$enrutador = new Enrutador($contenedor);
$contenedor->registrarInstancia(Enrutador::class, $enrutador);

// Carga y registra los diferentes grupos de rutas disponibles en la aplicación
(require $rutaBase . '/routes/publicas.php')($enrutador);
(require $rutaBase . '/routes/administrador.php')($enrutador);
(require $rutaBase . '/routes/roles.php')($enrutador);
(require $rutaBase . '/routes/api.php')($enrutador);

// Retorna el contenedor completamente configurado para ser utilizado por el punto de entrada de la aplicación
return $contenedor;