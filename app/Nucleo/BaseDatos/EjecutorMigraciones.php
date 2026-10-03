<?php

declare(strict_types=1);

namespace App\Nucleo\BaseDatos;

use PDO;

final class EjecutorMigraciones
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * Devuelve las migraciones que todavía no aparecen registradas como ejecutadas.
     *
     * @return array<int, string>
     */
    public function pendientes(string $directorio): array
    {
        $this->asegurarTabla();
        $ejecutadas = $this->conexion->pdoObligatorio()->query('SELECT migracion FROM migraciones')->fetchAll(PDO::FETCH_COLUMN);
        $archivos = glob(rtrim($directorio, '/\\') . '/*.sql') ?: [];
        sort($archivos, SORT_STRING);

        return array_values(array_filter(
            $archivos,
            static fn (string $archivo): bool => !in_array(basename($archivo), $ejecutadas, true)
        ));
    }

    /**
     * Ejecuta las migraciones pendientes y registra cada ejecución.
     *
     * @return array<int, string>
     */
    public function migrar(string $directorio): array
    {
        $pendientes = $this->pendientes($directorio);
        if ($pendientes === []) {
            return [];
        }

        $pdo = $this->conexion->pdoObligatorio();
        $lote = (int) $pdo->query('SELECT COALESCE(MAX(lote), 0) + 1 FROM migraciones')->fetchColumn();

        foreach ($pendientes as $archivo) {
            $consultaSql = file_get_contents($archivo);
            if ($consultaSql === false) {
                throw new \RuntimeException('No se puede leer la migración: ' . basename($archivo));
            }

            try {
                $pdo->exec($consultaSql);
                $sentencia = $pdo->prepare('INSERT INTO migraciones(migracion, lote) VALUES(?, ?)');
                $sentencia->execute([basename($archivo), $lote]);
            } catch (\Throwable $excepcion) {
                throw $excepcion;
            }
        }

        return array_map('basename', $pendientes);
    }

    /**
     * Devuelve el estado actual del recurso o proceso consultado.
     *
     * @return array<int, array{migracion:string,ejecutada:bool}>
     */
    public function estado(string $directorio): array
    {
        $pendientes = array_map('basename', $this->pendientes($directorio));
        $archivos = glob(rtrim($directorio, '/\\') . '/*.sql') ?: [];
        sort($archivos, SORT_STRING);

        return array_map(
            static fn (string $archivo): array => [
                'migracion' => basename($archivo),
                'ejecutada' => !in_array(basename($archivo), $pendientes, true),
            ],
            $archivos
        );
    }

    /**
     * Crea la tabla de control de migraciones si todavía no existe.
     */
    private function asegurarTabla(): void
    {
        $this->conexion->pdoObligatorio()->exec(
            'CREATE TABLE IF NOT EXISTS migraciones (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migracion VARCHAR(255) NOT NULL UNIQUE,
                lote INT UNSIGNED NOT NULL,
                ejecutada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB'
        );
    }
}
