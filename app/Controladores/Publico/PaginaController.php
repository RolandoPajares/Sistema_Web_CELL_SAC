<?php

declare(strict_types=1);

namespace App\Controladores\Publico;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\DAO\Contacto\ContactoDAO;

final class PaginaController
{
    public function __construct(
        private Vista $vista,
        private MensajeFlashServicio $mensajes,
        private ContactoDAO $contactos,
    ) {
    }

    public function nosotros(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('publico.nosotros.indice', ['tituloPagina' => 'Nosotros']);
    }

    public function contacto(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('publico.contacto.indice', [
            'tituloPagina' => 'Contacto',
            'exito' => $this->mensajes->extraer('success'),
            'error' => $this->mensajes->extraer('error'),
        ]);
    }

    public function enviarContacto(Solicitud $solicitud): Respuesta
    {
        $texto = static function (mixed $valor): string {
            return is_scalar($valor) ? trim((string) $valor) : '';
        };
        $nombre = $texto($solicitud->entrada('name'));
        $contacto = $texto($solicitud->entrada('contact'));
        $mensaje = $texto($solicitud->entrada('message'));

        if (
            $nombre === ''
            || $contacto === ''
            || $mensaje === ''
            || mb_strlen($nombre) > 120
            || mb_strlen($contacto) > 160
            || mb_strlen($mensaje) > 2000
        ) {
            $this->mensajes->error('Completa todos los campos de la consulta.');

            return redirect('contact');
        }

        try {
            $this->contactos->crear($nombre, $contacto, $mensaje);
            $this->mensajes->exito('Consulta enviada correctamente. Te contactaremos pronto.');
        } catch (\Throwable) {
            $this->mensajes->error('No se pudo enviar la consulta. Inténtalo nuevamente.');
        }

        return redirect('contact');
    }
}
