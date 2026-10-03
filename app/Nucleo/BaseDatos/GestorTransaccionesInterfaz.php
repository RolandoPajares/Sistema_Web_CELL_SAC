<?php

declare(strict_types=1);

namespace App\Nucleo\BaseDatos;

interface GestorTransaccionesInterfaz
{
    /**
     * Ejecuta la operación dentro de una transacción y revierte los cambios si ocurre un error.
     */
    public function transaccion(callable $operacion): mixed;
}
