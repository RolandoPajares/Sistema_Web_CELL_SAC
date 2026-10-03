<?php

declare(strict_types=1);

/**
 * Funciones auxiliares globales de la aplicación.
 */

use App\Nucleo\Http\RespuestaRedireccion;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Aplicacion;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\Soporte\Seguridad\GestorTokenCsrf;
use App\Soporte\GeneradorUrl;
use App\Soporte\Sesion\GestorSesion;
use App\Soporte\Autorizacion\AccesoRol;

/**
 * Devuelve el contenedor o resuelve el servicio solicitado.
 */
function resolver_servicio(?string $abstracto = null): mixed
{
    return $abstracto === null
        ? Aplicacion::contenedor()
        : Aplicacion::obtener($abstracto);
}

/**
 * Obtiene un valor de configuración o devuelve el valor alternativo.
 */
function configuracion(string $clave, mixed $predeterminado = null): mixed
{
    $repositorioConfiguracion = resolver_servicio(RepositorioConfiguracion::class);

    return $repositorioConfiguracion->obtener($clave, $predeterminado);
}

/**
 * Escapa un valor para mostrarlo de forma segura en HTML.
 */
function e(mixed $valor): string
{
    $valorCadena = (string) $valor;

    return htmlspecialchars(
        $valorCadena,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Formatea un importe con la moneda configurada para la aplicación.
 */
function formatear_dinero(mixed $valor): string
{
    $monedaConfigurada = (string) configuracion('app.currency', 'S/');
    $valorNumerico = (float) $valor;
    $importeFormateado = number_format($valorNumerico, 2);

    return $monedaConfigurada . ' ' . $importeFormateado;
}

/**
 * Genera la URL interna correspondiente a la ruta indicada.
 */
function url_interna(string $ruta = ''): string
{
    $generadorUrl = resolver_servicio(GeneradorUrl::class);

    return $generadorUrl->generar($ruta);
}

/**
 * Genera la URL pública de un recurso estático.
 */
function url_recurso_estatico(string $ruta): string
{
    $generadorUrl = resolver_servicio(GeneradorUrl::class);

    return $generadorUrl->urlRecursoEstatico($ruta);
}

/**
 * Convierte una ruta de imagen de producto en una URL pública válida.
 */
function url_imagen_producto(string $ruta): string
{
    $rutaLimpia = trim($ruta);
    $esquema = strtolower((string) parse_url($rutaLimpia, PHP_URL_SCHEME));

    if ($rutaLimpia === '') {
        return '';
    }

    if (in_array($esquema, ['http', 'https'], true)) {
        return filter_var($rutaLimpia, FILTER_VALIDATE_URL) ? $rutaLimpia : '';
    }

    $rutaRelativa = ltrim($rutaLimpia, '/');
    $archivoPublico = dirname(__DIR__) . '/public/' . $rutaRelativa;

    $esRutaValida = preg_match('#^assets/[A-Za-z0-9_./-]+$#D', $rutaRelativa) === 1;
    $contieneSaltoDirectorio = str_contains($rutaRelativa, '..');
    $existeArchivo = is_file($archivoPublico);

    if (!$esRutaValida || $contieneSaltoDirectorio || !$existeArchivo) {
        return '';
    }

    return url_recurso_estatico($rutaRelativa);
}

/**
 * Devuelve el icono correspondiente a la categorÃ­a de un producto.
 */
function icono_categoria_producto(string $categoria): string
{
    if (stripos($categoria, 'audio') !== false) {
        return 'bi-headphones';
    }

    if (stripos($categoria, 'celular') !== false) {
        return 'bi-phone';
    }

    return 'bi-box-seam';
}

/**
 * Prepara una redirección a la ruta indicada.
 */
function redirigir(string $ruta): RespuestaRedireccion
{
    $urlDestino = url_interna($ruta);

    return new RespuestaRedireccion($urlDestino);
}

/**
 * Crea una respuesta HTTP con contenido, estado y encabezados.
 */
function respuesta_http(string $contenido = '', int $estado = 200, array $encabezados = []): Respuesta
{
    return new Respuesta(
        $contenido,
        $estado,
        $encabezados
    );
}

/**
 * Obtiene el token de seguridad de la sesión actual.
 */
function csrf_token(): string
{
    $gestorCsrf = resolver_servicio(GestorTokenCsrf::class);

    return $gestorCsrf->token();
}

/**
 * Genera el campo oculto con el token CSRF para un formulario.
 */
// function csrf_field(): string para generar el campo oculto con el token CSRF para un formulario
function csrf_field(): string
{
    $gestorCsrf = resolver_servicio(GestorTokenCsrf::class); // Devuelve el campo oculto con el token CSRF para un formulario

    return $gestorCsrf->campo(); // Devuelve el campo oculto con el token CSRF para un formulario
}

/**
 * Devuelve los datos del usuario autenticado o null si no hay sesión.
 */
function usuario_actual(): ?array
{
    $gestorSesion = resolver_servicio(GestorSesion::class); // Devuelve los datos del usuario autenticado o null si no hay sesión
    $usuario = $gestorSesion->obtener('user');

    return is_array($usuario) ? $usuario : null;
}

/**
 * Indica si el usuario actual tiene permisos de administrador.
 */
function es_administrador(): bool
{
    $usuario = usuario_actual();
    $rolCrudo = (string) ($usuario['rol'] ?? '');
    $rolNormalizado = AccesoRol::normalizarRol($rolCrudo);

    return $rolNormalizado === 'administrador';
}

/**
 * Obtiene el rol asignado al usuario actual.
 */
function rol_usuario_actual(): string
{
    $usuario = usuario_actual();
    $rolCrudo = (string) ($usuario['rol'] ?? 'visitante');

    return AccesoRol::normalizarRol($rolCrudo);
}

/**
 * Comprueba si el usuario puede acceder al módulo indicado.
 */
function usuario_puede_acceder(string $modulo): bool
{
    $rolActual = rol_usuario_actual();

    return AccesoRol::puedeAcceder($rolActual, $modulo);
}

/**
 * Cuenta las unidades que contiene el carrito actual.
 */
function unidades_carrito(): int
{
    $gestorSesion = resolver_servicio(GestorSesion::class);
    $carrito = (array) $gestorSesion->obtener('cart', []);

    return array_sum($carrito);
}

/**
 * Devuelve la representación visual asociada a la marca indicada.
 */
function representacion_visual_marca(string $marca): string
{
    $mapa = [
        'samsung' => 'S',
        'apple'   => 'A',
        'oppo'    => 'O',
        'xiaomi'  => 'MI',
        'honor'   => 'H',
        'jbl'     => 'JBL',
        'beats'   => 'b',
    ];

    $marcaLimpia = trim($marca);
    $marcaNormalizada = mb_strtolower($marcaLimpia, 'UTF-8');
    $subcadenaAlternativa = mb_substr($marcaLimpia, 0, 2, 'UTF-8');

    return $mapa[$marcaNormalizada]
        ?? ($subcadenaAlternativa !== '' ? $subcadenaAlternativa : 'MD');
}

/**
 * Devuelve las campañas activas disponibles.
 *
 * @return array<int, array<string, mixed>>
 */
function campanias_activas(): array
{
    try {
        $servicioCampania = resolver_servicio(\App\Servicios\Campanias\CampaniaServicio::class);

        return $servicioCampania->ubicacionesActivas();
    } catch (\Throwable $excepcion) {
        $registradorArchivo = resolver_servicio(\App\Soporte\Registros\RegistradorArchivo::class);
        $registradorArchivo->error('Falló la consulta de campañas.', [
            'message' => $excepcion->getMessage(),
        ]);

        return [];
    }
}

/**
 * Genera la URL pública del destino de una campaña.
 */
function url_campania(string $destino): string
{
    $destinoLimpio = trim($destino);

    if ($destinoLimpio === '') {
        return url_interna('catalog');
    }

    $esUrlExterna = preg_match('#^https?://#i', $destinoLimpio) === 1;
    if ($esUrlExterna) {
        return $destinoLimpio;
    }

    $rutaLimpia = ltrim($destinoLimpio, '/');

    return url_interna($rutaLimpia);
}

/**
 * Prepara la URL de imagen de una campaña, si está disponible.
 */
function url_imagen_campania(?string $ruta): string
{
    $rutaLimpia = trim((string) $ruta);

    if ($rutaLimpia === '') {
        return url_recurso_estatico('assets/img/publico/inicio/banners/exhibicion1.jpg');
    }

    $esUrlExterna = preg_match('#^https?://#i', $rutaLimpia) === 1;
    if ($esUrlExterna) {
        return $rutaLimpia;
    }

    $rutaRelativa = ltrim($rutaLimpia, '/');

    return url_recurso_estatico($rutaRelativa);
}
