<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioUsuarioInterfaz
{
    public function estaDisponible(): bool;

    /** @return array<string, mixed>|null */
    public function buscarPorCorreo(string $correo): ?array;

    /** @param array{nombre:string,correo:string,contrasena:string,rol:string} $datos */
    public function crear(array $datos): int;

    /** @return array<int, array<string, mixed>> */
    public function todos(): array;

    public function contar(): int;
}
