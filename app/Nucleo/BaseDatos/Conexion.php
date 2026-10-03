<?php

declare(strict_types=1);

namespace App\Nucleo\BaseDatos;

use App\Soporte\Configuracion\RepositorioConfiguracion;
use PDO;
use Throwable;

final class Conexion implements GestorTransaccionesInterfaz
{
    /*
     * Valores predeterminados de conexión.
     * config/base_datos.php o .env pueden reemplazarlos.
     */
    private string $host = '127.0.0.1';
    private int $puerto = 3306;
    private string $nombreBaseDatosPredeterminado = 'md_tecnologia_digital_cell';
    private string $charset = 'utf8mb4';

    private ?PDO $pdo = null;
    private bool $resuelta = false;
    private ?Throwable $errorConexion = null;
    private int $contadorSavepoints = 0;

    public function __construct(
        private RepositorioConfiguracion $configuracion,
        ?PDO $pdo = null
    ) {
        $this->pdo = $pdo;
        $this->resuelta = $pdo !== null;
    }

    /**
     * Obtiene la conexión PDO y detiene la operación si no está disponible.
     */
    public function pdo(): ?PDO
    {
        if ($this->resuelta) {
            return $this->pdo;
        }

        $this->resuelta = true;

        try {
            $host = (string) $this->configuracion->obtener(
                'database.host',
                $this->host
            );

            $puerto = (int) $this->configuracion->obtener(
                'database.port',
                $this->puerto
            );

            $baseDatos = (string) $this->configuracion->obtener(
                'database.database',
                $this->nombreBaseDatosPredeterminado
            );

            $juegoCaracteres = (string) $this->configuracion->obtener(
                'database.charset',
                $this->charset
            );

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $host,
                $puerto,
                $baseDatos,
                $juegoCaracteres
            );

            $this->pdo = new PDO(
                $dsn,
                (string) $this->configuracion->obtener('database.username'),
                (string) $this->configuracion->obtener('database.password'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (Throwable $excepcion) {
            $this->errorConexion = $excepcion;
            $this->pdo = null;
        }

        return $this->pdo;
    }

    /**
     * Devuelve la conexión PDO o lanza una excepción si no está disponible.
     */
    public function pdoObligatorio(): PDO
    {
        $pdo = $this->pdo();

        if (!$pdo) {
            throw new \RuntimeException(
                'La conexión con la base de datos no está disponible.',
                0,
                $this->errorConexion
            );
        }

        return $pdo;
    }

    /**
     * Ejecuta la operación dentro de una transacción y revierte los cambios si ocurre un error.
     */
    public function transaccion(callable $operacion): mixed
    {
        $pdo = $this->pdoObligatorio();
        $transaccionExterna = $pdo->inTransaction();
        $savepoint = 'sp_aplicacion_' . (++$this->contadorSavepoints);

        try {
            if ($transaccionExterna) {
                $pdo->exec('SAVEPOINT ' . $savepoint);
            } else {
                $pdo->beginTransaction();
            }

            $resultado = $operacion();

            if ($transaccionExterna) {
                $pdo->exec('RELEASE SAVEPOINT ' . $savepoint);
            } else {
                $pdo->commit();
            }

            return $resultado;
        } catch (Throwable $excepcion) {
            if ($transaccionExterna && $pdo->inTransaction()) {
                $pdo->exec('ROLLBACK TO SAVEPOINT ' . $savepoint);
            } elseif ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $excepcion;
        }
    }
}
