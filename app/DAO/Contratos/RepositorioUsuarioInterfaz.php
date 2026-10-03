<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioUsuarioInterfaz
{
    /**
     * Indica si el repositorio puede consultar la base de datos.
     */
    public function estaDisponible(): bool;

    /**
     * Busca un usuario por su correo electrónico.
     * @return array<string, mixed>|null
     */
    public function buscarPorCorreo(string $correo): ?array;

    /**
     * Crea un usuario a partir de sus datos ya validados.
     * @param array{nombre:string,correo:string,contrasena:string,rol:string} $datos
     */
    public function crear(array $datos): int;

    /**
     * Devuelve los registros disponibles que cumplen los filtros actuales.
     * @return array<int, array<string, mixed>>
     */
    public function todos(): array;

    /**
     * Cuenta los elementos que cumplen las condiciones recibidas.
     */
    public function contar(): int;
}
