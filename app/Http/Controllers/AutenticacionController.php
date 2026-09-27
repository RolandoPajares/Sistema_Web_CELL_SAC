<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Solicitudes\SolicitudInicioSesion;
use App\Http\Solicitudes\SolicitudRegistro;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\AutenticacionServicio;
use App\Servicios\MensajeFlashServicio;
use App\Infraestructura\Registros\RegistradorArchivo;
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

    public function formularioInicioSesion(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('autenticacion.iniciar_sesion', [
            'tituloPagina' => 'Ingresar',
            'error' => $this->mensajes->extraer('error'),
        ]);
    }

    public function iniciarSesion(Solicitud $solicitud): Respuesta
    {
        try {
            $credenciales = SolicitudInicioSesion::validar($solicitud);
            $usuario = $this->autenticacion->iniciarSesion($credenciales['correo'], $credenciales['contrasena']);
            $this->limitador->limpiar($solicitud->ruta() . '|' . $solicitud->direccionIp());

            return redirect('panel');
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

        return redirect('login');
    }

    public function formularioRegistro(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('autenticacion.registro', [
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

        return redirect('register');
    }

    public function cerrarSesion(Solicitud $solicitud): Respuesta
    {
        $this->autenticacion->cerrarSesion();

        return redirect('');
    }

    public function formularioCierreSesion(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('autenticacion.cerrar_sesion', ['tituloPagina' => 'Cerrar sesión']);
    }
}
