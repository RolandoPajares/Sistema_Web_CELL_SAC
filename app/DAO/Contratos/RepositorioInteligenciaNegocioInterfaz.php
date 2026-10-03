<?php

declare(strict_types=1);

namespace App\DAO\Contratos;

interface RepositorioInteligenciaNegocioInterfaz
{
    /** @return array{ingresos:float,destacados:array<int,array<string,mixed>>,existencias_bajas:array<int,array<string,mixed>>,diario:array<int,array<string,mixed>>} */
    public function resumenActual(): array;

    /** @return array<int, array{periodo:string,total:float,pedidos:int}> */
    public function ventasMensuales(int $meses = 12): array;

    /** @return array<int, array<string, mixed>> */
    public function pedidosRecientes(int $limite = 6): array;

    /** @return array{ventas:float,pedidos:int,productos_vendidos:int,clientes:int} */
    public function resumenPeriodo(string $desde, string $hasta): array;

    /** @return array<int, array{periodo:string,total:float,pedidos:int}> */
    public function ventasDiarias(string $desde, string $hasta): array;

    /** @return array<int, array<string, mixed>> */
    public function detalleReporte(string $tipo, string $desde, string $hasta): array;
}
