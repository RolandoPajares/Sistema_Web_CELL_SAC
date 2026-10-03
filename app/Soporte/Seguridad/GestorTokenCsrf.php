<?php

declare(strict_types=1);

namespace App\Soporte\Seguridad;

use App\Soporte\Sesion\GestorSesion;

final class GestorTokenCsrf
{
    public function __construct(private GestorSesion $sesion)
    {
    }

    /**
     * Genera o recupera el token asociado al contexto actual.
     */
    public function token(): string
    {
        if (!$this->sesion->tiene('_csrf_token')) {
            $this->sesion->guardar('_csrf_token', bin2hex(random_bytes(32)));
        }

        return (string) $this->sesion->obtener('_csrf_token');
    }

    /**
     * Comprueba que los datos cumplan las reglas antes de continuar.
     */
    public function validar(?string $token): bool
    {
        return is_string($token)
            && $this->sesion->tiene('_csrf_token')
            && hash_equals((string) $this->sesion->obtener('_csrf_token'), $token);
    }

    /**
     * Prepara el campo de formulario con sus atributos y valor.
     */
    public function campo(): string
    {
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars($this->token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
    }
}
