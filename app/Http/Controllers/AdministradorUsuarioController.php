<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Solicitud;
use App\Http\Respuestas\Respuesta;
use App\Http\Respuestas\Vista;
use App\Servicios\UsuarioServicio;

final class AdministradorUsuarioController
{
    public function __construct(private Vista $vista, private UsuarioServicio $usuarios)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('administrador.usuarios', [
            'tituloPagina' => 'Usuarios',
            'usuarios' => $this->usuarios->todos(),
        ], 'administrador');
    }
}
