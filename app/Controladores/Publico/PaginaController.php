<?php

declare(strict_types=1);

namespace App\Controladores\Publico;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Servicios\Contacto\ContactoServicio;

final class PaginaController
{
    public function __construct(
        private Vista $vista,
        private MensajeFlashServicio $mensajes,
        private ContactoServicio $contactos,
    ) {
    }

    /**
     * Prepara la página informativa de la empresa.
     */
    public function nosotros(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('publico.nosotros.indice', ['tituloPagina' => 'Nosotros']);
    }

    /**
     * Prepara la página y los datos del formulario de contacto.
     */
    public function contacto(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('publico.contacto.indice', [
            'tituloPagina' => 'Contacto',
            'exito' => $this->mensajes->extraer('success'),
            'error' => $this->mensajes->extraer('error'),
        ]);
    }

    /**
     * Valida y procesa el mensaje enviado desde el formulario de contacto.
     */
    public function enviarContacto(Solicitud $solicitud): Respuesta
    {
        try {
            $this->contactos->registrarConsulta(
                $this->textoEntrada($solicitud->entrada('name')),
                $this->textoEntrada($solicitud->entrada('contact')),
                $this->textoEntrada($solicitud->entrada('message'))
            );
            $this->mensajes->exito('Consulta enviada correctamente. Te contactaremos pronto.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (\Throwable) {
            $this->mensajes->error('No se pudo enviar la consulta. Inténtalo nuevamente.');
        }

        return redirigir('contact');
    }

    private function textoEntrada(mixed $valor): string
    {
        return is_scalar($valor) ? (string) $valor : '';
    }
}
