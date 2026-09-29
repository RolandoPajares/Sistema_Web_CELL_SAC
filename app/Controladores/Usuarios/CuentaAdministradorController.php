<?php

declare(strict_types=1);

namespace App\Controladores\Usuarios;

use App\Nucleo\Http\Respuesta;
use App\Nucleo\Http\Solicitud;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Usuarios\CuentaAdministradorServicio;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
use App\Soporte\Sesion\GestorSesion;
use Throwable;

final class CuentaAdministradorController
{
    public function __construct(
        private Vista $vista,
        private CuentaAdministradorServicio $cuenta,
        private MensajeFlashServicio $mensajes,
        private GestorSesion $sesion,
        private RegistradorArchivo $registro,
    ) {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.cuenta.indice', [
            'tituloPagina' => 'Mi cuenta',
            'usuarioCuenta' => $this->cuenta->perfil($this->idActual()),
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    public function actualizarPerfil(Solicitud $solicitud): Respuesta
    {
        try {
            $usuario = $this->cuenta->actualizarPerfil(
                $this->idActual(),
                $solicitud->entrada('nombre'),
                $solicitud->entrada('correo')
            );
            $this->sesion->guardar('user', [
                'id' => (int) $usuario['id'],
                'nombre' => (string) $usuario['nombre'],
                'correo' => (string) $usuario['correo'],
                'rol' => (string) $usuario['rol'],
            ]);
            $this->mensajes->exito('La información de tu cuenta se actualizó correctamente.');
        } catch (\InvalidArgumentException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('No se pudo actualizar la cuenta del administrador.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo actualizar la información de la cuenta.');
        }

        return redirect('admin/account');
    }

    public function cambiarContrasena(Solicitud $solicitud): Respuesta
    {
        try {
            $this->cuenta->cambiarContrasena(
                $this->idActual(),
                $solicitud->entrada('contrasena_actual'),
                $solicitud->entrada('contrasena_nueva'),
                $solicitud->entrada('contrasena_confirmacion')
            );
            $this->sesion->regenerar();
            $this->mensajes->exito('La contraseña de acceso se actualizó correctamente.');
        } catch (\InvalidArgumentException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (Throwable $excepcion) {
            $this->registro->error('No se pudo cambiar la contraseña del administrador.', ['message' => $excepcion->getMessage()]);
            $this->mensajes->error('No se pudo actualizar la contraseña.');
        }

        return redirect('admin/account');
    }

    private function idActual(): int
    {
        $id = filter_var(current_user()['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new \RuntimeException('La sesión del administrador no es válida.');
        }

        return (int) $id;
    }
}
