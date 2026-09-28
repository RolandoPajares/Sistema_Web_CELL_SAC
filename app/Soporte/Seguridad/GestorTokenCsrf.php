<?php

declare(strict_types=1);

namespace App\Soporte\Seguridad;

use App\Soporte\Sesion\GestorSesion;

final class GestorTokenCsrf
{
    public function __construct(private GestorSesion $sesion)
    {
    }

    public function token(): string
    {
        if (!$this->sesion->tiene('_csrf_token')) {
            $this->sesion->guardar('_csrf_token', bin2hex(random_bytes(32)));
        }

        return (string) $this->sesion->obtener('_csrf_token');
    }

    public function validar(?string $token): bool
    {
        return is_string($token)
            && $this->sesion->tiene('_csrf_token')
            && hash_equals((string) $this->sesion->obtener('_csrf_token'), $token);
    }

    public function campo(): string
    {
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars($this->token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
    }
}
