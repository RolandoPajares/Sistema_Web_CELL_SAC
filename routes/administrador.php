<?php

declare(strict_types=1);

use App\Controladores\Panel\PanelAdministradorController;
use App\Controladores\Campanias\AdministradorCampaniaController;
use App\Controladores\Pedidos\AdministradorPedidoController;
use App\Controladores\Productos\AdministradorProductoController;
use App\Controladores\Usuarios\AdministradorUsuarioController;
use App\Middleware\AdministradorMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Nucleo\Enrutamiento\Enrutador;

return static function (Enrutador $enrutador): void {
    $administrador = [AdministradorMiddleware::class];
    $administradorPost = [AdministradorMiddleware::class, CsrfMiddleware::class];

    $enrutador->obtener('/admin', [PanelAdministradorController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/campaigns', [AdministradorCampaniaController::class, 'indice'], $administrador);
    $enrutador->post('/admin/campaigns', [AdministradorCampaniaController::class, 'guardar'], $administradorPost);
    $enrutador->post('/admin/campaigns/{id}/deactivate', [AdministradorCampaniaController::class, 'desactivar'], $administradorPost);
    $enrutador->obtener('/admin/products', [AdministradorProductoController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/products/{id}/edit', [AdministradorProductoController::class, 'editar'], $administrador);
    $enrutador->post('/admin/products', [AdministradorProductoController::class, 'guardar'], $administradorPost);
    $enrutador->post('/admin/products/{id}', [AdministradorProductoController::class, 'actualizar'], $administradorPost);
    $enrutador->post('/admin/products/{id}/deactivate', [AdministradorProductoController::class, 'eliminar'], $administradorPost);
    $enrutador->post('/admin/products/{id}/variants', [AdministradorProductoController::class, 'crearVariante'], $administradorPost);
    $enrutador->post('/admin/products/{id}/variants/{variant}/delete', [AdministradorProductoController::class, 'eliminarVariante'], $administradorPost);
    $enrutador->post('/admin/products/{id}/variants/{variant}/images', [AdministradorProductoController::class, 'subirImagenes'], $administradorPost);
    $enrutador->post('/admin/products/{id}/images/{image}/delete', [AdministradorProductoController::class, 'eliminarImagen'], $administradorPost);
    $enrutador->post('/admin/products/{id}/reference-image', [AdministradorProductoController::class, 'subirImagenReferencia'], $administradorPost);
    $enrutador->post('/admin/products/{id}/reference-image/delete', [AdministradorProductoController::class, 'eliminarImagenReferencia'], $administradorPost);
    $enrutador->obtener('/admin/orders', [AdministradorPedidoController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/users', [AdministradorUsuarioController::class, 'indice'], $administrador);
};
