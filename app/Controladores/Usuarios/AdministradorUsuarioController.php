<?php

declare(strict_types=1);

namespace App\Controladores\Usuarios;

use App\Nucleo\Http\Solicitud;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Servicios\Usuarios\UsuarioServicio;

final class AdministradorUsuarioController
{
    public function __construct(private Vista $vista, private UsuarioServicio $usuarios)
    {
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        return $this->vista->renderizar('roles.internos.administrador.usuarios.indice', [
            'tituloPagina' => 'Usuarios',
            'usuarios' => $this->usuarios->todos(),
        ], 'interno');
    }
}
