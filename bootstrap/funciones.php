<?php

declare(strict_types=1);

use App\Nucleo\Http\RespuestaRedireccion;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Aplicacion;
use App\Soporte\Configuracion\RepositorioConfiguracion;
use App\Soporte\Seguridad\GestorTokenCsrf;
use App\Soporte\GeneradorUrl;
use App\Soporte\Sesion\GestorSesion;
use App\Soporte\Autorizacion\AccesoRol;

function app(?string $abstracto = null): mixed
{
    return $abstracto === null ? Aplicacion::contenedor() : Aplicacion::obtener($abstracto);
}

function config(string $clave, mixed $predeterminado = null): mixed
{
    return app(RepositorioConfiguracion::class)->obtener($clave, $predeterminado);
}

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(mixed $valor): string
{
    return (string) config('app.currency', 'S/') . ' ' . number_format((float) $valor, 2);
}

function url(string $ruta = ''): string
{
    return app(GeneradorUrl::class)->generar($ruta);
}

function asset(string $ruta): string
{
    return app(GeneradorUrl::class)->asset($ruta);
}

function product_image_url(string $ruta): string
{
    $ruta = trim($ruta);
    $esquema = strtolower((string) parse_url($ruta, PHP_URL_SCHEME));
    if ($ruta === '') {
        return '';
    }
    if (in_array($esquema, ['http', 'https'], true)) {
        return filter_var($ruta, FILTER_VALIDATE_URL) ? $ruta : '';
    }

    $rutaRelativa = ltrim($ruta, '/');
    if (
        !preg_match('#^assets/[A-Za-z0-9_./-]+$#D', $rutaRelativa)
        || str_contains($rutaRelativa, '..')
        || !is_file(dirname(__DIR__) . '/public/' . $rutaRelativa)
    ) {
        return '';
    }

    return asset($rutaRelativa);
}

function redirect(string $ruta): RespuestaRedireccion
{
    return new RespuestaRedireccion(url($ruta));
}

function response(string $contenido = '', int $estado = 200, array $encabezados = []): Respuesta
{
    return new Respuesta($contenido, $estado, $encabezados);
}

function csrf_token(): string
{
    return app(GestorTokenCsrf::class)->token();
}

function csrf_field(): string
{
    return app(GestorTokenCsrf::class)->campo();
}

function current_user(): ?array
{
    $usuario = app(GestorSesion::class)->obtener('user');

    return is_array($usuario) ? $usuario : null;
}

function is_admin(): bool
{
    return AccesoRol::normalize((string) (current_user()['rol'] ?? '')) === 'administrador';
}

function user_role(): string
{
    return AccesoRol::normalize((string) (current_user()['rol'] ?? 'visitante'));
}

function user_can(string $modulo): bool
{
    return AccesoRol::can(user_role(), $modulo);
}

function cart_count(): int
{
    return array_sum((array) app(GestorSesion::class)->obtener('cart', []));
}

function product_visual(string $marca): string
{
    $mapa = [
        'Samsung' => 'S',
        'Apple' => 'A',
        'OPPO' => 'O',
        'Xiaomi' => 'MI',
        'HONOR' => 'H',
        'JBL' => 'JBL',
        'Beats' => 'b',
    ];

    return $mapa[$marca] ?? 'MD';
}

/** @return array<string, array<string, mixed>> */
function active_campaigns(): array
{
    try {
        return app(\App\Servicios\Campanias\CampaniaServicio::class)->ubicacionesActivas();
    } catch (\Throwable $excepcion) {
        app(\App\Soporte\Registros\RegistradorArchivo::class)->error('Falló la consulta de campañas.', [
            'message' => $excepcion->getMessage(),
        ]);
        return [];
    }
}
function campaign_url(string $destino): string
{
    $destino = trim($destino);
    if ($destino === '') {
        return url('catalog');
    }
    if (preg_match('#^https?://#i', $destino) === 1) {
        return $destino;
    }

    return url(ltrim($destino, '/'));
}

function campaign_image(?string $ruta): string
{
    $ruta = trim((string) $ruta);
    if ($ruta === '') {
        return asset('assets/img/publico/inicio/banners/exhibicion1.jpg');
    }
    if (preg_match('#^https?://#i', $ruta) === 1) {
        return $ruta;
    }

    return asset(ltrim($ruta, '/'));
}
