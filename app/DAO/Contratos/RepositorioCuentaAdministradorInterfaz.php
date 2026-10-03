<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioCuentaAdministradorInterfaz
{
    /** @return array<string, mixed>|null */
    public function buscarPorId(int $idUsuario): ?array;

    public function correoEnUso(string $correo, int $idExcluido): bool;

    public function actualizarPerfil(int $idUsuario, string $nombre, string $correo): void;

    public function hashContrasena(int $idUsuario): ?string;

    public function actualizarContrasena(int $idUsuario, string $hash): void;
}
