<?php

declare(strict_types=1);

namespace App\Soporte\Seguridad;

final class LimitadorSolicitudes
{
    public function __construct(private string $directorio)
    {
    }

    /**
     * Comprueba si se alcanzó el límite de intentos permitido.
     */
    public function demasiadosIntentos(string $clave, int $maximoIntentos, int $segundosCaducidad): bool
    {
        $intentos = $this->leer($clave, $segundosCaducidad);

        return count($intentos) >= $maximoIntentos;
    }

    /**
     * Crea o guarda la información relacionada con «intento».
     */
    public function registrarIntento(string $clave, int $segundosCaducidad): void
    {
        $intentos = $this->leer($clave, $segundosCaducidad);
        $intentos[] = time();
        $this->escribir($clave, $intentos);
    }

    /**
     * Limpia el estado actual y elimina los datos temporales asociados.
     */
    public function limpiar(string $clave): void
    {
        $archivo = $this->archivo($clave);
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }

    /**
     * Lee el valor o contenido almacenado en la ubicación indicada.
     *
     * @return array<int, int>
     */
    private function leer(string $clave, int $segundosCaducidad): array
    {
        $archivo = $this->archivo($clave);
        $valores = is_file($archivo) ? json_decode((string) file_get_contents($archivo), true) : [];
        $umbral = time() - $segundosCaducidad;

        return array_values(array_filter((array) $valores, static fn (mixed $valor): bool => is_int($valor) && $valor > $umbral));
    }

    /**
     * Guarda el mensaje y el contexto recibidos en el registro correspondiente.
     *
     * @param array<int, int> $intentos
     */
    private function escribir(string $clave, array $intentos): void
    {
        if (!is_dir($this->directorio)) {
            mkdir($this->directorio, 0775, true);
        }
        file_put_contents($this->archivo($clave), json_encode($intentos), LOCK_EX);
    }

    /**
     * Resuelve el archivo asociado a la ruta o identificador recibido.
     */
    private function archivo(string $clave): string
    {
        return rtrim($this->directorio, '/\\') . '/rate-' . hash('sha256', $clave) . '.json';
    }
}
