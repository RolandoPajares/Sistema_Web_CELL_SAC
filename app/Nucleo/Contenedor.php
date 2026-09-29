<?php

declare(strict_types=1);

namespace App\Nucleo;

use ReflectionClass;
use ReflectionNamedType;

final class Contenedor
{
    /** @var array<string, callable|string> */
    private array $enlaces = [];

    /** @var array<string, mixed> */
    private array $instancias = [];

    public function definir(string $abstracto, callable|string $concreto): void
    {
        $this->enlaces[$abstracto] = $concreto;
    }

    public function registrarInstancia(string $abstracto, mixed $instancia): void
    {
        $this->instancias[$abstracto] = $instancia;
    }

    public function obtener(string $abstracto): mixed
    {
        if (array_key_exists($abstracto, $this->instancias)) {
            return $this->instancias[$abstracto];
        }

        $concreto = $this->enlaces[$abstracto] ?? $abstracto;

        if (is_callable($concreto) && !is_string($concreto)) {
            return $this->instancias[$abstracto] = $concreto($this);
        }

        if ($concreto !== $abstracto) {
            return $this->instancias[$abstracto] = $this->obtener($concreto);
        }

        return $this->instancias[$abstracto] = $this->construir($abstracto);
    }

    private function construir(string $clase): object
    {
        if (!class_exists($clase)) {
            throw new \RuntimeException("No se puede resolver la clase {$clase}.");
        }

        $reflexion = new ReflectionClass($clase);
        $constructor = $reflexion->getConstructor();

        if ($constructor === null) {
            return new $clase();
        }

        $dependencias = [];

        foreach ($constructor->getParameters() as $parametro) {
            $tipo = $parametro->getType();

            if (!$tipo instanceof ReflectionNamedType || $tipo->isBuiltin()) {
                if ($parametro->isDefaultValueAvailable()) {
                    $dependencias[] = $parametro->getDefaultValue();
                    continue;
                }

                throw new \RuntimeException("No se puede resolver el parámetro {$parametro->getName()} de {$clase}.");
            }

            $dependencias[] = $this->obtener($tipo->getName());
        }

        return $reflexion->newInstanceArgs($dependencias);
    }
}
