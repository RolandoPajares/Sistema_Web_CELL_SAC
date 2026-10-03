<?php

declare(strict_types=1);

namespace App\Servicios\Contacto;

use App\DAO\Contratos\RepositorioContactoInterfaz;

final class ContactoServicio
{
    public function __construct(private RepositorioContactoInterfaz $contactos)
    {
    }

    public function registrarConsulta(string $nombre, string $contacto, string $mensaje): void
    {
        $nombre = trim($nombre);
        $contacto = trim($contacto);
        $mensaje = trim($mensaje);

        if (
            $nombre === ''
            || $contacto === ''
            || $mensaje === ''
            || mb_strlen($nombre) > 120
            || mb_strlen($contacto) > 160
            || mb_strlen($mensaje) > 2000
        ) {
            throw new \DomainException('Completa todos los campos de la consulta.');
        }

        $this->contactos->crear($nombre, $contacto, $mensaje);
    }
}
