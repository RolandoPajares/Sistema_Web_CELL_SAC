<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

use App\DTO\Productos\FiltroProducto;

interface RepositorioProductoInterfaz
{
    /**
     * Indica si el repositorio puede consultar la base de datos.
     */
    public function estaDisponible(): bool;

    /**
     * Devuelve todos los registros activos del repositorio.
     * @return array<int, array<string, mixed>>
     */
    public function todosActivos(): array;

    /**
     * Devuelve productos activos con descuentos reales, ordenados según ventas y disponibilidad.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ofertasPopulares(): array;

    /**
     * Devuelve los productos de la página solicitada junto con los datos de paginación.
     *
     * @return array{productos:array<int,array<string,mixed>>,total:int,pagina:int,por_pagina:int,ultima_pagina:int}
     */
    public function paginar(FiltroProducto $filtro): array;

    /**
     * Devuelve los registros disponibles para el panel de administración.
     * @return array<int, array<string, mixed>>
     */
    public function todosParaAdministrador(): array;

    /**
     * Busca el registro activo que coincide con el identificador o filtro indicado.
     * @return array<string, mixed>|null
     */
    public function buscarActivo(int $idProducto): ?array;

    /**
     * Devuelve las imágenes vinculadas al producto en el orden definido para la galería.
     *
     * @return array<int, string>
     */
    public function imagenesRelacionadas(int $idProducto): array;

    /**
     * Recupera las características asociadas al producto solicitado.
     *
     * @return array<int, array{nombre:string,valor:string}>
     */
    public function caracteristicas(int $idProducto): array;

    /**
     * Busca un producto por su identificador para mostrarlo en la administración.
     * @return array<string, mixed>|null
     */
    public function buscarParaAdministrador(int $idProducto): ?array;

    /**
     * Busca un producto activo para modificarlo.
     * @return array<string, mixed>|null
     */
    public function buscarActivoParaActualizar(int $idProducto): ?array;

    /**
     * Comprueba si ya existe un producto con esa marca y ese nombre, excepto el identificador indicado.
     */
    public function existeConMarcaYNombre(string $marca, string $nombre, ?int $idExcluido = null): bool;

    /**
     * Crea un producto con los datos validados por el servicio.
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): int;

    /**
     * Actualiza los datos del producto indicado.
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $idProducto, array $datos): void;

    /**
     * Elimina físicamente el producto solo cuando no tiene pedidos ni movimientos asociados.
     */
    public function eliminarFisicamente(int $idProducto): bool;

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idProducto): void;

    /**
     * Descuenta del inventario la cantidad indicada para el producto correspondiente.
     */
    public function reducirStock(int $idProducto, int $cantidad): void;

    /**
     * Cuenta los elementos relacionados con «activos».
     */
    public function contarActivos(): int;

    /**
     * Suma las existencias registradas para los productos activos.
     */
    public function stockTotal(): int;
}
