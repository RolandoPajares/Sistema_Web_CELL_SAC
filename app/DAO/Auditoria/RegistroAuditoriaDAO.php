<?php

declare(strict_types=1);

namespace App\DAO\Auditoria;

use App\Nucleo\BaseDatos\Conexion;

final class RegistroAuditoriaDAO
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * @param array<string, mixed>|null $valoresAnteriores
     * @param array<string, mixed>|null $valoresNuevos
     */
    public function registrar(
        ?int $idUsuario,
        string $accion,
        string $entidad,
        ?int $idEntidad,
        ?array $valoresAnteriores,
        ?array $valoresNuevos,
        string $direccionIp,
    ): void {
        $sentencia = $this->conexion->pdoObligatorio()->prepare(
            'INSERT INTO registros_auditoria(usuario_id, accion, entidad, entidad_id, valores_anteriores, valores_nuevos, direccion_ip)
             VALUES(?, ?, ?, ?, ?, ?, ?)'
        );
        $sentencia->execute([
            $idUsuario,
            $accion,
            $entidad,
            $idEntidad,
            $valoresAnteriores === null ? null : json_encode($valoresAnteriores, JSON_UNESCAPED_UNICODE),
            $valoresNuevos === null ? null : json_encode($valoresNuevos, JSON_UNESCAPED_UNICODE),
            $direccionIp,
        ]);
    }
}
