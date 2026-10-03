<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioContactoInterfaz
{
    public function crear(string $nombre, string $contacto, string $mensaje): void;
}
