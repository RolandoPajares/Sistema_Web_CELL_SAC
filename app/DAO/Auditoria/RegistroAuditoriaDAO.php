<?php

declare(strict_types=1);

namespace App\DAO\Auditoria;

use App\DAO\Contratos\RepositorioAuditoriaInterfaz;
use App\Nucleo\BaseDatos\Conexion;

final class RegistroAuditoriaDAO implements RepositorioAuditoriaInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * Guarda una acción auditada con los valores anteriores y nuevos.
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

    /**
     * Devuelve los registros disponibles que cumplen los filtros actuales.
     * @return array<int, array<string, mixed>>
     */
    public function listar(string $desde, string $hasta, string $entidad = '', int $idUsuario = 0): array
    {
        $condiciones = ['DATE(a.creado_en) BETWEEN :desde AND :hasta'];
        $parametros = [':desde' => $desde, ':hasta' => $hasta];
        if ($entidad !== '') {
            $condiciones[] = 'a.entidad = :entidad';
            $parametros[':entidad'] = $entidad;
        }
        if ($idUsuario > 0) {
            $condiciones[] = 'a.usuario_id = :usuario';
            $parametros[':usuario'] = $idUsuario;
        }
        $sentencia = $this->conexion->pdoObligatorio()->prepare(
            'SELECT a.id, a.creado_en, COALESCE(u.nombre, "Sistema") AS usuario,
                    a.usuario_id, a.accion, a.entidad, a.entidad_id, a.direccion_ip,
                    a.valores_anteriores, a.valores_nuevos
             FROM registros_auditoria a
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             WHERE ' . implode(' AND ', $condiciones) . '
             ORDER BY a.creado_en DESC, a.id DESC LIMIT 500'
        );
        $sentencia->execute($parametros);

        return $sentencia->fetchAll();
    }

    /**
     * Calcula un resumen consolidado de la información solicitada.
     *
     * @return array{hoy:int,total:int,usuarios:int,entidades:int}
     */
    public function resumen(): array
    {
        $fila = $this->conexion->pdoObligatorio()->query(
            'SELECT COUNT(*) AS total,
                    SUM(DATE(creado_en) = CURRENT_DATE) AS hoy,
                    COUNT(DISTINCT usuario_id) AS usuarios,
                    COUNT(DISTINCT entidad) AS entidades
             FROM registros_auditoria'
        )->fetch() ?: [];

        return [
            'hoy' => (int) ($fila['hoy'] ?? 0),
            'total' => (int) ($fila['total'] ?? 0),
            'usuarios' => (int) ($fila['usuarios'] ?? 0),
            'entidades' => (int) ($fila['entidades'] ?? 0),
        ];
    }

    /**
     * Agrupa los registros de actividad por entidad para el reporte.
     *
     * @return array<int, array{entidad:string,total:int}>
     */
    public function actividadPorEntidad(): array
    {
        return $this->conexion->pdoObligatorio()->query(
            'SELECT entidad, COUNT(*) AS total FROM registros_auditoria
             GROUP BY entidad ORDER BY total DESC, entidad LIMIT 8'
        )->fetchAll();
    }
}
