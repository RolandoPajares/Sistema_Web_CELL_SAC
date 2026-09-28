<?php

declare(strict_types=1);

namespace App\Soporte\Sesion;

final class GestorSesion
{
    /** @param array<string, mixed> $configuracion */
    public static function start(array $configuracion): void
    {
        if (PHP_SAPI === 'cli') {
            $_SESSION ??= [];
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', (bool) ($configuracion['use_strict_mode'] ?? true) ? '1' : '0');
        ini_set('session.use_only_cookies', '1');
        session_name((string) ($configuracion['nombre'] ?? 'md_technology_session'));

        if (!empty($configuracion['save_path'])) {
            $rutaGuardado = (string) $configuracion['save_path'];

            if (!is_dir($rutaGuardado)) {
                mkdir($rutaGuardado, 0775, true);
            }

            session_save_path($rutaGuardado);
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => (bool) ($configuracion['secure'] ?? false),
            'httponly' => (bool) ($configuracion['httponly'] ?? true),
            'samesite' => (string) ($configuracion['samesite'] ?? 'Lax'),
        ]);

        session_start();
    }

    public function obtener(string $clave, mixed $predeterminado = null): mixed
    {
        return $_SESSION[$clave] ?? $predeterminado;
    }

    public function guardar(string $clave, mixed $valor): void
    {
        $_SESSION[$clave] = $valor;
    }

    public function tiene(string $clave): bool
    {
        return array_key_exists($clave, $_SESSION);
    }

    public function eliminar(string $clave): void
    {
        unset($_SESSION[$clave]);
    }

    public function regenerar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function destruir(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parametros['path'],
                'domain' => $parametros['domain'],
                'secure' => (bool) $parametros['secure'],
                'httponly' => (bool) $parametros['httponly'],
                'samesite' => $parametros['samesite'],
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
