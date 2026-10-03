<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioCategoriaInterfaz
{
    /**
     * Devuelve todos los registros de la entidad administrada por el repositorio.
     *
     * @return array<int, array<string, mixed>>
     */
    public function todas(): array;

    /**
     * Devuelve únicamente los registros activos de la entidad.
     *
     * @return array<int, array<string, mixed>>
     */
    public function activas(): array;

    /**
     * Busca una categoría por su identificador.
     * @return array<string, mixed>|null
     */
    public function buscar(int $idCategoria): ?array;

    /**
     * Busca el registro que coincide con el identificador y verifica que siga activo.
     *
     * @return array<string, mixed>|null
     */
    public function buscarActiva(int $idCategoria): ?array;

    /**
     * Busca el registro usando el criterio «nombre».
     * @return array<string, mixed>|null
     */
    public function buscarPorNombre(string $nombre): ?array;

    /**
     * Comprueba si otra categoría ya utiliza el nombre indicado.
     */
    public function existeNombre(string $nombre, ?int $idExcluido = null): bool;

    /**
     * Crea una categoría con los datos validados por el servicio.
     * @param array{nombre:string,descripcion:string} $datos
     */
    public function crear(array $datos): int;

    /**
     * Actualiza los datos de la categoría indicada.
     * @param array{nombre:string,descripcion:string} $datos
     */
    public function actualizar(int $idCategoria, array $datos): void;

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idCategoria): void;
}
