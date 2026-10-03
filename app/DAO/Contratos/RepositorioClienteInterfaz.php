<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioClienteInterfaz
{
    /**
     * Devuelve todos los clientes registrados.
     * @return array<int, array<string, mixed>>
     */
    public function todos(): array;

    /**
     * Busca un cliente por su identificador.
     * @return array<string, mixed>|null
     */
    public function buscar(int $idCliente): ?array;

    /**
     * Comprueba si otro cliente ya utiliza el documento indicado.
     */
    public function existeDocumento(string $documento, ?int $idExcluido = null): bool;

    /**
     * Crea un cliente con los datos validados por el servicio.
     * @param array<string, string> $datos
     */
    public function crear(array $datos): int;

    /**
     * Actualiza los datos del cliente indicado.
     * @param array<string, string> $datos
     */
    public function actualizar(int $idCliente, array $datos, ?string $segmentoPermitido = null): void;

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idCliente, ?string $segmentoPermitido = null): void;
}
