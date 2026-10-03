<?php

declare(strict_types=1);

namespace App\Controladores\Autenticacion;

use App\Nucleo\Http\Solicitud;
use App\Validacion\Autenticacion\SolicitudInicioSesion;
use App\Validacion\Autenticacion\SolicitudRegistro;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Autenticacion\AutenticacionServicio;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Registros\RegistradorArchivo;
use App\Soporte\Excepciones\ExcepcionAutenticacion;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Seguridad\LimitadorSolicitudes;

final class AutenticacionController
{
    public function __construct(
        private Vista $vista,
        private AutenticacionServicio $autenticacion,
        private MensajeFlashServicio $mensajes,
        private RegistradorArchivo $registro,
        private LimitadorSolicitudes $limitador,
    ) {
    }

    /**
     * Prepara el formulario para iniciar sesión.
     */
    public function formularioInicioSesion(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('publico.autenticacion.iniciar-sesion', [
            'tituloPagina' => 'Ingresar',
            'error' => $this->mensajes->extraer('error'),
        ]);
    }

    /**
     * Valida las credenciales e inicia la sesión del usuario.
     */
    public function iniciarSesion(Solicitud $solicitud): Respuesta
    {
        try {
            $credenciales = SolicitudInicioSesion::validar($solicitud);
            $usuario = $this->autenticacion->iniciarSesion($credenciales['correo'], $credenciales['contrasena']);
            $this->limitador->limpiar($solicitud->ruta() . '|' . $solicitud->direccionIp());

            return redirigir(($usuario['rol'] ?? '') === 'administrador' ? 'admin' : 'panel');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Datos inválidos.');
        } catch (ExcepcionAutenticacion $excepcion) {
            $this->mensajes->error('Credenciales incorrectas.');
            $this->registro->advertencia('Inicio de sesión fallido.', [
                'email' => (string) $solicitud->entrada('email'),
                'ip' => $solicitud->direccionIp(),
            ]);
        }

        return redirigir('login');
    }

    /**
     * Prepara el formulario para registrar una cuenta.
     */
    public function formularioRegistro(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('publico.autenticacion.registro', [
            'tituloPagina' => 'Registro',
            'error' => $this->mensajes->extraer('error'),
            'exito' => $this->mensajes->extraer('success'),
        ]);
    }

    public function registrar(Solicitud $solicitud): Respuesta
    {
        try {
            $datos = SolicitudRegistro::validar($solicitud);
            $this->autenticacion->registrar($datos);
            $this->limitador->limpiar($solicitud->ruta() . '|' . $solicitud->direccionIp());
            $this->mensajes->exito('Cuenta creada. Ya puedes iniciar sesión.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Datos inválidos.');
        } catch (\RuntimeException $excepcion) {
            $this->registro->advertencia('Registro de usuario fallido.', [
                'reason' => $excepcion->getMessage(),
                'ip' => $solicitud->direccionIp(),
            ]);
            $this->mensajes->error('No se pudo crear la cuenta. El correo puede estar registrado.');
        }

        return redirigir('register');
    }

    /**
     * Cierra la sesión activa y limpia los datos asociados.
     */
    public function cerrarSesion(Solicitud $solicitud): Respuesta
    {
        $this->autenticacion->cerrarSesion();

        return redirigir('');
    }

    /**
     * Prepara la confirmación para cerrar la sesión activa.
     */
    public function formularioCierreSesion(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('modulos.cuenta.cerrar-sesion', ['tituloPagina' => 'Cerrar sesión']);
    }
}
