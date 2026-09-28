<?php

declare(strict_types=1);

namespace App\DAO\Campanias;

use App\Nucleo\BaseDatos\Conexion;
use PDO;
use Throwable;

/**
 * Data Access Object para la gestión de Campañas Publicitarias.
 * Centraliza todas las consultas a la tabla 'campanas_publicitarias'.
 */
final class CampaniaDAO
{
    public function __construct(private Conexion $conexion)
    {
    }

    /**
     * Verifica si la tabla de campañas existe en la base de datos.
     * Útil para health-checks o rutinas de instalación/actualización automáticas
     * sin que la aplicación colapse si la tabla aún no fue migrada.
     */
    public function estaDisponible(): bool
    {
        try {
            return (bool) $this->conexion->pdoObligatorio()->query("SHOW TABLES LIKE 'campanas_publicitarias'")->fetchColumn();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Obtiene todas las campañas sin filtros de fecha.
     * Orden estratégico: Primero las activas, luego las más recientes,
     * ideal para el panel de administración donde se necesita visibilidad total.
     *
     * @return array<int, array<string, mixed>>
     */
    public function todosParaAdministrador(): array
    {
        return $this->pdo()->query('SELECT * FROM campanas_publicitarias ORDER BY activo DESC, inicia_en DESC, id DESC')->fetchAll();
    }

    /**
     * @return array<string, mixed>|null Devuelve null si no existe, permitiendo un manejo limpio de errores 404.
     */
    public function buscar(int $idCampania): ?array
    {
        $sentencia = $this->pdo()->prepare('SELECT * FROM campanas_publicitarias WHERE id = :id LIMIT 1');
        $sentencia->bindValue(':id', $idCampania, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->fetch() ?: null;
    }

    /**
     * Recupera solo las campañas que deben mostrarse al usuario final en este momento.
     * Regla de negocio: debe estar marcada como activa (1) y la fecha/hora actual
     * debe estar dentro de la ventana definida por inicia_en y finaliza_en.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ubicacionesActivas(): array
    {
        return $this->pdo()->query(
            'SELECT * FROM campanas_publicitarias WHERE activo = 1 AND inicia_en <= NOW() AND finaliza_en >= NOW() ORDER BY id DESC'
        )->fetchAll();
    }

    /**
     * Crea una nueva campaña publicitaria.
     * Se delega la validación de los datos a capas superiores (Servicios/Controladores).
     *
     * @param array<string, mixed> $datos
     * @return int El ID autoincremental generado tras la inserción.
     */
    public function crear(array $datos): int
    {
        $sentencia = $this->pdo()->prepare(
            'INSERT INTO campanas_publicitarias (nombre, ubicacion, titulo, descripcion, texto_boton, url_boton, url_imagen, precio_anterior, precio_oferta, inicia_en, finaliza_en, activo)
             VALUES (:nombre, :ubicacion, :titulo, :descripcion, :texto_boton, :url_boton, :url_imagen, :precio_anterior, :precio_oferta, :inicia_en, :finaliza_en, :activo)'
        );

        $this->vincularCampania($sentencia, $datos);
        $sentencia->execute();

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $datos Datos a actualizar. Deben incluir todas las columnas mapeadas.
     */
    public function actualizar(int $idCampania, array $datos): void
    {
        $sentencia = $this->pdo()->prepare(
            'UPDATE campanas_publicitarias
             SET nombre=:nombre, ubicacion=:ubicacion, titulo=:titulo, descripcion=:descripcion,
                 texto_boton=:texto_boton, url_boton=:url_boton, url_imagen=:url_imagen, precio_anterior=:precio_anterior,
                 precio_oferta=:precio_oferta, inicia_en=:inicia_en, finaliza_en=:finaliza_en, activo=:activo
             WHERE id=:id'
        );

        $this->vincularCampania($sentencia, $datos);
        $sentencia->bindValue(':id', $idCampania, PDO::PARAM_INT);
        $sentencia->execute();
    }

    /**
     * "Soft delete" / Desactivación manual.
     * Es preferible esto a borrar el registro físicamente (DELETE) para preservar
     * las estadísticas de clics e impresiones (tracks) de campañas pasadas.
     */
    public function desactivar(int $idCampania): void
    {
        $sentencia = $this->pdo()->prepare('UPDATE campanas_publicitarias SET activo = 0 WHERE id = :id');
        $sentencia->bindValue(':id', $idCampania, PDO::PARAM_INT);
        $sentencia->execute();
    }

    /**
     * Registra métricas de rendimiento (impresiones o clics) en tiempo real.
     * Prevención de SQL Injection: Aunque se interpola `$columna` directamente en la consulta,
     * está estrictamente controlado por el operador ternario (solo puede ser 'clics' o 'vistas').
     */
    public function registrarEvento(int $idCampania, string $evento): void
    {
        $columna = $evento === 'click' ? 'clics' : 'vistas';

        // Se exige activo = 1 para evitar conteos si la campaña ya expiró o se pausó.
        $sentencia = $this->pdo()->prepare(
            "UPDATE campanas_publicitarias SET {$columna} = {$columna} + 1 WHERE id = :id AND activo = 1"
        );

        $sentencia->bindValue(':id', $idCampania, PDO::PARAM_INT);
        $sentencia->execute();
    }

    /**
     * Helper centralizado para bindear el array asociativo de parámetros a la sentencia preparada.
     * Deduce dinámicamente el tipo de dato de PDO (PARAM_NULL, PARAM_INT, PARAM_STR)
     * lo cual mejora el rendimiento y la seguridad del motor de base de datos.
     *
     * @param array<string, mixed> $datos
     */
    private function vincularCampania(\PDOStatement $sentencia, array $datos): void
    {
        foreach ($datos as $campo => $valor) {
            $tipo = $valor === null ? PDO::PARAM_NULL : (is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
            $sentencia->bindValue(':' . $campo, $valor, $tipo);
        }
    }

    /**
     * Facilita el acceso a la instancia PDO garantizando que la conexión
     * esté viva o lanzando una excepción si falla.
     */
    private function pdo(): PDO
    {
        return $this->conexion->pdoObligatorio();
    }
}
