<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Repositorios\Contratos\RepositorioUsuarioInterfaz;
use App\Soporte\Excepciones\ExcepcionAutenticacion;
use App\Soporte\Sesion\GestorSesion;

final class AutenticacionServicio
{
    public function __construct(private RepositorioUsuarioInterfaz $usuarios, private GestorSesion $sesion)
    {
    }

    /** @return array<string, mixed> */
    public function iniciarSesion(string $correo, string $contrasena): array
    {
        if (!$this->usuarios->estaDisponible()) {
            throw new ExcepcionAutenticacion('Base de datos no disponible. Ejecuta las migraciones CLI.');
        }

        $usuario = $this->usuarios->buscarPorCorreo($correo);

        if (!$usuario || !password_verify($contrasena, (string) $usuario['contrasena'])) {
            throw new ExcepcionAutenticacion('Credenciales incorrectas.');
        }

        $this->sesion->regenerar();
        $usuarioSesion = [
            'id' => (int) $usuario['id'],
            'nombre' => (string) $usuario['nombre'],
            'correo' => (string) $usuario['correo'],
            'rol' => (string) $usuario['rol'],
        ];
        $this->sesion->guardar('user', $usuarioSesion);

        return $usuarioSesion;
    }

    /** @param array{nombre:string,correo:string,contrasena:string} $datos */
    public function registrar(array $datos): void
    {
        if (!$this->usuarios->estaDisponible()) {
            throw new \RuntimeException('La base de datos no está disponible.');
        }

        if ($this->usuarios->buscarPorCorreo($datos['correo'])) {
            throw new \RuntimeException('Ese correo ya está registrado.');
        }

        $this->usuarios->crear([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'contrasena' => password_hash($datos['contrasena'], PASSWORD_DEFAULT),
            'rol' => 'cliente_minorista',
        ]);
    }

    public function cerrarSesion(): void
    {
        $this->sesion->destruir();
    }
}
