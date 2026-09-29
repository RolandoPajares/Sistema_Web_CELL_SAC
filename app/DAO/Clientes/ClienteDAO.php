<?php

declare(strict_types=1);

namespace App\DAO\Clientes;

use App\DAO\Contratos\RepositorioClienteInterfaz;
use App\Nucleo\BaseDatos\Conexion;
use PDO;

final class ClienteDAO implements RepositorioClienteInterfaz
{
    public function __construct(private Conexion $conexion)
    {
    }

    public function todos(): array
    {
        return $this->pdo()->query(
            'SELECT c.id, c.tipo, c.documento, c.razon_social AS empresa, c.nombre_contacto AS contacto,
                    c.correo, c.telefono, c.ciudad, c.activo, c.creado_en,
                    COUNT(p.id) AS pedidos
             FROM clientes c
             LEFT JOIN pedidos p ON p.usuario_id = c.usuario_id
             GROUP BY c.id, c.tipo, c.documento, c.razon_social, c.nombre_contacto,
                      c.correo, c.telefono, c.ciudad, c.activo, c.creado_en
             ORDER BY c.activo DESC, c.id DESC'
        )->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $sentencia = $this->pdo()->prepare(
            'SELECT id, tipo, documento, razon_social AS empresa, nombre_contacto AS contacto,
                    correo, telefono, ciudad, activo
             FROM clientes WHERE id = :id LIMIT 1'
        );
        $sentencia->execute([':id' => $id]);
        $cliente = $sentencia->fetch();

        return $cliente ?: null;
    }

    public function existeDocumento(string $documento, ?int $idExcluido = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM clientes WHERE documento = :documento';
        if ($idExcluido !== null) {
            $sql .= ' AND id <> :id';
        }
        $sentencia = $this->pdo()->prepare($sql);
        $sentencia->bindValue(':documento', $documento);
        if ($idExcluido !== null) {
            $sentencia->bindValue(':id', $idExcluido, PDO::PARAM_INT);
        }
        $sentencia->execute();

        return (int) $sentencia->fetchColumn() > 0;
    }

    public function crear(array $datos): int
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO clientes(tipo, documento, razon_social, nombre_contacto, correo, telefono, ciudad, activo)
             VALUES(:tipo, :documento, :empresa, :contacto, :correo, :telefono, :ciudad, 1)'
        );
        $sentencia->execute($this->parametros($datos));

        return (int) $this->pdo()->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE clientes SET tipo=:tipo, documento=:documento, razon_social=:empresa,
             nombre_contacto=:contacto, correo=:correo, telefono=:telefono, ciudad=:ciudad WHERE id=:id'
        );
        $parametros = $this->parametros($datos);
        $parametros[':id'] = $id;
        $sentencia->execute($parametros);
        if ($sentencia->rowCount() === 0 && $this->buscar($id) === null) {
            throw new \DomainException('El cliente no existe.');
        }
    }

    public function desactivar(int $id): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE clientes SET activo = 0 WHERE id = :id AND activo = 1');
        $sentencia->execute([':id' => $id]);
        if ($sentencia->rowCount() !== 1) {
            throw new \DomainException('El cliente no existe o ya está inactivo.');
        }
    }

    /** @param array<string, string> $datos */
    private function parametros(array $datos): array
    {
        return [':tipo' => $datos['tipo'], ':documento' => $datos['documento'], ':empresa' => $datos['empresa'],
            ':contacto' => $datos['contacto'], ':correo' => $datos['correo'], ':telefono' => $datos['telefono'],
            ':ciudad' => $datos['ciudad']];
    }

    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
