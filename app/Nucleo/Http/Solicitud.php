<?php

declare(strict_types=1);

namespace App\Nucleo\Http;

final class Solicitud
{
    /**
     * @param array<string, mixed> $consulta
     * @param array<string, mixed> $cuerpo
     * @param array<string, mixed> $servidor
     * @param array<string, mixed> $archivos
     * @param array<string, string> $parametrosRuta
     */
    public function __construct(
        private string $metodo,
        private string $ruta,
        private array $consulta,
        private array $cuerpo,
        private array $servidor,
        private array $archivos = [],
        private array $parametrosRuta = [],
    ) {
    }

    public static function capture(): self
    {
        $metodo = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $cuerpo = $_POST;

        if ($metodo === 'POST' && isset($cuerpo['_method'])) {
            $metodo = strtoupper((string) $cuerpo['_method']);
        }

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $ruta = parse_url($uri, PHP_URL_PATH) ?: '/';
        $ruta = self::normalizePath($ruta);

        return new self($metodo, $ruta, $_GET, $cuerpo, $_SERVER, $_FILES);
    }

    private static function normalizePath(string $ruta): string
    {
        $ruta = '/' . ltrim(str_replace('\\', '/', $ruta), '/');
        $rutaScript = '/' . ltrim(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $directorio = rtrim(str_replace('\\', '/', dirname($rutaScript)), '/');

        if (str_contains($rutaScript, '/admin/')) {
            $directorio = rtrim(str_replace('\\', '/', dirname($directorio)), '/');
        }

        if ($directorio !== '' && $directorio !== '/' && str_starts_with($ruta, $directorio . '/')) {
            $ruta = substr($ruta, strlen($directorio));
        }

        if ($ruta === '') {
            return '/';
        }

        return '/' . trim($ruta, '/');
    }

    public function metodo(): string
    {
        return $this->metodo;
    }

    public function ruta(): string
    {
        return $this->ruta;
    }

    public function esMetodo(string $metodo): bool
    {
        return $this->metodo === strtoupper($metodo);
    }

    public function consulta(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->consulta[$clave] ?? $predeterminado;
    }

    public function entrada(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->cuerpo[$clave] ?? $predeterminado;
    }

    /** @return array<string, mixed> */
    public function todos(): array
    {
        return array_merge($this->consulta, $this->cuerpo);
    }

    public function parametroRuta(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->parametrosRuta[$clave] ?? $predeterminado;
    }

    public function direccionIp(): string
    {
        return (string) ($this->servidor['REMOTE_ADDR'] ?? 'unknown');
    }

    public function esSegura(): bool
    {
        return ($this->servidor['HTTPS'] ?? '') !== '' && ($this->servidor['HTTPS'] ?? '') !== 'off';
    }

    /** @return array<string, mixed>|null */
    public function archivo(string $clave): ?array
    {
        $archivo = $this->archivos[$clave] ?? null;

        return is_array($archivo) ? $archivo : null;
    }

    /** @param array<string, string> $parametros */
    public function conParametrosRuta(array $parametros): self
    {
        return new self(
            $this->metodo,
            $this->ruta,
            $this->consulta,
            $this->cuerpo,
            $this->servidor,
            $this->archivos,
            $parametros
        );
    }
}
