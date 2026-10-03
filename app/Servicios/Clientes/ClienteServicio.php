<?php

declare(strict_types=1);

namespace App\Servicios\Clientes;

use App\DAO\Contratos\RepositorioClienteInterfaz;

final class ClienteServicio
{
    public function __construct(private RepositorioClienteInterfaz $clientes)
    {
    }

    /**
     * Devuelve los registros disponibles que cumplen los filtros actuales.
     */
    public function todos(): array
    {
        return $this->clientes->todos();
    }

    public function buscar(int $idCliente): ?array
    {
        return $this->clientes->buscar($idCliente);
    }

    /**
     * Calcula un resumen consolidado de la información solicitada.
     *
     * @return array{total:int,minoristas:int,mayoristas:int,inactivos:int}
     */
    public function resumen(): array
    {
        $clientes = $this->todos();

        return [
            'total' => count($clientes),
            'minoristas' => count(array_filter($clientes, static fn (array $cliente): bool => $cliente['tipo'] === 'minorista' && (int) $cliente['activo'] === 1)),
            'mayoristas' => count(array_filter($clientes, static fn (array $cliente): bool => $cliente['tipo'] === 'mayorista' && (int) $cliente['activo'] === 1)),
            'inactivos' => count(array_filter($clientes, static fn (array $cliente): bool => (int) $cliente['activo'] === 0)),
        ];
    }

    public function guardar(array $datos, ?int $idCliente = null): int
    {
        return $this->guardarConSegmento($datos, $idCliente, null, false);
    }

    /**
     * Guarda un cliente del segmento autorizado para el módulo de ventas.
     *
     * @param array<string, string> $datos
     */
    public function guardarParaSegmento(array $datos, string $segmento, ?int $idCliente = null): int
    {
        $this->validarSegmento($segmento);
        if ($idCliente !== null) {
            $cliente = $this->clientes->buscar($idCliente);
            if ($cliente === null) {
                throw new \DomainException('El cliente no existe.');
            }
            if (($cliente['tipo'] ?? null) !== $segmento) {
                throw new \DomainException('El cliente no pertenece al segmento autorizado.');
            }
        }

        $datos['tipo'] = $segmento;

        return $this->guardarConSegmento($datos, $idCliente, $segmento, true);
    }

    /**
     * Persiste los datos y aplica el segmento cuando la operación viene del panel de ventas.
     *
     * @param array<string, string> $datos
     */
    private function guardarConSegmento(
        array $datos,
        ?int $idCliente,
        ?string $segmento,
        bool $clienteComprobado
    ): int {
        if ($this->clientes->existeDocumento($datos['documento'], $idCliente)) {
            throw new \DomainException('Ya existe un cliente con ese documento.');
        }
        if ($idCliente === null) {
            return $this->clientes->crear($datos);
        }
        if (!$clienteComprobado && $this->clientes->buscar($idCliente) === null) {
            throw new \DomainException('El cliente no existe.');
        }
        $this->clientes->actualizar($idCliente, $datos, $segmento);

        return $idCliente;
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(int $idCliente): void
    {
        $this->clientes->desactivar($idCliente);
    }

    public function desactivarParaSegmento(int $idCliente, string $segmento): void
    {
        $this->validarSegmento($segmento);
        $cliente = $this->clientes->buscar($idCliente);
        if ($cliente === null) {
            throw new \DomainException('El cliente no existe.');
        }
        if (($cliente['tipo'] ?? null) !== $segmento) {
            throw new \DomainException('El cliente no pertenece al segmento autorizado.');
        }

        $this->clientes->desactivar($idCliente, $segmento);
    }

    private function validarSegmento(string $segmento): void
    {
        if (!in_array($segmento, ['minorista', 'mayorista'], true)) {
            throw new \DomainException('El segmento de cliente no es válido.');
        }
    }
}
