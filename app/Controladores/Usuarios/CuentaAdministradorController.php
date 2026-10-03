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

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $usuarioCuenta = $this->cuenta->perfil($this->idActual());
        $nombreActual = trim((string) ($usuarioCuenta['nombre'] ?? ''));
        $iniciales = '';

        foreach (preg_split('/\s+/u', $nombreActual, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $parte) {
            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
            if (mb_strlen($iniciales) >= 2) {
                break;
            }
        }

        $rolActual = ucfirst(str_replace('_', ' ', (string) ($usuarioCuenta['rol'] ?? '')));
        $fechaRegistro = strtotime((string) ($usuarioCuenta['creado_en'] ?? ''));
        $fechaRegistroVista = $fechaRegistro !== false ? date('d/m/Y', $fechaRegistro) : '';

        return $this->vista->renderizar('roles.internos.administrador.cuenta.indice', [
            'tituloPagina' => 'Mi cuenta',
            'usuarioCuenta' => $usuarioCuenta,
            'nombreActual' => $nombreActual,
            'iniciales' => $iniciales,
            'atributoInicialesOculto' => $iniciales !== '' ? '' : 'hidden',
            'atributoIconoAvatarOculto' => $iniciales === '' ? '' : 'hidden',
            'rolActual' => $rolActual,
            'atributoRolOculto' => $rolActual !== '' ? '' : 'hidden',
            'fechaRegistroVista' => $fechaRegistroVista,
            'atributoFechaRegistroOculto' => $fechaRegistroVista !== '' ? '' : 'hidden',
            'atributoResumenCuentaOculto' => $rolActual !== '' || $fechaRegistroVista !== '' ? '' : 'hidden',
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ], 'administrador');
    }

    /**
     * Actualiza la información relacionada con «perfil».
     */
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

        return redirigir('admin/account');
    }

    /**
     * Valida la solicitud y actualiza la contraseña de la cuenta.
     */
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

        return redirigir('admin/account');
    }

    /**
     * Obtiene el identificador asociado al usuario o contexto actual.
     */
    private function idActual(): int
    {
        $idUsuario = filter_var(usuario_actual()['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idUsuario === false) {
            throw new \RuntimeException('La sesión del administrador no es válida.');
        }

        return (int) $idUsuario;
    }
}
