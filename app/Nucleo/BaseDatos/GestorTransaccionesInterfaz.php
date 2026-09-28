<?php

declare(strict_types=1);

namespace App\Nucleo\BaseDatos;

interface GestorTransaccionesInterfaz
{
    public function transaccion(callable $operacion): mixed;
}
