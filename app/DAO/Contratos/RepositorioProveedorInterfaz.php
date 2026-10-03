<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioProveedorInterfaz
{
    /**
     * Devuelve todos los proveedores registrados.
     * @return array<int, array<string, mixed>>
     */
    public function todos(): array;

    /**
     * Busca un proveedor por su identificador.
     * @return array<string, mixed>|null
     */
    public function buscar(int $idProveedor): ?array;

    /**
     * Comprueba si otro proveedor ya utiliza el RUC indicado.
     */
    public function existeRuc(string $ruc, ?int $idExcluido = null): bool;

    /**
     * Crea un proveedor con los datos validados por el servicio.
     * @param array<string, string> $datos
     */
    public function crear(array $datos): int;

    /**
     * Actualiza los datos del proveedor indicado.
     * @param array<string, string> $datos
     */
    public function actualizar(int $idProveedor, array $datos): void;

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idProveedor): void;
}
