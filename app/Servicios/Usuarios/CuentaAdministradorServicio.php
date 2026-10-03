<?php

declare(strict_types=1);

namespace App\Servicios\Usuarios;

use App\DAO\Contratos\RepositorioCuentaAdministradorInterfaz;

final class CuentaAdministradorServicio
{
    public function __construct(private RepositorioCuentaAdministradorInterfaz $usuarios)
    {
    }

    /**
     * Obtiene los datos del perfil relacionado con la solicitud.
     *
     * @return array<string, mixed>
     */
    public function perfil(int $idUsuario): array
    {
        $usuario = $idUsuario > 0 ? $this->usuarios->buscarPorId($idUsuario) : null;
        if ($usuario === null || ($usuario['rol'] ?? '') !== 'administrador') {
            throw new \RuntimeException('No se encontró la cuenta del administrador autenticado.');
        }

        return $usuario;
    }

    /**
     * Actualiza la información relacionada con «perfil».
     * @return array<string, mixed>
     */
    public function actualizarPerfil(int $idUsuario, mixed $nombreEntrada, mixed $correoEntrada): array
    {
        $nombre = is_scalar($nombreEntrada) ? trim((string) $nombreEntrada) : '';
        $correo = is_scalar($correoEntrada) ? trim((string) $correoEntrada) : '';

        if ($nombre === '' || mb_strlen($nombre) > 120) {
            throw new \InvalidArgumentException('Ingresa un nombre de hasta 120 caracteres.');
        }
        if ($correo === '' || mb_strlen($correo) > 160 || filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Ingresa un correo electrónico válido de hasta 160 caracteres.');
        }
        $this->perfil($idUsuario);
        if ($this->usuarios->correoEnUso($correo, $idUsuario)) {
            throw new \InvalidArgumentException('Ese correo ya está en uso por otra cuenta.');
        }

        $this->usuarios->actualizarPerfil($idUsuario, $nombre, $correo);

        return $this->perfil($idUsuario);
    }

    /**
     * Valida la solicitud y actualiza la contraseña de la cuenta.
     */
    public function cambiarContrasena(int $idUsuario, mixed $actualEntrada, mixed $nuevaEntrada, mixed $confirmacionEntrada): void
    {
        $actual = is_string($actualEntrada) ? $actualEntrada : '';
        $nueva = is_string($nuevaEntrada) ? $nuevaEntrada : '';
        $confirmacion = is_string($confirmacionEntrada) ? $confirmacionEntrada : '';
        $this->perfil($idUsuario);

        $hashActual = $this->usuarios->hashContrasena($idUsuario);
        // password_verify compara la contraseña actual con el hash almacenado.
        if ($hashActual === null || !password_verify($actual, $hashActual)) {
            throw new \InvalidArgumentException('La contraseña actual no es correcta.');
        }
        if (mb_strlen($nueva) < 10 || mb_strlen($nueva) > 255) {
            throw new \InvalidArgumentException('La nueva contraseña debe tener entre 10 y 255 caracteres.');
        }
        if ($nueva !== $confirmacion) {
            throw new \InvalidArgumentException('La confirmación no coincide con la nueva contraseña.');
        }

        // password_hash genera el hash que se almacenará para la nueva contraseña.
        $this->usuarios->actualizarContrasena($idUsuario, password_hash($nueva, PASSWORD_DEFAULT));
    }
}
