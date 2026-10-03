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

    /**
     * Construye la solicitud a partir de las variables globales de PHP.
     */
    public static function capturarActual(): self
    {
        // El campo _method permite representar otros verbos mediante POST.
        $metodo = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $cuerpo = $_POST;

        if ($metodo === 'POST' && isset($cuerpo['_method'])) {
            $metodo = strtoupper((string) $cuerpo['_method']);
        }

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $ruta = parse_url($uri, PHP_URL_PATH) ?: '/';
        $ruta = self::normalizarRuta($ruta);

        return new self($metodo, $ruta, $_GET, $cuerpo, $_SERVER, $_FILES);
    }

    /**
     * Normaliza la ruta y elimina el prefijo del directorio de la aplicación.
     */
    private static function normalizarRuta(string $ruta): string
    {
        $ruta = '/' . ltrim(str_replace('\\', '/', $ruta), '/'); // Asegura que la ruta comience con una barra y reemplaza barras invertidas por barras normales    
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

    /**
     * Devuelve el método HTTP de la solicitud actual.
     */
    public function metodo(): string
    {
        return $this->metodo;
    }

    /**
     * Devuelve la ruta solicitada por el cliente.
     */
    public function ruta(): string
    {
        return $this->ruta;
    }

    /**
     * Compara el método HTTP de la solicitud con el indicado.
     */
    public function esMetodo(string $metodo): bool
    {
        return $this->metodo === strtoupper($metodo);
    }

    /**
     * Obtiene el valor del parámetro de consulta indicado, si está disponible.
     */
    public function consulta(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->consulta[$clave] ?? $predeterminado;
    }

    /**
     * Obtiene un valor de entrada de la solicitud actual.
     */
    public function entrada(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->cuerpo[$clave] ?? $predeterminado;
    }

    /**
     * Combina los parámetros de consulta y los campos enviados en el cuerpo.
     * @return array<string, mixed>
     */
    public function todos(): array
    {
        return array_merge($this->consulta, $this->cuerpo);
    }

    /**
     * Obtiene el valor del parámetro asociado a la ruta.
     */
    public function parametroRuta(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->parametrosRuta[$clave] ?? $predeterminado;
    }

    /**
     * Obtiene la dirección IP del cliente a partir de la solicitud.
     */
    public function direccionIp(): string
    {
        return (string) ($this->servidor['REMOTE_ADDR'] ?? 'unknown');
    }

    /**
     * Indica si la solicitud llegó mediante HTTPS.
     */
    public function esSegura(): bool
    {
        return ($this->servidor['HTTPS'] ?? '') !== '' && ($this->servidor['HTTPS'] ?? '') !== 'off';
    }

    /**
     * Devuelve los datos de un archivo cargado con la solicitud.
     *
     * @return array<string, mixed>|null
     */
    public function archivo(string $clave): ?array
    {
        $archivo = $this->archivos[$clave] ?? null;

        return is_array($archivo) ? $archivo : null;
    }

    /**
     * Crea una solicitud con los parámetros de ruta encontrados.
     *
     * @param array<string, string> $parametros
     */
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
