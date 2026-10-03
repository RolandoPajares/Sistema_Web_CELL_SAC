<?php

declare(strict_types=1);

namespace App\Soporte;

final class GeneradorUrl
{
    /**
     * Obtiene la ruta base configurada para la aplicación.
     */
    public function rutaBase(): string
    {
        $rutaScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $directorio = rtrim(str_replace('\\', '/', dirname($rutaScript)), '/');

        if (str_contains($rutaScript, '/admin/')) {
            $directorio = rtrim(str_replace('\\', '/', dirname($directorio)), '/');
        }

        if ($directorio === '.') {
            return '';
        }

        return $directorio;
    }

    /**
     * Indica si la ruta base incluye el directorio público de la aplicación.
     */
    public function esControladorFrontalPublico(): bool
    {
        return str_ends_with($this->rutaBase(), '/public') || $this->rutaBase() === '/public';
    }

    /**
     * Genera la URL interna correspondiente a la ruta indicada.
     */
    public function generar(string $ruta = ''): string
    {
        $ruta = ltrim($ruta, '/');

        return rtrim($this->rutaBase(), '/') . '/' . $ruta;
    }

    /**
     * Genera la URL pública de un recurso estático.
     */
    public function urlRecursoEstatico(string $ruta): string
    {
        return $this->generar(ltrim($ruta, '/'));
    }
}
