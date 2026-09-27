<?php

declare(strict_types=1);

namespace App\Infraestructura\BaseDatos;

use App\Contratos\GestorTransaccionesInterfaz;
use App\Soporte\RepositorioConfiguracion;
use PDO;
use Throwable;

final class Conexion implements GestorTransaccionesInterfaz
{
    private ?PDO $pdo = null;
    private bool $resuelta = false;
    private ?Throwable $errorConexion = null;

    public function __construct(private RepositorioConfiguracion $configuracion, ?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        $this->resuelta = $pdo !== null;
    }

    public function pdo(): ?PDO
    {
        if ($this->resuelta) {
            return $this->pdo;
        }

        $this->resuelta = true;

        try {
            $baseDatos = $this->configuracion->obtener('database.database');
            $juegoCaracteres = $this->configuracion->obtener('database.charset', 'utf8mb4');
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->configuracion->obtener('database.host'),
                (int) $this->configuracion->obtener('database.port', 3306),
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

    public function transaccion(callable $operacion): mixed
    {
        $pdo = $this->pdoObligatorio();

        try {
            $pdo->beginTransaction();
            $resultado = $operacion();
            $pdo->commit();

            return $resultado;
        } catch (Throwable $excepcion) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $excepcion;
        }
    }
}
