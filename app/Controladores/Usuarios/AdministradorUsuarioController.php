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

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $usuarios = array_map(static function (array $usuario): array {
            $usuario['fecha_registro_vista'] = date('d/m/Y', strtotime((string) $usuario['creado_en']));

            return $usuario;
        }, $this->usuarios->todos());
        $resumen = $this->usuarios->resumen();
        $tarjetasKpi = [
            ['etiqueta' => 'Usuarios registrados', 'valor' => (string) $resumen['total'], 'detalle' => 'Cuentas en MySQL', 'icono' => 'bi-people', 'tono' => 'azul'],
            ['etiqueta' => 'Administradores', 'valor' => (string) $resumen['administradores'], 'detalle' => 'Rol oficial administrador', 'icono' => 'bi-shield-check', 'tono' => 'violeta'],
            ['etiqueta' => 'Otros roles', 'valor' => (string) $resumen['otros_roles'], 'detalle' => 'Seis roles oficiales restantes', 'icono' => 'bi-person', 'tono' => 'verde'],
            ['etiqueta' => 'Últimos accesos', 'valor' => '—', 'detalle' => 'Dato no disponible en el esquema', 'icono' => 'bi-clock', 'tono' => 'rojo'],
        ];
        $etiquetasRol = [
            'cliente_minorista' => 'Cliente minorista',
            'cliente_mayorista' => 'Cliente mayorista',
            'administrador' => 'Administrador',
            'compras_logistica' => 'Compras y logística',
            'ventas_mayoristas' => 'Ventas mayoristas',
            'ventas_minoristas' => 'Ventas minoristas',
            'marketing' => 'Marketing',
        ];

        return $this->vista->renderizar('roles.internos.administrador.usuarios.indice', [
            'tituloPagina' => 'Usuarios',
            'usuarios' => $usuarios,
            'resumen' => $resumen,
            'tarjetasKpi' => $tarjetasKpi,
            'etiquetasRol' => $etiquetasRol,
        ], 'administrador');
    }
}
