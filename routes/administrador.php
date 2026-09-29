<?php

declare(strict_types=1);

use App\Controladores\Panel\PanelAdministradorController;
use App\Controladores\Panel\AdministradorFuturoController;
use App\Controladores\Campanias\AdministradorCampaniaController;
use App\Controladores\Pedidos\AdministradorPedidoController;
use App\Controladores\Productos\AdministradorProductoController;
use App\Controladores\Usuarios\AdministradorUsuarioController;
use App\Controladores\Usuarios\CuentaAdministradorController;
use App\Controladores\Categorias\CategoriaController;
use App\Controladores\Proveedores\ProveedorController;
use App\Controladores\Clientes\ClienteController;
use App\Controladores\Inventario\InventarioController;
use App\Middleware\AdministradorMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Nucleo\Enrutamiento\Enrutador;

return static function (Enrutador $enrutador): void {
    $administrador = [AdministradorMiddleware::class];
    $administradorPost = [AdministradorMiddleware::class, CsrfMiddleware::class];

    $enrutador->obtener('/admin', [PanelAdministradorController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/account', [CuentaAdministradorController::class, 'indice'], $administrador);
    $enrutador->post('/admin/account/profile', [CuentaAdministradorController::class, 'actualizarPerfil'], $administradorPost);
    $enrutador->post('/admin/account/password', [CuentaAdministradorController::class, 'cambiarContrasena'], $administradorPost);
    $enrutador->post('/admin/assistant', [PanelAdministradorController::class, 'respuestaAsistente'], $administradorPost);
    $enrutador->obtener('/admin/campaigns', [AdministradorCampaniaController::class, 'indice'], $administrador);
    $enrutador->post('/admin/campaigns', [AdministradorCampaniaController::class, 'guardar'], $administradorPost);
    $enrutador->post('/admin/campaigns/{id}/deactivate', [AdministradorCampaniaController::class, 'desactivar'], $administradorPost);
    $enrutador->obtener('/admin/products', [AdministradorProductoController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/products/{id}/edit', [AdministradorProductoController::class, 'editar'], $administrador);
    $enrutador->post('/admin/products', [AdministradorProductoController::class, 'guardar'], $administradorPost);
    $enrutador->post('/admin/products/{id}', [AdministradorProductoController::class, 'actualizar'], $administradorPost);
    $enrutador->post('/admin/products/{id}/deactivate', [AdministradorProductoController::class, 'eliminar'], $administradorPost);
    $enrutador->obtener('/admin/orders', [AdministradorPedidoController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/orders/{id}', [AdministradorPedidoController::class, 'detalle'], $administrador);
    $enrutador->post('/admin/orders/{id}/status', [AdministradorPedidoController::class, 'actualizarEstado'], $administradorPost);
    $enrutador->obtener('/admin/users', [AdministradorUsuarioController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/categories', [CategoriaController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/categories/{id}/edit', [CategoriaController::class, 'editar'], $administrador);
    $enrutador->post('/admin/categories', [CategoriaController::class, 'guardar'], $administradorPost);
    $enrutador->post('/admin/categories/{id}', [CategoriaController::class, 'actualizar'], $administradorPost);
    $enrutador->post('/admin/categories/{id}/deactivate', [CategoriaController::class, 'desactivar'], $administradorPost);
    $enrutador->obtener('/admin/suppliers', [ProveedorController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/suppliers/{id}/edit', [ProveedorController::class, 'editar'], $administrador);
    $enrutador->post('/admin/suppliers', [ProveedorController::class, 'guardar'], $administradorPost);
    $enrutador->post('/admin/suppliers/{id}', [ProveedorController::class, 'actualizar'], $administradorPost);
    $enrutador->post('/admin/suppliers/{id}/deactivate', [ProveedorController::class, 'desactivar'], $administradorPost);
    $enrutador->obtener('/admin/customers', [ClienteController::class, 'indice'], $administrador);
    $enrutador->obtener('/admin/customers/{id}/edit', [ClienteController::class, 'editar'], $administrador);
    $enrutador->post('/admin/customers', [ClienteController::class, 'guardar'], $administradorPost);
    $enrutador->post('/admin/customers/{id}', [ClienteController::class, 'actualizar'], $administradorPost);
    $enrutador->post('/admin/customers/{id}/deactivate', [ClienteController::class, 'desactivar'], $administradorPost);
    $enrutador->obtener('/admin/inventory', [InventarioController::class, 'indice'], $administrador);
    $enrutador->post('/admin/inventory', [InventarioController::class, 'guardar'], $administradorPost);
    $enrutador->obtener('/admin/reports', [AdministradorFuturoController::class, 'reportes'], $administrador);
    $enrutador->obtener('/admin/audit', [AdministradorFuturoController::class, 'auditoria'], $administrador);
};
