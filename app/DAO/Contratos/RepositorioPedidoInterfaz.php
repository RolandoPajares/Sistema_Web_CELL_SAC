<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioPedidoInterfaz
{
    /**
     * Indica si el repositorio puede consultar la base de datos.
     */
    public function estaDisponible(): bool;

    /**
     * Crea o guarda la información relacionada con «pedido pendiente».
     */
    public function crearPedidoPendiente(int $idUsuario, float $total): int;

    /**
     * Crea o guarda la información relacionada con «detalle».
     */
    public function agregarDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): void;

    /**
     * Devuelve la lista de registros relacionados con «con usuarios».
     * @return array<int, array<string, mixed>>
     */
    public function todosConUsuarios(): array;

    /**
     * Cuenta los elementos que cumplen las condiciones recibidas.
     */
    public function contar(): int;

    /**
     * Obtiene el pedido por su identificador e incluye sus líneas de detalle.
     * @return array<string, mixed>|null
     */
    public function buscarConDetalle(int $idPedido): ?array;

    /**
     * Actualiza la información relacionada con «estado».
     */
    public function actualizarEstado(int $idPedido, string $estado): void;

    /** Actualiza el estado solo si el pedido pertenece al tipo de cuenta del módulo operativo. */
    public function actualizarEstadoParaRolCliente(int $idPedido, string $estado, string $rolCliente): void;

    /**
     * Cuenta los elementos relacionados con «por estado».
     * @return array<string, int>
     */
    public function contarPorEstado(): array;

    /**
     * Cuenta los elementos relacionados con «ventas registradas».
     */
    public function contarVentasRegistradas(): int;

    /**
     * Calcula las ventas y pedidos acumulados durante el periodo indicado.
     */
    public function totalVentasPeriodo(): float;
}
