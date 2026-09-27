<?php

declare(strict_types=1);

namespace App\Contratos;

interface GestorTransaccionesInterfaz
{
    public function transaccion(callable $operacion): mixed;
}
