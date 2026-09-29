<?php

declare(strict_types=1);

namespace App\Soporte;

final class GeneradorUrl
{
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

    public function esControladorFrontalPublico(): bool
    {
        return str_ends_with($this->rutaBase(), '/public') || $this->rutaBase() === '/public';
    }

    public function generar(string $ruta = ''): string
    {
        $ruta = ltrim($ruta, '/');

        return rtrim($this->rutaBase(), '/') . '/' . $ruta;
    }

    public function asset(string $ruta): string
    {
        return $this->generar(ltrim($ruta, '/'));
    }
}
