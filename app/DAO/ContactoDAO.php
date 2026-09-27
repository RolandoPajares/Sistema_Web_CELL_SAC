<?php

declare(strict_types=1);

namespace App\DAO;

use App\Infraestructura\BaseDatos\Conexion;
use PDO;

final class ContactoDAO
{
    public function __construct(private Conexion $conexion)
    {
    }

    public function crear(string $nombre, string $contacto, string $mensaje): void
    {
        $sentencia = $this->conexion->pdoObligatorio()->prepare(
            'INSERT INTO mensajes_contacto(nombre, contacto, mensaje) VALUES(:nombre, :contacto, :mensaje)'
        );
        $sentencia->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $sentencia->bindValue(':contacto', $contacto, PDO::PARAM_STR);
        $sentencia->bindValue(':mensaje', $mensaje, PDO::PARAM_STR);
        $sentencia->execute();
    }
}
