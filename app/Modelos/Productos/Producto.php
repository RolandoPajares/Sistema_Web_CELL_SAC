<?php

declare(strict_types=1);

namespace App\Modelos\Productos;

/**
 * Representa un producto del catálogo y concentra reglas propias del producto.
 * La persistencia continúa delegada en el repositorio/DAO.
 */
final class Producto
{
    public const MAXIMO_CENTIMOS = 9999999999;

    /**
     * @param array<string, mixed> $atributos
     */
    private function __construct(private array $atributos)
    {
    }

    /**
     * Construye el modelo a partir de un registro obtenido por el repositorio.
     *
     * @param array<string, mixed> $registro
     */
    public static function desdeRegistro(array $registro): self
    {
        return new self($registro);
    }

    /**
     * Devuelve el registro con el formato que esperan los controladores y las vistas.
     *
     * @return array<string, mixed>
     */
    public function comoRegistro(): array
    {
        return $this->atributos;
    }

    /**
     * Devuelve el precio vigente para una nueva venta, en unidades monetarias.
     */
    public function precioEfectivo(): float
    {
        return $this->precioEfectivoEnCentimos() / 100; // Convertir a unidades monetarias
    }

    /**
     * Devuelve el precio vigente en centimos para operar sin acumulacion decimal.
     */
    public function precioEfectivoEnCentimos(): int
    {
        $precioBase = self::aCentimos($this->atributos['precio'] ?? null, 'precio');
        if (!array_key_exists('precio_oferta', $this->atributos) || $this->atributos['precio_oferta'] === null) {
            return $precioBase;
        }

        return self::aCentimos($this->atributos['precio_oferta'], 'precio de oferta');
    }

    /** Calcula un subtotal dentro de la precisión decimal(10,2) del esquema. */
    public static function subtotalEnCentimos(int $precioCentimos, int $cantidad): int
    {
        if ($precioCentimos < 0 || $precioCentimos > self::MAXIMO_CENTIMOS || $cantidad < 1
            || ($precioCentimos > 0 && $cantidad > intdiv(self::MAXIMO_CENTIMOS, $precioCentimos))
        ) {
            throw new \DomainException('El subtotal excede el importe permitido para el pedido.');
        }

        return $precioCentimos * $cantidad;
    }

    public function nombre(): string
    {
        return (string) ($this->atributos['nombre'] ?? '');
    }

    public function marca(): string
    {
        return (string) ($this->atributos['marca'] ?? '');
    }

    public function categoria(): string
    {
        return (string) ($this->atributos['categoria'] ?? '');
    }

    public function estaActivo(): bool
    {
        $activo = $this->atributos['activo'] ?? false;

        return $activo === true || $activo === 1 || $activo === '1';
    }

    public function tieneStockBajo(int $umbral = 8): bool
    {
        return $this->estaActivo()
            && (int) ($this->atributos['existencias'] ?? 0) <= max(0, $umbral);
    }

    public function coincideConTexto(string $texto): bool
    {
        $texto = trim($texto);

        return $texto === ''
            || stripos($this->nombre() . ' ' . $this->marca(), $texto) !== false;
    }

    private static function aCentimos(mixed $importe, string $campo): int
    {
        if (!is_scalar($importe) || !is_numeric($importe)) {
            throw new \DomainException('El ' . $campo . ' del producto no es válido.');
        }

        $numero = (float) $importe;
        if (!is_finite($numero) || $numero < 0 || $numero > self::MAXIMO_CENTIMOS / 100) {
            throw new \DomainException('El ' . $campo . ' del producto no es válido.');
        }

        return (int) round($numero * 100, 0, PHP_ROUND_HALF_UP);
    }
}
