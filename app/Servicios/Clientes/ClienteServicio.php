<?php

declare(strict_types=1);

namespace App\Servicios\Clientes;

use App\DAO\Contratos\RepositorioClienteInterfaz;

final class ClienteServicio
{
    public function __construct(private RepositorioClienteInterfaz $clientes)
    {
    }

    public function todos(): array
    {
        return $this->clientes->todos();
    }

    public function buscar(int $id): ?array
    {
        return $this->clientes->buscar($id);
    }

    /** @return array{total:int,minoristas:int,mayoristas:int,inactivos:int} */
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

    public function guardar(array $datos, ?int $id = null): int
    {
        if ($this->clientes->existeDocumento($datos['documento'], $id)) {
            throw new \DomainException('Ya existe un cliente con ese documento.');
        }
        if ($id === null) {
            return $this->clientes->crear($datos);
        }
        if ($this->clientes->buscar($id) === null) {
            throw new \DomainException('El cliente no existe.');
        }
        $this->clientes->actualizar($id, $datos);

        return $id;
    }

    public function desactivar(int $id): void
    {
        $this->clientes->desactivar($id);
    }
}
